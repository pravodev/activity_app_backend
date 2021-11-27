<?php
namespace App\Services\Implementations;

use App\Services\Contracts\CategoryServiceContract;
use App\Repositories\Contracts\CategoryRepositoryContract as CategoryRepo;
use App\Exceptions\StoreDataFailedException;

class CategoryServiceImplementation implements CategoryServiceContract {
    protected $categoryRepo;
    public function __construct(CategoryRepo $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function get() {
        return $this->categoryRepo->allOrder('id', 'desc');
    }

    public function store($input) {
        return $this->categoryRepo->store($input);
    }

    public function update($input, $id) {
        return $this->categoryRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->categoryRepo->delete($id);
    }

    public function search($fields) {
        return $this->categoryRepo->search($fields);
    }

    public function getUsingMonthYear($month, $year) {
        return $this->categoryRepo->getUsingMonthYear($month, $year);
    }

    public function changePosition($new_position) {
        return $this->categoryRepo->changePosition($new_position);
    }
}