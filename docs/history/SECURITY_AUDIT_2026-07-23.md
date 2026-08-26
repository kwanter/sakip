# SAKIP Security Audit

**Date:** 2026-07-23  
**Scope:** Repository source, first-party PHP/JS, routes, policies, migrations, lockfiles, Docker/runtime configuration  
**Method:** Read-only static review, Semgrep, Composer advisory audit, npm advisory audit, route/controller inventory, manual exploitability verification  
**Limitations:** No live target, database, `vendor/`, `node_modules/`, authenticated browser session, or dynamic penetration test

## Executive Summary

Current codebase is **not safe to deploy or distribute as-is**.

Most urgent issue is active data exposure: public GitHub repository contains a tracked MySQL backup with a user row, credential hash material, roles/permissions, audit data, institution data, and government-domain email data. Snapshot is present on `origin/main`, so deleting only local file will not contain incident.

| Severity | Count |
|---|---:|
| Critical | 1 |
| High | 9 |
| Medium | 3 |
| **Total confirmed findings** | **13** |

### Immediate Actions

1. Treat `SEC-001` as security incident. Restrict repository access if possible, preserve incident evidence, identify exposed identities/data owners, and assess notification obligations.
2. Remove backup from current tree **and Git history**. History rewrite and force-push are destructive/shared operations; require explicit owner approval and coordination with every clone/fork.
3. Rotate exposed account passwords, invalidate sessions/password-reset tokens if snapshot had them, rotate application/database/Redis credentials, and replace `APP_KEY` if any image built from local workspace was published.
4. Stop production deployment until tenant authorization, evidence storage, Docker context, dependencies, and audit trail are fixed.

## Findings

### SEC-001 — Public repository contains database backup with sensitive data

**Severity:** Critical  
**OWASP:** A02 Cryptographic Failures / A05 Security Misconfiguration  
**Evidence:** `docker/mysql/docker/mysql/backups/sakip_backup_20260123_200108.sql.gz`; introduced by commit `6192f45`; confirmed present on `origin/main` and public at `https://github.com/kwanter/sakip`.

Backup is valid gzip SQL and contains inserts for:

- `users`
- `roles`, `permissions`, `model_has_roles`, `role_has_permissions`
- `audit_logs`
- `instansis`, `sasaran_strategis`
- cache and migration tables

Safe inspection confirmed one `users` insert row and multiple government-domain email occurrences. Values were not copied into this report.

**Impact:** Anyone can download credential hashes and government/accountability data. Password cracking, account targeting, organizational reconnaissance, privacy breach, and audit-data disclosure become possible.

**Fix:**

- Restrict public access immediately.
- Purge file from all refs/history using coordinated `git filter-repo` or equivalent; remove cached GitHub objects through provider support if needed.
- Add broad ignores such as `**/backups/**`, `*.sql`, `*.sql.gz`, `*.dump`, `*.bak`.
- Rotate affected credentials and invalidate relevant authentication state.
- Review forks, Actions artifacts, container images, mirrors, and local clones.

---

### SEC-002 — Production Docker build copies local `.env` and database backup into image

**Severity:** High  
**OWASP:** A05 Security Misconfiguration / A08 Software and Data Integrity Failures  
**Evidence:** `.dockerignore:28-31`, `.dockerignore:39-40`, `Dockerfile:10-15`, `Dockerfile:95`, `Dockerfile:103-109`.

`.dockerignore` excludes `.env.local` and `.env.*.local`, but not `.env`. It also does not exclude nested backup directory. `COPY . .` therefore copies local `.env` and tracked SQL backup into image. Build then runs `key:generate` and `config:cache`, which can bake build-machine secrets into `bootstrap/cache/config.php`. Local `.env` currently has populated application, DB, and Redis secret-class keys; values are intentionally omitted here.

`composer.lock` is excluded from Docker context while Dockerfile expects it, so production dependencies resolve from version ranges instead of audited lock state.

**Impact:** Secrets and backup data can leak through image layers/registry access. Runtime environment overrides may not work after build-time `config:cache`. Builds are non-reproducible and may pull newly vulnerable dependencies.

**Fix:**

