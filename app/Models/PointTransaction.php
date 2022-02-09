<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'history_id',
        'value',
        'date',
        'time',
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

    public static function calculate($activity_id, $month = null, $year = null)
    {
        $activity = Activity::find($activity_id);
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        $history = History::where('activity_id', $activity->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
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
            PointTransaction::updateOrCreate(
                [
                    'date' => now()->month($month)->year($year)->format('Y-m-d'),
                    'activity_id' => $activity->id,
                ],
                [
                    'time' => now()->month($month)->year($year)->format('H:m:s'),
                    'value' => $point,
                ]
            );
        }
    }

    public static function booted()
    {
        static::saving(function($model){
            $model->user_id = auth()->id();
        });

        static::addGlobalScope('byuser', function ($builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
