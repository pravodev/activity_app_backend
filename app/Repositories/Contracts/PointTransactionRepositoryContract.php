<?php

namespace App\Repositories\Contracts;

interface PointTransactionRepositoryContract
{
    public function search($fields);

    public function getPointTransactionRange();

    public function storeBulk($input);

}
