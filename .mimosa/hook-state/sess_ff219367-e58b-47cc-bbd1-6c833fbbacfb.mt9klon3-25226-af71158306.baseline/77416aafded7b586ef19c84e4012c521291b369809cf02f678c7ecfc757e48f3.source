<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view-audit-logs');
    }

    /**
     * Display audit logs with filtering
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', 'like', "%{$request->get('action')}%");
        }

        // Filter by user
        if ($request->has('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('user')}%")
                  ->orWhere('email', 'like', "%{$request->get('user')}%");
            });
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.audit-logs', compact('logs'));
    }
}
