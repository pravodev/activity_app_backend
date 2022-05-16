<?php

namespace App\Repositories\Contracts;

interface SettingRepositoryContract
{
    public function getFormatted();

    public function save($key, $value, $data = null);

}
