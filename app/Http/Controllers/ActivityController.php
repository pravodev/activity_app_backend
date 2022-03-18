<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Http\Requests\StoreActivity;
use App\Http\Requests\UpdateActivity;
use App\Http\Requests\SearchActivity;
use App\Http\Requests\ChangePositionActivity;
use App\Services\Contracts\ActivityServiceContract as ActivityService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\SearchDataFailedException;

class ActivityController extends Controller
{
    private $activityService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    public function index()
    {
        try {
            $data = $this->activityService->get();
            $response = ['error' => false, 'data'=>$data];
            return response()->json($response);
        } catch (\Throwable $th) {
            return $th;
            throw new GetDataFailedException('Get Data Failed : Undefined Error');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActivity $request)
    {
        try {
            $data = $request->validated();
            $this->activityService->store($data);
            $response = ['error' => false, 'message'=>'create data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            throw new StoreDataFailedException('Store Data Failed : Undefined Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActivity $request, Activity $activity)
    {
        try {
            $data = $request->validated();
            $this->activityService->update($data, $activity->id);
            $response = ['error' => false, 'message'=>'update data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new UpdateDataFailedException('Update Data Failed : Undefined Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Activity $activity)
    {
        try {
            $this->activityService->delete($activity->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }
    }

    public function search(SearchActivity $request) {
        try {
            $data = $request->validated();
            $result = $this->activityService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }

    public function getUsingMonthYear($month, $year) {
        try {
            $result = $this->activityService->getUsingMonthYear($month, $year);
            $response = ['error' => false, 'data' => $result];
            return response()->json($response);
        } catch (\Throwsable $th) {
            // dd($th);
            throw new GetHistoryRangeFailedException('Get History Range Failed : Undefined Error');
        }
    }

    public function updatePosition(ChangePositionActivity $request) {
        try {
            $data = $request->validated();
            $result = $this->activityService->changePosition($request->position);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            throw new UpdateDataFailedException('Update Data Failed : Undefined Error');
        }
    }

    public function import()
    {
        $parentId = auth()->id();
        $result = $this->activityService->import($parentId);

        $response = [
            'error' => false,
            'message' => $result . ' activities imported'
        ];

        return response()->json($response);
    }
}
