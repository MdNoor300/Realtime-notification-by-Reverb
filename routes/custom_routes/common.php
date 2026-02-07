<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| common Routes
|--------------------------------------------------------------------------
| These routes handle common actions.
*/

Route::middleware('auth:sanctum')
->post('/chat/send', [ChatController::class, 'send']);

// Route::post('login', [AuthController::class, 'login']);
// Route::post('registration', [AuthController::class, 'registration']);
// Route::post('reset-password', [AuthController::class, 'resetPassword']);
// Route::post('send-otp', [VarificationController::class, 'sendOtp']);
// Route::post('verify-otp', [VarificationController::class, 'verifyOtp']);


