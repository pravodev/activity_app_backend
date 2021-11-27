<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\ActivityRepositoryContract;
use App\Models\Activity;
use DB;

class ActivityRepositoryImplementation extends BaseRepositoryImplementation implements ActivityRepositoryContract  {
    public function __construct(Activity $builder)
    {
        $this->builder = $builder;
    }

    public function allOrder($orderBy, $orderType)
    {
        $data = $this->builder->orderBy($orderBy, $orderType)->get();

        $data = $data->map(function($activity){
            $array = $activity->toArray();
            if($activity->type == 'badhabit') {
                $score = $activity->histories()->sum('value');

                $array['is_red'] = $score > $activity->target;
            };

            return $array;
        });

        return $data;
    }
    
    public function search($fields) {
        $result = \App\Models\Activity::orWhere(function($query) use ($fields) {
            foreach ($fields as $key => $value) {
                $query->orWhere($key, 'like', "%" . $value . "%");
            }
        })->get();
        return $result;
    }

    public function getUsingMonthYear($month, $year) {
        // return Activity::with(['histories' => function($query) use ($month, $year) {
        //     $query->whereYear("date", $year)->whereMonth("date", $month);
        // }])->get();

        $get_score_query = "
        CASE
            WHEN activities.type IN('count') THEN COUNT(histories.id)
            WHEN activities.type IN('value', 'badhabit') THEN SUM(histories.value)
        END as score
        ";
        
        $join_histories = function($join) use($month, $year) {
            $join->on('histories.activity_id', 'activities.id')
                ->whereYear("histories.date", $year)
                ->whereMonth("histories.date", $month);
        };
        
        $activities = Activity::with(['histories' => function($query) use ($month, $year) {
                $query->whereYear("date", $year)->whereMonth("date", $month);
            }])
            ->leftJoin('histories', $join_histories)
            ->select(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value'))
            ->addSelect(DB::raw($get_score_query))
            ->addSelect(DB::raw('COUNT(histories.id) as count'))
            ->groupBy('histories.activity_id')
            ->groupBy(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value'))
            ->orderByDesc(DB::raw('MAX(histories.created_at)'))
            ->get()
            ;

        $activities = $activities->map(function($activity){
            $left = $activity->target - $activity->score;
            $is_red_count = $activity->count < $activity->target;
            
            $data = [
                'id' => $activity->id,
                'type' => $activity->type,
                'title' => $activity->title,
                'target' => $activity->target,
                'score' => $activity->score ?? 0,
                'count' => $activity->count,
            ];
            
            if($activity->type == 'speedrun') {
                $histories = $activity->histories;
                if(count($histories)) {
                    $timestamps = $histories->map(function($history){
                        return [
                            'timestamp' => Activity::convertSpeedrunValueToTimestamp($history->value),
                            'value' => $history->value
                        ];
                    });
                    $avg = $timestamps->avg('timestamp');
                    $score = Activity::convertTimestampToSpeedrunValue($avg);


                    $speedtarget = $activity->value;
                    $speedtarget_timestamp = Activity::convertSpeedrunValueToTimestamp($speedtarget);

                    $is_red = $avg > $speedtarget_timestamp;
                    $fastest_time = $timestamps->min('timestamp');

                    $best_time = $timestamps->filter(function($t) use($fastest_time) {
                        return $t['timestamp'] == $fastest_time;
                    })->first();
                    $data['best_time'] = $best_time['value'];
                } else {
                    $score = '0h 0m 0s';
                    $is_red = false;
                    $data['best_time'] = $score . ' 0ms';

                }
                $data['title'] .= " ({$activity->value})";
                $data['score'] = $score;
                $left = $activity->target - $activity->count;

            } else if($activity->type == 'badhabit') {
                $is_red = $activity->score > $activity->target;
            } else {
                $is_red = $activity->score < $activity->target;
            }

            $data['left'] = $left < 0 ? 0 : $left;
            $data['is_red'] = $is_red;
            $data['is_red_count'] = $is_red_count;
            $data['histories'] = $activity->histories;

            return $data;
        });

        return $activities;
    }

    public function changePosition($new_position) {
        foreach($new_position as $position => $id) {
            $activity = Activity::find($id);
            $activity->position = $position;
            $activity->save();
        }
    }

    public function delete($id)
    {
        $activity = $this->builder->find($id);

        if($activity) {
            if($activity->histories()->count()) {
                return $activity->delete();
            } else {
                return $activity->forceDelete();
            }
            
        }
    }
}