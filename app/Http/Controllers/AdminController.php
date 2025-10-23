<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Services\SystemSettingsService;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected $adminService;
    protected $settingsService;

    public function __construct(
        AdminService $adminService,
        SystemSettingsService $settingsService,
    ) {
        $this->adminService = $adminService;
        $this->settingsService = $settingsService;
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
        $user->load(["roles.permissions", "auditLogs"]);
        $roles = Role::all();

        return view("admin.users.show", compact("user", "roles"));
    }

    public function createUser()
    {
        $roles = Role::all();
        return view("admin.users.create", compact("roles"));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|confirmed",
            "roles" => "array",
            "roles.*" => "exists:roles,id",
        ]);

        $user = $this->adminService->createUser($validated);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User created successfully.");
    }

    public function editUser(User $user)
    {
        $user->load("roles");
        $roles = Role::all();
        $permissions = Permission::all();

        return view(
            "admin.users.edit",
            compact("user", "roles", "permissions"),
        );
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("users")->ignore($user->id),
            ],
            "password" => "nullable|string|min:8|confirmed",
            "email_verified" => "sometimes|accepted",
        ]);

        $this->adminService->updateUser($user, $validated);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User updated successfully.");
    }

    /**
     * Update user roles separately
     */
    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            "roles" => "array",
            "roles.*" => "exists:roles,id",
        ]);

        $this->adminService->assignRoles($user, $validated["roles"] ?? []);

        return redirect()
            ->route("admin.users.show", $user)
            ->with("success", "User roles updated successfully.");
    }

    /**
     * Update user permissions separately
     */
    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            "permissions" => "array",
            "permissions.*" => "exists:permissions,id",
        ]);

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
            $query->where("created_at", ">=", $request->get("date_from"));
        }

        if ($request->has("date_to")) {
            $query->where(
                "created_at",
                "<=",
                $request->get("date_to") . " 23:59:59",
            );
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

    public function updateSettings(Request $request)
    {
        $this->authorize("manage-high-level-settings");

        $rules = [
            "settings" => "required|array",
            "settings.*.key" => "required|string",
            "settings.*.value" => "required",
            "settings.*.type" =>
                "required|string|in:string,integer,float,boolean,array,json",
            "settings.*.description" => "nullable|string",
            // Specific application settings constraints
            "settings.app\.name.value" => "sometimes|required|string|max:150",
            "settings.app\.description.value" =>
                "sometimes|nullable|string|max:500",
        ];

        $validated = $request->validate($rules);

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

        return redirect()
            ->route("admin.settings.index")
            ->with("success", "Settings updated successfully.");
    }

    /**
     * Clear application caches (unified maintenance action).
     */
    public function clearCache()
    {
        $this->authorize("manage-high-level-settings");
        try {
            \Artisan::call("cache:clear");
            \Artisan::call("config:clear");
            \Artisan::call("route:clear");
            \Artisan::call("view:clear");

            return response()->json([
                "success" => true,
                "message" => "Cache cleared successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to clear cache: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Optimize application (unified maintenance action).
     */
    public function optimizeApp()
    {
        $this->authorize("manage-high-level-settings");
        try {
            \Artisan::call("optimize");
            \Artisan::call("config:cache");
            \Artisan::call("route:cache");
            \Artisan::call("view:cache");

            return response()->json([
                "success" => true,
                "message" => "Application optimized successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Failed to optimize application: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Backup database based on detected database engine (unified maintenance action).
     */
    public function backupDatabase()
    {
        $this->authorize("manage-high-level-settings");
        try {
            $connection = config("database.default");
            $config = config("database.connections.{$connection}");
            $driver = $config["driver"];

            $backupPath = storage_path("app/backups");
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $timestamp = date("Y-m-d_H-i-s");
            $filename = "backup_{$timestamp}";

            switch ($driver) {
                case "mysql":
                case "mariadb":
                    $result = $this->backupMySQL(
                        $config,
                        $backupPath,
                        $filename,
                    );
                    break;
                case "pgsql":
                    $result = $this->backupPostgreSQL(
                        $config,
                        $backupPath,
                        $filename,
                    );
                    break;
                case "sqlite":
                    $result = $this->backupSQLite(
                        $config,
                        $backupPath,
                        $filename,
                    );
                    break;
                case "sqlsrv":
                    $result = $this->backupSQLServer(
                        $config,
                        $backupPath,
                        $filename,
                    );
                    break;
                default:
                    throw new \Exception(
                        "Database driver '{$driver}' not supported for backup.",
                    );
            }

            return response()->json([
                "success" => true,
                "message" => "Database backup created! File: {$result["filename"]}",
                "file_path" => $result["path"],
                "file_size" => $result["size"],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to create backup: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    // Helper methods for database backup
    private function backupMySQL($config, $backupPath, $filename)
    {
        $host = $config["host"];
        $port = $config["port"];
        $database = $config["database"];
        $username = $config["username"];
        $password = $config["password"];

        $backupFile = $backupPath . "/" . $filename . ".sql";

        $command = "mysqldump --host={$host} --port={$port} --user={$username}";
        if ($password) {
            $command .= " --password={$password}";
        }
        $command .= " --single-transaction --routines --triggers {$database} > {$backupFile}";

        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new \Exception(
                "Failed to run mysqldump. Ensure mysqldump is installed.",
            );
        }

        return [
            "filename" => $filename . ".sql",
            "path" => $backupFile,
            "size" => $this->formatBytes(filesize($backupFile)),
        ];
    }

    private function backupPostgreSQL($config, $backupPath, $filename)
    {
        $host = $config["host"];
        $port = $config["port"];
        $database = $config["database"];
        $username = $config["username"];
        $password = $config["password"];

        $backupFile = $backupPath . "/" . $filename . ".sql";

        if ($password) {
            putenv("PGPASSWORD={$password}");
        }

        $command = "pg_dump --host={$host} --port={$port} --username={$username} --format=plain --no-owner --no-acl {$database} > {$backupFile}";

        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new \Exception(
                "Failed to run pg_dump. Ensure PostgreSQL client is installed.",
            );
        }

        return [
            "filename" => $filename . ".sql",
            "path" => $backupFile,
            "size" => $this->formatBytes(filesize($backupFile)),
        ];
    }

    private function backupSQLite($config, $backupPath, $filename)
    {
        $databasePath = $config["database"];
        if (!file_exists($databasePath)) {
            throw new \Exception("SQLite database file not found.");
        }

        $backupFile = $backupPath . "/" . $filename . ".sqlite";
        if (!copy($databasePath, $backupFile)) {
            throw new \Exception("Failed to copy SQLite database file.");
        }

        return [
            "filename" => $filename . ".sqlite",
            "path" => $backupFile,
            "size" => $this->formatBytes(filesize($backupFile)),
        ];
    }

    private function backupSQLServer($config, $backupPath, $filename)
    {
        $host = $config["host"];
        $database = $config["database"];
        $username = $config["username"];
        $password = $config["password"];

        $backupFile = $backupPath . "/" . $filename . ".bak";

        $command = "sqlcmd -S {$host} -U {$username} -P {$password} -Q \"BACKUP DATABASE [{$database}] TO DISK = '{$backupFile}'\"";

        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new \Exception(
                "Failed to run SQL Server backup. Ensure sqlcmd is installed.",
            );
        }

        return [
            "filename" => $filename . ".bak",
            "path" => $backupFile,
            "size" => $this->formatBytes(filesize($backupFile)),
        ];
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
