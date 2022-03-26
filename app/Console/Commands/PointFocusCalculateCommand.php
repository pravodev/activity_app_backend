<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PointFocus;
use App\Models\Activity;
use App\Models\History;
use App\Models\User;
use Carbon\Carbon;

class PointFocusCalculateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'point-focus:calculate
        {--user_id= : the ID of the user}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // options
        $user_id = $this->option('user_id');

        $list_users = User::query();
        if($user_id) {
            $list_users = $list_users->where('id', $user_id);
        }

        $list_users = $list_users->get();

        foreach($list_users as $user) {
            $historiesGroupByActivity = History::select('date', 'activity_id')->where('user_id', $user->id)->groupBy('date', 'activity_id')->orderBy(\DB::raw('DATE(`date`)'))->get()->groupBy('activity_id');

            foreach ($historiesGroupByActivity as $activity_id => $histories) {
                $pointFocus = null;
                $prevDate = null;

                foreach($histories as $history) {
                    if(!$pointFocus && !$prevDate) {
                        $pointFocus = PointFocus::updateOrCreate(
                            [
                                'activity_id' => $activity_id,
                                'start_date' => $history->date,
                            ],
                            [
                                'repeated_days_count' => 1,
                                'point' => 0,
                                'user_id' => $user->id,
                            ]
                        );

                        $prevDate = Carbon::parse($history->date);

                        continue;
                    }

                    if(Carbon::parse($history->date)->diffInDays($prevDate) == 1) {
                        $pointFocus->repeated_days_count += 1;
                        $pointFocus->end_date = $history->date;
                        $pointFocus->point = $pointFocus->repeated_days_count == 1 ? 1 : pow(2, $pointFocus->repeated_days_count);
                        $pointFocus->save();
                    } else {
                        // create new point focus row
                        $pointFocus = PointFocus::updateOrCreate(
                            [
                                'activity_id' => $activity_id,
                                'start_date' => $history->date,
                            ],
                            [
                                'repeated_days_count' => 1,
                                'point' => 0,
                                'user_id' => $user->id,
                            ]
                        );

                    }
                    $prevDate = Carbon::parse($history->date);
                }
            }
        }

        return 0;
    }
}
