# Security Audit Remediation Plan — 2026-07-24

> Source findings: `docs/security/audit-2026-07-24-findings.md`. Read it before touching code.
> Discipline: TDD per finding (red → green → commit), one subagent per task, review between commits. **Do not execute until the user approves with `go` / `yes`.**

## Objective
Resolve all 14 audit findings (3 HIGH, 6 MED, 5 LOW) with the smallest correct diff per finding, verified by failing-then-passing tests, no regressions to the existing green baseline.

## Assumptions & Constraints
- **Stack:** Laravel 12 / PHP 8.3 / spatie-permission / multi-tenant via `instansi_id`.
- **Test gating:** local MariaDB may be offline → focused non-DB tests gate each commit; full suite run when DB is up. Existing baseline tests (`tests/Feature/*`, `tests/Unit/SecurityHeadersTest`) must stay green.
- **Branch:** `fix/security-audit-2026-07-24` off current HEAD. One commit per finding.
- **No behavior change outside the reported issue.** No opportunistic refactors.
- **Super-Admin override must survive** the tenant fixes (admins legitimately create resources for other instansi).

## Cross-cutting contract (load-bearing — I own this, executors implement)
**Ownership/tenant fields are NEVER client input.** Applies to `instansi_id` (Program, SasaranStrategis, AuditLog-policy) and parent-FK tenant anchors (`program_id` for Kegiatan). Also `status` (Program, Kegiatan).
- Enforcement at the **controller write site**, not the form request.
- Non-admin: `instansi_id = Auth::user()->instansi_id` (server-derived, ignores any client value).
- Admin (Super Admin / Executive with `instansi_id === null`): may target a chosen instansi, but only via an explicit, permission-gated branch — the value is still re-validated `exists:instansis,id`.
- Reference implementation already exists and is correct: `PerformanceIndicatorController::store:167` (`$user->instansi_id ?? $request->get('instansi_id')`) + `update` omits instansi_id. **Copy that pattern.**

## Affected modules / files
- Tenant cluster: `app/Http/Controllers/Sakip/{Program,SasaranStrategis,Kegiatan}Controller.php`, `app/Http/Requests/Sakip/{Program,Kegiatan}FormRequest.php`, `app/Policies/{Kegiatan,AuditLog}Policy.php`, `app/Http/Controllers/Sakip/SakipAuditController.php`, `routes/web_sakip.php`.
- CSP: `app/Http/Middleware/SecurityHeadersMiddleware.php` + every `resources/views/**/*.blade.php` with inline `<script>`.
- Input/logging: `bootstrap/app.php`, `app/Http/Middleware/SanitizeInputMiddleware.php`, `app/Services/SystemSettingsService.php`, `resources/views/components/modern/data-table.blade.php`.
- CSRF: `bootstrap/app.php` (api group), SPA HTTP client.
- Dead code: `routes/api_v1.php`, `app/Policies/Sakip/*`, orphaned api upload routes.
- Config: `.env.example`, `config/session.php`.

---

## Phase 0 — Safety net (do first, inline, not delegated)
1. Confirm green baseline: `php artisan test` (or focused subset if DB down). Capture pass count.
2. `git checkout -b fix/security-audit-2026-07-24`.
3. Verify reproduction of each HIGH before fixing (so the red test is meaningful).

---

## Phase 1 — Tenant-isolation cluster (HIGH + MED) — delegates after contract above is set

### Task 1.1 — Program tenant-spoofing (HIGH)
- **Files:** `ProgramController.php:127-141` (store), `:191-205` (update); `ProgramFormRequest.php:42-46`; `app/Models/Program.php:22`.
- **RED:** new `tests/Feature/ProgramTenantIsolationTest.php` — (a) non-admin POST with foreign `instansi_id` → record saved with user's own instansi (foreign value ignored); (b) non-admin PUT changing `instansi_id` → rejected/ignored (immutable); (c) Super Admin POST with chosen instansi → honored. Assert via DB row inspection.
- **GREEN:** in `store`, build the create array from `$request->safe()->except(['instansi_id','status'])` + `'instansi_id' => $user->instansi_id ?? $request->input('instansi_id')` (admin branch guarded by `authorize` already). In `update`, strip `instansi_id` from the validated set entirely (relocation forbidden). Remove `instansi_id` from `Program::$fillable` (defense-in-depth; controller sets it explicitly).
- **Verify:** RED→GREEN; existing Program tests still pass.
- **Commit:** `fix(program): server-derive instansi_id, forbid tenant relocation`.

