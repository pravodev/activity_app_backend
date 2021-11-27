<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-timespeed', function() {
    // format example : 1h 34m 33s 00ms
    $split = explode(' ', request()->value);
    $passes = null;

    if(count($split) > 4) {
        $passes = false;
        dd('here');
    } else if(!strpos('h', $split[0]) || 
        !strpos('m', $split[1]) ||
        !strpos('s', $split[2]) ||
        !strpos('ms', $split[3])) {
            $passes = false;
    } else {
        $passes= true;
    }
dd($passes);
});

Route::get('generate', function (){
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    echo 'ok';
});