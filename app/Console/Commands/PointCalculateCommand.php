<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\PointTransaction;

class PointCalculateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'point:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate point activity';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!get_settings('point_system')) {
            $this->error('point system not enabled');
            return 0;
        }

        // $activities = Activity::has('histories')->get();

        // foreach($activities as $activity) {
        //     PointTransaction::calculate($activity->id);
        // }

        $dates = History::selectRaw('MONTH(date) date, YEAR(date) year')->groupBy(\DB::raw('MONTH(date), YEAR(date)'))->get();
        $activities = Activity::has('histories')->get();

        foreach($dates as $date) {
            foreach($activities as $activity) {
                PointTransaction::calculate($activity->id, $date->date, $date->year);
            }
        }

        return 0;
    }
}
