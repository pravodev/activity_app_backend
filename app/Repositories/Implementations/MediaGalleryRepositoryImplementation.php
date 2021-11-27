<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\MediaGalleryRepositoryContract;
use App\Models\MediaGallery;
use DB;

class MediaGalleryRepositoryImplementation extends BaseRepositoryImplementation implements MediaGalleryRepositoryContract  {
    public function __construct(MediaGallery $builder)
    {
        $this->builder = $builder;
    }

    public function search($fields) {
        $result = \App\Models\MediaGallery::orWhere(function($query) use ($fields) {
            foreach ($fields as $key => $value) {
                if($key == 'category_id') {
                    $query->orWhere($key, $value);
                } else {
                    $query->orWhere($key, 'like', "%" . $value . "%");
                }
            }
        })->get();

        return $result;
    }
}