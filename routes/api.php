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
use App\Http\Controllers\Api\PoliController;
use App\Http\Controllers\Api\PatientPortalController;
use App\Http\Controllers\Api\SuperAdminController;

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
    
    // ============================================
    // SUPER ADMIN ROUTES (System Management)
    // ============================================
    // SUPER ADMIN ONLY ROUTES
    // ============================================
    Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
        // Dashboard stats
        Route::get('/dashboard', [SuperAdminController::class, 'getDashboardStats']);
        
        // Audit & Security Logs
        Route::get('/audit-logs', [SuperAdminController::class, 'getAuditLogs']);
        Route::get('/login-history', [SuperAdminController::class, 'getLoginHistory']);
        Route::get('/failed-logins', [SuperAdminController::class, 'getFailedLogins']);
        
        // User Management
        Route::get('/users', [SuperAdminController::class, 'getUsers']);
        Route::post('/users', [SuperAdminController::class, 'createUser']);
        Route::put('/users/{userToUpdate}', [SuperAdminController::class, 'updateUser']);
        Route::delete('/users/{userToDelete}', [SuperAdminController::class, 'deleteUser']);
        Route::post('/users/{userToReset}/reset-password', [SuperAdminController::class, 'resetUserPassword']);
        
        // Poli Management - Full CRUD
        Route::get('/polis', [SuperAdminController::class, 'getPolis']);
        Route::post('/polis', [PoliController::class, 'store']);
        Route::put('/polis/{poli}', [PoliController::class, 'update']);
        Route::delete('/polis/{poli}', [PoliController::class, 'destroy']);
    });
    
    // Poli — READ ONLY untuk staff (admin, dokter, terapis)
    Route::middleware('role:admin,dokter,terapis')->get('/polis', [PoliController::class, 'index']);

    // HAPUS: /admin/users - User management dipindah ke /super-admin/users
    
    // Patients management (staff only)
    Route::middleware('role:admin,dokter,terapis')->prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::post('/', [PatientController::class, 'store']);
        Route::get('/{patient}', [PatientController::class, 'show']);
        Route::put('/{patient}', [PatientController::class, 'update']);
        Route::delete('/{patient}', [PatientController::class, 'destroy']);
    });
    
    // Queues (admin, dokter, terapis — semua staff)
    Route::middleware('role:admin,dokter,terapis')->prefix('queues')->group(function () {
        Route::get('/stats', [QueueController::class, 'stats']);
        Route::get('/', [QueueController::class, 'index']);
        Route::post('/', [QueueController::class, 'store']);
        Route::put('/{queue}', [QueueController::class, 'update']);
        Route::delete('/{queue}', [QueueController::class, 'destroy']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
    });
    
    // Assessments (admin, dokter)
    Route::middleware('role:admin,dokter')->prefix('assessments')->group(function () {
        Route::get('/',                         [AssessmentController::class, 'index']);
        Route::post('/',                        [AssessmentController::class, 'store']);
        Route::get('/{assessment}',             [AssessmentController::class, 'show']);
        Route::put('/{assessment}',             [AssessmentController::class, 'update']);
        Route::delete('/{assessment}',          [AssessmentController::class, 'destroy']);
        Route::post('/{assessment}/submit',     [AssessmentController::class, 'submit']);
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
        Route::get('/polis', [PoliController::class, 'aktif']);
        Route::get('/antrian-saya', [PatientAuthController::class, 'getMyQueues']);
        Route::get('/jadwal-terapi', [PatientAuthController::class, 'getTherapySchedule']);
        Route::get('/riwayat-medis', [PatientAuthController::class, 'getMedicalHistory']);
    });
});