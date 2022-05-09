<?php

namespace App\Services\Contracts;

interface SettingServiceContract {
    public function getFormatted();

    public function save($key, $value, $data = null);

}
