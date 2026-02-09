<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\MaintenanceController;

// Dashboard Route with auth/verification-aware redirect
Route::get("/", function () {
    $user = auth()->user();
    // Guest: no authenticated user
    if (!$user) {
        return redirect()->route("login"); // Guest → login
    }
    // Authenticated but unverified: send to verification notice
    if (!$user->hasVerifiedEmail()) {
        return redirect()->route("verification.notice"); // Unverified → verify
    }
    // Authenticated and verified: go to SAKIP dashboard
    return redirect()->route("sakip.dashboard"); // Verified → SAKIP
})->name("home");

// Dashboard Route - redirects to appropriate dashboard based on role
Route::get("/dashboard", function () {
    $user = auth()->user();

    if (!$user) {
        return redirect()->route("login");
    }

    if (!$user->hasVerifiedEmail()) {
        return redirect()->route("verification.notice");
    }

    // Redirect based on user role
    if ($user->hasRole("Super Admin") || $user->hasRole("admin")) {
        return redirect()->route("admin.dashboard");
    }

    // Default to SAKIP dashboard for all other roles
    return redirect()->route("sakip.dashboard");
})
    ->middleware("auth")
    ->name("dashboard");

// Auth routes
Route::get("/login", [
    \App\Http\Controllers\Auth\LoginController::class,
    "show",
])
    ->middleware("guest")
    ->name("login");
Route::post("/login", [
    \App\Http\Controllers\Auth\LoginController::class,
    "login",
])
    ->middleware("throttle:login")
    ->name("auth.login");
Route::post("/logout", [
    \App\Http\Controllers\Auth\LogoutController::class,
    "logout",
])->name("logout");

// Email verification routes
Route::middleware("auth")->group(function () {
    Route::get("/email/verify", [
        \App\Http\Controllers\Auth\EmailVerificationController::class,
        "notice",
    ])->name("verification.notice");

    Route::get("/email/verify/{id}/{hash}", [
        \App\Http\Controllers\Auth\EmailVerificationController::class,
        "verify",
    ])
        ->middleware("signed")
        ->name("verification.verify");

    Route::post("/email/resend", [
        \App\Http\Controllers\Auth\EmailVerificationController::class,
        "resend",
    ])
        ->middleware("throttle:email_verification")
        ->name("verification.resend");
});

