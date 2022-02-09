<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::firstOrCreate(['key' => 'beep_sound'], ['value' => 1]);
        Setting::firstOrCreate(['key' => 'point_system'], ['value' => 0]);
    }
}
