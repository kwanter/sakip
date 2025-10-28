<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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
])->name("auth.login");
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
        ->middleware("can:admin.dashboard")
        ->group(function () {
            // Dashboard
            Route::get("/", [AdminController::class, "dashboard"])->name(
                "admin.dashboard",
            );

            // User Management
            Route::prefix("users")->group(function () {
                Route::get("/", [AdminController::class, "users"])->name(
                    "admin.users.index",
                );
                Route::get("/create", [
                    AdminController::class,
                    "createUser",
                ])->name("admin.users.create");
                Route::post("/", [AdminController::class, "storeUser"])->name(
                    "admin.users.store",
                );
                Route::get("/{user}", [
                    AdminController::class,
                    "showUser",
                ])->name("admin.users.show");
                Route::get("/{user}/edit", [
                    AdminController::class,
                    "editUser",
                ])->name("admin.users.edit");
                Route::put("/{user}", [
                    AdminController::class,
                    "updateUser",
                ])->name("admin.users.update");
                // Separate submission routes for roles and permissions management
                Route::post("/{user}/roles", [
                    AdminController::class,
                    "updateRoles",
                ])->name("admin.users.roles.update");
                Route::post("/{user}/permissions", [
                    AdminController::class,
                    "updatePermissions",
                ])->name("admin.users.permissions.update");
                // Cleanup legacy roles & permissions for a user (supports dry_run)
                Route::post("/{user}/cleanup-access", [
                    AdminController::class,
                    "cleanupAccess",
                ])->name("admin.users.cleanup-access");
                Route::delete("/{user}", [
                    AdminController::class,
                    "destroyUser",
                ])->name("admin.users.destroy");
            });

            // Role Management
            Route::prefix("roles")->group(function () {
                Route::get("/", [AdminController::class, "roles"])->name(
                    "admin.roles.index",
                );
                Route::get("/create", [
                    AdminController::class,
                    "createRole",
                ])->name("admin.roles.create");
                Route::post("/", [AdminController::class, "storeRole"])->name(
                    "admin.roles.store",
                );
                Route::get("/{role}", [
                    AdminController::class,
                    "showRole",
                ])->name("admin.roles.show");
                Route::get("/{role}/edit", [
                    AdminController::class,
                    "editRole",
                ])->name("admin.roles.edit");
                Route::put("/{role}", [
                    AdminController::class,
                    "updateRole",
                ])->name("admin.roles.update");
                Route::delete("/{role}", [
                    AdminController::class,
                    "destroyRole",
                ])->name("admin.roles.destroy");
                Route::post("/{role}/permissions", [
                    AdminController::class,
                    "updateRolePermissions",
                ])->name("admin.roles.permissions.update");
            });

            // Permission Management
            Route::prefix("permissions")
                ->middleware("can:manage-permissions")
                ->group(function () {
                    Route::get("/", [
                        AdminController::class,
                        "permissions",
                    ])->name("admin.permissions.index");
                    Route::get("/create", [
                        AdminController::class,
                        "createPermission",
                    ])->name("admin.permissions.create");
                    Route::post("/", [
                        AdminController::class,
                        "storePermission",
                    ])->name("admin.permissions.store");
                    Route::get("/{permission}", [
                        AdminController::class,
                        "showPermission",
                    ])->name("admin.permissions.show");
                    Route::get("/{permission}/edit", [
                        AdminController::class,
                        "editPermission",
                    ])->name("admin.permissions.edit");
                    Route::put("/{permission}", [
                        AdminController::class,
                        "updatePermission",
                    ])->name("admin.permissions.update");
                    Route::delete("/{permission}", [
                        AdminController::class,
                        "destroyPermission",
                    ])->name("admin.permissions.destroy");
                });

            // Audit Logs
            Route::get("/audit-logs", [
                AdminController::class,
                "auditLogs",
            ])->name("admin.audit-logs");

            // System Settings + Maintenance (Unified)
            Route::middleware("can:admin.settings")->group(function () {
                Route::get("/settings", [
                    AdminController::class,
                    "systemSettings",
                ])->name("admin.settings.index");
                Route::post("/settings", [
                    AdminController::class,
                    "updateSettings",
                ])->name("admin.settings.update");
                Route::post("/settings/clear-cache", [
                    AdminController::class,
                    "clearCache",
                ])->name("admin.settings.clear-cache");
                Route::post("/settings/optimize", [
                    AdminController::class,
                    "optimizeApp",
                ])->name("admin.settings.optimize");
                Route::post("/settings/backup", [
                    AdminController::class,
                    "backupDatabase",
                ])->name("admin.settings.backup");
            });
        });
});

// Include SAKIP routes
require __DIR__ . "/web_sakip.php";

// SAKIP Test Routes (Development only)
if (app()->environment("local", "development")) {
    Route::prefix("sakip-test")->group(function () {
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
     */
    Route::get("/healthz", function () {
        return response("ok", 200);
    })->name("healthz");

    /**
     * Session and authentication debug endpoint (local only).
     * Helps diagnose cookie, session domain, and redirect issues by exposing
     * minimal, non-sensitive runtime state for the current request.
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
    })->name("debug.session");
}

// API Routes untuk AJAX (Legacy redirects)
Route::prefix("api")
    ->middleware("auth")
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