### Task 1.2 — SasaranStrategis tenant-spoofing (HIGH)
- **Files:** `SasaranStrategisController.php:73-97` (store), `:126-150` (update).
- **RED:** `tests/Feature/SasaranStrategisTenantIsolationTest.php` mirroring 1.1.
- **GREEN:** store: `$validated = $request->validate([...without instansi_id...]); $validated['instansi_id'] = $user->instansi_id ?? ($user->hasRole('Super Admin') ? $request->input('instansi_id') : null);` reject if null. update: drop `instansi_id` from validated (immutable).
- **Commit:** `fix(sasaran-strategis): server-derive instansi_id`.

### Task 1.3 — Kegiatan cross-tenant read + attach (MED)
- **Files:** `KegiatanController.php:26-60` (index), `:82-84` (create dropdown), `:94-106`/`:143-155` (store/update); `KegiatanPolicy.php:10-33`; `KegiatanFormRequest.php:43-47`.
- **RED:** `tests/Feature/KegiatanTenantIsolationTest.php` — (a) `index` returns only user-tenant kegiatans; (b) `create` dropdown lists only user-tenant programs; (c) POST with foreign-tenant `program_id` → 403/422; (d) `show` foreign kegiatan → 403.
- **GREEN:** `index()` add `->whereHas('program', fn($q)=>$q->where('instansi_id', $user->instansi_id))` (admin bypass). `create()` scope the `Program::where(...)` by instansi. Rewrite `KegiatanPolicy@view/viewAny` to check `$kegiatan->program->instansi_id === $user->instansi_id`. In `store`/`update`, before create, assert `Program::find($validated['program_id'])->instansi_id === $user->instansi_id` (or authorize a new `createFor` on the program).
- **Commit:** `fix(kegiatan): enforce tenant scope on read + program attach`.

### Task 1.4 — AuditLog latent cross-tenant IDOR + broken route (MED)
- **Files:** `routes/web_sakip.php:336` (`showAuditLog`→`show`); `app/Policies/AuditLogPolicy.php:42-45`; `app/Http/Controllers/Sakip/SakipAuditController.php:152-176`.
- **RED:** `tests/Feature/AuditLogTenantIsolationTest.php` — (a) GET foreign-tenant audit detail → 403; (b) `relatedLogs` contains only same-tenant entries; (c) GET own-tenant detail → 200 (route now resolves).
- **GREEN:** fix route callback to `"show"`. `AuditLogPolicy@view`: `return $user->hasRole('Super Admin') || ($user->hasPermissionTo('view-audit-trails') && $user->instansi_id === $auditLog->instansi_id);`. Scope `relatedLogs` query by `$user->instansi_id` (or `$auditLog->instansi_id`).
- **Commit:** `fix(audit-log): tenant-scope detail view + related logs, fix route callback`.

### Task 1.5 — status workflow bypass (MED)
- **Files:** `ProgramFormRequest.php:70-73`; `KegiatanFormRequest.php:64-68`.
- **RED:** test that create/update cannot set `status=completed`; default is `draft`/`active` server-side.
- **GREEN:** remove `status` from client `rules()`; set server default in controller create; transitions only via dedicated actions (out of scope to build new endpoints — just stop accepting client status).
- **Commit:** `fix(program,kegiatan): remove client-controlled status`.

---

## Phase 2 — CSP nonce consumption (HIGH, independent)
### Task 2.1 — Emit nonce on every inline script
- **Files:** `app/Http/Middleware/SecurityHeadersMiddleware.php` (expose helper); all `resources/views/**/*.blade.php` with `<script>` (35+).
- **Decision (smallest correct diff):** add `csp_nonce()` helper returning `app('csp-nonce')`; codemod every `<script>` (and `<script src=...>`) to carry `nonce="{{ csp_nonce() }}"`. External CDN srcs already allowlisted in CSP (jsdelivr/jquery/datatables/cdnjs).
- **RED/verify (browser-level, not unit):** `curl -I` confirms prod CSP still `nonce-`-based, no `unsafe-inline`; load `/sakip/dashboard` in prod env → no console CSP violations; charts/datatables/theme render.
- **GREEN:** apply nonce; run existing `SecurityHeadersTest` (must stay green).
- **Subtasks:** (a) add helper + register; (b) codemod views (mechanical — single subagent); (c) browser smoke.
- **Commit:** `fix(csp): consume nonce on inline scripts (restore prod JS)`.

---

## Phase 3 — Input / config hardening (MED)
### Task 3.1 — Remove `SanitizeInputMiddleware`
- **Files:** `bootstrap/app.php:44-46`; delete `app/Http/Middleware/SanitizeInputMiddleware.php`.
- **RED:** none (removal); **verify:** existing tests unaffected; spot-check a create still stores literal text un-mangled.
- **Commit:** `fix(middleware): remove net-negative SanitizeInputMiddleware`.

