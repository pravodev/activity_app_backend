<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Setting;
use App\Models\History;

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
        // $activities = Activity::has('histories')->get();

        // foreach($activities as $activity) {
        //     PointTransaction::calculate($activity->id);
        // }

        $userIds = Setting::withoutGlobalScopes()->where('key', 'point_system')->where('value', 1)->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get();

        foreach($users as $user) {
            $dates = History::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->selectRaw('MONTH(date) date, YEAR(date) year')
                ->groupBy(\DB::raw('MONTH(date), YEAR(date)'))
                ->get();

            $activities = Activity::withoutGlobalScopes()->has('histories')->where('user_id', $user->id)->get();

            foreach($dates as $date) {
                foreach($activities as $activity) {
                    PointTransaction::calculate($activity->id, $date->date, $date->year, $user->id);
                }
            }
        }

        return 0;
    }
}