- Add `.env`, `.env.*`, `**/backups/**`, database dumps, logs, and local agent files to `.dockerignore`; explicitly keep only safe examples.
- Keep `composer.lock` in build context and require locked install.
- Never generate `APP_KEY` or cache environment-specific config during image build.
- Inject secrets at runtime through orchestrator secret mechanisms; run `config:cache` only after runtime environment is present.
- Scan and rebuild every published image; rotate secrets if any affected image left workstation.

---

### SEC-003 — DataTables indicator API leaks records and identifiers across institutions

**Severity:** High  
**OWASP:** A01 Broken Access Control  
**Evidence:** `routes/api_sakip.php:24-26`, `routes/api_sakip.php:434-438`, `routes/api_sakip.php:451-454`, `app/Http/Controllers/Api/Sakip/SakipDataTableController.php:49-58`, `app/Http/Controllers/Api/Sakip/SakipDataTableController.php:101-112`, `app/Services/SakipDataTableService.php:53-65`, `app/Services/SakipDataTableService.php:90-94`.

All authenticated users with generic `view-dashboard` permission can query indicator DataTables endpoint and its export endpoint. Service uses `DB::table` and never constrains results to `auth()->user()->instansi_id`. Client-supplied `instansi_id` is trusted as filter, including other institutions. Responses include indicator IDs and institution names. Export path forces `per_page = 999999`, returning all matched records.

Program/activity/report handlers are currently broken by pluralization mismatches, so this finding is limited to indicator path.

**Impact:** Ordinary users can enumerate and bulk-export cross-tenant indicators and identifiers. IDs enable follow-on IDOR attacks.

**Fix:**

- Derive tenant ID from authenticated user; ignore tenant filter for non-super-admin users.
- Use policy-aware Eloquent query or add mandatory tenant predicate before optional filters.
- Require indicator-view permission, not generic dashboard access.
- Add two-tenant feature tests proving both list, explicit filter, and export isolation.

---

### SEC-004 — Master-data policies allow cross-tenant reads and writes

**Severity:** High  
**OWASP:** A01 Broken Access Control  
**Evidence:** `app/Policies/InstansiPolicy.php:10-42`, `app/Policies/ProgramPolicy.php:10-42`, `app/Policies/SasaranStrategisPolicy.php:10-32`, `app/Http/Controllers/Sakip/ProgramController.php:193-200`, `app/Http/Requests/Sakip/ProgramFormRequest.php:34-45`, `app/Http/Controllers/Sakip/SasaranStrategisController.php:127-141`, `database/seeders/RolesAndPermissionsSeeder.php:100-115`, `database/seeders/RolesAndPermissionsSeeder.php:184-199`.

Policies authorize by role only and return `true` for any authenticated user on reads. Update methods do not compare resource institution with user institution. Assessor and Government Official roles are seeded with master-data management access. Update requests accept `instansi_id`, allowing authorized role to edit or reassign another institution's records.

**Impact:** Cross-institution data tampering and disclosure. Government performance hierarchy can be changed by users outside owning institution.

**Fix:**

- Add same-instansi checks to `view`, `update`, and `delete`; reserve cross-tenant behavior for explicit Super Admin bypass.
- Force `instansi_id` from authenticated context for tenant users; reject client-controlled reassignment.
- Add global tenant scopes consistently or centralize tenant query constraints.
- Review whether Assessor/Government Official should have mutation rights at all.

---

### SEC-005 — Record policies permit cross-tenant PerformanceData and Assessment access

**Severity:** High  
**OWASP:** A01 Broken Access Control  
**Evidence:** `app/Policies/PerformanceDataPolicy.php:25-45`, `app/Policies/AssessmentPolicy.php:19-42`, `app/Policies/AssessmentPolicy.php:121-124`, `app/Http/Controllers/Sakip/DataCollectionController.php:422-423`, `app/Http/Controllers/Sakip/AssessmentController.php:352-353`.

`PerformanceDataPolicy::view()` grants access to any record when user has generic `view-data-collection-forms`; no institution check. `AssessmentPolicy::view()` grants access to every assessment to anyone with `view-assessment-reports`; no institution check. `AssessmentPolicy::assess()` checks only permission, not performance-data tenant.

Route model binding retrieves these models without global tenant scope. UUIDs reduce guessing but are not authorization and can leak through logs, links, exports, and `SEC-003`-style APIs.

**Impact:** Cross-tenant performance records and assessments can be viewed; assessors may create assessments against another institution's performance data.