### Task 3.2 — `SystemSettingsService::set()` key allowlist
- **Files:** `app/Services/SystemSettingsService.php`; `AdminController.php:244-247`.
- **RED:** test that an unknown key is rejected (422); known key persists.
- **GREEN:** add `protected array $allowedKeys = [...]`; in `set()`, `abort(422)`/throw on unknown.
- **Commit:** `fix(settings): allowlist SystemSetting keys`.

### Task 3.3 — data-table render closure escaping
- **Files:** `resources/views/components/modern/data-table.blade.php:54`; audit every `render` callback passed to the component.
- **RED:** store `<img src=x onerror=alert(1)>` in a rendered field; assert escaped in HTML.
- **GREEN:** each closure wraps user data in `e()`.
- **Commit:** `fix(xss): escape data-table render closures`.

### Task 3.4 — api_sakip CSRF hardening (MED, conditional)
- **Decision:** add `\Illuminate\Session\Middleware\EnsureFrontendRequestsAreStateful::class` to the api group (`bootstrap/app.php`) + ensure SPA sends `XSRF-TOKEN`. (Alternative: migrate to sanctum token auth — larger, defer.)
- **RED:** test that a cross-origin POST without CSRF token is rejected (419) for state-changing api routes.
- **Commit:** `fix(api): enforce CSRF on cookie-auth api_sakip`.

---

## Phase 4 — Dead code + low hardening (LOW)
### Task 4.1 — Delete dead surfaces
- `routes/api_v1.php`; `app/Policies/Sakip/*` stubs; orphaned api upload routes (`api_sakip.php:344,351` → nonexistent methods) — delete the routes.
- **Verify:** `php artisan route:list` has no dangling refs; no controller import breaks.
- **Commit:** `chore: remove dead api_v1 + Sakip policy stubs + orphaned upload routes`.

### Task 4.2 — Wire or delete `SecureFileUploadMiddleware`
- **Decision:** wire to live upload routes (`api_sakip.php`, web store) as defense-in-depth (controllers already validate). Or delete if deemed noise.
- **Commit:** `fix(uploads): apply SecureFileUploadMiddleware to live upload routes`.

### Task 4.3 — Delete `handleValidationError` (dead cred-logger)
- **Files:** `app/Http/Controllers/Controller.php:67-88`.
- **Commit:** `chore: remove dead handleValidationError`.

### Task 4.4 — Session encryption + version-drift note
- `.env.example`: `SESSION_ENCRYPT=true` (or document tradeoff). Open follow-up issue for Laravel 12→13 / spatie 6→8 bumps.
- **Commit:** `chore: enable session encryption; note version-drift follow-up`.

---

## Phase 5 — Final verification (inline)
1. `php artisan test` — full suite green (or focused subset documented if DB down).
2. Re-run `composer audit` + `npm audit` — still 0.
3. Manual smoke: login → create Program/Indicator in own tenant (works) → attempt foreign `instansi_id` (blocked) → prod CSP loads dashboard JS (no console errors) → audit detail view renders (route fixed) → cross-tenant audit detail 403.
4. Update `docs/security/audit-2026-07-24-findings.md` with ✅ status per finding (append-only Remediation Log section).

## Risks & failure modes
- **Admin-override regression:** over-tightening breaks Super Admin cross-tenant creation → mitigated by the explicit admin branch in the contract; covered by test (c).
- **CSP codemod misses a script** → mitigated by browser smoke + the CSP violation endpoint (`/api/csp-reports`) surfacing leftovers.
- **Removing `SanitizeInputMiddleware`** un-masks a real XSS sink that relied on it → mitigated by Task 3.3 (escape closures) + Blade auto-escaping; the middleware was already bypassable.
- **CSRF hardening breaks the SPA** if the client doesn't send `XSRF-TOKEN` → verify SPA axios `withCredentials` + `xsrfCookieName`/`xsrfHeaderName`.
- **KegiatanPolicy rewrite** changes `viewAny` semantics → re-check any `Gate::before` Super-Admin bypass still applies.

## Rollback
- Each finding = one commit on a feature branch → `git revert <sha>` per finding. No schema migrations, so no data rollback needed.

## Subagent dispatch plan (on approval)
Parallelizable waves (independent files):
- **Wave A (parallel, 4 agents):** Task 1.1, 1.2, 1.3, 1.4 — each owns one controller+policy+test, distinct files, no overlap.
- **Wave B (after A):** Task 1.5 (touches Program+Kegiatan form requests — depends on 1.1/1.3 landing).
- **Wave C (parallel, independent):** Task 2.1 (CSP), 3.1 (remove middleware), 3.2 (settings), 4.1/4.3 (dead code).
- **Wave D:** Task 3.3 (XSS closures — needs runtime confirm), 3.4 (CSRF — SPA check), 4.2, 4.4.
- **Phase 5:** inline verification, then findings-report status update.
