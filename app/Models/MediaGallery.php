<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_id',
        'value',
        'thumbnail',
    ];

    protected $appends = [
        'value_url',
        'thumbnail_url',
    ];

    public function getValueUrlAttribute()
    {
        if($this->value && $this->type !== 'youtube') {
            return asset('storage/'.$this->value);
        }

        return $this->value;
    }

    public function getThumbnailUrlAttribute()
    {
        if($this->thumbnail && $this->type !== 'youtube') {
            return asset('storage/'.$this->thumbnail);
        }

        return $this->thumbnail;
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