**Fix:**

- Require `user->instansi_id === resource tenant` in policy methods, except explicit global roles.
- Derive Assessment tenant through `performanceData.performanceIndicator` if table has no direct `instansi_id`.
- Scope route binding or use `scopeBindings()` where nested.
- Add BOLA tests using known foreign UUIDs.

---

### SEC-006 — Evidence documents are stored under public URLs, bypassing policy

**Severity:** High  
**OWASP:** A01 Broken Access Control / A02 Cryptographic Failures  
**Evidence:** `config/filesystems.php:40-47`, `config/filesystems.php:75-77`, `app/Http/Controllers/Sakip/DataCollectionController.php:931-958`, `app/Services/EvidenceDocumentService.php:45-63`, `app/Models/EvidenceDocument.php:176-179`, `resources/views/sakip/data-collection/show.blade.php:186-193`.

Evidence uploads use `public` disk and application creates `/storage/...` URLs. Blade links directly to files rather than an authorized controller. `EvidenceDocumentPolicy::download()` exists but is bypassed by web server/static storage.

**Impact:** Anyone possessing or receiving URL can download evidence without authentication, institution checks, document status checks, or audit logging. Evidence may contain sensitive government documents and PII.

**Fix:**

- Store evidence on private disk outside web root.
- Serve through controller that loads model, calls `authorize('download', $document)`, and streams file.
- Use short-lived signed URLs only after authorization if offloading storage.
- Migrate existing public files and invalidate old URLs.

---

### SEC-007 — Production lockfiles contain known vulnerable dependencies

**Severity:** High  
**OWASP:** A06 Vulnerable and Outdated Components  
**Evidence:** `composer.lock:645-646`, `composer.lock:1056-1057`, `composer.lock:1464-1465`, `package-lock.json:1737-1738`, `package-lock.json:2347-2348`, `package-lock.json:2876-2884`, `package-lock.json:3085-3086`.

`composer audit --locked --no-dev` reports **26 production advisories**: 3 high, 21 medium, 1 low, 1 unclassified. Affected packages include:

- `laravel/framework v12.48.1` — advisories fixed in later 12.x releases, including signed URL path confusion and email validation CRLF injection.
- `guzzlehttp/guzzle 7.10.0`, `guzzlehttp/psr7 2.8.0` — cookie, proxy, host confusion, and CRLF advisories.
- `league/commonmark 2.8.0` — raw HTML/embed restriction bypasses.
- Symfony HTTP, routing, mailer, MIME, IDN, and process components.
- `psy/psysh 0.12.18`; development `phpunit 11.5.48` separately has unsafe deserialization advisory.

`npm audit --package-lock-only --omit=dev` reports **6 production vulnerabilities**: 4 high, 2 moderate. Affected packages include `axios 1.13.6`, `form-data 4.0.5`, `follow-redirects 1.15.11`, `lodash/lodash-es 4.17.23`, and `qs 6.15.0`.

Not every advisory is reachable from app behavior, but framework/parser/network packages form broad attack surface and must be patched.

**Fix:**

- Upgrade Laravel to supported patched 12.x; update Guzzle, PSR-7, CommonMark, Symfony components, PsySH, and PHPUnit within compatible constraints.
- Run `composer update` in isolated branch, then `composer audit --locked --no-dev` and full tests.
- Run `npm audit fix` or targeted upgrades; verify build and browser flows.
- Add Composer/npm audit gates to CI and retain lockfiles in production builds.

---

### SEC-008 — Production seeding can create Super Admin and destroy permission state

**Severity:** High  
**OWASP:** A05 Security Misconfiguration / A07 Identification and Authentication Failures  
**Evidence:** `database/seeders/AdminUserSeeder.php:34-42`, `database/seeders/AdminUserSeeder.php:74-99`, `database/seeders/DatabaseSeeder.php:24-28`, `database/seeders/RolesAndPermissionsSeeder.php:21-27`, `README.md:185-187`, `DOCKER.md:47-49`.

Production guard in `AdminUserSeeder` is commented out. Default `DatabaseSeeder` always calls it and prints generated Super Admin password to command output. Same seed flow also calls `UserSeeder`, while `RolesAndPermissionsSeeder` truncates role/permission tables and disables foreign-key checks. Documentation tells operators to run generic `db:seed` / `migrate --seed`.

