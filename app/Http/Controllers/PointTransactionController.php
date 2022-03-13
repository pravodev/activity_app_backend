<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PointTransaction;
use App\Http\Requests\StorePointTransaction;
use App\Http\Requests\BulkStorePointTransaction;
use App\Http\Requests\UpdatePointTransaction;
use App\Http\Requests\SearchPointTransaction;
use App\Http\Requests\SearchPointTransactionRange;
use App\Services\Contracts\PointTransactionServiceContract as PointTransactionService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\DeleteDataFailedException;
use App\Exceptions\SearchDataFailedException;
use App\Exceptions\GetPointTransactionRangeFailedException;


class PointTransactionController extends Controller
{
    private $pointTransactionService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(PointTransactionService $pointTransactionService)
    {
        $this->pointTransactionService = $pointTransactionService;
    }
    public function index()
    {
        try {
            $data = $this->pointTransactionService->get();
            $response = ['error' => false, 'data'=>$data];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw $th;
            throw new GetDataFailedException('Get Data Failed : Undefined Error');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePointTransaction $request)
    {
        try {
            $data = $request->validated();
            $this->pointTransactionService->store($data);
            $response = ['error' => false, 'message'=>'create data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw $th;
            throw new StoreDataFailedException('Store Data Failed : Undefined Error');
        }


    }

    /**
     * Store bulk pointTransaction
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(BulkStorePointTransaction $request)
    {
        try {
            $data = $request->validated();
            $this->pointTransactionService->storeBulk($data);
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
    public function update(UpdatePointTransaction $request, PointTransaction $pointTransaction)
    {
        try {
            $data = $request->validated();
            $this->pointTransactionService->update($data, $pointTransaction->id);
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
    public function destroy(PointTransaction $pointTransaction)
    {
        try {
            $this->pointTransactionService->delete($pointTransaction->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }

    }

    public function search(SearchPointTransaction $request) {
        try {
            $data = $request->validated();
            $result = $this->pointTransactionService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            // dd($th);
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }

    public function getPointTransactionRange(SearchPointTransactionRange $request) {
        try {
            $result = $this->pointTransactionService->getPointTransactionRange($request->all());
            $response = ['error' => false, 'data' => $result];
            return response()->json($response);
        } catch (\Throwsable $th) {
            // dd($th);
            throw new GetPointTransactionRangeFailedException('Get PointTransaction Range Failed : Undefined Error');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'pointTransaction' => 'required|array',
        ]);

        try {
            foreach($request->pointTransaction as $id) {
                $this->pointTransactionService->delete($id);
            }
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }

    }
}
