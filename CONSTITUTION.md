# Project Constitution

> **Project:** SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
> **Mission:** Operate the Indonesian government performance-accountability pipeline end to end — performance planning, indicator and target setting, data collection, assessment, and reporting — so that agency performance data is complete, verifiable, instansi-scoped, and auditable.
> **Technology:** Laravel 12, PHP 8.3+ (CI matrix 8.3/8.4), Blade, Bootstrap 5.3 (CDN) + `public/css/modern-sakip.css`, Vite 7 + Tailwind CSS 4, Chart.js 4, MySQL 8/SQLite, Spatie Laravel Permission, PHPUnit 11, Laravel Pint, GitHub Actions, Docker Compose.
> **Established On:** 2026-09-24

<!-- markdownlint-disable -->

## Core Principles

### I. Test-First Mandate (NON-NEGOTIABLE)

- Functional code is never written before a failing automated test exists at a pre-agreed public seam.
- The Red-Green-Refactor cycle is required for features and bug fixes.
- Tests assert observable state and outcomes (HTTP status, persisted rows, rendered content, service return values) rather than private implementation details or incidental interactions.
- Every behavior change includes focused regression coverage and must pass the full relevant test suite.

### II. Specification-Driven Truth

- Product behavior and technical contracts are defined in approved artifacts under `docs/`, `/spec/`, `/plan/`, and `docs/adr/` before implementation.
- Specifications govern behavior; code is the implementation of those specifications.
- When implementation reveals a specification gap or contradiction, the upstream artifact is clarified before behavior is extended.

### III. Vertical Slicing (Tracer Bullets)

- Features are delivered as complete, demonstrable vertical slices across migration/model, service, authorization, route, and Blade view as applicable.
- Layer-by-layer horizontal implementation is prohibited when it prevents an independently verifiable user outcome.
- Each slice must include automated tests at its public seams, including tenant-isolation and authorization coverage whenever the slice touches instansi-scoped data.

### IV. Domain Language and Workflow Fidelity

- Use canonical SAKIP terms consistently in domain-facing artifacts and tests: `Instansi`, `SasaranStrategis`, `Program`, `Kegiatan`, `PerformanceIndicator`, `Target`, `PerformanceData`, `Assessment`, `EvidenceDocument`, `Report`, `AuditLog`.
- Preserve the performance-data lifecycle and its transitions: `draft → submitted → validated → approved`. Transitions are executed through the service layer, never as ad-hoc controller writes.
- Preserve tenant isolation: models holding a direct `instansi_id` column retain the `InstansiScope` global scope. Bypassing that scope is permitted only on explicitly authorized admin/report paths and must be covered by a test.
- Preserve data-integrity conventions: UUID primary keys, soft deletes on data-tracking models, and `AuditLog` entries for state-changing actions.
- User-facing strings are written in Bahasa Indonesia; identifiers, code, and SDLC documentation are written in English.
- Accessibility is a floor, not a feature: WCAG AA contrast, visible focus indicators, and meaning never conveyed by color alone.

### V. Simplicity and Minimal Footprint (Rule 0)

- Choose the simplest implementation that satisfies the approved specification and passing tests.
- Avoid speculative abstractions, duplicate sources of truth, and unrelated refactoring.
- Preserve existing project conventions (service layer for business logic, thin controllers, `app/Constants` for system-wide values, Laravel policy-based authorization) and minimize the blast radius of each change.
