<?php

namespace App\Services\Contracts;

interface CategoryServiceContract {
    public function get();

    public function store($input);

    public function update($input, $id);

    public function delete($id);

    public function search($fields);
}