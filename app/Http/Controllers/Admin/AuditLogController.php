<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'user_id' => ['nullable', 'integer'],
            'action' => ['nullable', 'string', 'max:60'],
            'tab' => ['nullable', 'in:all,today,mutations,security'],
        ]);

        $tab = $request->query('tab', 'all');

        $query = AuditLog::with(['user:id,name,role_id', 'user.role:id,name,display_name'])
            ->when($request->filled('from'), fn ($q) => $q->where('created_at', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('created_at', '<=', Carbon::parse($request->to)->endOfDay()))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', $request->action.'%'));

        if ($tab === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($tab === 'mutations') {
            $query->where(function ($q) {
                $q->whereNotNull('old_values')->orWhereNotNull('new_values');
            });
        } elseif ($tab === 'security') {
            $query->where(function ($q) {
                $q->where('action', 'like', 'settings.%')
                  ->orWhere('action', 'like', 'user.%')
                  ->orWhere('action', 'like', 'role.%')
                  ->orWhere('action', 'like', 'auth.%');
            });
        }

        $logs = $query->latest('id')
            ->paginate(30)
            ->withQueryString();

        $metrics = [
            'totalLogs' => AuditLog::count(),
            'todayLogs' => AuditLog::whereDate('created_at', today())->count(),
            'activeUsers' => AuditLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'stateMutations' => AuditLog::where(function ($q) {
                $q->whereNotNull('old_values')->orWhereNotNull('new_values');
            })->count(),
            'securityLogs' => AuditLog::where(function ($q) {
                $q->where('action', 'like', 'settings.%')
                  ->orWhere('action', 'like', 'user.%')
                  ->orWhere('action', 'like', 'role.%')
                  ->orWhere('action', 'like', 'auth.%');
            })->count(),
        ];

        return view('admin.audit.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'metrics' => $metrics,
            'tab' => $tab,
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        return view('admin.audit.show', ['log' => $auditLog->load(['user.role'])]);
    }
}
