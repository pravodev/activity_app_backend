<?php

if(!function_exists('get_settings')) {
    $setting = null;

    function get_settings($key = '')
    {
        global $setting;

        if(!$setting) {
            $setting = [];
            foreach(\App\Models\Setting::all() as $set) {
                $setting[$set->key] = $set->value;
            }
        }

        if($key) {
            return $setting[$key] ?? null;
        }

        return (object) $setting;
    }
}
