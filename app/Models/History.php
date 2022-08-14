<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ["type", "activity_id", "date", "time", "value", "value_textfield"];

    public function activity() {
        return $this->belongsTo(Activity::class);
    }

    public function point()
    {
        return $this->hasOne(PointTransaction::class);
    }

    protected static function booted()
    {
        static::creating(function($model) {
            if(!$model->time) {
                $model->time = now()->format('H:i:s');
            }

            if(!$model->date) {
                $model->date = now()->format('Y-m-d');
            }
        });

        static::created(function($model) {
            if($model->activity->is_focus_enabled) {
                PointFocus::calculate($model);
            }
        });

        static::saving(function($model){
            if(!$model->user_id) {
                $model->user_id = auth()->id();
            }
        });

        static::saved(function($model) {
            $user_id = request()->student_id ?: auth()->id();

            if(get_settings('point_system', $user_id)) {
                $date = \Carbon\Carbon::parse($model->date);
                PointTransaction::calculate($model->activity_id, $date->month, $date->year, $user_id);
            }
        });

        static::updated(function($model) {
            if($model->isDirty('date') && $model->activity->is_focus_enabled) {
                PointFocus::recalculate($model->activity, \Carbon\Carbon::parse($model->date)->month);
            }
        });

        static::addGlobalScope('byuser', function ($builder) {
            if(auth()->id()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
