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

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');
    Route::post('reset-password-request', 'UserController@resetPasswordRequest');
    Route::post('reset-password', 'UserController@resetPassword');

    Route::group(['middleware' => 'auth:sanctum'], function() {
        Route::group(['middleware' => 'ability:user,admin'], function() {
            Route::get('profile', 'UserController@profile');
            Route::post('change-password', 'UserController@changePassword');
            Route::post('update-profile', 'UserController@updateProfile');
            Route::get('logout','UserController@logout');
        });
    });
});

Route::group(['prefix' => 'user'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function() {
        Route::group(['middleware' => 'ability:user'], function() {
            Route::resource('posts', 'PostController');
            Route::resource('comments', 'CommentController');
            Route::post('like-unlike-post', 'LikeController@store');
            Route::post('follow-unfollow-user', 'FollowerController@store');
            Route::get('feeds', 'PostController@index');
        });
    });
});
