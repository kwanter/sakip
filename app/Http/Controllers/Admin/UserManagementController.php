<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;
use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\Request;
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

        // SECURITY (audit run-3, admin-cross-tenant): a delegated manager
        // only ever sees the accounts of their own institution, plus the
        // unplaced ones they may adopt. Super Admin keeps the system-wide view.
        if (! auth()->user()?->isAdmin()) {
            $instansiId = auth()->user()?->instansi_id;
            $query->where(function ($q) use ($instansiId) {
                $q->where('instansi_id', $instansiId)
                    ->orWhereNull('instansi_id');
            });
        }

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
        $this->authorize('view', $user);

        $user->load(['roles.permissions', 'auditLogs']);
        $roles = Role::all();

        return view('admin.users.show', compact('user', 'roles'));
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();

        // SECURITY (audit run-3, admin-cross-tenant): a delegated manager may
        // only place new accounts into their own institution, so the foreign
        // ones are not offered at all.
        $instansis = auth()->user()?->isAdmin()
            ? Instansi::orderBy('nama_instansi')->get()
            : Instansi::whereKey(auth()->user()?->instansi_id)->get();

        return view('admin.users.create', compact('roles', 'instansis'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'instansi_id' => 'nullable|exists:instansis,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        // SECURITY: a delegated manager may only create accounts inside their
        // own institution (Super Admin keeps cross-instansi authority).
        if (! auth()->user()?->isAdmin()
            && ($validated['instansi_id'] ?? null) !== auth()->user()?->instansi_id) {
            return back()
                ->withInput()
                ->withErrors([
                    'instansi_id' => 'Instansi tujuan harus sesuai dengan instansi Anda.',
                ]);
        }

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
        $this->authorize('update', $user);

        $user->load('roles');
        $roles = Role::all();
        $permissions = Permission::all();
        $instansis = Instansi::orderBy('nama_instansi')->get();

        return view('admin.users.edit', compact('user', 'roles', 'permissions', 'instansis'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

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

        // SECURITY (audit run-3, admin-cross-tenant): a delegated manager may
        // not move an account into another institution (or out of their own).
        // Only Super Admin may retarget someone's institution.
        if (! auth()->user()?->isAdmin() && array_key_exists('instansi_id', $validated)) {
            $ownInstansiId = auth()->user()?->instansi_id;

            if ($validated['instansi_id'] !== $ownInstansiId) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'instansi_id' => 'Instansi tujuan harus sesuai dengan instansi Anda.',
                    ]);
            }
        }

        $this->adminService->updateUser($user, $validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update user roles
     */
    public function updateRoles(UpdateRolesRequest $request, User $user)
    {
        $this->authorize('manageProtectedAccount', $user);
        $this->authorize('view', $user);

        $validated = $request->validated();

        $this->adminService->assignRoles($user, $validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(UpdatePermissionsRequest $request, User $user)
    {
        $this->authorize('manageProtectedAccount', $user);
        $this->authorize('view', $user);

        $validated = $request->validated();

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

        $this->authorize('delete', $user);

        $this->adminService->deleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
