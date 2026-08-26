<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
        $this->middleware('can:manage-users');
    }

    /**
     * Display list of users with search and filtering
     */
    public function index(Request $request)
    {
        $query = User::with(['roles.permissions']);

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

    /**
     * Display user details with roles and audit logs
     */
    public function show(User $user)
    {
        $user->load(['roles.permissions', 'auditLogs']);
        $roles = Role::all();

        return view('admin.users.show', compact('user', 'roles'));
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        $roles = Role::all();
        $instansis = \App\Models\Instansi::orderBy('nama_instansi')->get();
        return view('admin.users.create', compact('roles', 'instansis'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'instansi_id' => 'nullable|exists:instansis,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = $this->adminService->createUser($validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    /**
     * Show form to edit user
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        $permissions = Permission::all();
        $instansis = \App\Models\Instansi::orderBy('nama_instansi')->get();

        return view('admin.users.edit', compact('user', 'roles', 'permissions', 'instansis'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'email_verified' => 'sometimes|accepted',
            'instansi_id' => 'nullable|exists:instansis,id',
        ]);

        $this->adminService->updateUser($user, $validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update user roles
     */
    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $this->adminService->assignRoles($user, $validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->adminService->assignPermissions($user, $validated['permissions'] ?? []);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User permissions updated successfully.');
    }

    /**
     * Delete user (with safety check for self-deletion)
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->adminService->deleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
