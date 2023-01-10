<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\StoreController;
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


Route::namespace('App\Http\Controllers\Api\Common')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('unauthorized', 'unauthorized')->name('unauthorized');
        Route::post('login', 'login')->name('login');
        Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
        Route::post('update-password', 'updatePassword')->name('update-password');
        Route::post('logout', 'logout')->name('logout')->middleware('auth:api');
        Route::post('change-password', 'changePassword')->name('changePassword')->middleware('auth:api');
    });

    Route::group(['middleware' => 'auth:api'],function () {
        Route::controller(FileUploadController::class)->group(function () {
            Route::post('file-uploads', 'fileUploads')->name('file-uploads');
            Route::post('file-upload', 'store')->name('file-upload');
        });
        
    });
});


Route::namespace('App\Http\Controllers\Api\Admin')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'admin'],function () {

            Route::post('cafe',[CafeController::class, 'cafes'])->name('cafes');
            Route::resource('cafe', CafeController::class)->only([
                'store','destroy','show', 'update'
            ]);
        
        });
    });
});

Route::namespace('App\Http\Controllers\Api\Cafe')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'cafe'],function () {

        
        });
    });
});
