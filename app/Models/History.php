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
    }
}
