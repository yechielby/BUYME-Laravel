<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'cors'], function() {

    // --------------- [ User Controller Route] ------------
    Route::post('user-registration', 'UserController@registerUser');
    Route::post('user-login', 'UserController@loginUser');
    // -------------------- [ Auth Tokens ]
    Route::group(['middleware' => 'auth:api'], function () {

        Route::get('user-detail', 'UserController@userDetail');

        Route::post('update-user', 'UserController@update');

        Route::delete('delete-user', 'UserController@destroy');

        Route::post('create-task', 'TaskController@createTask');

        Route::get('task-listing', 'TaskController@taskListing');

        Route::get('task-detail/{task_id}', 'TaskController@taskDetail');

        Route::post('share-task', 'TaskController@shareTask');

        Route::post('update-task', 'TaskController@updateTask');

        Route::delete('delete-task/{task_id}', 'TaskController@deleteTask');
    });

});
