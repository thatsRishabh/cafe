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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// User login
Route::post('user-login', [App\Http\Controllers\Api\UserLoginController::class, 'login']);


// Users
// Route::post('users', [App\Http\Controllers\Api\UserController::class, 'searchUser']); 
// Route::resource('user', App\Http\Controllers\Api\UserController::class)->only(['store','destroy','show']);
// Route::post('user-update/{id?}', [App\Http\Controllers\Api\UserController::class, 'update']); 

//  // change-password
// Route::post('change-password', [App\Http\Controllers\UserLoginController::class, 'changePassword']);
Route::middleware('auth:api')->group(function () {
    // User logout 
    Route::post('user-logout', [App\Http\Controllers\Api\UserLoginController::class, 'logout']);

    // change-password
    Route::post('change-password', [App\Http\Controllers\UserLoginController::class, 'changePassword']);

    // forget password
    Route::post('forget-password', [App\Http\Controllers\UserLoginController::class, 'forgetPassword']);

    // Users
    Route::post('users', [App\Http\Controllers\Api\UserController::class, 'searchUser']); 
    Route::resource('user', App\Http\Controllers\Api\UserController::class)->only(['store','destroy','show']);
    Route::post('user-update/{id?}', [App\Http\Controllers\Api\UserController::class, 'update']); 

     // CafeSetting
     Route::post('cafe-settings', [App\Http\Controllers\Api\CafeSettingController::class, 'searchCafeSetting']); 
     Route::resource('cafe-setting', App\Http\Controllers\Api\CafeSettingController::class)->only(['store','destroy','show']);
     Route::post('cafe-setting-update/{id?}', [App\Http\Controllers\Api\CafeSettingController::class, 'update']); 

});