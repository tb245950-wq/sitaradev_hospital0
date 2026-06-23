<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, AnalyticsController, PatientController, QueueController, AssessmentController, TherapyController, MonitoringController, UserManagementController};

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Analytics
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [UserManagementController::class, 'index']);
            Route::post('/users', [UserManagementController::class, 'store']);
        });
    });
    
    // Staff routes: Patients
    Route::middleware('role:admin,dokter,terapis')->prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::post('/', [PatientController::class, 'store']);
        Route::get('/{patient}', [PatientController::class, 'show']);
        Route::put('/{patient}', [PatientController::class, 'update']);
        Route::delete('/{patient}', [PatientController::class, 'destroy']);
    });
    
    // Staff routes: Queues
    Route::middleware('role:admin,dokter')->prefix('queues')->group(function () {
        Route::get('/', [QueueController::class, 'index']);
        Route::post('/', [QueueController::class, 'store']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
    });
    
    // Staff routes: Assessment
    Route::middleware('role:admin,dokter')->prefix('assessments')->group(function () {
        Route::get('/', [AssessmentController::class, 'index']);
        Route::post('/', [AssessmentController::class, 'store']);
        Route::get('/{assessment}', [AssessmentController::class, 'show']);
    });
    
    // Staff routes: Therapy & Monitoring
    Route::middleware('role:admin,dokter,terapis')->group(function () {
        Route::apiResource('therapies', TherapyController::class);
        Route::apiResource('monitoring', MonitoringController::class);
    });
});
