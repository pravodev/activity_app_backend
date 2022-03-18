<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'history_id',
        'value',
        'date',
        'time',
        'bonus_value',
        'penalty_value',
        'point_weight',
    ];

    public function getValueAttribute($value)
    {
        return (float) $value;
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function history()
    {
        return $this->belongsTo(History::class);
    }

    public static function calculate($activity_id, $month = null, $year = null, $user_id = null)
    {
        $user_id = $user_id ?: auth()->id();

        $activity = Activity::withoutGlobalScope('byuser')->find($activity_id);
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        $history = History::where('activity_id', $activity->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->withoutGlobalScope('byuser')
                    ->get();

        if(in_array($activity->type, ['speedrun', 'count'])) {
            $total = $history->count();
        } else {
            $total = $history->sum('value');
        }

        $target = $activity->target;
        $point_weight = $activity->point_weight ?? 1;
        $point = null;

        if($activity->type == 'badhabit') {
            if($activity->bonus_value && $total < $target) {
                $extra_value = $target - $total;
                $point = ($extra_value / $activity->bonus_value) * $point_weight;
            }
            if($activity->penalty_value && $total > $target) {
                $left_value = $target - $total;
                $point = ($left_value / $activity->penalty_value) * $point_weight;
            }
        } else  {
            if($activity->bonus_value && $total > $target) {
                $extra_value = $total - $target;
                $point = ($extra_value / $activity->bonus_value) * $point_weight;
            }
            if($activity->penalty_value && $total < $target) {
                $left_value = $total - $target;
                $point = ($left_value / $activity->penalty_value) * $point_weight;
            }
        }

        // if best record this month and all time is same, will get extra 10 point
        if($activity->type == 'speedrun') {
            $histories = $activity->histories->map(function($history){
                return [
                    'timestamp' => Activity::convertSpeedrunValueToTimestamp($history->value),
                    'value' => $history->value,
                    'date' => $history->date,
                ];
            });;

            $historyThisMonth = $histories->filter(function($history) use($month, $year) {
                $date = \Carbon\Carbon::parse($history['date']);
                $valueMonth = $date->month;
                $valueYear = $date->year;
                return $valueMonth == $month && $valueYear == $year;
            });
            $criteria_time = $activity->criteria == 'shorter' ? $historyThisMonth->min('timestamp') : $historyThisMonth->max('timestamp');
            // $best_time = $historyThisMonth->filter(function($t) use($criteria_time) {
            //     return $t['timestamp'] == $criteria_time;
            // })->first();

            $criteria_alltime = $activity->criteria == 'shorter' ? $histories->min('timestamp') : $histories->max('timestamp');
            // $best_record_alltime = $histories->filter(function($t) use($criteria_time) {
            //     return $t['timestamp'] == $criteria_time;
            // })->first();

            if($histories->count() && $criteria_time == $criteria_alltime) {
                $point += 10;
            }
        }

        if(!is_null($point)) {
            $model = PointTransaction::where('activity_id', $activity->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->first() ?? new PointTransaction;

            $model->activity_id = $activity->id;
            $model->user_id = $user_id;
            $model->date = now()->month($month)->year($year)->format('Y-m-d');
            $model->time = now()->month($month)->year($year)->format('H:m:s');
            $model->value = $point;
            $model->save();
        }

        return $point;
    }

    public static function calculateBonus($activity_id, $month = null, $year = null)
    {
        $activity = Activity::find($activity_id);
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        $histories = History::where('activity_id', $activity->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->doesntHave('point')
                    ->get();

        $target = $activity->target;
        $point_weight = $activity->point_weight ?? 1;

        foreach($histories as $history) {
            $total = in_array($activity->type,  ['speedrun', 'count']) ? 1 : $history->value;
            $point = null;

            if($activity->type == 'badhabit') {
                if($activity->bonus_value && $total < $target) {
                    $extra_value = $target - $total;
                    $point = ($extra_value / $activity->bonus_value) * $point_weight;
                }
                if($activity->penalty_value && $total > $target) {
                    $left_value = $target - $total;
                    $point = ($left_value / $activity->penalty_value) * $point_weight;
                }
            } else  {
                if($activity->bonus_value && $total > $target) {
                    $extra_value = $total - $target;
                    $point = ($extra_value / $activity->bonus_value) * $point_weight;
                }
                if($activity->penalty_value && $total < $target) {
                    $left_value = $total - $target;
                    $point = ($left_value / $activity->penalty_value) * $point_weight;
                }
            }

            if(!is_null($point)) {
                PointTransaction::firstOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'history_id' => $history->id,
                    ],
                    [
                        'date' => $history->date,
                        'time' => $history->time,
                        'value' => $point,
                    ]
                );
            }
        }
    }

    public static function booted()
    {
        static::saving(function($model){
            if(!$model->user_id) {
                $model->user_id = auth()->id();
            }

            if($activity = $model->activity) {
                $model->bonus_value = $activity->bonus_value;
                $model->penalty_value = $activity->penalty_value;
                $model->point_weight = $activity->point_weight;
            }
        });

        static::addGlobalScope('byuser', function ($builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
