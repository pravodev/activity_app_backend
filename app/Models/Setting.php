<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PointTransaction;
use App\Models\History;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'data'];

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getDataAttribute()
    {
        return json_decode($value, true);
    }

    protected static function booted()
    {
        static::saved(function($model){
            if($model->key === 'point_system' && $model->value == 1) {
                $dates = History::selectRaw('MONTH(date) date, YEAR(date) year')->groupBy(\DB::raw('MONTH(date), YEAR(date)'))->get();
                $activities = Activity::has('histories')->get();

                foreach($dates as $date) {
                    foreach($activities as $activity) {
                        PointTransaction::calculate($activity->id, $date->date, $date->year);
                    }
                }
            }
        });

        static::saving(function($model){
            $model->user_id = auth()->id();
        });

        static::addGlobalScope('byuser', function ($builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
