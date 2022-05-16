<?php

if(!function_exists('get_settings')) {
    $setting = null;

    function get_settings($key = '', $user_id = null)
    {
        global $setting;

        if(!$setting) {
            $setting = [];

            $query = new \App\Models\Setting;

            if($user_id) {
                $query = $query->withoutGlobalScope('byuser')->where('user_id', $user_id);
            }

            foreach($query->get() as $set) {
                $setting[$set->key] = $set->data ?: $set->value;
            }
        }

        if($key) {
            return $setting[$key] ?? null;
        }

        return (object) $setting;
    }
}
