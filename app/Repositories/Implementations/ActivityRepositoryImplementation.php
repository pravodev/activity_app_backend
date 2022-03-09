<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\ActivityRepositoryContract;
use App\Models\Activity;
use App\Models\History;
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

            if($activity->type == 'speedrun') {
                $array['value'] = $this->removeSpeedrunZero($array['value']);
            }

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
            ->select(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value, activities.criteria'))
            ->addSelect(DB::raw($get_score_query))
            ->addSelect(DB::raw('COUNT(histories.id) as count'))
            ->groupBy('histories.activity_id')
            ->groupBy(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value'))
            ->orderByDesc(DB::raw('MAX(histories.created_at)'))
            ->whereNull('histories.deleted_at')
            // ->where('type', 'speedrun')
            ->get()
            ;

        $activities = $activities->map(function($activity){
            $left = $activity->target - $activity->score;
            $is_red_count = $activity->score < $activity->target;

            $data = [
                'id' => $activity->id,
                'type' => $activity->type,
                'title' => $activity->title,
                'target' => $activity->target,
                'score' => $activity->score ?? 0,
                'count' => $activity->count,
                'histories' => $activity->histories,
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
                    $score = $this->removeSpeedrunZero($score);
                    $speedtarget = $activity->value;
                    $speedtarget_timestamp = Activity::convertSpeedrunValueToTimestamp($speedtarget);

                    $is_red = $activity->criteria == 'shorter' ? $avg > $speedtarget_timestamp : $avg < $speedtarget_timestamp;
                    $criteria_time = $activity->criteria == 'shorter' ? $timestamps->min('timestamp') : $timestamps->max('timestamp');

                    $best_time = $timestamps->filter(function($t) use($criteria_time) {
                        return $t['timestamp'] == $criteria_time;
                    })->first();
                    $data['best_time'] = $this->removeSpeedrunZero($best_time['value']);

                    $data['histories'] = $data['histories']->map(function($history) {
                        $history['value'] = $this->removeSpeedrunZero($history['value']);
                        return $history;
                    });
                } else {
                    $score = '0h 0m 0s';
                    $is_red = false;
                    // $data['best_time'] = $score . ' 0ms';
                    $data['best_time'] = '0m';
                    $data['best_record_alltime'] = '0m';

                }

                $timestamps_alltime = History::where('activity_id', $activity->id)->get()->map(function($history){
                    return [
                        'timestamp' => Activity::convertSpeedrunValueToTimestamp($history->value),
                        'value' => $history->value
                    ];
                });
                $criteria_time = $activity->criteria == 'shorter' ? $timestamps_alltime->min('timestamp') : $timestamps_alltime->max('timestamp');
                $best_record_alltime = $timestamps_alltime->filter(function($t) use($criteria_time) {
                    return $t['timestamp'] == $criteria_time;
                })->first();
                if($best_record_alltime) {
                    $data['best_record_alltime'] = $this->removeSpeedrunZero($best_record_alltime['value']);
                }

                // $data['title'] .= " ({$activity->value})";
                $data['value'] = $this->removeSpeedrunZero($activity->value);
                $data['score'] = $score;
                $left = $activity->target - $activity->count;
                $is_red_count = $activity->count < $activity->target;
            } else if($activity->type == 'badhabit') {
                $is_red = $activity->score > $activity->target;
            } else {
                $is_red = $activity->score < $activity->target;
            }

            $data['left'] = $left < 0 ? 0 : $left;
            $data['is_red'] = $is_red;
            $data['is_red_count'] = $is_red_count;

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

    public function removeSpeedrunZero($timetarget)
    {
        $split = explode(' ', $timetarget);
        $prev = null;
        $text = "";
        foreach($split as $time) {
            preg_match_all('!\d+!', $time, $matches);
            $number = (int) $matches[0][0] ?? null;
            $letter = preg_replace("/[^a-zA-Z]+/", "", $time);

            // if()
            if($number > 0) {
                $text .= "{$number}{$letter} ";
            }
        }

        return trim($text);
    }
}
