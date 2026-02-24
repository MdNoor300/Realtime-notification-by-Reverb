<?php

use App\Http\Controllers\OdooTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function () {
    return view('chat.index');
});


Route::get('/odoo-test', [OdooTestController::class, 'testConnection']);
