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

        if($this->type === 'youtube') {
            preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $this->value, $matches);
            if(isset($matches[0])) {
                $embed_link = "https://www.youtube.com/embed/".$matches[0];

                return $embed_link;
            }
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
