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

    // Patient CRUD routes (tetap pakai apiResource karena sudah benar)
    Route::apiResource('patients', PatientController::class);

    // Queue routes - MANUAL ROUTES (bukan apiResource)
    Route::get('/queues', [QueueController::class, 'index']);
    Route::post('/queues', [QueueController::class, 'store']);
    Route::get('/queues/{id_antrian}', [QueueController::class, 'show']);
    Route::put('/queues/{id_antrian}', [QueueController::class, 'update']);
    Route::delete('/queues/{id_antrian}', [QueueController::class, 'destroy']);
    Route::post('/queues/call-next', [QueueController::class, 'callNext']);
});