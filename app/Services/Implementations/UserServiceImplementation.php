<?php
namespace App\Services\Implementations;

use App\Services\Contracts\UserServiceContract;
use App\Repositories\Contracts\UserRepositoryContract as UserRepo;
use App\Exceptions\StoreDataFailedException;
use Illuminate\Support\Arr;

class UserServiceImplementation implements UserServiceContract {
    protected $userRepo;
    public function __construct(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function getProfile() {
        return $this->userRepo->getProfile();
    }
}
