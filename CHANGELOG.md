# Changelog

All notable changes to SAKIP are documented in this file.

## [v1.1.0] — 2026-08-22 — Security & Architecture Hardening

### Security
- Removed dead parallel API stacks (`api_sakip`, `api_v1`); the live API is the session-authenticated `sakip/api/*` group
- Blocked Super Admin self-escalation: `AdminService::assignRoles` allowlist
- Cross-tenant pivot fix: non-HQ users pinned to own institution on all API endpoints
- Evidence documents: server-pinned `file_path`, archives (zip/rar) blocked, tenant-scoped reads/deletes, private-disk download only
- Registered `AssessmentCriterionPolicy`; implemented `ReportPolicy::approve`
- Removed data-corrupting `SanitizeInputMiddleware`
- `sort_by` whitelists in 6 services
- Encrypted sessions + secure cookies; capped CSP-report log writes
- Dependency updates: guzzlehttp/guzzle 7.15.3, league/commonmark 2.10.0 — `composer audit` clean (0 advisories)

### Build & Fixes
- Restored export stack (`phpoffice/phpspreadsheet` + `dompdf`); formula-injection guard; export row cap 10,000
- Fixed 3 latent fatals: audit-log `action` column, `Cache::getRedis()` on non-redis stores (6 services), evidence eager-loads referencing nonexistent relations

### Maintenance
- Deleted unreferenced services (`AuditService`, `EnhancedAuditService`, `ValidationOrchestrator`), Repository layer, unmapped React/Inertia pages, `Policies/Sakip` stubs
- Collapsed duplicate `performance-data`/`data-collection` routes
- Repo hygiene: stray SQLite DB + compiled view cache gitignored

### Verification
- 77 tests passing, 0 failures (2 known-flaky skipped)
- CI green: lint, PHP 8.3 + 8.4 test matrix, security audit, frontend build

## [v1.0.0] — Security Hardened Release

Initial tagged release with tenant-scoped authorization, CSP/HSTS headers, and baseline test suite.
