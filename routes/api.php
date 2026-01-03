<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

// Protected Routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    Route::apiResource('services', ServiceController::class);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});

// Login Route
use App\Http\Controllers\Auth\ApiLoginController;

Route::post('/login', [ApiLoginController::class, 'login']);

// Registration Route
use App\Http\Controllers\Auth\RegisteredUserController;

Route::post('/register', [RegisteredUserController::class, 'store']);
