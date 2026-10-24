<?php

namespace App\Http\Controllers\Admin;

use App\Constants\SystemRoles;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage-roles');
    }

    /**
     * Display list of roles with search
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users', 'permissions');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show form to create new role
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        abort_if(
            $request->input('name') === SystemRoles::SUPER_ADMIN
                && ! $request->user()->hasRole(SystemRoles::SUPER_ADMIN),
            403,
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $this->authorize('grantPermissions', [User::class, $validated['permissions'] ?? []]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display role details with permissions and users
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show form to edit role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        abort_if(
            ($role->name === SystemRoles::SUPER_ADMIN || $request->input('name') === SystemRoles::SUPER_ADMIN)
                && ! $request->user()->hasRole(SystemRoles::SUPER_ADMIN),
            403,
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'guard_name' => ['nullable', 'string', 'max:255'],
        ]);

        // The reserved name must survive the Super Admin Gate bypass.
        abort_if($role->name === SystemRoles::SUPER_ADMIN && $validated['name'] !== SystemRoles::SUPER_ADMIN, 403);

        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete role (with protection for Super Admin role)
     */
    public function destroy(Role $role)
    {
        if ($role->name === \App\Constants\SystemRoles::SUPER_ADMIN) {
            return back()->with('error', 'Cannot delete Super Admin role.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Role $role)
    {
        abort_if(
            $role->name === SystemRoles::SUPER_ADMIN
                && ! $request->user()->hasRole(SystemRoles::SUPER_ADMIN),
            403,
        );

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $this->authorize('grantPermissions', [User::class, $validated['permissions'] ?? []]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}
