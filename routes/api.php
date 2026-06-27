<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\TherapyController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\UserManagementController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tidak perlu login)
|--------------------------------------------------------------------------
*/

// Staff login (admin, dokter, terapis)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Patient login & register - PATH HARUS /pasien/ bukan /patients/
Route::post('/pasien/login', [PatientAuthController::class, 'login']);
Route::post('/pasien/register', [PatientAuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Butuh login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // ============================================
    // STAFF ROUTES (Admin, Dokter, Terapis)
    // ============================================
    
    // Staff auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Analytics
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
    
    // Admin only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
    });
    
    // Patients management (staff only)
    Route::middleware('role:admin,dokter,terapis')->prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::post('/', [PatientController::class, 'store']);
        Route::get('/{patient}', [PatientController::class, 'show']);
        Route::put('/{patient}', [PatientController::class, 'update']);
        Route::delete('/{patient}', [PatientController::class, 'destroy']);
    });
    
    // Queues (admin, dokter)
    Route::middleware('role:admin,dokter')->prefix('queues')->group(function () {
        Route::get('/', [QueueController::class, 'index']);
        Route::post('/', [QueueController::class, 'store']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
    });
    
    // Assessments (admin, dokter)
    Route::middleware('role:admin,dokter')->prefix('assessments')->group(function () {
        Route::get('/', [AssessmentController::class, 'index']);
        Route::post('/', [AssessmentController::class, 'store']);
        Route::get('/{assessment}', [AssessmentController::class, 'show']);
    });
    
    // Therapies & Monitoring (admin, dokter, terapis)
    Route::middleware('role:admin,dokter,terapis')->group(function () {
        Route::apiResource('therapies', TherapyController::class);
        Route::apiResource('monitoring', MonitoringController::class);
    });
    
    // ============================================
    // PATIENT ROUTES (Khusus pasien)
    // ============================================
    Route::prefix('pasien')->group(function () {
        // Patient auth - HANYA PatientAuthController
        Route::post('/logout', [PatientAuthController::class, 'logout']);
        Route::get('/user', [PatientAuthController::class, 'user']);
        
        // Patient features
        Route::get('/dashboard', [PatientAuthController::class, 'dashboard']);
        Route::get('/profile', [PatientAuthController::class, 'profile']);
        Route::put('/profile', [PatientAuthController::class, 'updateProfile']);
        Route::post('/booking', [PatientAuthController::class, 'booking']);
        Route::get('/doctors', [PatientAuthController::class, 'getDoctors']);
    });
});