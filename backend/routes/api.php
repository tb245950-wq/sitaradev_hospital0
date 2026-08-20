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
use App\Http\Controllers\Api\PasswordResetController;

/*
|--------------------------------------------------------------------------
| HEALTH CHECK (Render.com / load balancer ping)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tidak perlu login)
|--------------------------------------------------------------------------
*/

// Staff login (admin, dokter, terapis)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Staff forgot password (tidak perlu login)
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);

// Patient login & register - PATH HARUS /pasien/ bukan /patients/
Route::post('/pasien/login', [PatientAuthController::class, 'login']);
Route::post('/pasien/register', [PatientAuthController::class, 'register']);
Route::post('/pasien/forgot-password', [PatientAuthController::class, 'forgotPassword']);

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
        Route::get('/activity-logs', [SuperAdminController::class, 'getActivityLogs']);
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

        // Backup
        Route::get('/backups', [SuperAdminController::class, 'getBackups']);
        Route::post('/backup', [SuperAdminController::class, 'createBackup']);
        Route::get('/backups/{filename}/download', [SuperAdminController::class, 'downloadBackup']);
        Route::get('/export/csv', [SuperAdminController::class, 'exportToCSV']);

        // Settings
        Route::get('/settings', [SuperAdminController::class, 'getSettings']);
        Route::post('/settings', [SuperAdminController::class, 'saveSettings']);
    });
    
    // Poli — READ ONLY untuk staff (admin, dokter, terapis)
    Route::middleware('role:admin,dokter,terapis')->get('/polis', [PoliController::class, 'index']);

    // ============================================
    // ADMIN KLINIK: User Management (dokter, terapis)
    // Admin klinik bisa kelola akun dokter & terapis (bukan super_admin)
    // ============================================
    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/',                         [UserManagementController::class, 'index']);
        Route::post('/',                        [UserManagementController::class, 'store']);
        Route::get('/{user}',                   [UserManagementController::class, 'show']);
        Route::put('/{user}',                   [UserManagementController::class, 'update']);
        Route::delete('/{user}',                [UserManagementController::class, 'destroy']);
        Route::patch('/{user}/status',          [UserManagementController::class, 'updateStatus']);
        Route::post('/{user}/reset-password',   [UserManagementController::class, 'resetPassword']);
    });

    // Admin: daftar pasien + riwayat login (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/patients-accounts',  [UserManagementController::class, 'getPatients']);
        Route::get('/login-history',      [UserManagementController::class, 'getLoginHistory']);
    });
    
    // Patients management (staff only)
    Route::middleware('role:admin,dokter,terapis')->prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::post('/', [PatientController::class, 'store']);
        Route::get('/{patient}', [PatientController::class, 'show']);
        Route::put('/{patient}', [PatientController::class, 'update']);
        Route::delete('/{patient}', [PatientController::class, 'destroy']);
        Route::get('/{patient}/latest-assessment', [PatientController::class, 'latestAssessment']);
        Route::get('/{patient}/progress-stats', [PatientController::class, 'progressStats']);
    });
    
    // Queues (admin, dokter, terapis — semua staff)
    Route::middleware('role:admin,dokter,terapis')->prefix('queues')->group(function () {
        Route::get('/stats', [QueueController::class, 'stats']);
        Route::get('/', [QueueController::class, 'index']);
        Route::post('/', [QueueController::class, 'store']);
        Route::put('/{queue}', [QueueController::class, 'update']);
        Route::delete('/{queue}', [QueueController::class, 'destroy']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
        // Endpoint khusus selesaikan antrian (dokter & terapis bisa akses)
        Route::post('/{queue}/complete', [QueueController::class, 'completeQueue']);
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
        Route::apiResource('monitorings', MonitoringController::class);
        
        // Generate Monitoring Report PDF
        Route::get('/monitorings/{id_pasien}/report-pdf', [MonitoringController::class, 'generateMonitoringReportPdf']);
        Route::get('/monitorings/{id_pasien}/{id_terapi}/report-pdf', [MonitoringController::class, 'generateMonitoringReportPdf']);
    });

    // Reports (admin, dokter bisa akses daily; semua staff bisa dashboard)
    Route::prefix('reports')->group(function () {
        Route::middleware('role:admin,dokter')->group(function () {
            Route::get('/daily', [\App\Http\Controllers\Api\ReportController::class, 'daily']);
            Route::get('/monthly', [\App\Http\Controllers\Api\ReportController::class, 'monthly']);
            Route::get('/patient/{id_pasien}', [\App\Http\Controllers\Api\ReportController::class, 'patientReport']);
        });
        Route::middleware('role:admin,dokter,terapis')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\ReportController::class, 'dashboard']);
        });
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
        Route::get('/profile-status', [PatientAuthController::class, 'profileStatus']);

        // Upload foto KTP & profil
        Route::post('/upload/ktp',    [\App\Http\Controllers\Api\PatientUploadController::class, 'uploadKtp']);
        Route::post('/upload/avatar', [\App\Http\Controllers\Api\PatientUploadController::class, 'uploadAvatar']);
        Route::get('/ktp-status',     [\App\Http\Controllers\Api\PatientUploadController::class, 'ktpStatus']);
        Route::post('/booking', [PatientAuthController::class, 'booking']);
        Route::get('/doctors', [PatientAuthController::class, 'getDoctors']);
        Route::get('/polis', [PoliController::class, 'aktif']);
        Route::get('/antrian-saya', [PatientAuthController::class, 'getMyQueues']);
        Route::get('/jadwal-terapi', [PatientAuthController::class, 'getTherapySchedule']);
        Route::get('/riwayat-medis', [PatientAuthController::class, 'getMedicalHistory']);
    });
});