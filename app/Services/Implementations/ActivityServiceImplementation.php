<?php
namespace App\Services\Implementations;

use App\Services\Contracts\ActivityServiceContract;
use App\Repositories\Contracts\ActivityRepositoryContract as ActivityRepo;
use App\Exceptions\StoreDataFailedException;

class ActivityServiceImplementation implements ActivityServiceContract {
    protected $activityRepo;
    public function __construct(ActivityRepo $activityRepo)
    {
        $this->activityRepo = $activityRepo;
    }

    public function get() {
        if(request()->has('sortbyposition')) {
            return $this->activityRepo->allOrder('position', 'asc');
        }
        return $this->activityRepo->allOrder('id', 'desc');
    }

    public function store($input) {
        $can_change = 0;
        $use_textfield = 0;

        if(in_array($input['type'], ['count', 'speedrun'])) {
            $can_change = 1;
            $use_textfield = 1;
        } else if(in_array($input['type'], ['value', 'badhabit'])) {
            $can_change = $input['can_change'];
        }

        $input['can_change'] = $can_change;
        // $input['use_textfield'] = $use_textfield;
        
        return $this->activityRepo->store($input);
    }

    public function update($input, $id) {
        // $can_change = 0;
        // $use_textfield = 0;

        // if($input['type'] == 'count') {
        //     $can_change = 1;
        //     $use_textfield = 1;
        // } else if($input['type'] == 'speedrun') {
        //     $can_change = 1;
        //     $use_textfield = 1;
        // }

        // $input['can_change'] = $can_change;
        // $input['use_textfield'] = $use_textfield;

        return $this->activityRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->activityRepo->delete($id);
    }

    public function search($fields) {
        return $this->activityRepo->search($fields);
    }

    public function getUsingMonthYear($month, $year) {
        return $this->activityRepo->getUsingMonthYear($month, $year);
    }

    public function changePosition($new_position) {
        return $this->activityRepo->changePosition($new_position);
    }
}