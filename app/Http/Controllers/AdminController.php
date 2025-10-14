<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Services\SystemSettingsService;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected $adminService;
    protected $settingsService;
    
    public function __construct(AdminService $adminService, SystemSettingsService $settingsService)
    {
        $this->adminService = $adminService;
        $this->settingsService = $settingsService;
        $this->middleware('can:admin.dashboard');
    }
    
    public function dashboard()
    {
        $stats = $this->adminService->getSystemStats();
        $recentLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
        
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentLogs', 'userGrowth'));
    }
    
    public function users(Request $request)
    {
-        $query = User::with(['roles', 'permissions']);
+        $query = User::with(['roles.permissions']);
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }
        
        $users = $query->paginate(20);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }
    
    public function showUser(User $user)
    {
-        $user->load(['roles', 'permissions', 'auditLogs']);
+        $user->load(['roles.permissions', 'auditLogs']);
        $roles = Role::all();
        
        return view('admin.users.show', compact('user', 'roles'));
    }
    
    public function createUser()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }
    
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        $user = $this->adminService->createUser($validated);
        
        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }
    
    public function editUser(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        
        return view('admin.users.edit', compact('user', 'roles'));
    }
    
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        $this->adminService->updateUser($user, $validated);
        
        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }
    
    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        $this->adminService->deleteUser($user);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');
        
        if ($request->has('action')) {
            $query->where('action', 'like', "%{$request->get('action')}%");
        }
        
        if ($request->has('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('user')}%")
                  ->orWhere('email', 'like', "%{$request->get('user')}%");
            });
        }
        
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }
        
        $logs = $query->latest()->paginate(50);
        
        return view('admin.audit-logs', compact('logs'));
    }
    
    public function systemSettings(Request $request)
    {
        $this->authorize('admin.settings');
        
        $settings = $this->settingsService->getAll();
        
        return view('admin.settings.index', compact('settings'));
    }
    
    public function updateSettings(Request $request)
    {
        $this->authorize('admin.settings');
        
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'required|string|in:string,integer,float,boolean,array,json',
            'settings.*.description' => 'nullable|string',
        ]);
        
        foreach ($validated['settings'] as $setting) {
            $this->settingsService->set(
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['description'] ?? null
            );
        }
        
        $this->adminService->logAction('settings.updated', [
            'settings' => $validated['settings']
        ]);
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}