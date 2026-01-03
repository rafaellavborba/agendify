<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\ApiLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [RegisteredUserController::class, 'store']);
// Protected Routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('appointments', AppointmentController::class);

    Route::post('/logout', [ApiLoginController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
