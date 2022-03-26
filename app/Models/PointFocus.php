<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointFocus extends Model
{
    use HasFactory;

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

    protected static function booted()
    {
        static::saving(function($model) {
            if(!$model->end_date) {
                $model->end_date = $model->start_date;
            }
        });
    }
}
