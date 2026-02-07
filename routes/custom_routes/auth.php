<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VarificationController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
| These routes handle Auth access and registration.
*/


Route::post('login', [AuthController::class, 'login']);
Route::post('registration', [AuthController::class, 'registration']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('send-otp', [VarificationController::class, 'sendOtp']);
Route::post('verify-otp', [VarificationController::class, 'verifyOtp']);

