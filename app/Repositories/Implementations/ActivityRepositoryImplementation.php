<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\ActivityRepositoryContract;
use App\Models\Activity;
use App\Models\History;
use App\Models\PointTransaction;
use DB;
use App\Models\User;
use App\Models\PointFocus;

class ActivityRepositoryImplementation extends BaseRepositoryImplementation implements ActivityRepositoryContract  {
    public function __construct(Activity $builder)
    {
        $this->builder = $builder;
    }

    public function allOrder($orderBy, $orderType)
    {
        $month = now()->month;
        $year = now()->year;
        $data = $this->getUsingMonthYear($month, $year);

        $data->transform(function($activity){
            $activity['score_target'] = $activity['score'] . " / " . $activity['target'];

            if($activity['type'] == 'speedrun') {
                $activity['score_target'] = $activity['score'] . " / " . $activity['count'];
            }

            unset($activity['histories']);
            return $activity;
        })->sortBy('id');

        if($orderBy && $orderType) {
            $data = $data->sortBy([
                [$orderBy, $orderType],
                ['id', 'asc'],
            ]);
        }

        return $data->values();

        // $data = $this->builder->orderBy($orderBy, $orderType)->get();

        // $data = $data->map(function($activity){
        //     $array = $activity->toArray();
        //     if($activity->type == 'badhabit') {
        //         $score = $activity->histories()->sum('value');

        //         $array['is_red'] = $score > $activity->target;
        //     };

        //     if($activity->type == 'speedrun') {
        //         $array['value'] = $this->removeSpeedrunZero($array['value']);
        //     }

        //     return $array;
        // });

        // return $data;
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
        $get_score_query = "
        CASE
            WHEN activities.type IN('count') THEN COUNT(histories.id)
            WHEN activities.type IN('value', 'badhabit') THEN SUM(histories.value)
        END as score
        ";

        $join_histories = function($join) use($month, $year) {
            $join->on('histories.activity_id', 'activities.id')
                ->whereYear("histories.date", $year)
                ->whereMonth("histories.date", $month)
                ->whereNull('histories.deleted_at');
        };

        $activities = Activity::with(['histories' => function($query) use ($month, $year) {
                $query->whereYear("date", $year)->whereMonth("date", $month);
            }])
            ->leftJoin('histories', $join_histories)
            ->select(DB::raw('activities.*'))
            ->addSelect(DB::raw($get_score_query))
            ->addSelect(DB::raw('COUNT(histories.id) as count'))
            ->groupBy('histories.activity_id')
            ->groupBy(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value'))
            ->orderByDesc(DB::raw('MAX(histories.created_at)'));

        if($student_id = request()->query('student_id')) {
            $activities = $activities->withoutGlobalScope('byuser')->where('activities.user_id', $student_id);
        }
            // ->where('type', 'speedrun')
        $activities = $activities->get();
        $user_id = request()->query('student_id') ?: auth()->id();

        $list_points = [];
        if(get_settings('point_system', $user_id)) {
            $list_points = PointTransaction::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->selectRaw('activity_id, SUM(value) as total')
                ->groupBy('activity_id')
                ->withoutGlobalScope('byuser')
                ->where('user_id', $user_id)
                ->get();
        }

        $activities = $activities->map(function($activity) use($list_points, $month, $year, $user_id) {
            $left = $activity->target - $activity->score;
            $is_red_count = $activity->score < $activity->target;

            $data = array_merge($activity->toArray(), [
                'id' => $activity->id,
                'type' => $activity->type,
                'title' => $activity->title,
                'target' => $activity->target,
                'score' => $activity->score ?? 0,
                'count' => $activity->count,
                'point' => null,
                'histories' => $activity->histories,
                'position' => rtrim(floatval($activity->position), 0),
            ]);

            if(get_settings('point_system', $user_id)) {
                $point = $list_points->where('activity_id', $activity->id)->pluck('total')->first();
                $data['point'] = is_null($point) ? PointTransaction::calculate($activity->id, $month, $year, $user_id)  : $point;
            }

            if($activity->type == 'speedrun') {
                $histories = $activity->histories;
                if(count($histories)) {
                    $timestamps = $histories->map(function($history){
                        return [
                            'timestamp' => Activity::convertSpeedrunValueToTimestamp($history->value),
                            'millisecond' => Activity::convertSpeedrunValueToMillisecond($history->value),
                            'value' => $history->value
                        ];
                    });
                    $avg = $timestamps->avg('millisecond');
                    $score = Activity::convertMillisecondToSpeedrunValue($avg);
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
        foreach($new_position as $data) {
            $activity = Activity::find($data['activity_id']);
            $activity->position = $data['position'];
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

    public function import($parent_id)
    {
        $activities = $this->builder->where('user_id', $parent_id)->get();

        $student_ids = User::select('id')->where('parent_id', $parent_id)->pluck('id');

        foreach($activities as $activity) {
            foreach($student_ids as $student_id) {
                $duplicated = $activity->replicate();
                $duplicated->user_id = $student_id;
                $duplicated->created_at = now();
                $duplicated->updated_at = now();
                $duplicated->save();
            }
        }

        return $activities->count();
    }

    public function getDailyUsingMonthYear($date)
    {
        $get_score_query = "
        CASE
        WHEN activities.type IN('value', 'badhabit') THEN SUM(histories.value)
        ELSE COUNT(histories.id)
        END as score
        ";

        $join_histories = function($join) use($date) {
            $join->on('histories.activity_id', 'activities.id')->whereDate('date', $date);
        };

        $activities = Activity::with(['histories' => function($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->whereHas('histories', function($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->leftJoin('histories', $join_histories)
            ->select(DB::raw('activities.*'))
            ->addSelect(DB::raw($get_score_query))
            ->addSelect(DB::raw('COUNT(histories.id) as count'))
            ->groupBy('histories.activity_id')
            ->groupBy(DB::raw('activities.id, activities.type, activities.title, activities.target, activities.value'))
            ->orderByDesc(DB::raw('MAX(histories.created_at)'))
            ->whereNull('histories.deleted_at');

        if($student_id = request()->query('student_id')) {
            $activities = $activities->withoutGlobalScope('byuser')->where('activities.user_id', $student_id);
        }

        $activities = $activities->get();
        $user_id = request()->query('student_id') ?: auth()->id();

        $activities = $activities->map(function($activity) {
            $histories = $activity->histories;

            $data = [
                'id' => $activity->id,
                'type' => $activity->type,
                'title' => $activity->title,
                'score' => $activity->score,
                'value' => $activity->value,
                'percent' => round((int) $activity->score / (int) $activity->target * 100),
                'stopwatch_value' => [],
            ];

            if($activity->type == 'speedrun') {
                $data['stopwatch_value'] = $histories->pluck('value');
            }

            return $data;
        });

        return $activities;
    }

    public function getFocusReport($month, $year)
    {
        $user = auth()->user();

        $pointFocus = PointFocus::with('activity')->whereMonth('start_date', $month)->whereYear('start_date', $year)->get();

        $pointFocus->transform(function($data){
            $result = $data->toArray();
            unset($data['activity']);
            $result['activity_title'] = $data->activity->title;
            $result['activity_value'] = $data->activity->value;

            return $result;
        });
        return $pointFocus;
    }
}
