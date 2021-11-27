<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $use_textfield = rand(0,1) == 1;
        // $default_value = 0;
        // $target = 0;
        // $can_change = true;
        // if($use_textfield == false) { //if not use textfield
        //     $default_value = 50;
        //     $target = 1000;
        // } else { //if use textfield, then meaning of target is target count NOT target total value
        //     $target = 10; //this is target count
        // }
        $can_change = 1;
        $available_types = ['value', 'count', 'speedrun'];
        $rand_type = $available_types[rand(0, count($available_types)-1)];
        
        return [
            'type' => $rand_type,
            'color' => '#4e73df',
            'title' => $this->faker->title,
            'value' => function(array $attributes) {
                switch ($attributes['type']) {
                    case 'value':
                        return 50;
                    case 'count':
                        return null;
                    case 'speedrun':
                        return '1h 24m 40s 10ms';
                }
            },
            'target' => function(array $attributes) {
                switch ($attributes['type']) {
                    case 'value':
                        return 1000;
                    case 'count':
                        return 50;
                    case 'speedrun':
                        return 50;
                }
            },
            'can_change' => $can_change,
            // 'use_textfield' => $use_textfield,
        ];
    }
}
