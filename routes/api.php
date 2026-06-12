<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Support\Facades\Route;

// Public routes (tanpa token)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (butuh token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Patient CRUD routes
    Route::apiResource('patients', PatientController::class);

    // Queue routes
    Route::apiResource('queues', QueueController::class);
    Route::post('/queues/call-next', [QueueController::class, 'callNext']);
});