**Impact:** Production operator can accidentally create globally privileged account, leak password into terminal/CI logs, or reset access-control state.

**Fix:**

- Restore hard production block in all demo/admin seeders.
- Remove privileged/test seeders from default `DatabaseSeeder`.
- Never print passwords; use one-time secure provisioning/reset flow.
- Make role seeding idempotent (`firstOrCreate`/`syncPermissions`) and never truncate production ACL tables.
- Correct deployment docs.

---

### SEC-009 — Audit trail silently drops security fields and audit dashboard is broken

**Severity:** High  
**OWASP:** A09 Security Logging and Monitoring Failures  
**Evidence:** `app/Models/AuditLog.php:19-27`, `app/Http/Controllers/Sakip/DataCollectionController.php:603-610`, `app/Http/Controllers/Sakip/ReportController.php:542-549`, `app/Http/Controllers/Sakip/SakipAuditController.php:58-60`, `app/Http/Controllers/Sakip/SakipAuditController.php:387-389`, `database/migrations/2025_10_14_051742_create_audit_logs_table.php:14-24`, `database/migrations/2025_10_14_080007_enhance_audit_logs_for_sakip.php:17-39`.

`AuditLog::$fillable` includes only `user_id`, `action`, `details`, `ip_address`, and `user_agent`. Most calls write `instansi_id`, `module`, `description`, `old_values`, and `new_values`; Eloquent silently discards these attributes. Some fields (`description`, `old_values`, `new_values`) do not exist in migrations at all.

Audit dashboard filters `module = SAKIP` and eager-loads `instansi`, but model has no `instansi()` relationship. Result: incomplete records and dashboard query failure/empty trail. Existing masking protects only `details`, not alternate data fields used throughout code.

**Impact:** Critical actions cannot be reconstructed reliably. Incident detection, accountability, tamper review, and compliance evidence fail.

**Fix:**

- Define one canonical audit schema/model and remove competing field vocabularies.
- Add missing columns/casts/relations or write all event payload into masked `details` JSON.
- Enable `Model::preventSilentlyDiscardingAttributes()` outside production tests to catch drift.
- Make logs append-only at DB/app permission level; define retention and integrity controls.
- Add tests asserting persisted tenant, actor, action, before/after, IP, and masking.

---

### SEC-010 — Docker deployment exposes stateful services with weak defaults

**Severity:** High  
**OWASP:** A05 Security Misconfiguration  
**Evidence:** `docker-compose.yml:33`, `docker-compose.yml:51-53`, `docker-compose.yml:68-71`, `docker-compose.yml:81-86`, `docker-compose.yml:97-102`, `docker/php/php.ini:15-17`, `.env.docker.example:8-11`, `.env.docker.example:23-27`, `.env.docker.example:43-44`, `.env.docker.example:61-68`.

Production-default Compose publishes MySQL and Redis on all host interfaces. MySQL falls back to root password `root` and app password `secret`. Redis password example is empty. App configuration advertises HTTP; PHP forces `session.cookie_secure = 0`. Port 443 is mapped, but repository contains no working TLS virtual host/certificate configuration for Apache.

**Impact:** On Internet- or LAN-reachable host, database/cache/session/queue data may be attacked directly. Session cookies can traverse HTTP. Weak defaults become real credentials when operators omit environment variables.

**Fix:**

- Remove MySQL/Redis host port publication in production; keep internal Docker network only or bind explicitly to `127.0.0.1` for local development.
- Fail startup when DB root/app/Redis credentials are absent; remove default secrets.
- Terminate TLS correctly and set `APP_URL=https://...`, `SESSION_SECURE_COOKIE=true`.
- Separate development and production Compose files.

---

### SEC-011 — Nested target routes do not bind target to indicator

**Severity:** Medium  
**OWASP:** A01 Broken Access Control  
**Evidence:** `routes/web_sakip.php:86-123`, `app/Http/Controllers/Sakip/TargetController.php:152-176`, `app/Http/Controllers/Sakip/TargetController.php:246-248`, `app/Http/Controllers/Sakip/TargetController.php:290-304`.

Routes contain both `{indicator}` and `{target}` but do not call `scopeBindings()`. Controller authorizes indicator only for edit/update/delete and separately checks generic permission for approve/reject/revise. It never verifies `target.performance_indicator_id === indicator.id` or calls target policy.

