<?php

namespace App\Services\Contracts;

interface PointTransactionServiceContract {
    public function get();

    public function store($input);

    public function storeBulk($input);

    public function update($input, $id);

    public function delete($id);
    
    public function search($fields);

    public function getPointTransactionRange();

}