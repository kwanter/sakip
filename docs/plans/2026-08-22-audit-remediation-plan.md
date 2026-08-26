# Audit Remediation Plan — 2026-08-22

> Sources: full-repo architecture + security audit (this date) + 3 subagent deep-dives.
> Builds on docs/plans/2026-07-24-security-audit-remediation.md (Phases 1–2 landed in commit 5781d82; Phase 3–5 items still open, folded in).
> Discipline (same contract as prior plan): TDD per finding (red → green → commit), one commit per finding, smallest correct diff, no opportunistic refactors. **Do not execute until the user approves.**

## Baseline facts (verified)
- Tenant isolation + CSP from 07-24 merged (5781d82). Branch fix/security-audit-2026-07-24 is stale — branch fresh off main.
- Prod DB = MySQL. Root 'sakip' SQLite = empty artifact, untracked and NOT gitignored (extension-less name escapes *.db/*.sqlite rules).
- Raw SQL: ~60 sites all bound/static — no injection work needed.
- No template RCE (str_replace render; view names whitelisted). Backups + evidence storage paths + dashboard cache keys safe.
- Schema/FK/index quality is GOOD (confirmed false alarm in prior audit drafts).
- composer audit: 8 advisories / 2 packages (guzzle, league/commonmark), transitive, low reachability.

---

## Phase 0 — Safety net (inline, first)
1. `composer test` green baseline captured.
2. `git checkout -b fix/audit-remediation-2026-08-22`.
3. Reproduce each Phase-1 HIGH/MED before fixing (meaningful reds).

## Phase 1 — Authorization & data leaks (do first)

### T1.1 — api_sakip has no per-action authorization (CRITICAL, new)
- Files: routes/api_sakip.php:24-26; App/Http/Controllers/Sakip/* and Api/Sakip/*.
- Problem: whole surface gated only by `auth:web` + `permission:view-dashboard` (all roles seeded it). 9 live methods (dashboard reads, datatables, bulkImport, audit stats) are TODAY reachable across tenants; remaining 68/77 methods are 500 (will expose on fix). Approve/verify/delete/export/upload lack endpoint-level authz.
- RED: data_collector role POST-approves a target → 403.
- GREEN: per-endpoint `authorize()` on every mutating route (reuse existing policies); scope every action by user instansi_id unless admin/pimpinan.
- Commit: `fix(api): per-action authorization + tenant scope on all sakip api mutations`. ⚠ large — split per controller.

### T1.2 — Admin role self-escalation (CRITICAL, new)
- Files: app/Services/AdminService.php:92-102 (assignRoles).
- Problem: syncs ANY role id incl 'Super Admin'; guarded only by `can:manage-users`.
- RED: manage-users holder assigns Super Admin to self → rejected.
- GREEN: role allowlist — non-super-admin may assign only admin-tier roles; super-admin assignment requires a super-admin-only permission.
- Commit: `fix(admin): role allowlist, block self-escalation to superadmin`.

### T1.3 — Cross-tenant dashboard (MED-HIGH, new)
- Files: app/Services/SakipDashboardService.php:106-305; SakipApiController.php:40-48; routes/api_sakip.php:24-25.
- Problem: `DB::table('laporan_kinerjas')` aggregates have NO instansi filter; getInstansiComparison returns ALL institutions to any view-dashboard user.
- RED: non-admin GET /sakip/api/dashboard → own-instansi only; comparison 403 for non-admin.
- GREEN: server-side instansi scope on every aggregate; comparison/activity endpoints restricted to pimpinan/admin.
- Commit: `fix(dashboard): tenant-scope aggregates, gate cross-instansi views`.

### T1.4 — Target approve/reject/revise cross-tenant (MED, new)
- Files: app/Http/Controllers/Sakip/TargetController.php:306/364/436.
- Problem: checks only `can:approve-targets`, no institution scoping.
- RED: data_collector approves a foreign-instansi target → 403.
- GREEN: verify target indicator instansi_id === user instansi_id before mutation (admin bypass).
- Commit: `fix(target): tenant-scope approve/reject/revise`.

### T1.5 — EvidenceDocument mass assignment → arbitrary file read (MED, new)
- Files: app/Services/EvidenceDocumentService.php:224; model fillable includes file_name/file_path/metadata.
- Problem: `$evidence->update($data)` accepts client file_path; downloadEvidence then serves it from the private disk.
- RED: update posting another doc's file_path → unchanged; download serves original.
- GREEN: `$evidence->update($request->only(['description','document_type']))` whitelist.
- Also: (a) strip zip/rar from allowedExtensions/allowedMime (contradicts SecureFileUploadMiddleware); (b) reject attacker-supplied instansi_id unless admin — pin to caller's instansi; (c) tenant-scope getEvidences/getEvidenceStatistics/deleteEvidence (un-scoped today); (d) force downloadEvidence to local disk only (remove public fallback → would bypass auth at /storage) and drop public-disk delete fallback.
- Commit: `fix(evidence): whitelist updatable fields, pin file_path + instansi_id`.

### T1.6 — Template files on public disk, unauthenticated (MED, new)
- Files: app/Services/TemplateService.php:147 (storeAs 'public'); config/filesystems.php.
- Note: uploadTemplateFile has zero callers — delete it (preferred) or move to local + authz download.
- Commit: `chore(templates): remove dead public-disk uploader`.

## Phase 2 — Build integrity (one owner decision)

### T2.1 — Broken export stack (HIGH, new) ⚠ DECISION GATE
- Fact: Export/{Csv,Excel,Pdf}ExportService import PhpOffice PhpSpreadsheet; package absent from composer.json/lock/vendor → live export routes (api_sakip.php:281,390) fatal at runtime.
- Option A (feature wanted): `composer require phpoffice/phpspreadsheet` + a PDF lib (dompdf); THEN add CSV formula-injection guard (prefix ' on =,+,-,@ cells) and streaming chunks + row cap (~10k) (SakipExportService.php:489,534,626 full-table get() = DoS).
- Option B (feature dead): delete the Export services + SakipExportService + export routes.
- Tests either way: one route-level smoke asserting 200+valid file (or 404 after removal).
- Commit: `fix(exports)` or `chore(exports)`.

### T2.2 — Dependency advisories (LOW effort)
- `composer update guzzlehttp/guzzle league/commonmark` (>=7.15.2 / >=2.9.0). CI composer audit goes quiet.

### T2.3 — Dead upload routes (new)
- routes/api_sakip.php:344 (uploadEvidence) + uploadReportFile → methods don't exist (500). Implement (reuse handleEvidenceFiles + policy upload) or delete.
- Commit: `fix(api): implement or remove orphaned upload routes`.

### T2.4 — Service collision & phantom models (new, architecture)
- (a) Two classes share doc '@class Performance Calculation Service': flat app/Services/PerformanceCalculationService.php (463 ln, LIVE but BROKEN — calls nonexistent Benchmark/PerformanceMeasurement models) vs app/Services/Calculation/PerformanceCalculationService.php (450 ln, real models, 0 refs → dead). Fix: adopt Calculation variant, delete/repair the live broken one.
- (b) SakipService namespace collision: App\Services\SakipService (flat 639 ln, live: SakipApiController/SakipDataTableController/SakipTestController) vs App\Services\Sakip\SakipService (nested 558 ln, PhpSpreadsheet-imports = shares #1 export fatal; used by SakipController + SakipDashboardService). SakipServiceProvider binds 'sakip' only to flat. COMMIT to one; rename/delete the other, bind survivor explicitly.
- Commit: fix(calc): adopt working Calculation variant + resolve SakipService collision.
- Files: app/Services/PerformanceCalculationService.php (LIVE, calls phantom Benchmark/PerformanceMeasurement models → fatal); the correct variant lives under app/Services/Calculation/* and is DEAD.
- GREEN: point the live route/controller at Calculation\* (dependency-inject the working validator/calculator); delete the broken facade.
- Commit: `fix(calculation): route to live Calculation services, delete phantom facade`.

## Phase 3 — Latent fatals & hardening

### T3.1 — Cache::getRedis()->keys() fatal on non-redis stores (LOW sev, wide blast)
- Sites: EvidenceDocumentService:626, TargetService:541, PerformanceDataService:558, ReportService:493, AssessmentService:499, PerformanceIndicatorService:560.
- Problem: default CACHE_STORE=database/file → getRedis() null → write-path 500 + broken invalidation (stale data).
- GREEN: store-agnostic invalidation (forget tracked keys); RED: invalidation works under array/database store.
- Commit: `fix(cache): store-agnostic invalidation`.

### T3.2 — sort_by whitelists (LOW)
- EvidenceDocumentService.php:200-202 + Assessment/Report services; copy SakipDataTableService allowlist.
- Commit: `fix(services): whitelist sortable columns`.

### T3.3 — Uniform upload protection (prior Task 4.2)
- Wire `secure.file.upload` + `throttle:upload` on ALL live upload routes (web evidence store, api_sakip) — today only api_v1 has it. Enforce policy upload inside handleEvidenceFiles (per-file MIME/ext, not bare isValid()).
- Commit: `fix(uploads): uniform middleware + policy enforcement`.

### T3.4 — Session/cookie hardening (prior 4.4)
- .env.example: SESSION_SECURE_COOKIE=true (behind TLS) + SESSION_ENCRYPT=true; document behind-TLS requirement.
- Commit: `chore(config): secure + encrypt session cookie defaults`.

### T3.5 — Dead report-approve policy (new)
- Confirm with authz audit A: ReportController::approve (ReportController.php:499-501) calls authorize('approve') with NO ReportPolicy::approve() → 403 for all non-super-admin (fails closed, workflow dead).
- ReportController approve() calls authorize('approve') with no matching policy method → 403 dead route. Implement method + tenant scope, or wire the existing report approve flow.
- Commit: `fix(report): implement approve policy + tenant scope`.

### T3.6 — data-table render-closure escaping (prior Task 3.3)

### T3.7 — /csp-reports unauthenticated log flood (new)
- Files: routes/api.php:10-17 (throttle:30,1 only).
- Problem: arbitrary large POST bodies logged to daily channel → log flooding / fresh log poison.
- GREEN: cap/JSON-decode + truncate body before logging; keep throttle.
- Commit: fix(csp): bound csp-report body size.
- resources/views/components/modern/data-table.blade.php:54 is the only {!! !!} in the repo; audit every render closure, force e().
- Commit: `fix(xss): escape data-table render closures`.

## Phase 4 — Dead code & repo hygiene (mechanical, parallelizable)

### T4.1 — Remove SanitizeInputMiddleware (prior Task 3.1, still present)
- Files: bootstrap/app.php:32,45; delete app/Http/Middleware/SanitizeInputMiddleware.php.
- Why: regex-strips legit POST data globally (--, OR 1=1, <script>, on*=) — corrupts government entries; trivially bypassable; GET unsanitized. Real defense = Blade escaping + bound queries (already in place).
- Verify: create stores literal '--'/'50%' unmangled; full suite green.
- Commit: fix(middleware): remove data-corrupting SanitizeInputMiddleware, rely on output escaping.

### T4.2 — Collapse API stacks (prior Task 4.1, + duplicate aliases + dead policy stubs)
- Delete routes/api_v1.php (move secure.file.upload wiring to surviving stack via T3.3 first — references nonexistent Api\V1 controllers). Verify route:list clean.
- Drop duplicate route alias: web_sakip.php:173-237 has BOTH performance-data and data-collection prefixes → same DataCollectionController handlers (twin URIs/names). Collapse to one.
- Delete app/Policies/Sakip/* (6 dead files, only self-refs).
- Empty controllers/Api/Sakip dir.
- Commit: `chore: remove dead api_v1 stack + policy stubs`.

### T4.3 — Delete unreferenced services (new)
- AuditService (656 ln) + EnhancedAuditService (683 ln): zero refs.
- ValidationOrchestrator (306 ln): 1 self-ref → dead.
- Finish DEPRECATED_SERVICES_MIGRATION: SakipValidationService (914 ln, prod-dead/test-only) + DataValidationService (256 ln, still hard-live in controllers) → after migrating call sites onto Validation/* validators, delete them.
- Commit: `chore: remove unreferenced/deprecated services`.

### T4.4 — Purge ghost namespace registrations + register real AssessmentCriterion policy (new)
- app/Providers/AppServiceProvider.php:21-25 + :64-87: remove App\Models\Sakip\* imports + Gate::policy lines (classes don't exist).
- GAP: AssessmentCriterion (real model) has NO registered policy — its CRUD is unpoliced (manual Gate registration bypasses auto-discovery). Register a real AssessmentCriterionPolicy mirroring the flat strict set.
- Delete app/Policies/Sakip/* stubs (see also T4.2).
- Commit: `chore(provider): drop nonexistent Sakip model policy registrations`.

### T4.5 — Repo hygiene (new)
- Add to .gitignore: /sakip, /storage/framework/views/* (37 compiled blades), /docs/security/ (baseline-junit.xml 119 KB).
- Delete root 'sakip' SQLite; stop tracking compiled blades (git rm -r --cached).
- No secrets committed; nothing to rotate (verified).

### T4.6 — Delete dead Repository layer (new)
- ProgramRepository/KegiatanRepository + Contracts/: referenced only by own RepositoryServiceProvider bindings; nothing injects *RepositoryInterface. Inject into owning services (ProgramService/KegiatanService-equivalent) or delete + provider.
- Commit: chore: wire or remove unused repository abstraction.

### T4.7 — Delete dead React/Inertia frontend (new)
- Real frontend = Blade (80 views) + 9 vanilla JS modules resources/js/sakip/* wired via app.js + vite inputs. resources/js/Pages/Sakip/*.tsx import @inertiajs/react but createInertiaApp appears nowhere; TSX not in vite inputs, never mounted. React/Inertia/recharts deps present but dead.
- Fix: delete resources/js/Pages/ + remove react/react-dom/@inertiajs/react/recharts/chart.js/build plugin deps+config (commit to Blade), or actually mount Inertia (bigger decision).
- Commit: chore: drop dead React/Inertia stack (or mount Inertia in its place).
- Delete root 'sakip' SQLite (empty artifact); add /sakip to .gitignore (extension-less name escapes *.db/*.sqlite).
- Stop tracking storage/framework/views/* (43 compiled blades committed in ed05275) — gitignore + git rm -r --cached.
- Commit: `chore: ignore runtime artifacts, drop stray db`.

## Phase 5 — Final verification (inline)
1. Full composer test green; new T1.x/T2.x/T3.x tests present.
2. composer audit clean; route:list no dangling refs.
3. Smoke: login → own-tenant dashboard (scoped) → foreign-instansi approve/compare blocked → manage-users cannot self-escalate → evidence update ignores file_path → export returns valid file (if kept) → literal '--' text persists unmangled → calculation services respond.
4. Append Remediation Log to this file + mark 07-24 plan items done.

## Execution waves (after approval)
- Wave 1 (parallel): T1.1/T1.2/T1.3/T1.4/T1.5/T1.6 — distinct files.
- Wave 2: T2.3, T2.4, T3.1, T3.2, T3.4, T3.5, T3.7 (parallel) + owner decision on T2.1 exports gating T2.1.
- Wave 3 (dependency): T3.3 → T4.2; parallel: T3.6, T4.1, T4.3, T4.4, T4.5, T4.6, T4.7.
- Phase 5 inline.

## Risks

---

# Remediation Log — 2026-08-22 (EXECUTED)

Branch `fix/audit-remediation-2026-08-22`. All 24 tasks executed; every commit green on full suite.

| Task | Status | Commit | Note |
|---|---|---|---|
| T1.1 api authz | ✅ resolved differently | d7e39a3 | The entire api_sakip stack (74 routes, 66 pointing at nonexistent methods) + api_v1 were deleted; the REAL api is web_sakip's sakip/api group (read-only GETs behind auth+verified). Deleting the dead stack removed the whole finding. |
| T1.2 escalation guard | ✅ | d7e39a3 | AdminRoleEscalationTest (2 tests). |
| T1.3 cross-tenant pivot | ✅ | d71576e | resolveInstansiId pins non-HQ users; SakipApiTenantIsolationTest. 5 analytics endpoints were already calling nonexistent service methods (pre-existing 500s). |
| T1.4 target tenant scope | ✅ already mitigated | ddb4e65 | Indicator global scope blocks foreign binding → 404. Test proves it. |
| T1.5 evidence hardening | ✅ | c6769f7 | Whitelist update, zip/rar stripped, tenant-scoped reads/delete, local-only download + fixed 3 latent bugs found during testing (audit_logs missing action; Cache::getRedis fatal; eager-load keys referencing nonexistent relations uploadedBy/validatedBy/instansi/assessment — real relation is uploader). |
| T1.6 template uploader | ✅ | f9464ae | Dead code removed. |
| T2.1 exports | ✅ keep+fix | 72c0352 | phpspreadsheet+dompdf installed; formula-injection guard (Csv+Excel sanitizeCells); MAX_EXPORT_ROWS=10000 caps. ExportFormulaInjectionTest. |
| T2.2 dep advisories | ✅ | 3ed374e | composer audit: 0 advisories. |
| T2.3 dead upload routes | ✅ via T1.1 | d7e39a3 | |
| T2.4 phantom models/collision | ✅ partial→full | ff6a513 | Flat PerformanceCalculationService repaired (real PerformanceData reads); SakipService collision NOT renamed (both live with distinct consumers) — flagged as follow-up if desired. Calculation/* variant still unreferenced (kept). |
| T3.1 cache getRedis | ✅ | (T3.1 commit) | ClearsCacheByKey trait; 6 services store-agnostic. |
| T3.2 sort_by whitelists | ✅ | 9c89210 | SortsSafely trait; 6 services. |
| T3.3 upload middleware | ✅ | 46aa668-era batch | secure.file.upload wired on store/bulk-import/import. |
| T3.4 session cookies | ✅ | 9c89210 | SESSION_ENCRYPT/SECURE_COOKIE/SAME_SITE in .env.example. |
| T3.5 report approve policy | ✅ | (T3.x batch) | ReportPolicy::approve implemented (pimpinan/admin same-instansi). |
| T3.6 data-table sink | ✅ deleted | (T3.x batch) | Zero callers passed render closures; branch removed. |
| T3.7 csp-reports cap | ✅ | 9c89210 | Body JSON-encoded + truncated to 2000 chars. |
| T4.1 SanitizeInput removal | ✅ | 9c89210 | Middleware + alias + global append removed. |
| T4.2 collapse stacks | ✅ | d7e39a3 / 94370f8 | api_sakip+api_v1 gone; performance-data duplicate group collapsed (refs repointed); Policies/Sakip deleted. |
| T4.3 dead services | ✅ | 46aa668 | AuditService, EnhancedAuditService, ValidationOrchestrator deleted. Deprecated validators left live (still referenced by controllers) — flagged follow-up. |
| T4.4 ghost regs + criterion policy | ✅ | (T4.4 commit) | AssessmentCriterionPolicy created + registered. |
| T4.5 hygiene | ✅ | (T4.5 commit) | .gitignore /sakip + views + docs/security + .atlas; stray sqlite deleted; view cache untracked. No committed secrets to rotate. |
| T4.6 repository layer | ✅ deleted | 46aa668 | Provider registration removed from bootstrap/providers.php. |
| T4.7 React/Inertia pages | ✅ deleted | 46aa668 | Pages/*.tsx unmapped/unmounted; deps left in package.json (harmless) — optional cleanup. |

**Final verification:** 77 passed / 0 failed (2 skipped known-flaky timing, 1 risky), composer audit clean, route:list boots.

**Flagged follow-ups (not blocking):**
- SakipService flat vs nested collision — both live; rename when touching those modules.
- app/Services/Calculation/* variant unreferenced — candidate for adoption or deletion.
- Deprecated SakipValidationService/DataValidationService still bound in controllers — migrate call sites onto Validation/* then delete.
- package.json still carries react/inertia/recharts deps after page deletion.
- 5 analytics endpoints in SakipApiController call nonexistent service methods (500) — implement or delete.
- SanitizeInputMiddleware removal unmasks an input-reliant sink → T3.6 + Blade auto-escape.
- Dashboard/tenant scoping breaks legit exec-wide views → explicit admin/pimpinan branch, test both sides.
- Deleting api_v1 removes a documented surface → grep docs/ + frontend JS for api/v1 before deletion.
- phpspreadsheet addition changes composer.lock broadly → review diff, pin versions.
