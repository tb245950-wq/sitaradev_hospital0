<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * GET /api/activity-logs
     * Get all activity logs with filters
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['patient', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        // Filter by user role
        if ($request->has('role')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->role);
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Limit for dashboard (recent 10)
        $limit = $request->input('limit', 10);
        
        // If "all" is requested, use pagination
        if ($request->has('all')) {
            $perPage = $request->input('per_page', 20);
            $activities = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        }
        
        $activities = $query->limit($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    /**
     * GET /api/activity-logs/all
     * Get ALL activity logs for "Lihat Semua" feature
     */
    public function allActivities(Request $request)
    {
        $query = ActivityLog::with(['patient', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 20);
        $activities = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
}
