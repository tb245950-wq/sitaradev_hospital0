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
    Route::middleware('role:admin,dokter')->group(function () {
        Route::post('/patients', [PatientController::class, 'store']);
        Route::put('/patients/{patient}', [PatientController::class, 'update']);
    });
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->middleware('role:admin');
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);

    // Assessment
    Route::post('/assessments', [AssessmentController::class, 'store'])->middleware('role:dokter');
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->middleware('role:admin');
    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::get('/assessments/{assessment}', [AssessmentController::class, 'show']);
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update']);
    Route::get('/patients/{id_pasien}/latest-assessment', [AssessmentController::class, 'latestByPatient']);

    // Therapy
    Route::get('/therapies', [TherapyController::class, 'index']);
    Route::post('/therapies', [TherapyController::class, 'store'])->middleware('role:dokter');
    Route::get('/therapies/{id}', [TherapyController::class, 'show']);
    Route::put('/therapies/{id}', [TherapyController::class, 'update'])->middleware('role:dokter');
    Route::delete('/therapies/{id}', [TherapyController::class, 'destroy'])->middleware('role:admin');

    // Monitoring
    Route::get('/monitorings', [MonitoringController::class, 'index']);
    Route::post('/monitorings', [MonitoringController::class, 'store'])->middleware('role:dokter,terapis');
    Route::get('/monitorings/{id}', [MonitoringController::class, 'show']);
    Route::put('/monitorings/{id}', [MonitoringController::class, 'update'])->middleware('role:dokter,terapis');
    Route::get('/patients/{id_pasien}/progress-stats', [MonitoringController::class, 'progressStats']);

    // Reports
    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    });
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->middleware('role:admin,dokter,terapis');
    Route::get('/reports/patients/{id_pasien}', [ReportController::class, 'patientReport'])->middleware('role:admin,dokter,terapis');
});