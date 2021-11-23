<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    // protected $fillable = ['type', 'title', 'value', 'target', 'can_change', 'use_textfield', 'color'];
    protected $fillable = ['type', 'title', 'value', 'target', 'color', 'description', 'can_change', 'increase_value', 'is_hide'];

    protected $appends = [
        'speedrun_parsed',
        'target',
        'is_red',
        'type_text',
    ];

    protected $casts = [
        'can_change' => 'integer',
        'increase_value' => 'integer',
        'is_hide' => 'integer',
    ];
    
    public function histories() {
        return $this->hasMany(History::class);
    }

    public function delete()
    {   
        foreach($this->histories as $history) { $history->delete(); }
        return parent::delete();
    }

    /**
     * Pase speedrun value to HH:MM:SS
     *
     * @param string $value ex: 1h 34m 33s 00ms
     * @return void
     */
    public static function convertSpeedrunValueToTimestamp($value)
    {
        $split = explode(' ', $value);
        $array_times = [];
        
        foreach($split as $i => $value) {
            preg_match_all('!\d+!', $value, $matches);
            $number = $matches[0][0] ?? null;

            array_push($array_times, $number);
        }

        $formatted = "{$array_times[0]}:{$array_times[1]}:{$array_times[2]}.{$array_times[3]}";
        $timestamps = strtotime($formatted);
        return $timestamps;
    }

    public static function convertTimestampToSpeedrunValue($value)
    {
        $date = \Carbon\Carbon::createFromTimestamp($value)->format('H\h i\m s\s');

        return $date;
    }
    
    // public static function booted()
    // {
    //     static::creating(function($model){
    //         $lastposition = self::get()->pluck('position')->first() ?? 0;
    //         $model->position = $lastposition+1;
    //     });
    // }

    public function getSpeedrunParsedAttribute()
    {
        if($this->type !== 'speedrun') return null;

        $value = $this->value;
        $split = explode(' ', $value);
        $new_values = [];
        $keyname = [
            'h',
            'm',
            's',
            'ms'
        ];
        
        foreach($split as $i => $value) {
            preg_match_all('!\d+!', $value, $matches);
            $number = $matches[0][0] ?? null;

            $new_values[$keyname[$i]] = (int) $number;
        }

        return $new_values;
    }

    public function getTargetAttribute()
    {
        $value = $this->attributes['target'];
        if(is_null($value)) {
            return null;
        }
        
        return (int) $value;
    }

    public function getIsRedAttribute()
    {
        $activity = $this;
        $is_red = $activity->score < $activity->target;

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
            // $left = $activity->target - $activity->count;

        } else if($activity->type == 'badhabit') {
            $is_red = $activity->score > $activity->target;
        }

        return $is_red;
    }

    public function getTypeTextAttribute()
    {
        $type = $this->type;

        $options = [
            'value' => 'Number Count',
            'count' => 'Text Count',
            'speedrun' => 'Speedrun',
            'badhabit' => 'Bad Habit'
        ];

        if(in_array($type, array_keys($options))) {
            return $options[$type];
        }

        return $type;
    }
}
