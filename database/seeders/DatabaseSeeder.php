<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ActivitySeeder::class);
        $this->call(HistorySeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(CategorySeeder::class);
    }
}
