<?php
namespace App\Services\Implementations;

use App\Services\Contracts\ActivityServiceContract;
use App\Repositories\Contracts\ActivityRepositoryContract as ActivityRepo;
use App\Exceptions\StoreDataFailedException;
use App\Models\PointTransaction;
use Storage;
use App\Models\Activity;
use Illuminate\Http\UploadedFile;

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

        if(!isset($input['status'])) {
            // default active
            $input['status'] = 1;
        }

        if(in_array($input['type'], ['count', 'speedrun'])) {
            $can_change = 1;
            $use_textfield = 1;
        } else if(in_array($input['type'], ['value', 'badhabit'])) {
            $can_change = $input['can_change'];
        }

        $input['can_change'] = $can_change;
        // $input['use_textfield'] = $use_textfield;

        if($input['type'] == 'speedrun') {
            $input['criteria'] = isset($input['criteria']) ? $input['criteria'] : 'shorter';
        }

        if($input['is_media_enabled']) {
            $path = 'user_'.auth()->id().'/activities/';
            $fileid = uniqid(time());
            $name = $input['media_type'].'-'.$fileid.'.'.($input['media_file']->guessExtension() ?: 'jpg');
            $input['media_file'] = $input['media_file']->storeAs($path, $name, 'public');
        }

        return $this->activityRepo->store($input);
    }

    public function update($input, $id) {
        if(!empty($input['is_media_enabled']) && is_a($input['media_file'], UploadedFile::class) ) {
            $activity = Activity::find($id);
            Storage::disk('public')->delete($activity->media_file);

            $path = 'user_'.auth()->id().'/activities';
            $fileid = uniqid(time());
            $name = $input['media_type'].'-'.$fileid.'.'.($input['media_file']->guessExtension() ?: 'jpg');
            $input['media_file'] = $input['media_file']->storeAs($path, $name, 'public');
        } else {
            unset($input['media_file']);
        }

        return $this->activityRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->activityRepo->delete($id);
    }

    public function search($fields) {
        return $this->activityRepo->search($fields);
    }

    public function getUsingMonthYear($month, $year, $showOnlyActiveStatus = true) {
        return $this->activityRepo->getUsingMonthYear($month, $year, $showOnlyActiveStatus);
    }

    public function changePosition($new_position) {
        return $this->activityRepo->changePosition($new_position);
    }

    public function import($parent_id)
    {
        return $this->activityRepo->import($parent_id);
    }

    public function getDailyUsingMonthYear($date, $showOnlyActiveStatus = true) {
        return $this->activityRepo->getDailyUsingMonthYear($date, $showOnlyActiveStatus);
    }

    public function getFocusReport($month, $year)
    {
        return $this->activityRepo->getFocusReport($month, $year);
    }
}