// Protected Routes untuk SAKIP
Route::middleware(["auth", "verified"])->group(function () {
    /**
     * Legacy route redirects to SAKIP equivalents
     * We intentionally remove legacy resources and forward users to the new module.
     */
    Route::any("instansi/{any?}", function () {
        return redirect()->route("sakip.dashboard");
    })->where("any", ".*");

    Route::any("program/{any?}", function () {
        return redirect()->route("sakip.performance-data.index");
    })->where("any", ".*");

    Route::any("kegiatan/{any?}", function () {
        return redirect()->route("sakip.performance-data.index");
    })->where("any", ".*");

    Route::any("indikator-kinerja/{any?}", function () {
        return redirect()->route("sakip.indicators.index");
    })->where("any", ".*");

    Route::any("laporan-kinerja/{any?}", function () {
        return redirect()->route("sakip.reports.index");
    })->where("any", ".*");

    // Profile page
    Route::get("/profile", [
        \App\Http\Controllers\ProfileController::class,
        "show",
    ])->name("profile.show");

    // Account Settings page (user-level settings)
    Route::get("/settings/account", [
        \App\Http\Controllers\AccountSettingsController::class,
        "show",
    ])->name("settings.account");

    // Update Password
    Route::put("/settings/password", [
        \App\Http\Controllers\AccountSettingsController::class,
        "updatePassword",
    ])->name("settings.password.update");

    // Help page
    Route::get("/help", [
        \App\Http\Controllers\HelpController::class,
        "index",
    ])->name("help");

    // Feedback page
    Route::get("/feedback", [
        \App\Http\Controllers\FeedbackController::class,
        "index",
    ])->name("feedback");
    Route::post("/feedback", [
        \App\Http\Controllers\FeedbackController::class,
        "store",
    ])->name("feedback.store");

    // Documentation page
    Route::get("/documentation", [
        \App\Http\Controllers\DocumentationController::class,
        "index",
    ])->name("documentation");

    // Legal pages
    Route::get("/privacy-policy", [
        \App\Http\Controllers\LegalController::class,
        "privacyPolicy",
    ])->name("privacy-policy");
    Route::get("/terms-of-service", [
        \App\Http\Controllers\LegalController::class,
        "termsOfService",
    ])->name("terms-of-service");
    Route::get("/disclaimer", [
        \App\Http\Controllers\LegalController::class,
        "disclaimer",
    ])->name("disclaimer");
    Route::get("/accessibility", [
        \App\Http\Controllers\LegalController::class,
        "accessibility",
    ])->name("accessibility");

    // Pengaturan legacy redirects (preserve URLs; forward to unified admin settings)
    Route::middleware(["auth", "verified", "role:superadmin"])->group(
        function () {
            Route::get("pengaturan", function () {
                return redirect()->route("admin.settings.index");
            })->name("pengaturan.index");
            Route::put("pengaturan", function (
                \Illuminate\Http\Request $request,
            ) {
                return redirect()->route("admin.settings.update");
            })->name("pengaturan.update");
            Route::post("pengaturan/clear-cache", function () {
                return redirect()->route("admin.settings.clear-cache");
            })->name("pengaturan.clear-cache");
            Route::post("pengaturan/optimize", function () {
                return redirect()->route("admin.settings.optimize");
            })->name("pengaturan.optimize");
            Route::post("pengaturan/backup", function () {
                return redirect()->route("admin.settings.backup");
            })->name("pengaturan.backup");
        },
    );

    // Admin Routes (Comprehensive Admin System)
    Route::prefix("admin")
        ->middleware(["auth", "can:admin.dashboard", "throttle:60,1"])
        ->group(function () {
            // Dashboard
            Route::get("/", [AdminDashboardController::class, "index"])->name(
                "admin.dashboard",
            );

            // User Management
            Route::prefix("users")
                ->middleware(["auth", "can:manage-users"])
                ->group(function () {
                    Route::get("/", [
                        UserManagementController::class,
                        "index",
                    ])->name("admin.users.index");
                    Route::get("/create", [
                        UserManagementController::class,
                        "create",
                    ])->name("admin.users.create");
                    Route::post("/", [
                        UserManagementController::class,
                        "store",
                    ])->name("admin.users.store");
                    Route::get("/{user}", [
                        UserManagementController::class,
                        "show",
                    ])->name("admin.users.show");
                    Route::get("/{user}/edit", [
                        UserManagementController::class,
                        "edit",
                    ])->name("admin.users.edit");
                    Route::put("/{user}", [
                        UserManagementController::class,
                        "update",
                    ])->name("admin.users.update");
                    // Separate submission routes for roles and permissions management
                    Route::post("/{user}/roles", [
                        UserManagementController::class,
                        "updateRoles",
                    ])->name("admin.users.roles.update");
                    Route::post("/{user}/permissions", [
                        UserManagementController::class,
                        "updatePermissions",
                    ])->name("admin.users.permissions.update");
                    // Cleanup legacy roles & permissions for a user (supports dry_run)
                    Route::post("/{user}/cleanup-access", [
                        AdminController::class,
                        "cleanupAccess",
                    ])->name("admin.users.cleanup-access");
                    Route::delete("/{user}", [
                        UserManagementController::class,
                        "destroy",
                    ])->name("admin.users.destroy");
                });

            // Role Management
            Route::prefix("roles")
                ->middleware(["auth", "can:manage-roles"])
                ->group(function () {
                    Route::get("/", [
                        RoleManagementController::class,
                        "index",
                    ])->name("admin.roles.index");
                    Route::get("/create", [
                        RoleManagementController::class,
                        "create",
                    ])->name("admin.roles.create");
                    Route::post("/", [
                        RoleManagementController::class,
                        "store",
                    ])->name("admin.roles.store");
                    Route::get("/{role}", [
                        RoleManagementController::class,
                        "show",
                    ])->name("admin.roles.show");
                    Route::get("/{role}/edit", [
                        RoleManagementController::class,
                        "edit",
                    ])->name("admin.roles.edit");
                    Route::put("/{role}", [
                        RoleManagementController::class,
                        "update",
                    ])->name("admin.roles.update");
                    Route::delete("/{role}", [
                        RoleManagementController::class,
                        "destroy",
                    ])->name("admin.roles.destroy");
                    Route::post("/{role}/permissions", [
                        RoleManagementController::class,
                        "updatePermissions",
                    ])->name("admin.roles.permissions.update");
                });

            // Permission Management
            Route::prefix("permissions")
                ->middleware(["auth", "can:manage-permissions"])
                ->group(function () {
                    Route::get("/", [
                        PermissionManagementController::class,
                        "index",
                    ])->name("admin.permissions.index");
                    Route::get("/create", [
                        PermissionManagementController::class,
                        "create",
                    ])->name("admin.permissions.create");
                    Route::post("/", [
                        PermissionManagementController::class,
                        "store",
                    ])->name("admin.permissions.store");
                    Route::get("/{permission}", [
                        PermissionManagementController::class,
                        "show",
                    ])->name("admin.permissions.show");
                    Route::get("/{permission}/edit", [
                        PermissionManagementController::class,
                        "edit",
                    ])->name("admin.permissions.edit");
                    Route::put("/{permission}", [
                        PermissionManagementController::class,
                        "update",
                    ])->name("admin.permissions.update");
                    Route::delete("/{permission}", [
                        PermissionManagementController::class,
                        "destroy",
                    ])->name("admin.permissions.destroy");
                });

            // Audit Logs
            Route::get("/audit-logs", [
                AuditLogController::class,
                "index",
            ])->name("admin.audit-logs");

            // System Settings
            Route::prefix("settings")
                ->middleware(["auth", "can:admin.settings"])
                ->group(function () {
                    Route::get("/", [
                        SystemSettingsController::class,
                        "index",
                    ])->name("admin.settings.index");
                    Route::post("/", [
                        SystemSettingsController::class,
                        "update",
                    ])->name("admin.settings.update");

                    // Maintenance actions
                    Route::get("/maintenance", [
                        MaintenanceController::class,
                        "index",
                    ])->name("admin.maintenance.index");
                    Route::post("/maintenance/clear-cache", [
                        MaintenanceController::class,
                        "clearCache",
                    ])->name("admin.maintenance.clear-cache");
                    Route::post("/maintenance/optimize", [
                        MaintenanceController::class,
                        "optimizeApp",
                    ])->name("admin.maintenance.optimize");
                    Route::post("/maintenance/backup", [
                        MaintenanceController::class,
                        "backupDatabase",
                    ])->name("admin.maintenance.backup");
                    Route::get("/maintenance/backup/{filename}", [
                        MaintenanceController::class,
                        "downloadBackup",
                    ])->name("admin.maintenance.backup.download");
                    Route::delete("/maintenance/backup/{filename}", [
                        MaintenanceController::class,
                        "deleteBackup",
                    ])->name("admin.maintenance.backup.delete");
                    Route::get("/maintenance/health", [
                        MaintenanceController::class,
                        "healthCheck",
                    ])->name("admin.maintenance.health");

                    // Legacy route aliases (preserve compatibility)
                    Route::post("/clear-cache", [
                        MaintenanceController::class,
                        "clearCache",
                    ])->name("admin.settings.clear-cache");
                    Route::post("/optimize", [
                        MaintenanceController::class,
                        "optimizeApp",
                    ])->name("admin.settings.optimize");
                    Route::post("/backup", [
                        MaintenanceController::class,
                        "backupDatabase",
                    ])->name("admin.settings.backup");
                });
        });
});

