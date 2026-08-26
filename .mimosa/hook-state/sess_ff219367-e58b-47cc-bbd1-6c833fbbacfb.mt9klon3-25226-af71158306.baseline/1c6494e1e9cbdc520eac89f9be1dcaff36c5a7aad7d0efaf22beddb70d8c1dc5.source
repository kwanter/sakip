<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Services\SystemSettingsService;
use App\Services\DropdownCacheService;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected $adminService;
    protected $settingsService;
    protected $dropdownCache;

    public function __construct(
        AdminService $adminService,
        SystemSettingsService $settingsService,
        DropdownCacheService $dropdownCache,
    ) {
        $this->adminService = $adminService;
        $this->settingsService = $settingsService;
        $this->dropdownCache = $dropdownCache;
        $this->middleware("can:manage-high-level-settings");
    }

    public function dashboard()
    {
        $stats = $this->adminService->getSystemStats();
        $recentLogs = AuditLog::with("user")->latest()->limit(10)->get();

        $userGrowth = User::selectRaw(
            "DATE(created_at) as date, COUNT(*) as count",
        )
            ->where("created_at", ">", now()->subDays(30))
            ->groupBy("date")
            ->orderBy("date")
            ->get();

        return view(
            "admin.dashboard",
            compact("stats", "recentLogs", "userGrowth"),
        );
    }

    public function users(Request $request)
    {
        $query = User::with(["roles.permissions"]);

        if ($request->has("search")) {
            $search = $request->get("search");
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")->orWhere(
                    "email",
                    "like",
                    "%{$search}%",
                );
            });
        }

        if ($request->has("role")) {
            $query->whereHas("roles", function ($q) use ($request) {
                $q->where("name", $request->get("role"));
            });
        }

        $users = $query->paginate(20);
        $roles = Role::all();

        return view("admin.users.index", compact("users", "roles"));
    }

    public function showUser(User $user)
    {
        $this->authorize('view', $user);

        $user->load(["roles.permissions", "auditLogs"]);
        $roles = Role::all();

        return view("admin.users.show", compact("user", "roles"));
    }

    public function createUser()
    {
        $roles = Role::all();
        $instansis = $this->dropdownCache->getActiveInstansi();
        return view("admin.users.create", compact("roles", "instansis"));
    }

    public function storeUser(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = $this->adminService->createUser($validated);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User created successfully.");
    }

    public function editUser(User $user)
    {
        $this->authorize('update', $user);

        $user->load("roles");
        $roles = Role::all();
        $permissions = Permission::all();
        $instansis = $this->dropdownCache->getActiveInstansi();

        return view(
            "admin.users.edit",
            compact("user", "roles", "permissions", "instansis"),
        );
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $this->adminService->updateUser($user, $validated);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User updated successfully.");
    }

    /**
     * Update user roles separately
     */
    public function updateRoles(UpdateRolesRequest $request, User $user)
    {
        $validated = $request->validated();

        $this->adminService->assignRoles($user, $validated["roles"] ?? []);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User roles updated successfully.");
    }

    /**
     * Update user permissions separately
     */
    public function updatePermissions(UpdatePermissionsRequest $request, User $user)
    {
        $validated = $request->validated();

        $this->adminService->assignPermissions(
            $user,
            $validated["permissions"] ?? [],
        );

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User permissions updated successfully.");
    }

    public function destroyUser(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route("admin.users.index")
                ->with("error", "You cannot delete your own account.");
        }

        $this->adminService->deleteUser($user);

        return redirect()
            ->route("admin.users.index")
            ->with("success", "User deleted successfully.");
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with("user");

        if ($request->has("action")) {
            $query->where("action", "like", "%{$request->get("action")}%");
        }

        if ($request->has("user")) {
            $query->whereHas("user", function ($q) use ($request) {
                $q->where("name", "like", "%{$request->get("user")}%")->orWhere(
                    "email",
                    "like",
                    "%{$request->get("user")}%",
                );
            });
        }

        if ($request->has("date_from")) {
            try {
                $dateFrom = \Carbon\Carbon::parse($request->get("date_from"))->startOfDay();
                $query->where("created_at", ">=", $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        if ($request->has("date_to")) {
            try {
                $dateTo = \Carbon\Carbon::parse($request->get("date_to"))->endOfDay();
                $query->where("created_at", "<=", $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }

        $logs = $query->latest()->paginate(50);

        return view("admin.audit-logs", compact("logs"));
    }

    /**
     * Display the system settings page.
     * Returns raw SystemSetting models so Blade can access key/type/value/description safely.
     */
    public function systemSettings(Request $request)
    {
        $this->authorize("manage-high-level-settings");

        $settings = \App\Models\SystemSetting::all();

        return view("admin.settings.index", compact("settings"));
    }

    public function updateSettings(UpdateSystemSettingsRequest $request)
    {
        $this->authorize("manage-high-level-settings");

        $validated = $request->validated();

        foreach ($validated["settings"] as $setting) {
            $this->settingsService->set(
                $setting["key"],
                $setting["value"],
                $setting["type"],
                $setting["description"] ?? null,
            );
        }

        $this->adminService->logAction("settings.updated", [
            "settings" => $validated["settings"],
        ]);

        // Clear dropdown cache when settings change
        $this->dropdownCache->clearCache();

        return redirect()
            ->route("admin.settings.index")
            ->with("success", "Settings updated successfully.");
    }


    // =====================================================
    // ROLE MANAGEMENT
    // =====================================================

    public function roles(Request $request)
    {
        $query = Role::withCount("users", "permissions");

        if ($request->has("search")) {
            $search = $request->get("search");
            $query->where("name", "like", "%{$search}%");
        }

        $roles = $query->paginate(15);

        return view("admin.roles.index", compact("roles"));
    }

    public function createRole()
    {
        $permissions = Permission::all();
        return view("admin.roles.create", compact("permissions"));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255", "unique:roles,name"],
            "guard_name" => ["nullable", "string", "max:255"],
            "permissions" => ["nullable", "array"],
            "permissions.*" => ["exists:permissions,id"],
        ]);

        $role = Role::create([
            "name" => $validated["name"],
            "guard_name" => $validated["guard_name"] ?? "web",
        ]);

        if (!empty($validated["permissions"])) {
            $role->syncPermissions($validated["permissions"]);
        }

        return redirect()
            ->route("admin.roles.index")
            ->with("success", "Role created successfully.");
    }

    public function showRole(Role $role)
    {
        $role->load(["permissions", "users"]);
        return view("admin.roles.show", compact("role"));
    }

    public function editRole(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck("id")->toArray();

        return view(
            "admin.roles.edit",
            compact("role", "permissions", "rolePermissions"),
        );
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("roles")->ignore($role->id),
            ],
            "guard_name" => ["nullable", "string", "max:255"],
        ]);

        $role->update([
            "name" => $validated["name"],
            "guard_name" => $validated["guard_name"] ?? "web",
        ]);

        return redirect()
            ->route("admin.roles.index")
            ->with("success", "Role updated successfully.");
    }

    public function destroyRole(Role $role)
    {
        // Prevent deletion of Super Admin role
        if ($role->name === "Super Admin") {
            return redirect()
                ->route("admin.roles.index")
                ->with("error", "Cannot delete Super Admin role.");
        }

        $role->delete();

        return redirect()
            ->route("admin.roles.index")
            ->with("success", "Role deleted successfully.");
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            "permissions" => ["nullable", "array"],
            "permissions.*" => ["exists:permissions,id"],
        ]);

        $role->syncPermissions($validated["permissions"] ?? []);

        return redirect()
            ->route("admin.roles.show", $role)
            ->with("success", "Role permissions updated successfully.");
    }

    // =====================================================
    // PERMISSION MANAGEMENT
    // =====================================================

    public function permissions(Request $request)
    {
        $query = Permission::withCount("roles", "users");

        if ($request->has("search")) {
            $search = $request->get("search");
            $query->where("name", "like", "%{$search}%");
        }

        $permissions = $query->paginate(15);

        return view("admin.permissions.index", compact("permissions"));
    }

    public function createPermission()
    {
        return view("admin.permissions.create");
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                "unique:permissions,name",
            ],
            "guard_name" => ["nullable", "string", "max:255"],
        ]);

        Permission::create([
            "name" => $validated["name"],
            "guard_name" => $validated["guard_name"] ?? "web",
        ]);

        return redirect()
            ->route("admin.permissions.index")
            ->with("success", "Permission created successfully.");
    }

    public function showPermission(Permission $permission)
    {
        $permission->load(["roles", "users"]);
        return view("admin.permissions.show", compact("permission"));
    }

    public function editPermission(Permission $permission)
    {
        return view("admin.permissions.edit", compact("permission"));
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("permissions")->ignore($permission->id),
            ],
            "guard_name" => ["nullable", "string", "max:255"],
        ]);

        $permission->update([
            "name" => $validated["name"],
            "guard_name" => $validated["guard_name"] ?? "web",
        ]);

        return redirect()
            ->route("admin.permissions.index")
            ->with("success", "Permission updated successfully.");
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();

        return redirect()
            ->route("admin.permissions.index")
            ->with("success", "Permission deleted successfully.");
    }
}
