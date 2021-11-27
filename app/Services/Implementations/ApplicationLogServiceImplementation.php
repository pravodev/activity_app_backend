<?php
namespace App\Services\Implementations;

use App\Services\Contracts\ApplicationLogServiceContract;
use App\Repositories\Contracts\ApplicationLogRepositoryContract as ApplicationLogRepo;
use App\Exceptions\StoreDataFailedException;
use Storage;

class ApplicationLogServiceImplementation implements ApplicationLogServiceContract {
    protected $applicationLogRepo;
    public function __construct(ApplicationLogRepo $applicationLogRepo)
    {
        $this->applicationLogRepo = $applicationLogRepo;
    }

    public function get() {
        return $this->applicationLogRepo->allOrder('id', 'desc');
    }

    public function store($input) {
        return $this->applicationLogRepo->store($input);
    }

    public function update($input, $id) {
        return $this->applicationLogRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->applicationLogRepo->delete($id);
    }
}