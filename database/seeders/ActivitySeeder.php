<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Activity::factory()->create([
            'title' => 'First Activity',
            'type' => 'value'
        ]);
        Activity::factory()->create([
            'title' => 'Second Activity',
            'type' => 'value',
            'can_change' => 0,
        ]);
        Activity::factory()->create([
            'title' => 'Third Activity',
            'type' => 'value'
        ]);

        Activity::factory()->create([
            'title' => 'Learning',
            'type' => 'count'
        ]);
        Activity::factory()->create([
            'title' => 'To Do',
            'type' => 'count'
        ]);

        Activity::factory()->create([
            'title' => 'TAST Example 1',
            'type' => 'speedrun'
        ]);
        Activity::factory()->create([
            'title' => 'TAST Example 2',
            'type' => 'speedrun'
        ]);
        Activity::factory()->create([
            'title' => 'TAST Example 3',
            'type' => 'speedrun'
        ]);
    }
}
