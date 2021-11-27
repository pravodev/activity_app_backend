<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Models\Category;
use DB;

class CategoryRepositoryImplementation extends BaseRepositoryImplementation implements CategoryRepositoryContract  {
    public function __construct(Category $builder)
    {
        $this->builder = $builder;
    }

    public function search($fields) {
        $result = \App\Models\Category::orWhere(function($query) use ($fields) {
            foreach ($fields as $key => $value) {
                $query->orWhere($key, 'like', "%" . $value . "%");
            }
        })->get();

        return $result;
    }
}