// Include SAKIP routes
require __DIR__ . "/web_sakip.php";

// SAKIP Test Routes (Development only)
if (app()->environment("local", "development")) {
    Route::prefix("sakip-test")
        ->middleware(["auth", "can:debug"])
        ->group(function () {
            Route::get("/dashboard", [
                \App\Http\Controllers\SakipTestController::class,
                "testDashboard",
            ])->name("sakip.test.dashboard");
            Route::get("/datatable", [
                \App\Http\Controllers\SakipTestController::class,
                "testDataTable",
            ])->name("sakip.test.datatable");
            Route::get("/notification", [
                \App\Http\Controllers\SakipTestController::class,
                "testNotification",
            ])->name("sakip.test.notification");
            Route::get("/configuration", [
                \App\Http\Controllers\SakipTestController::class,
                "testConfiguration",
            ])->name("sakip.test.configuration");
            Route::get("/helpers", [
                \App\Http\Controllers\SakipTestController::class,
                "testHelpers",
            ])->name("sakip.test.helpers");
        });

    /**
     * Health check endpoint for local debugging.
     * Returns HTTP 200 with a simple body to confirm server is responsive.
     * SECURED: Requires authentication and debug permission
     */
    Route::get("/healthz", function () {
        return response("ok", 200);
    })
        ->middleware(["auth", "can:debug"])
        ->name("healthz");

    /**
     * Session and authentication debug endpoint (local only).
     * Helps diagnose cookie, session domain, and redirect issues by exposing
     * minimal, non-sensitive runtime state for the current request.
     * SECURED: Requires authentication and debug permission
     */
    Route::get("/debug/session", function (\Illuminate\Http\Request $request) {
        $user = \Illuminate\Support\Facades\Auth::user();
        return response()->json([
            "host" => $request->getHost(),
            "url" => url()->current(),
            "app_url" => config("app.url"),
            "authenticated" => \Illuminate\Support\Facades\Auth::check(),
            "user_id" => $user?->id,
            "verified" => $user?->hasVerifiedEmail(),
            "session_id" => $request->session()->getId(),
            "session_cookie_name" => config("session.cookie"),
            "session_domain" => config("session.domain"),
            "session_path" => config("session.path"),
            "session_secure" => config("session.secure"),
            "session_same_site" => config("session.same_site"),
        ]);
    })
        ->middleware(["auth", "can:debug"])
        ->name("debug.session");
}

