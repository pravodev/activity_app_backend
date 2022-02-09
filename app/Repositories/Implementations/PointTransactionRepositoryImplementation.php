<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\PointTransactionRepositoryContract;
use App\Models\PointTransaction;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PointTransactionRepositoryImplementation extends BaseRepositoryImplementation implements PointTransactionRepositoryContract  {
    public function __construct(PointTransaction $builder)
    {
        $this->builder = $builder;
    }

    public function get() {
        $query = $this->builder;
        $request = request();

        if($activity_id = $request->activity_id) {
            $query = $query->where('activity_id', $activity_id);
        }

        if($month = $request->month) {
            $query = $query->whereMonth('date', $month);
        }

        if($year = $request->year) {
            $query = $query->whereYear('date', $year);
        }
        
        return $query->orderBy('date', 'desc')->get();
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
            ->select("histories.*", "activities.id as activity_id", "activities.title as activity_title")
            ->get();
          
        return $result;
    }

    public function getPointTransactionRange($params = []) {
        // $result = DB::table('histories')->get();
        // return $result;
        $result = DB::table('histories')->select(DB::raw("DATE_FORMAT(histories.date, '%m-%Y') as historyDate"),  DB::raw('YEAR(histories.date) year, MONTH(histories.date) month'))
        ->where('deleted_at', null)
        ->groupby('year','month', 'historyDate')
        ->orderBy(DB::raw("YEAR(histories.date)"), 'DESC')
        ->orderBy(DB::raw("MONTH(histories.date)"), 'DESC');

        if(isset($params['year'])) {
            $result = $result->where(DB::raw("YEAR(histories.date)"), $params['year']);
        }

        $result = $result->get();
        return $result;
    }

    public function storeBulk($histories) {
        
        $newData = $this->builder->insert($histories);
        return $newData;
    }

    
}