**Impact:** User with relevant indicator/target permission can combine an authorized indicator UUID with another target UUID and mutate wrong target. Exploit requires target UUID and permission, but tenant checks on parent do not protect child.

**Fix:** Enable scoped bindings, load target through `$indicator->targets()`, and authorize target itself for every operation.

---

### SEC-012 — CSP does not provide claimed script-injection protection

**Severity:** Medium  
**OWASP:** A03 Injection / A05 Security Misconfiguration  
**Evidence:** `app/Http/Middleware/SecurityHeadersMiddleware.php:80-107`, `public/js/custom-scripts.js:267-283`, `app/Providers/AppServiceProvider.php:182-193`, `resources/views/layouts/app.blade.php:321+`, `resources/views/layouts/modern.blade.php:327+`.

Production `script-src` always allows both `'unsafe-inline'` and `'unsafe-eval'`. Global handler executes arbitrary `data-onclick` content using `new Function`. Nonce helper exists but audit found no Blade usage. CSP report endpoint is declared but no route handles `/api/csp-reports`.

**Impact:** CSP cannot meaningfully contain HTML/script injection. Any attacker-controlled `data-onclick` attribute becomes code execution. Documentation labels this pattern "safe" and CSP-compliant, increasing maintenance risk.

**Fix:** Replace code strings with named event handlers/data parameters, remove `new Function`, move inline scripts to external bundles or nonce every required block, remove `'unsafe-eval'` and production `'unsafe-inline'`, and add/report-test CSP endpoint.

---

### SEC-013 — DataTables accepts unbounded pagination and export dump

**Severity:** Medium  
**OWASP:** A04 Insecure Design  
**Evidence:** `app/Services/SakipDataTableService.php:116-121`, `app/Http/Controllers/Api/Sakip/SakipDataTableController.php:101-112`.

`page` and `per_page` come directly from request without numeric validation or upper bound. Export endpoint forces `per_page = 999999`. Authenticated user can request huge result sets repeatedly. Generic API limiter reduces frequency but not query/response size.

**Impact:** Excessive DB load, memory usage, response size, and availability degradation; amplifies `SEC-003` data exposure.

**Fix:** Validate positive integers; clamp `per_page` to small maximum (for example 100), ban export dump-all shortcuts, cap page/offset, and allowlist sortable columns.

---

## Defense-in-Depth Gap (not counted)

CSV/Excel-capable services write database strings to spreadsheet cells without formula neutralization (`app/Services/Export/CsvExportService.php:41-49`, `app/Services/ReportService.php:355-383`). Current route wiring does not expose these services as a confirmed reachable download flow, so this is not scored as a vulnerability. Before enabling exports, force text or prefix values beginning with `=`, `+`, `-`, `@`, tab, or carriage return and add formula-payload tests.

## Reliability Blockers Affecting Security Validation

These are not counted as security findings but prevent confidence in controls:

1. **PHP parse error:** `app/Http/Controllers/FeedbackController.php:30-34` uses `validate([[ ... ])`; first-party PHP syntax scan found exactly one syntax failure.
2. **Active API drift:** Static inventory found 77 actions in `routes/api_sakip.php`; 67 unique action names have no public controller method. Many protected routes therefore fail at runtime. DataTable `programs`/`activities`/`reports` also fail because service method names do not match controller type plurals.
3. **Tests unavailable:** `vendor/` and `node_modules/` are absent. `php artisan route:list`, `composer test`, and frontend build cannot run. Composer metadata validation passed.
4. **Prior audit regression:** `SECURITY_FIXES_IMPLEMENTATION.md` says all recommendations are production-ready, but feedback fix is syntactically invalid and audit/security behavior remains broken. Prior claims were re-verified rather than trusted.

## Tool Results

| Check | Result |
|---|---|
| PHP syntax scan (`app routes config database bootstrap tests`) | 1 failure: `FeedbackController.php` |
| `composer validate --strict --no-check-publish` | Pass |
| `composer audit --locked --no-dev --format=json` | Fail: 26 production advisories |
| `npm audit --package-lock-only --omit=dev --json` | Fail: 6 production vulnerabilities (4 high, 2 moderate) |
| Semgrep auto rules | 27 raw findings; manually triaged |
| `php artisan route:list` | Blocked: `vendor/autoload.php` missing |
| `composer test` | Blocked: `vendor/` missing |
| `npm run build` | Blocked: `node_modules/` missing |