// API Routes untuk AJAX (Legacy redirects)
Route::prefix("api")
    ->middleware(["auth", "throttle:api"])
    ->group(function () {
        // Map: /api/program/by-instansi/{instansi} -> /api/sakip/datatables/program?instansi_id=...
        Route::get("program/by-instansi/{instansi}", function ($instansi) {
            $target = route("sakip.api.datatables.program");
            $url = $target . "?instansi_id=" . urlencode($instansi);
            return redirect()->to($url);
        });

        // Map: /api/kegiatan/by-program/{program} -> /api/sakip/datatables/kegiatan?program_id=...
        Route::get("kegiatan/by-program/{program}", function ($program) {
            $target = route("sakip.api.datatables.kegiatan");
            $url = $target . "?program_id=" . urlencode($program);
            return redirect()->to($url);
        });

        // Map: /api/indikator/by-kegiatan/{kegiatan} -> /api/sakip/datatables/indicator?instansi_id=...
        // We infer instansi_id from kegiatan, since indikator-by-kegiatan no longer exists in SAKIP.
        Route::get("indikator/by-kegiatan/{kegiatan}", function ($kegiatanId) {
            $kegiatan = \App\Models\Kegiatan::with("program")->find(
                $kegiatanId,
            );
            $instansiId = $kegiatan?->program?->instansi_id;
            $target = route("sakip.api.datatables.indicator");
            $url = $instansiId
                ? $target . "?instansi_id=" . urlencode($instansiId)
                : $target;
            return redirect()->to($url);
        });
    });
