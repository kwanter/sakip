# Plan: SAKIP Codebase Security Audit

## Objective
Produce fresh, evidence-backed security audit of SAKIP Laravel 12 app. Find vulnerabilities missed or introduced since prior audit (2026-01-23). Output: prioritized findings report (`SECURITY_AUDIT_<date>.md`) with file:line refs, exploit scenarios, severity (CVSS-ish), and concrete fixes. No code changes in this pass — audit only. User decides what to fix after.

## Affected Areas (read-only review)
- **Auth & sessions**: `app/Http/Controllers/Auth/*`, `config/auth.php`, `config/session.php`
- **Authorization**: `app/Policies/*`, routes middleware (`routes/*.php`), Spatie roles/perms (`config/permission.php`, seeders)
- **Multi-tenancy**: `app/Models/Scopes/*` (instansi scoping correctness — IDOR / cross-tenant leak risk)
- **Input & validation**: `app/Http/Requests/*`, `app/Http/Middleware/SanitizeInputMiddleware`, form requests per controller
- **File handling**: `SecureFileUploadMiddleware`, `EvidenceDocumentService`, `MaintenanceController` backup download, `CsvImportService`, export services
- **Mass assignment**: all models `$fillable` / `$guarded`
- **SQL injection**: raw queries (`DB::raw`, `whereRaw`, `orderByRaw`, DataTables)
- **XSS**: Blade views `{!! !!}`, JS files, Inertia React pages
- **CSRF**: route exemptions, API routes
- **Secrets & config**: `.env*`, `config/*`, `docker/*`, `composer.lock` versions
- **Deps**: `composer.lock` (known CVEs), `package-lock.json`
- **Infra**: `docker-compose.yml`, `Dockerfile`, nginx/apache confs, `php.ini`
- **Logging & error handling**: `config/logging.php`, Telescope exposure, debug routes
- **Dev/test residue**: test routes, debug endpoints, seeders w/ default passwords

## Methodology
**Static, manual + tool-assisted.** No live exploitation, no traffic to external services.

Audit tracks (run as parallel `explore` subagents where independent):
1. **AuthN/AuthZ/IDOR** — every controller route ↔ middleware ↔ policy mapping. Confirm tenant isolation. Check `show`/`update`/`destroy` use route-model-binding w/ policy + scope.
2. **Injection** — grep `raw|DB::statement|whereRaw|selectRaw|orderBy.*\$` across `app/`. Review DataTables query builders (`SakipDataTableService`).
3. **File upload/download** — MIME validation, extension whitelist vs `allowed_mime_types`, path traversal, symlinks, storage disk (public vs private), backup download authz.
4. **Mass assignment & scope bypass** — every `create`/`update`/`fill`. Soft-delete bypass (`withTrashed`/`forceDelete`). Global scope tampering.
5. **Secrets/CVEs** — `composer audit` + `npm audit` + manual scan of `.env.example`, docker configs, scripts for hardcoded creds. Check Laravel & Spatie versions against advisories.
6. **XSS/CSRF** — Blade unescaped output, JS `innerHTML`, React `dangerouslySetInnerHTML`. CSRF exempt routes, API auth.
7. **Crypto/sessions** — session cookie flags, password hashing (`bcrypt` rounds / `argon2`), reset tokens, signed routes.
8. **Infra/Docker** — exposed ports, default root pw in mysql, volume perms, `php.ini` `expose_php`, `display_errors`, nginx server tokens.
9. **Audit log integrity** — can a user tamper with their own `AuditLog` rows? Is it append-only?
10. **Privacy/PII** — feedback, profile, evidence docs — access control + retention + log redaction.

Tools/skills to use:
- `serena` MCP for symbol-level traversal (call graphs, find usages)
- `grep`/`ripgrep` for pattern sweeps
- `composer audit` and `npm audit` for CVEs
- Security skills: `php`, `laravel-patterns`, `code-review-checklist`, `web-design-guidelines` (for CSP), `supabase-best-practices` n/a (not used) — skip
- OWASP Top 10 2021 + Laravel-specific cheat sheet as checklist
- Existing docs as NEGATIVE controls (mark items as "previously fixed, re-verified" when confirmed)

## Steps
1. ✅ **Recon** — stack versions, env, Docker, prior security docs reviewed.
2. ✅ **Inventory attack surface** — route/middleware/controller/policy static inventory.
3. ✅ **Dependency audit** — Composer prod + npm prod lockfile advisories captured.
4. ✅ **Deep-scan tracks** — auth/tenant, injection/files, XSS/CSRF, infra/secrets reviewed (manual + Semgrep).
5. ✅ **Manual verification** — candidates triaged for reachability; false positives dropped.
6. ✅ **De-duplicate vs prior audit** — prior “fixed” claims re-verified; regressions noted.
7. ✅ **Draft report** — `SECURITY_AUDIT_2026-07-23.md` written.
8. ✅ **Sanity check** — PHP syntax scan, composer validate, dependency audits; full test suite blocked by missing vendor/node_modules.
9. ✅ **Present findings to user** — chat summary + full report.

## Progress
- Audit complete (read-only). No code fixes applied.

## Risks & Failure Modes (for the audit itself)
- **False positives** — mitigated: every finding cites file:line + minimal PoC.
- **False negatives** — mitigated: parallel tracks + checklist coverage matrix; manual spot-checks.
- **Time-boxed** — if a track goes deep, surface partial results + flagged unknowns rather than block.
- **No dynamic testing** — auth bypass that only manifests at runtime may be missed. Called out in report limitations.
- **Tool noise** — `npm audit` regurgitates many dev-only advisories; filtered to prod-relevant.

## Notes / Constraints
- **Read-only.** No fixes, no migrations, no config changes without separate approval.
- Report severity per CVSS v3.1 mental model but no formal scoring unless user asks.
- Scope: code in this repo only. Hosted environment, CI, cloud accounts out of scope.
- If a finding indicates active compromise or actively-exploited critical (e.g., public debug endpoints with secrets), escalate immediately inline before continuing.

## Out of Scope
- Performance/non-security bugs (except where they enable DoS — e.g., unbounded loops, missing rate limits on expensive ops).
- Frontend UX/accessibility review (separate ask).
- Writing fixes, patches, or migrations — that's a follow-up task after user reviews the report.
- Penetration testing against a running instance.
- Code in `vendor/`, `node_modules/` (only check declared versions for CVEs).

## Verification (how user knows audit is complete)
- `SECURITY_AUDIT_2026-07-23.md` exists with: exec summary, ≥1 finding per checked track or explicit "none found" + coverage matrix, severity table, file:line refs.
- Dependency audit JSON outputs archived in `.atlas/audit/` (or appended to report).
- Top findings summarized inline in chat with severity + 1-line fix each.
- Existing test suite status reported.
