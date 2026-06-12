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
    Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy'])->middleware('role:admin');
    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::get('/assessments/{id}', [AssessmentController::class, 'show'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::put('/assessments/{id}', [AssessmentController::class, 'update'])->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::get('/patients/{id_pasien}/latest-assessment', [AssessmentController::class, 'latestByPatient']);

    // Reports
    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    });
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->middleware('role:admin,dokter,terapis');
    Route::get('/reports/patients/{id_pasien}', [ReportController::class, 'patientReport'])->middleware('role:admin,dokter,terapis');
});