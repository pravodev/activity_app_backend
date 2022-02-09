<?php
namespace App\Services\Implementations;

use App\Services\Contracts\PointTransactionServiceContract;
use App\Repositories\Contracts\PointTransactionRepositoryContract as PointTransactionRepo;
use App\Exceptions\StoreDataFailedException;
use Illuminate\Support\Arr;

class PointTransactionServiceImplementation implements PointTransactionServiceContract {
    protected $pointTransactionRepo;
    public function __construct(PointTransactionRepo $pointTransactionRepo)
    {
        $this->pointTransactionRepo = $pointTransactionRepo;
    }

    public function get() {
        $data = $this->pointTransactionRepo->datatableWith(['activity:id,title'])->get();
        $data = collect($data)->map(function ($item) {
            if($item['activity'] == null) {
                $item = Arr::add($item, 'activity_title', "deleted activity");
            } else {
                $item = Arr::add($item, 'activity_title', $item['activity']['title']);
            }
            return Arr::except($item, ['activity']);
        });
        return $data->toArray();
    }

    public function store($input) {
        if(!array_key_exists("value", $input) && !array_key_exists("value_textfield", $input)) {
            $input['value'] = 50;
        }
        return $this->pointTransactionRepo->store($input);
    }

    public function storeBulk($input) {
        //include activity_id on pointTransaction array
        $activityId = $input['activity_id'];
        $input['pointTransaction'] = collect($input['pointTransaction'])->map(function($pointTransaction) use($activityId) {
            if(!array_key_exists("value", $pointTransaction) && !array_key_exists("value_textfield", $pointTransaction)) {
                $pointTransaction['value'] = 50;
            }    
            $pointTransaction["activity_id"] = $activityId;
            return $pointTransaction;
        });

        //prepare histories data that will store to database
        $histories = $input['pointTransaction']->toArray();

        return $this->pointTransactionRepo->storeBulk($histories);
    }

    public function update($input, $id) {
        return $this->pointTransactionRepo->update($input, $id);
    }

    public function delete($id) {
        return $this->pointTransactionRepo->delete($id);
    }

    public function search($fields) {
        return $this->pointTransactionRepo->search($fields);
    }

    public function getPointTransactionRange($params = []) {
        $dataRange = $this->pointTransactionRepo->getPointTransactionRange($params);
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