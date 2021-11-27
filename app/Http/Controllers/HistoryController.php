<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History;
use App\Http\Requests\StoreHistory;
use App\Http\Requests\BulkStoreHistory;
use App\Http\Requests\UpdateHistory;
use App\Http\Requests\SearchHistory;
use App\Http\Requests\SearchHistoryRange;
use App\Services\Contracts\HistoryServiceContract as HistoryService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\DeleteDataFailedException;
use App\Exceptions\SearchDataFailedException;
use App\Exceptions\GetHistoryRangeFailedException;


class HistoryController extends Controller
{
    private $historyService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }
    public function index()
    {
        try {
            $data = $this->historyService->get();
            $response = ['error' => false, 'data'=>$data];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw new GetDataFailedException('Get Data Failed : Undefined Error');
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHistory $request)
    {
        try {
            $data = $request->validated();
            $this->historyService->store($data);    
            $response = ['error' => false, 'message'=>'create data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw new StoreDataFailedException('Store Data Failed : Undefined Error');
        }
        
        
    }

    /**
     * Store bulk history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(BulkStoreHistory $request)
    {
        try {
            $data = $request->validated();
            $this->historyService->storeBulk($data);    
            $response = ['error' => false, 'message'=>'create data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json($th);
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
    public function update(UpdateHistory $request, History $history)
    {
        try {
            $data = $request->validated();
            $this->historyService->update($data, $history->id);
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
    public function destroy(History $history)
    {
        try {
            $this->historyService->delete($history->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }
        
    }

    public function search(SearchHistory $request) {
        try {
            $data = $request->validated();
            $result = $this->historyService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }

    public function getHistoryRange(SearchHistoryRange $request) {
        try {
            $result = $this->historyService->getHistoryRange($request->all());
            $response = ['error' => false, 'data' => $result];
            return response()->json($response);
        } catch (\Throwsable $th) {
            // dd($th);
            throw new GetHistoryRangeFailedException('Get History Range Failed : Undefined Error');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'history' => 'required|array',
        ]);

        try {
            foreach($request->history as $id) {
                $this->historyService->delete($id);
            }
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }
        
    }
}
