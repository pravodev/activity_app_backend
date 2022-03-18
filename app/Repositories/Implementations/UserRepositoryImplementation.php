<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\UserRepositoryContract;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\PointTransaction;

class UserRepositoryImplementation extends BaseRepositoryImplementation implements UserRepositoryContract  {
    public function __construct(User $builder)
    {
        $this->builder = $builder;
    }

    public function getProfile()
    {
        $data = $this->builder->with('parent')->withCount('childs')->find(auth()->id())->toArray();
        $data['total_points'] = PointTransaction::where('user_id', auth()->id())->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('value');

        return $data;
    }

    public function getStudents($parent_id)
    {
        return $this->builder->where('parent_id', $parent_id)->get();
    }
}
