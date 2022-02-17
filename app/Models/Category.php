<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
    ];

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
