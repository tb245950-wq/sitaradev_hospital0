<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\TherapyController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\PatientAuthController;
use App\Http\Controllers\Api\PatientPortalController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login');

// ===== PATIENT AUTH ROUTES (PUBLIC) =====
Route::post('/pasien/login', [PatientAuthController::class, 'login'])->middleware('throttle:login');
Route::post('/pasien/register', [PatientAuthController::class, 'register'])->middleware('throttle:login');

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // ===== PATIENT PORTAL ROUTES (PROTECTED - Role: pasien) =====
    Route::prefix('pasien')->group(function () {
        Route::post('/logout', [PatientAuthController::class, 'logout']);
        Route::get('/dashboard', [PatientPortalController::class, 'dashboard']);
        Route::get('/profile', [PatientPortalController::class, 'profile']);
        Route::put('/profile', [PatientPortalController::class, 'updateProfile']);
        Route::get('/doctors', [PatientPortalController::class, 'getDoctors']);
        Route::post('/booking', [PatientPortalController::class, 'bookQueue']);
        Route::get('/antrian-saya', [PatientPortalController::class, 'getMyQueues']);
        Route::post('/antrian/{id}/cancel', [PatientPortalController::class, 'cancelQueue']);
        Route::get('/jadwal', [PatientPortalController::class, 'schedule']);
        Route::get('/riwayat', [PatientPortalController::class, 'history']);
    });

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Analytics Routes
    Route::prefix('analytics')->group(function () {
        Route::get('/stats', [AnalyticsController::class, 'getStats']);
        Route::get('/visit-trends', [AnalyticsController::class, 'getVisitTrends']);
        Route::get('/diagnosis-distribution', [AnalyticsController::class, 'getDiagnosisDistribution']);
        Route::get('/recent-activities', [AnalyticsController::class, 'getRecentActivities']);
        Route::get('/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
    });

    // Admin only routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::get('/users/{user}', [UserManagementController::class, 'show']);
        Route::put('/users/{user}', [UserManagementController::class, 'update']);
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus']);
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
    });

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
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->middleware('role:admin,dokter');
    Route::get('/patients/{id_pasien}/latest-assessment', [AssessmentController::class, 'latestByPatient']);
    Route::post('/assessments/{assessment}/submit', [AssessmentController::class, 'submit']);
    Route::post('/assessments/{assessment}/approve', [AssessmentController::class, 'approve']);

    // Therapy
    Route::middleware('role:admin,dokter,terapis')->group(function () {
        Route::get('/therapies', [TherapyController::class, 'index']);
        Route::get('/therapies/{therapy}', [TherapyController::class, 'show']);
    });
    Route::post('/therapies', [TherapyController::class, 'store'])->middleware('role:dokter');
    Route::put('/therapies/{therapy}', [TherapyController::class, 'update'])->middleware('role:admin,dokter,terapis');
    Route::delete('/therapies/{therapy}', [TherapyController::class, 'destroy'])->middleware('role:admin');

    // Monitoring
    Route::middleware('role:admin,dokter,terapis')->group(function () {
        Route::get('/monitorings', [MonitoringController::class, 'index']);
        Route::get('/monitorings/{monitoring}', [MonitoringController::class, 'show']);
        Route::get('/patients/{id_pasien}/progress-stats', [MonitoringController::class, 'progressStats']);
        Route::post('/monitorings/generate-assessment/{id_terapi}', [MonitoringController::class, 'generateAssessment']);
    });
    Route::post('/monitorings', [MonitoringController::class, 'store'])->middleware('role:dokter,terapis');
    Route::put('/monitorings/{monitoring}', [MonitoringController::class, 'update'])->middleware('role:dokter,terapis');

    // Queue
    Route::middleware('role:admin,dokter,terapis')->group(function () {
        Route::get('/queues', [QueueController::class, 'index']);
        Route::post('/queues', [QueueController::class, 'store']);
        Route::get('/queues/{queue}', [QueueController::class, 'show']);
        Route::put('/queues/{queue}', [QueueController::class, 'update']);
        Route::post('/queues/call-next', [QueueController::class, 'callNext']);
    });
    Route::delete('/queues/{queue}', [QueueController::class, 'destroy'])->middleware('role:admin');

    // Reports
    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    });
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->middleware('role:admin,dokter,terapis');
    Route::get('/reports/patients/{id_pasien}', [ReportController::class, 'patientReport'])->middleware('role:admin,dokter,terapis');

    // Activity Logs Routes
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/all', [ActivityLogController::class, 'allActivities']);
});
