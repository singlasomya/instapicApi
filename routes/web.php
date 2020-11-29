<?php

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

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::prefix('instapic')->group(function($request) {
    //register user
    Route::post('/ajax/register', 'AjaxController@register');
    Route::post('/ajax/login', 'AjaxController@login');
    Route::post('/ajax/logout', 'AjaxController@logout');
    Route::post('/ajax/upload', 'AjaxController@upload');
    Route::post('/ajax/getUploadImages', 'AjaxController@getUploadImages');
});
