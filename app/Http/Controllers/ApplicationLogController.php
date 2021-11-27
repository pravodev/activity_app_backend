<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationLog;
use App\Http\Requests\StoreApplicationLog;
use App\Http\Requests\UpdateApplicationLog;
use App\Http\Requests\SearchApplicationLog;
use App\Services\Contracts\ApplicationLogServiceContract as ApplicationLogService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\SearchDataFailedException;

class ApplicationLogController extends Controller
{
    private $applicationLogService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(ApplicationLogService $applicationLogService)
    {
        $this->applicationLogService = $applicationLogService;
    }
    public function index(Request $request)
    {
        try {
            $data = $this->applicationLogService->get($request->all());
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
    public function store(StoreApplicationLog $request)
    {
        try {
            $data = $request->validated();
            $this->applicationLogService->store($data);    
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
    public function update(UpdateApplicationLog $request, ApplicationLog $application_log)
    {
        try {
            $data = $request->validated();
            $this->applicationLogService->update($data, $application_log->id);
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
    public function destroy(ApplicationLog $application_log)
    {
        try {
            $this->applicationLogService->delete($application_log->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }
        
    }

    public function search(SearchApplicationLog $request) {
        try {
            $data = $request->validated();
            $result = $this->applicationLogService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }
}
