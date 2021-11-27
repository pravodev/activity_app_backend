<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\StoreCategory;
use App\Http\Requests\UpdateCategory;
use App\Http\Requests\SearchCategory;
use App\Services\Contracts\CategoryServiceContract as CategoryService;
use App\Exceptions\GetDataFailedException;
use App\Exceptions\StoreDataFailedException;
use App\Exceptions\UpdateDataFailedException;
use App\Exceptions\SearchDataFailedException;
use App\Exceptions\DeleteDataFailedException;

class CategoryController extends Controller
{
    private $categoryService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    public function index(Request $request)
    {
        try {
            $data = $this->categoryService->search($request->all());
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
    public function store(StoreCategory $request)
    {
        try {
            $data = $request->validated();
            $this->categoryService->store($data);    
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
    public function update(UpdateCategory $request, Category $category)
    {
        try {
            $data = $request->validated();
            $this->categoryService->update($data, $category->id);
            $response = ['error' => false, 'message'=>'update data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw $th;
            throw new UpdateDataFailedException('Update Data Failed : Undefined Error');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->delete($category->id);
            $response = ['error' => false, 'message'=>'delete data success !'];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new DeleteDataFailedException('Delete Data Failed : Undefined Error');
        }
        
    }

    public function search(SearchCategory $request) {
        try {
            $data = $request->validated();
            $result = $this->categoryService->search($data);
            $response = ['error' => false, 'data'=> $result];
            return response()->json($response);
        } catch (\Throwable $th) {
            throw new SearchDataFailedException('Search Data Failed : Undefined Error');
        }
    }
}
