<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointFocus extends Model
{
    use HasFactory;

    static $config = null;

    protected $table = 'point_focus';

    protected $fillable = [
        'activity_id',
        'start_date',
        'end_date',
        'repeated_days_count',
        'point',
        'user_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public static function calculate($activity_id, $month = null, $year = null, $user_id = null)
    // {
    //     $user_id = $user_id ?: auth()->id();

    //     $activity = Activity::withoutGlobalScope('byuser')->find($activity_id);
    //     $month = $month ?: now()->month;
    //     $year = $year ?: now()->year;

    //     $historyGroupByDate = History::where('activity_id', $activity->id)
    //                 ->whereMonth('date', $month)
    //                 ->whereYear('date', $year)
    //                 ->withoutGlobalScope('byuser')
    //                 ->get();

    //     $historyGroupByDate
    // }

    public static function getConfiguration()
    {
        if(!static::$config) {
            $setting = get_settings('point_focus', auth()->id());
            $config = $setting ? $setting : null;
            if(!$setting) {
                $config = [
                    1 => 0,
                    2 => 1,
                    3 => 2,
                    4 => 4,
                    5 => 8,
                    6 => 16,
                    7 => 24,
                    8 => 32,
                    9 => 40,
                    10 => 40,
                ];
            }

            static::$config = $config;
        }

        return static::$config;
    }

    public static function calculate(History $model)
    {
        // $check =
        $yesterday = \Carbon\Carbon::createFromFormat('Y-m-d', $model->date)->subday()->format('Y-m-d');
        $check = History::where('activity_id', $model->activity_id)->where('date', $yesterday)->exists();
        $config = static::getConfiguration();
        $pointFocus = PointFocus::where('user_id', $model->user_id)->where('activity_id', $model->activity_id)->orderByDesc('start_date')->first();
        if($check && $pointFocus) {
            $repeated_day = $pointFocus->repeated_days_count+1;
            if($pointFocus->end_date == $model->date) {
                $repeated_day = $pointFocus->repeated_days_count;
            }

            $point = $repeated_day > 10 ? $config[10] : $config[$repeated_day];

            $pointFocus->end_date = $model->date;
            $pointFocus->repeated_days_count = $repeated_day;
            $pointFocus->point = $point;
            $pointFocus->save();
        } else {
            PointFocus::updateOrCreate([
                'activity_id' => $model->activity_id,
                'start_date' => $model->date,
                'end_date' => $model->date,
                'user_id' => $model->user_id,
            ], [
                'repeated_days_count' => 1,
                'point' => $config[1],
            ]);
        }

    }

    protected static function booted()
    {
        static::saving(function($model) {
            if(!$model->end_date) {
                $model->end_date = $model->start_date;
            }
        });

        static::addGlobalScope('byuser', function ($builder) {
            $builder->where('point_focus.user_id', auth()->id());
        });
    }
}