### Semgrep Triage Notes

Excluded from confirmed findings after manual review:

- Raw aggregate queries use constant SQL or parameter placeholders.
- `whereRaw("QUARTER(period) = ?", [$quarter])` is parameterized.
- Backup shell arguments are escaped and sourced from deployment config, not request input; still should move to process-array APIs for defense-in-depth.
- Several JS XSS/ReDoS candidates are legacy/unreferenced files or use locally generated data. Reachability was not proven.
- `orderBy($sortBy, $sortOrder)` needs allowlisting and validation, but Laravel quotes identifiers and validates direction; classified as robustness/DoS risk rather than confirmed SQL injection.

## Positive Controls Re-verified

- Login uses generic failure message and regenerates session after authentication: `app/Http/Controllers/Auth/LoginController.php:29-46`.
- Logout invalidates session and regenerates CSRF token: `app/Http/Controllers/Auth/LogoutController.php:10-16`.
- Email verification uses authenticated `EmailVerificationRequest` plus signed middleware: `routes/web.php:70-87`.
- Web state-changing routes retain Laravel CSRF middleware; no CSRF exemptions found.
- File middleware validates size, extension, MIME, extension/MIME pairing, and executable markers: `app/Http/Middleware/SecureFileUploadMiddleware.php:151-282`.
- Backup download uses basename whitelist and realpath containment: `app/Http/Controllers/Admin/MaintenanceController.php:156-194`.
- Report download calls policy before streaming: `app/Http/Controllers/Sakip/ReportController.php:580-613`.
- No confirmed request-driven OS command injection, unsafe deserialization, or SSRF sink found.

## OWASP Top 10 Coverage Matrix

| Category | Coverage | Result |
|---|---|---|
| A01 Broken Access Control | Routes, middleware, policies, tenant scopes, nested bindings, files | Multiple High findings |
| A02 Cryptographic Failures | Secrets, backups, storage, sessions/TLS | Critical + High findings |
| A03 Injection | SQL/raw queries, shell, XSS, CSV | CSP finding; CSV defense gap; no confirmed SQL/OS injection |
| A04 Insecure Design | Tenant model, pagination, workflow boundaries | Medium finding |
| A05 Security Misconfiguration | Docker, headers, debug/Telescope, seeders | Multiple High/Medium findings |
| A06 Vulnerable Components | Composer/npm lockfiles | High finding |
| A07 Auth Failures | Login, logout, verification, seeders | Seeder finding; core login controls positive |
| A08 Data/Software Integrity | Lockfiles, Docker context, audit data | High findings |
| A09 Logging/Monitoring | Audit schema/model/controllers | High finding |
| A10 SSRF | HTTP clients, URL/file sinks | No confirmed request-driven SSRF sink |

## Remediation Order

1. **Incident containment:** `SEC-001`.
2. **Secret/build containment:** `SEC-002`, `SEC-010`.
3. **Tenant boundary:** `SEC-003`, `SEC-004`, `SEC-005`, `SEC-011`.
4. **Private evidence:** `SEC-006`.
5. **Patch dependencies:** `SEC-007`.
6. **Production privilege safety:** `SEC-008`.
7. **Restore audit integrity:** `SEC-009`.
8. **Browser hardening:** `SEC-012`.
9. **Resource limits and reliability blockers:** `SEC-013`, parse/routes/tests.

## Verification Required After Fixes

- Two-tenant feature suite covering index, show, create, update, delete, export, target nesting, and evidence download.
- Anonymous and foreign-tenant evidence URL tests return 401/403/404 and never static file content.
- Clean Docker build from CI context contains no `.env`, dump, backup, log, session, or local cache; runtime config reflects injected secrets.
- `composer audit --locked --no-dev` and `npm audit --omit=dev` pass accepted policy.
- CSP browser test has no `unsafe-eval`, no production `unsafe-inline`, and reports violations to working endpoint.
- Audit test proves actor, tenant, action, before/after, timestamp, IP, and masked sensitive data persist and cannot be updated/deleted by app users.
- `php -l`, route cache, config cache, view cache, test suite, and frontend build pass from clean checkout.
