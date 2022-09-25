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
    protected $fillable = [
        'type',
        'title',
        'value',
        'target',
        'color',
        'description',
        'can_change',
        'increase_value',
        'is_hide',
        'is_ms_enable',
        'is_focus_enabled',
        'focus_min_value',
        'criteria',
        'bonus_value',
        'penalty_value',
        'point_weight',
        'is_media_enabled',
        'media_type',
        'media_file',
        'status',
    ];

    protected $appends = [
        'speedrun_parsed',
        'target',
        'is_red',
        'type_text',
        'media_file_link',
    ];

    protected $casts = [
        'can_change' => 'integer',
        'increase_value' => 'integer',
        'is_hide' => 'integer',
        'status' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('activities.status', 1);
    }

    public function scopeInactive($q)
    {
        return $q->where('activities.status', 0);
    }

    public function getBonusValueAttribute()
    {
        return floatval($this->attributes['bonus_value']);
    }

    public function getPenaltyValueAttribute()
    {
        return floatval($this->attributes['penalty_value']);
    }

    public function getPointWeightAttribute()
    {
        return floatval($this->attributes['point_weight']);
    }

    public function getMediaFileLinkAttribute()
    {
        return asset('storage').'/'.$this->media_file;
    }

    public function histories() {
        return $this->hasMany(History::class)->withoutGlobalScope('byuser');
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

    public static function convertSpeedrunValueToMillisecond($value)
    {
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


        $second = ($new_values['h'] * 3600) + ($new_values['m'] * 60) + $new_values['s'];

        $millisecond = ($second * 1000) + $new_values['ms'];
        return $millisecond;
    }

    public static function convertMillisecondToSpeedrunValue($value)
    {
        $value = $value / 1000;
        $second = (int) $value;
        $ms = (int) substr(round(($value - $second) * 1000), 0, 2);

        $hour = (int) floor($second / 3600);
        $minute = (int) ($second / 60) % 60;
        $second = $second % 60;

        return "{$hour}m {$minute}m ${second}s {$ms}ms";
    }

    public static function convertTimestampToSpeedrunValue($value)
    {
        $date = \Carbon\Carbon::createFromTimestamp($value)->format('H\h i\m s\s v\m\s');

        return $date;
    }

    public static function booted()
    {
        static::saving(function($model){
            if(!$model->user_id) {
                $model->user_id = auth()->id();
            }

            if($model->isDirty('title')) {
                // check same name
                $check = Activity::where('id', '!=', $model->id)->where('user_id', $model->user_id)->where('title', $model->title)->exists();
                if($check)  {
                    $model->title = $model->title .' 1';
                }
            }

            if($model->isDirty('is_focus_enabled') && $model->is_focus_enabled) {
                PointFocus::recalculate($model, now()->month);
            }
        });

        static::creating(function($model) {
            // if change is_hide but value of status 0
            // set is_hide to 1
            if($model->is_hide == 1) {
                $model->status = 0;
                $model->bonus_value = 0;
                $model->penalty_value = 0;
            } else {
                $model->status = 1;
            }
        });

        static::updating(function($model) {
            if($model->isDirty('status')) {
                if(!$model->status) {
                    $model->is_hide = 1;
                    $model->bonus_value = 0;
                    $model->penalty_value = 0;
                } else {
                    $model->is_hide = 0;
                }
            } else if($model->isDirty('is_hide')) {
                // if change is_hide but value of status 0
                // set is_hide to 1
                if($model->is_hide) {
                    $model->status = 0;
                    $model->bonus_value = 0;
                    $model->penalty_value = 0;
                } else {
                    $model->status = 1;
                }
            }
        });

        static::deleted(function($model){
            if($model->media_file) {
                \Storage::disk('public')->delete($model->media_file);
            }
        });

        static::addGlobalScope('byuser', function ($builder) {
            $builder->where('activities.user_id', auth()->id());
        });

    }

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
            } else {
                $is_red = false;
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
            'speedrun' => 'Stopwatch',
            'badhabit' => 'Bad Habit'
        ];

        if(in_array($type, array_keys($options))) {
            return $options[$type];
        }

        return $type;
    }
}
