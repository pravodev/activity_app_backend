<?php

namespace App\Listeners;

use App\Models\Activity;
use App\Models\History;
use App\Models\PointTransaction;
use DB;

class CalculatePointListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->value) {
            $listMonths = History::select(DB::raw('MONTH(date) month, YEAR(date) year'))->groupBy(DB::raw('MONTH(date), YEAR(date)'))->get();

            $activities = Activity::all();
            foreach ($activities as $activity) {
                foreach ($listMonths as $month) {
                    PointTransaction::calculate($activity->id, $month->month, $month->year);
                }
            }
        }
    }
}
