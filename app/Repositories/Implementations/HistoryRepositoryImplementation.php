<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\HistoryRepositoryContract;
use App\Models\History;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HistoryRepositoryImplementation extends BaseRepositoryImplementation implements HistoryRepositoryContract  {
    public function __construct(History $builder)
    {
        $this->builder = $builder;
    }

    public function search($fields) {
        $result = DB::table("histories")
            ->join("activities", "activities.id", "histories.activity_id")
            ->orWhere(function($query) use ($fields) {
                foreach ($fields as $key => $value) {
                    if($key != "activity_title") {
                        $query->orWhere($key, 'like', "%" . $value . "%");
                    } else {
                        $query->orWhere("activities.title", "like", "%" . $value . "%");
                    }
                }
            })
            ->where(function($q){
                $q->where('activities.status', 1);
            })
            ->select("histories.*", "activities.id as activity_id", "activities.title as activity_title")
            ->where('histories.user_id', auth()->id())
            ->get();

        return $result;
    }

    public function getHistoryRange($params = []) {
        // $result = DB::table('histories')->get();
        // return $result;
        $result = DB::table('histories')->select(DB::raw("DATE_FORMAT(histories.date, '%m-%Y') as historyDate"),  DB::raw('YEAR(histories.date) year, MONTH(histories.date) month'))
        ->where('histories.deleted_at', null)
        ->join("activities", "activities.id", "histories.activity_id")
        ->where(function($q){
            $q->where('activities.status', 1);
        })
        ->groupby('year','month', 'historyDate')
        ->orderBy(DB::raw("YEAR(histories.date)"), 'DESC')
        ->orderBy(DB::raw("MONTH(histories.date)"), 'DESC');

        if(isset($params['year'])) {
            $result = $result->where(DB::raw("YEAR(histories.date)"), $params['year']);
        }

        if(isset($params['student_id'])) {
            $result = $result->where('histories.user_id', $params['student_id']);
        } else {
            $result = $result->where('histories.user_id', auth()->id());
        }

        $result = $result->get();
        return $result;
    }

    public function storeBulk($histories) {

        $newData = $this->builder->insert($histories);
        return $newData;
    }


}
