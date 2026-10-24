<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionManagementController extends Controller
{
    /**
     * Gate names consumed by routes/middleware but not seeded (can:view-audit-logs,
     * can:debug, can:manage-system-maintenance). Reserved like the seeded vocabulary:
     * Spatie resolves gate abilities by permission NAME, so these labels are authority.
     */
    private const RESERVED_GATE_PERMISSION_NAMES = [
        'view-audit-logs',
        'debug',
        'manage-system-maintenance',
    ];

    public function __construct()
    {
        $this->middleware('can:manage-permissions');
    }

    /**
     * Reserved permission names: the seeded vocabulary plus route gate names.
     */
    private function reservedPermissionNames(): array
    {
        return array_values(array_unique(array_merge(
            RolesAndPermissionsSeeder::permissionNames(),
            self::RESERVED_GATE_PERMISSION_NAMES,
        )));
    }

    private function abortIfReservedName(string $name, string $action): void
    {
        if (in_array($name, $this->reservedPermissionNames(), true)) {
            abort(403, 'Nama permission "'.$name.'" dilindungi dan tidak dapat '.$action.'.');
        }
    }

    /**
     * Display list of permissions with search
     */
    public function index(Request $request)
    {
        $query = Permission::withCount('roles', 'users');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $permissions = $query->paginate(15);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show form to create new permission
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store new permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
            ],
            'guard_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $request->user()->isAdmin()) {
            $this->abortIfReservedName($validated['name'], 'dibuat');
        }

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display permission details with roles and users
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles', 'users']);

        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show form to edit permission
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update permission
     */
    public function update(Request $request, Permission $permission)
    {
        $user = $request->user();

        // Reserved identities must survive even the Super Admin Gate bypass
        // (same rationale as the reserved role name).
        $this->abortIfReservedName($permission->name, 'diubah');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($permission->id),
            ],
            'guard_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $user->isAdmin()) {
            $this->abortIfReservedName($validated['name'], 'dibuat');

            // Delegation ceiling: renaming a row the actor does not hold would
            // re-label authority the actor never had (Spatie resolves by name).
            if (! in_array($permission->id, $user->getAllPermissions()->modelKeys(), true)) {
                abort(403, 'Anda hanya dapat mengubah permission yang Anda pegang.');
            }
        }

        $permission->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Delete permission
     */
    public function destroy(Request $request, Permission $permission)
    {
        $user = $request->user();

        // Reserved permissions are the gate vocabulary; their removal would
        // cascade-revoke every non-super-admin holder (onDelete FKs).
        $this->abortIfReservedName($permission->name, 'dihapus');

        // In-use integrity: never silently cascade-revoke attached grants.
        if ($permission->roles()->exists() || $permission->users()->exists()) {
            abort(403, 'Permission masih terpasang pada peran atau pengguna dan tidak dapat dihapus.');
        }

        if (! $user->isAdmin() && ! in_array($permission->id, $user->getAllPermissions()->modelKeys(), true)) {
            abort(403, 'Anda hanya dapat menghapus permission yang Anda pegang.');
        }

        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
