<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\Contracts\SettingServiceContract as SettingService;
use App\Http\Requests\SaveSetting;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\UpdateDataFailedException;

class SettingController extends Controller
{
    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        try {
            $data = $this->settingService->getFormatted();
            $response = ['error' => false, 'data'=>$data];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            throw new GetDataFailedException('Get Data Failed : Undefined Error');
        }
    }

    public function save(SaveSetting $request)
    {
        try {
            $data = $request->validated();
            $this->settingService->save($data['key'], $data['value'], $data['data']);
            $response = ['error' => false, 'message'=>'save setting success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            throw new UpdateDataFailedException('Update Data Failed : Undefined Error');

        }
    }
}
