<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\ApplicationLogRepositoryContract;
use App\Models\ApplicationLog;
use DB;

class ApplicationLogRepositoryImplementation extends BaseRepositoryImplementation implements ApplicationLogRepositoryContract  {
    public function __construct(ApplicationLog $builder)
    {
        $this->builder = $builder;
    }
}