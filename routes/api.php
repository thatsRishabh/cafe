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
        // Route::get('unauthorized', 'unauthorized')->name('unauthorized');
        // Route::post('login', 'login')->name('login');
        // Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
        // Route::post('update-password', 'updatePassword')->name('update-password');
        // Route::post('logout', 'logout')->name('logout')->middleware('auth:api');
        // Route::post('change-password', 'changePassword')->name('changePassword')->middleware('auth:api');

        Route::post('user-login', 'login');
         Route::post('user-logout', 'logout')->middleware('auth:api');
    });

    // Route::post('user-login', [App\Http\Controllers\Api\Common\UserLoginController::class, 'login']);


    Route::group(['middleware' => 'auth:api'],function () {
        // Route::controller(FileUploadController::class)->group(function () {
        //     Route::post('file-uploads', 'fileUploads')->name('file-uploads');
        //     Route::post('file-upload', 'store')->name('file-upload');
        // });

        Route::controller(EmployeeController::class)->group(function () {
            Route::post('employees', 'searchEmployee');
            Route::post('employee-update/{id?}', 'update');
            Route::resource('employee', EmployeeController::class)->only([
                'store','destroy','show' ]);
        });

        Route::controller(CustomerController::class)->group(function () {
            Route::post('customers', 'searchCustomer');
            // Route::post('customer-update/{id?}', 'update');
            Route::resource('customer', CustomerController::class)->only([
                'store','destroy','show', 'update' ]);
        });
    });
});


Route::namespace('App\Http\Controllers\Api\Admin')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'admin'],function () {
        
            Route::controller(CafeController::class)->group(function () {
                Route::post('cafes', 'searchCafe');
                Route::post('cafe-update/{id?}', 'update');
                Route::resource('cafe', CafeController::class)->only([
                    'store','destroy','show' ]);
            });
        });
    });
});

Route::namespace('App\Http\Controllers\Api\Cafe')->group(function () {
    Route::group(['middleware' => 'auth:api'],function () {
        Route::group(['middleware' => 'cafe'],function () {

        
        });
    });
});

