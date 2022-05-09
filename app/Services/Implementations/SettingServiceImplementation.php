<?php
namespace App\Services\Implementations;

use App\Services\Contracts\SettingServiceContract;
use App\Repositories\Contracts\SettingRepositoryContract as SettingRepo;
use App\Exceptions\StoreDataFailedException;
use Illuminate\Support\Arr;

class SettingServiceImplementation implements SettingServiceContract {
    protected $historyRepo;
    public function __construct(SettingRepo $historyRepo)
    {
        $this->historyRepo = $historyRepo;
    }

    public function getFormatted() {
        return $this->historyRepo->getFormatted();
    }

    public function save($key, $value, $data = null) {
        return $this->historyRepo->save($key, $value, $data);
    }

}
