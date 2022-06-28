<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function(){

    Route::post('/activities/search', 'ActivityController@search');

    Route::get('/activities/getUsingMonthYear/{month}/{year}', 'ActivityController@getUsingMonthYear');
    Route::get('/activities/getDailyUsingMonthYear', 'ActivityController@getDailyUsingMonthYear');

    Route::patch('/activities/updatePosition', 'ActivityController@updatePosition');

    Route::post('/histories/search', 'HistoryController@search');

    Route::get('/histories/getHistoryRange', 'HistoryController@getHistoryRange');

    Route::post('/histories/bulkStore', 'HistoryController@bulkStore');

    Route::post('/activities/importActivitiesForStudents', 'ActivityController@import');


    Route::resource('activities', 'ActivityController')->except([
        'create', 'show'
    ]);
    Route::post('activities/{activity}', 'ActivityController@update');

    Route::post('/histories/bulkDelete', 'HistoryController@bulkDelete');
    Route::resource('histories', 'HistoryController')->except([
        'create', 'show'
    ]);

    Route::get('/setting', 'SettingController@index');
    Route::post('/setting', 'SettingController@save');

    Route::resource('categories', 'CategoryController')->except([
        'create', 'show'
    ]);

    Route::resource('media-galleries', 'MediaGalleryController')->except([
        'create', 'show'
    ]);

    Route::resource('application-logs', 'ApplicationLogController')->except([
        'create', 'show'
    ]);

    Route::post('/point-transactions/bulkDelete', 'PointTransactionController@bulkDelete');
    Route::resource('point-transactions', 'PointTransactionController')->except([
        'create', 'show'
    ]);

    Route::delete('/point-focus/{id}', 'PointFocusController@delete');

    Route::get('/auth/profile', 'AuthController@getProfile');
    Route::patch('/auth/profile', 'AuthController@updateProfile');
    Route::patch('/auth/updateParentEmail', 'AuthController@updateParentEmail');
    Route::get('/auth/getAllStudents', 'AuthController@getAllStudents');
    Route::get('/auth/getDetailStudent/{id}', 'AuthController@getDetailStudent');
});

Route::get('/google/redirect', 'GoogleController@redirect');
Route::post('/google/callback', 'GoogleController@callback');
