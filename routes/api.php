<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AssessmentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Patient routes
    Route::apiResource('patients', PatientController::class);

    // Queue routes
    Route::get('/queues', [QueueController::class, 'index']);
    Route::post('/queues', [QueueController::class, 'store']);
    Route::get('/queues/{id}', [QueueController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/queues/{id}', [QueueController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::delete('/queues/{id}', [QueueController::class, 'destroy'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::post('/queues/call-next', [QueueController::class, 'callNext']);

    // Assessment routes
    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::post('/assessments', [AssessmentController::class, 'store']);
    Route::get('/assessments/{id_assessment}', [AssessmentController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/assessments/{id_assessment}', [AssessmentController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::delete('/assessments/{id_assessment}', [AssessmentController::class, 'destroy'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::get('/patients/{id_pasien}/latest-assessment', [AssessmentController::class, 'latestByPatient']);
});