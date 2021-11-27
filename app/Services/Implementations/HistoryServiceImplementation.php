<?php
namespace App\Services\Implementations;

use App\Services\Contracts\HistoryServiceContract;
use App\Repositories\Contracts\HistoryRepositoryContract as HistoryRepo;
use App\Exceptions\StoreDataFailedException;
use Illuminate\Support\Arr;

class HistoryServiceImplementation implements HistoryServiceContract {
    protected $historyRepo;
    public function __construct(HistoryRepo $historyRepo)
    {
        $this->historyRepo = $historyRepo;
    }

    public function get() {
        $data = $this->historyRepo->datatableWith(['activity:id,title,can_change'])
            ->orderBy('id', 'desc')->get();
        $data = collect($data)->map(function ($item) {
            if($item['activity'] == null) {
                $item = Arr::add($item, 'activity_title', "deleted activity");
                $item = Arr::add($item, 'activity_can_change', 0);
            } else {
                $item = Arr::add($item, 'activity_title', $item['activity']['title']);
                $item = Arr::add($item, 'activity_can_change', $item['activity']['can_change']);
            }
            return Arr::except($item, ['activity']);
        });
        return $data->toArray();
    }

    public function store($input) {
        if(!array_key_exists("value", $input) && !array_key_exists("value_textfield", $input)) {
            $input['value'] = 50;
        }
        return $this->historyRepo->store($input);
    }

    public function storeBulk($input) {
        //include activity_id on history array
        $activityId = $input['activity_id'];
        $input['history'] = collect($input['history'])->map(function($history) use($activityId) {
            if(!array_key_exists("value", $history) && !array_key_exists("value_textfield", $history)) {
                $history['value'] = 50;
            }    
            $history["activity_id"] = $activityId;
            return $history;
        });

        //prepare histories data that will store to database
        $histories = $input['history']->toArray();

        return $this->historyRepo->storeBulk($histories);
    }

    public function update($input, $id) {
        return $this->historyRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->historyRepo->delete($id);
    }

    public function search($fields) {
        return $this->historyRepo->search($fields);
    }

    public function getHistoryRange($params = []) {
        $dataRange = $this->historyRepo->getHistoryRange($params);
        $result = [];
        $groupByYear = $dataRange->groupBy('year');

        foreach($groupByYear as $year => $range) {
            $result[] = [
                'year' => $year,
                'range'=> $range       
            ];
        }

        return $result;
    }

    
}