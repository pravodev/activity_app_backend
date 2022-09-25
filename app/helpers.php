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

if(!function_exists('crop_transparent')){
    function crop_transparent($path)
    {
        $im = imagecreatefrompng($path);
        $cropped = imagecropauto($im, IMG_CROP_TRANSPARENT);
        if ($cropped !== false) { // in case a new image resource was returned
            imagedestroy($im);    // we destroy the original image
            $im = $cropped;       // and assign the cropped image to $im
        }

        $cropped = imagecropauto($cropped, IMG_CROP_SIDES);

        imagepng($cropped, $path);
        imagedestroy($im);

        return $path;
    }
}
