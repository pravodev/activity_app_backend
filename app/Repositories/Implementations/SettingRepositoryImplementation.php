<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\SettingRepositoryContract;
use App\Models\Setting;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\PointFocus;

class SettingRepositoryImplementation extends BaseRepositoryImplementation implements SettingRepositoryContract  {
    public function __construct(Setting $builder)
    {
        $this->builder = $builder;
    }

    public function getFormatted() {
        $settings = $this->builder->where('user_id', auth()->id())->get();
        $result = [];

        foreach($settings as $setting) {
            $result[$setting->key] = $setting->data ?:  $setting->value;
        }

        if(!isset($setting['point_focus'])) {
            $result['point_focus'] = PointFocus::getConfiguration();
        }

        return $result;
    }

    public function save($key, $value, $data = null) {
        $setting = $this->builder->where('key', $key)->where('user_id', auth()->id())->first() ?: $this->builder;
        $setting->key = $key;
        $setting->value = $value;
        $setting->user_id = auth()->id();
        $setting->data = $data;
        $setting->save();

        return $setting;
    }


}
