<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaGallery;
use App\Http\Requests\StoreMediaGallery;
use App\Http\Requests\UpdateMediaGallery;
use App\Http\Requests\SearchMediaGallery;
use App\Services\Contracts\MediaGalleryServiceContract as MediaGalleryService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\DeleteDataFailedException;
use App\Exceptions\SearchDataFailedException;

class MediaGalleryController extends Controller
{
    private $mediaGalleryService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(MediaGalleryService $mediaGalleryService)
    {
        $this->mediaGalleryService = $mediaGalleryService;
    }
    public function index(Request $request)
    {
        try {
            $data = $this->mediaGalleryService->search($request->all());
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
    public function store(StoreMediaGallery $request)
    {
        try {
            $data = $request->validated();
            $this->mediaGalleryService->store($data);    
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
    public function update(UpdateMediaGallery $request, MediaGallery $activity)
    {
        try {
            $data = $request->validated();
            $this->mediaGalleryService->update($data, $activity->id);
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
    public function destroy(MediaGallery $media_gallery)
    {
        try {
            $this->mediaGalleryService->delete($media_gallery->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            // throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
            throw $th;
        }
        
    }

    public function search(SearchMediaGallery $request) {
        try {
            $data = $request->validated();
            $result = $this->mediaGalleryService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }
}
