<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\TherapyController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Patient
    Route::apiResource('patients', PatientController::class);

    // Queue
    Route::get('/queues', [QueueController::class, 'index']);
    Route::post('/queues', [QueueController::class, 'store']);
    Route::get('/queues/{id}', [QueueController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/queues/{id}', [QueueController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::delete('/queues/{id}', [QueueController::class, 'destroy'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::post('/queues/call-next', [QueueController::class, 'callNext']);

    // Assessment
    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::post('/assessments', [AssessmentController::class, 'store']);
    Route::get('/assessments/{id}', [AssessmentController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/assessments/{id}', [AssessmentController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::get('/patients/{id_pasien}/latest-assessment', [AssessmentController::class, 'latestByPatient']);

    // Therapy (FR-08)
    Route::get('/therapies', [TherapyController::class, 'index']);
    Route::post('/therapies', [TherapyController::class, 'store']);
    Route::get('/therapies/{id}', [TherapyController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/therapies/{id}', [TherapyController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::delete('/therapies/{id}', [TherapyController::class, 'destroy'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);

    // Monitoring (FR-09)
    Route::get('/monitorings', [MonitoringController::class, 'index']);
    Route::post('/monitorings', [MonitoringController::class, 'store']);
    Route::get('/monitorings/{id}', [MonitoringController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/monitorings/{id}', [MonitoringController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::get('/patients/{id_pasien}/progress-stats', [MonitoringController::class, 'progressStats']);

    // Reports (FR-10, FR-11, FR-12)
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    Route::get('/reports/patients/{id_pasien}', [ReportController::class, 'patientReport']);
});