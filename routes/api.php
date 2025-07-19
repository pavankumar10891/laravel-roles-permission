<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\KycController;
use App\Http\Controllers\Api\v1\CommonController;
use App\Http\Controllers\Api\v1\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {
    Route::post('register', [AuthApiController::class, 'register']);
    Route::post('verify-otp', [AuthApiController::class, 'verifyOtp']);
    Route::post('login', [AuthApiController::class, 'login']);
    Route::get('version', [CommonController::class, 'version']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthApiController::class, 'logout']);
        Route::post('send-device-id', [AuthApiController::class, 'updateDeviceId']);
        Route::post('get-user-details', [AuthApiController::class, 'getUserDetailById']);
        Route::post('request/kyc', [KycController::class, 'requestKyc']);
        Route::post('kyc/response',[KycController::class,'responseKyc']);

    });
});