<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Services\Contracts\ActivityServiceContract as ActivityService;

class MonthlyReportExport implements FromView
{
    public $month, $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $activityService = app()->make(ActivityService::class);
        $result = $activityService->getUsingMonthYear($this->month, $this->year, true);

        return view('exports.monthly', [
            'result' => $result
        ]);
    }
}
