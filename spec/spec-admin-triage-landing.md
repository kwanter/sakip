---
title: Admin Triage Landing — Period-Scoped, Instansi-Explicit Triage Read Model
version: 1.5
date_created: 2026-09-24
date_revised: 2026-09-25
status: Draft (v1.5 — review remediation closed; implementation complete)
remediation_of: docs/audit/clarification-report-spec-admin-triage-landing-2026-09-24.md
remediation_of_iteration_2: docs/audit/clarification-report-spec-admin-triage-landing-iteration-2-2026-09-24.md
remediation_of_consistency_audit: docs/audit/consistency-audit-admin-triage-landing-2026-09-24.md
upstream_prd: docs/prd/prd-admin-triage-landing.md
target_plan: /plan/plan-admin-triage-landing.md
---

<!-- markdownlint-disable -->

# Technical Specification: Admin Triage Landing (Landing Triage)

> **Grounding:** every technical fact below was re-verified against the working tree at `95ef4cc` on 2026-09-24 (`file:line` evidence inline). Upstream: `docs/prd/prd-admin-triage-landing.md` (v1.1, Readiness 82/100 after remediation of
> `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md`), `docs/discovery/idea-admin-triage-landing.md` §5 (four target seams), `CONTEXT.md` (seven canonical terms), `CONSTRAINTS.md` §2–§3, `CONSTITUTION.md` Prinsip I–V,
> `docs/ARCHITECTURE.md` §5–§6 and §10.
>
> **ADR status:** no ADR is created by this specification. The Triple Gate (`.agents/standards/ADR-FORMAT.md`) is unmet for every decision taken here — each is reversible, would not surprise a new engineer, and was an ordinary engineering call. This matches
> the clarification report's recorded verdict (§4). Rationale for the decisions is recorded in §9 instead.
>
> **Remediation note (v1.1).** This revision applies the mandatory correction list of `docs/audit/clarification-report-spec-admin-triage-landing-2026-09-24.md` (Iteration 1, Readiness 78/100, six
> critical findings C-1…C-6, ten corrections). Applied here: AC-033 rescoped to the triage region (C-1), the impossible S2 ambient-auth case moved to S3 (C-2), the S2 Given restated as an explicit fixture
> matrix (C-3), figure↔target agreement made assertable (C-4, C-5), the canonical assessment population fixed (C-6), `anchor()` precedence and the `active_year` cast pinned (C-7, C-8), and the factual
> corrections to §4.6, the configuration seam, the negative-assertion idiom, and the S4 viewer (C-9, corrections 8–10). **No ratified product decision was changed.** Decisions taken on the audit's
> recommendation are tagged `[ASSUMPTION]` in §1.2, §4.3 and §9.1 for Iteration 2 to interrogate.
>
> **Remediation note (v1.2).** This revision applies the Iteration-2 refinement list (D-1…D-8 plus the process note) of
> `docs/audit/clarification-report-spec-admin-triage-landing-iteration-2-2026-09-24.md` §4: a new S1 precedence criterion (AC-039, D-1), a falsifiable `(int)` cast in AC-004 (D-2), a scope clause on AC-037
> (D-5), the defined trashed-parent fixture **F-12** behind F-8 (D-4), and four one-line alignments (D-3 `instansis` needle, D-6 §4.3 reference, D-7 list formatting, D-8 REQ-007 wording). **No criterion was
> weakened and no decision was changed**; Readiness 89/100 → projected ≈97/100.
>
> **Remediation note (v1.3).** This revision applies the three Spec-side findings of `docs/audit/consistency-audit-admin-triage-landing-2026-09-24.md`. **A-2:** the phase allocation is corrected — FEAT-006
> (telemetry removal) belongs to **Phase 1**, matching §4.4, AC-033 and PRD §9 v1.2, and Phase 2 is FEAT-007 plus US-005 only. **A-3:** REQ-002 now carries the `[ASSUMPTION]` tag that PRD §7.5 item 2 points to,
> because the parameter name was frozen without a recorded product-owner confirmation. **A-4:** §4.2 states why the assessment filter value is held in a local constant, and records the decision about
> `ReportStatus`. **A-1 required no Spec change** — the Spec already recorded the ratified demotion; the PRD was the stale side and was amended to v1.2.
>
> **Post-verification addendum (Iteration 3).** The independent consistency audit confirmed A-1…A-4 as closed and found one residual **inside this Spec**: `REQ-011` still annotated FEAT-006 as "Phase 2" while §1,
> §4.4 and AC-033 place the telemetry removal in **Phase 1**. The annotation is corrected above (finding **A-11**), leaving no self-contradiction in this document. The two PRD-side residues (A-9, A-10) were
> corrected in PRD v1.2.
>
> **Remediation note (v1.4).** This revision applies the Spec-side findings of `docs/review/code-review-admin-triage-landing-2026-09-25.md` and nothing else. **`SPEC-B-02`** is closed in code, not here: the
> `data-triage-period-select` handle that §4.4 declares is now rendered by `resources/views/admin/dashboard.blade.php` and asserted by TC-051. **`SPEC-B-01`:** §4.5's Phase-1 "pendingDataCount continuity" clause is
> replaced by the delivered badge ownership (composer-only, from the first release), including the five-commit window in which no `layouts.modern` page rendered a badge. **`SPEC-B-04`:** §4.4's Phase-2 budget row for the
> unassigned state is corrected from `2` to `1` and the measured `5 / 6 / 1` is recorded, so §9.4 obligation 3 can be marked discharged rather than left as an instruction that would have produced a false assertion. **`SPEC-B-03`**
> is closed by a new S3 case (`test_switching_period_moves_only_the_verification_figure`, AC-028) because the criterion had no test at any seam; the Spec's own AC-028 text needed no change. No requirement was weakened, no
> acceptance criterion was removed, and no ratified decision was altered. **`STD-A-03` (added by the same execution):** §4.1 no longer declares `isYearScoped()` — the flag had zero production call sites, and the only predicate the
> deep-link rule can act on is `isSingleMonth()`, so the method was removed from the class rather than kept as declared-but-unused API. TC-004 now asserts the single-month flag alone; the checklist line for TC-004 keeps its
> older wording until the Phase-4 inventory reconciliation (TASK-403).
>
> **Remediation note (v1.5).** This revision records the two product-owner decisions that closed the review's last two findings, and nothing else. **Decision 1A (`SPEC-B-06`):** the PRD's long-agency-name criterion is
> **delivered**, not deferred — §4.4 now specifies the `triage-scope-chip` truncation contract (ellipsis beyond ≈49ch, with the untruncated name kept in `title`) and TC-072 asserts it at seam S3; the rendered ellipsis
> itself needs a browser, so it joins the deferred manual set. **Decision 2B (`SPEC-B-05`):** the three manually measured metrics are formally re-scoped to `[Assumed / Backlog]` by the product owner — §9.4 obligation 2 is
> discharged as a deliberate deferral rather than left as an unverifiable expectation, and the decision of record is PRD §6. Coverage is **not** part of that re-scope and remains an unmet, unverified gate.

## 1. Purpose & Scope

This specification defines the technical contracts for the **Landing Triage** vertical slice: the HQ landing at `GET /admin/dashboard` (`routes/web.php:205`, name `admin.dashboard`) becomes a period-scoped, instansi-explicit triage read model whose three
figures deep-link into the queues they describe.

It fixes, in machine-readable form: the period resolver contract (seam S1), the triage read-model contract (seam S2), the HTTP contract and render contract of the landing (seam S3), the sidebar-badge rendering seam (seam S4), the deep-link expressibility
rules, the Indonesian copy that CI asserts, the domain-query budget, and the authorization correction of finding C1.

**Delivered in two phases inside one specification:**

- **Phase 1 (MVP vertical slice)** — FEAT-001…FEAT-005, FEAT-008 and US-001…US-004, US-006, US-007 (PRD §9). Test seams S1, S2, S3. **FEAT-006 (telemetry removal) also belongs to Phase 1** — the Phase-1
  rewrite in §4.4 and §7.2 removes the telemetry figure from the triage row and AC-033 asserts its absence; the allocation is stated here to match PRD §9 v1.2 (consistency finding A-2).
- **Phase 2 (Edge cases & polish)** — FEAT-007 and US-005 (PRD §9 v1.2). Test seam S4. Phase 2 reuses the S1/S2 contracts unchanged; it adds no new domain concept.

### 1.1 Out of Scope

- **Audit-anomaly detection.** Requires its own rule design (idea **I2**); no anomaly concept exists in `app/` today.
- **Charts, trends, visualisations.** HQ triage needs counts and links first.
- **Bulk actions, inline approvals, an actionable inbox.** Rejected as discovery Option C.
- **Layout-shell defects** (duplicate H1, inert header search, permanent notification dot, dark-mode token remapping, mobile `.page-header` margins) — these are `layouts/modern.blade.php` concerns, not triage concerns. The existing H1/page-title copy
  (`Panel Admin`) is deliberately left untouched.
- **Any change to workflow states** (`draft → submitted → validated → approved`) or to `InstansiScope` semantics.
- **Schema changes.** This feature is read-only: no migration, no new column, no new index, no new persisted state, therefore no new `AuditLog` obligation and no retention change.
- **Caching.** D6: no caching in Phase 1. A caching decision with defined invalidation is Phase 3 (PRD §9) and must not be smuggled in.
- **Backlog F1** (2 skipped rate-limit tests) and **F2** (63 PHPUnit-12 deprecations) — separate routes.
- **`layouts/app.blade.php`** — it renders its own sidebar but carries no `sidebar-link-badge` today (verified: the badge exists only in `layouts/modern.blade.php:91-92`). FEAT-007 concerns `layouts.modern` only.

### 1.2 Open Questions & Assumptions

> [!NOTE]
> **A1 — RESOLVED (product owner, 2026-09-24).** PRD §7.5 item 1 asked the Spec to either confirm the `created_at`-year anchor for **Antrean Asesmen** or demote the figure to the labelled period-independent variant permitted by FEAT-003. The
> product owner ratified **demotion**: the assessment figure is **not period-scoped in Phase 1** and carries the explicit period-independent basis label `Tidak dibatasi periode`, exactly like **Antrean Laporan**. Consequences, all applied in this revision:
> §2 term table, REQ-007/REQ-008, §4.1 (one shared `PERIOD_INDEPENDENT_BASIS_LABEL`), §4.2 (the assessment count carries no period filter), §4.3 (the assessment deep link never sends `period`), AC-005, AC-019, AC-022, AC-027, AC-028 and AC-029, plus D-S1 in §9.1.
> Because the decision is taken **before** implementation, the demoted variant ships in **Phase 1**. PRD §9 v1.2 no longer carries a Phase-2 contingency entry for it: that entry is satisfied here and was removed
> from the PRD during the consistency remediation (finding A-2), so the demotion is a Phase-1 commitment rather than a deferred possibility.

- **ASSUMPTION A2 — the triage figure row carries exactly three figures.** FEAT-003 mandates three; the §6 metric counts "0 of 3 ambiguous"; D7 removes login telemetry. This specification therefore removes the two inventory cards (`Tervalidasi (<bulan>)`,
  `Indikator Kinerja`) from the landing's triage block as well. They are neither attention signals nor figures whose basis the PRD requires stating. If the product owner prefers to retain them, they must return with their own basis labels (a separate ticket).
  **Removal-assertion scope (C-1):** only the two strings the triage block owned — `Aktivitas Login (7 Hari)` and `Tervalidasi` — are asserted absent, and only inside the triage region defined in
  §4.4. The identical sidebar copy `Indikator Kinerja` rendered by `resources/views/layouts/modern.blade.php:86` is **out of scope**: §1.1 excludes the layout shell, and a body-wide negative assertion
  could never go green without either renaming that link (a layout change) or weakening the assertion (a floor-guard violation).
- **ASSUMPTION A3 — namespace placement.** `App\Support\ReportingPeriod`, `App\Support\TriageScope`, `App\Support\AdminTriageSummary` (discovery §5 seam 1 names `App\Support\...` as the resolver boundary) and `App\Services\AdminTriageService` (flat, matching
  `AdminService`/`AssessmentService`; ARCHITECTURE §6 "app/Services/ — Domain and workflow logic"). `app/Support/` is a **new directory**; ARCHITECTURE §5/§6 must be updated in the same Phase-1 change (Living Architecture Map Mandate, `AGENTS.md`).
- **ASSUMPTION A4 (revised by the A1 decision) — the assessment deep link carries `?status=pending` only and never a `period` parameter.** The figure it links to is period-independent (A1) and the target's `?period=` filter reads a calendar
  year (`AssessmentController.php:91-94`), which would contradict the figure's stated basis, so the link degrades to the status-filtered index per §4.3. The target index additionally hardcodes the current calendar year for its default query
  (`AssessmentController.php:53, :60`), which is the closest the target comes to a period frame and is the viewer's to interpret.
- **ASSUMPTION A5 — the Phase-2 badge composer resolves the period from the request** (§4.5), so US-005 scenario 1 ("same count as the landing's verification figure for the same scope") also holds when the viewer selected a non-default period on the landing.
- **ASSUMPTION A6 — no zero-value badge, and no badge on layouts other than `modern`** (US-005 scenario 2; verified badge markup at `layouts/modern.blade.php:91-92`).
- **ASSUMPTION A7 — agency-name resolution tolerates a soft-deleted `Instansi`.** `users.instansi_id` is `nullable` with `onDelete('set null')` (`2025_10_29_010000_add_instansi_id_to_users_table.php`), while `Instansi` itself uses `SoftDeletes`
  (`2025_10_29_010900_add_soft_deletes_to_instansis_table.php`), so an agency-bound viewer can point at a soft-deleted agency. The scope-label lookup therefore uses `Instansi::withTrashed()->whereKey($user->instansi_id)->value('nama_instansi')` — a narrow,
  label-only carve-out to §4.6's `withTrashed()` prohibition (no count ever uses it). If the name still resolves to `null` (only possible after a force delete, which the FK turns into `null`), the viewer is treated as `Unassigned` so that a scope label is never
  empty and the label/entitlement invariant in §4.1 still holds.
- **ASSUMPTION A8 — the canonical assessment population excludes soft-deleted parents (C-6).** Resolved by this revision after the Iteration-1 audit showed the two viewer states counting different
  populations. An `Assessment` whose parent `PerformanceData` row is soft-deleted is **not** pending work; both count forms therefore use `whereHas('performanceData')` (§4.2), asserted by AC-036. If the
  product owner disagrees (a trashed parent still owes an assessment), the change is a `withTrashed()` carve-out on this single count plus a replacement AC — never a silent flip of one viewer state.
- **ASSUMPTION A9 — the two figure↔target divergences are accepted, asserted and traceable (C-4, C-5).** (a) The assessment figure is period-independent while its target is year-bounded
  (`AssessmentController.php:53, :60`). (b) For the cross-agency viewer the verification target renders empty because `DataCollectionController@index:84-95` derives coverage from
  `performance_indicators.instansi_id` and has no cross-agency branch. Both are recorded as D-S7/D-S8 in §9.1 and asserted by AC-037/AC-038, so the mismatch is a documented, tested behaviour rather than
  a silent surprise. Matching the pair, or gating the link, is **not** done in Phase 1; either would be a Spec change with its own AC.
- **CLARIFICATION NEEDED — deep-link target authority.** The three queue indexes authorize independently of `admin.dashboard`. A landing viewer may therefore reach a link that resolves but returns 403 (for example an HQ administrator without
  `sakip.reports.view`). No new permission is created (FEAT-008, PRD §3.2) and Phase 1 does not gate links on target permission; the degraded status is recorded here as a known risk for the reviewer and Phase 3.
- **CLARIFICATION NEEDED — p95 < 500 ms** is a manual measurement (PRD §6, F-05): this repository installs no performance harness, so it creates no floor-guard obligation. The CI-enforced substitute is the domain-query count in §4.4.

## 2. Definitions & Domain Model (Aligned with `CONTEXT.md`)

`CONTEXT.md` is the single source of truth; if this section and `CONTEXT.md` diverge, `CONTEXT.md` wins. No term is invented here.

| Canonical term | Technical realisation in this feature | `_Avoid_` (from `CONTEXT.md`) |
| --- | --- | --- |
| **Periode Pelaporan** | `App\Support\ReportingPeriod` value object; five keys `current_year` (default), `current_quarter`, `last_quarter`, `current_month`, `last_month`; always rendered on screen next to the figure it scopes | periode, periode data, bulan berjalan, date range, rentang waktu |
| **Cakupan Instansi** | `App\Support\TriageScope` enum (`cross_agency` / `agency` / `unassigned`) plus the rendered `scopeLabel` | tenant, tenant scope, scope instansi, wilayah |
| **Semua Instansi** | `TriageScope::CrossAgency->defaultLabel()`; reserved for `SystemRoles::SUPER_ADMIN` (`app/Constants/SystemRoles.php:11`) | all tenants, global scope, lintas instansi, semua unit |
| **Instansi Belum Ditetapkan** | `TriageScope::Unassigned->defaultLabel()`; a non-`Super Admin` whose `users.instansi_id` is `null` | semua instansi, instansi kosong, instansi null, instansi global |
| **Antrean Verifikasi** | `PerformanceData::submitted()` (`app/Models/PerformanceData.php:195-198`) within the selected period | inbox, pending list, tugas saya, daftar tunggu |
| **Antrean Asesmen** | `Assessment::pending()` (`app/Models/Assessment.php:169-172`); **not** period-scoped in Phase 1 (A1 ratified 2026-09-24 — labelled `Tidak dibatasi periode`); agency coverage derived through `performance_data.instansi_id` (finding F-01) | antrean lain, menunggu, pending asesmen |
| **Antrean Laporan** | `Report::submitted()` (`app/Models/Report.php:181-184`); **not** period-scoped in Phase 1 (D2/D9) | antrean lain, menunggu, pending laporan |
| *Landing Triage* (feature name, not a glossary term) | This landing surface; appears in phase names and this document's title only | dashboard admin, beranda admin, overview |

**Domain invariants this specification preserves (do not weaken):**

1. `InstansiScope` default-deny (`app/Models/Scopes/InstansiScope.php:11-18`) is immutable: an authenticated non-`Super Admin` with `instansi_id === null` sees nothing, and no screen may label that viewer `Semua Instansi` (finding F-03).
2. `Assessment` carries no `instansi_id` and no global scope (verified: `app/Models/Assessment.php` has neither; `create_assessments_table.php` has only `performance_data_id`, `assessed_by`). Agency coverage for **Antrean Asesmen** is therefore always derived
   through `performance_data.instansi_id` (finding F-01), never invented from a second column path.
3. Period vocabularies differ per table and must never be silently mapped: `performance_data.period` is `string(7)` `YYYY-MM` (`2025_10_14_080002_create_performance_data_table.php:31`); `reports.period` is free-form `string(20)` seeded as `YYYY-Qn`
   (`2025_10_14_080006_create_reports_table.php:28`; `tests/Feature/ReportIndexRendersTest.php:34`).
4. UUID primary keys, soft deletes, and audit logging are untouched: this feature performs **reads only**.

**Relevant ADRs:** none. `docs/adr/` does not exist and no decision here meets the Triple Gate (`/Users/macbook/Developer/php/sakip/.agents/standards/ADR-FORMAT.md`).

## 3. Requirements, Constraints & Guidelines

### 3.1 Functional Requirements

- **REQ-001** (FEAT-001) A single period resolver, `ReportingPeriod`, owns every "what does this period mean" decision. Its key vocabulary is exactly
  `['current_year', 'current_quarter', 'last_quarter', 'current_month', 'last_month']`, default `current_year`.
- **REQ-002** (FEAT-001) `GET /admin/dashboard` accepts an optional `period` query parameter carrying one of those five keys. An unknown, empty, absent, or non-string value resolves to `current_year` and never raises an exception (US-001 scenario 2).
  **`[ASSUMPTION]`** — the parameter **name** `period` was frozen in v1.0 before the product owner confirmed it. PRD §7.5 item 2 (v1.2) records the same status; once the name is confirmed, that note and this
  tag are replaced by a ratified row in the PRD's decision log.
- **REQ-003** (FEAT-001) The selected *Periode Pelaporan* is rendered on the page in Indonesian at all times, and is reproducible from the URL: re-requesting the same URL yields the same period and the same figures (US-001 scenario 3).
- **REQ-004** (FEAT-002) The landing renders exactly one scope indicator, always visible, resolved to one of the three `TriageScope` states. Its absence, its omission for a single-agency install, and colour-only expression are all forbidden.
- **REQ-005** (FEAT-003) The landing renders exactly three triage figures — **Antrean Verifikasi**, **Antrean Asesmen**, **Antrean Laporan** — in that order, each carrying the same scope and each carrying its own basis label.
- **REQ-006** (FEAT-003) **Antrean Verifikasi** is scoped to the selected period via `whereBetween('performance_data.period', $period->performancePeriodRange())` (index-backed by `idx_perf_data_instansi_period`, `2026_01_23_add_performance_data_indexes.php:18`).
- **REQ-007** (FEAT-003, A1 ratified 2026-09-24) **Antrean Asesmen** is **not** period-scoped in Phase 1: it counts every pending assessment, restricted only by *Cakupan Instansi* **and the canonical population defined in §4.2** (an assessment whose parent `PerformanceData` is soft-deleted is not pending work — A8/D-S9), and is rendered with the explicit period-independent basis label `Tidak dibatasi periode`. It carries no period label
  whatsoever (US-006).
- **REQ-008** (FEAT-003, D2/D9) **Antrean Laporan** is **not** period-scoped in Phase 1; it is rendered with the same explicit period-independent basis label `Tidak dibatasi periode` and carries no period label whatsoever (US-006).
- **REQ-009** (FEAT-004) Every figure is a single anchor to a named queue route, pre-filtered by the rules in §4.3. No figure is rendered as a non-link, and no deep link is constructed by string concatenation.
- **REQ-010** (FEAT-005) When at least one queue count is greater than zero, the attention strip renders one signal per non-empty queue (three distinct signals possible), each with exactly one named action. When all three counts are zero, the strip element is not
  rendered at all and the empty-period sentence in §4.4 takes its place.
- **REQ-011** (FEAT-006, **Phase 1** — annotation corrected by consistency finding A-11) The login-event telemetry count is removed from the landing. No telemetry value may occupy a triage figure slot. Recent activity (`AuditLog`, `latest()`, limit 10) may remain as secondary content below the triage block.
- **REQ-012** (FEAT-007, Phase 2) The sidebar queue badge for `layouts.modern` is produced by a dedicated view composer, not by a controller view variable, and renders on every page that uses that layout.
- **REQ-013** (FEAT-008) Exactly one authorization gate governs the landing: the route middleware `can:admin.dashboard` (`routes/web.php:201`). The controller-level `$this->middleware('can:access-admin-dashboard')`
  (`app/Http/Controllers/Admin/AdminDashboardController.php:14`) is **deleted**; `access-admin-dashboard` is referenced nowhere else in the repository (verified by repository-wide grep: one hit, that line).
- **REQ-014** (PRD §7.5 item 3) One source of truth for the active reporting year: the dead duplicate `ForYear` trait inside `app/Models/Scopes/ForYearScope.php` is removed (no `use` site anywhere; verified grep), `ForYearTrait`
  (`app/Models/Scopes/ForYearTrait.php`, used by `PerformanceIndicator`, `PerformanceData`, `Program`) is retained, and its `scopeForCurrentYear` delegates to `ReportingPeriod::activeYear()` so the `date('Y')` drift documented as C2 cannot recur.
- **REQ-015** (D1) The active reporting year is derived from the server clock behind a configuration seam: a new **key** `active_year` inside the **existing** `reporting` block of `config/sakip.php`
  (that block already exists at `config/sakip.php:103-116` and holds the report-generation settings), read as `config('sakip.reporting.active_year')` and declared `env('SAKIP_ACTIVE_YEAR', null)`.
  The default `null` means "use the clock" and reproduces today's behaviour exactly. Because `env()` yields a **string** when set, the value is cast with `(int)` at its single read site, so
  `ReportingPeriod::activeYear()` and the `anchor()` year replacement always consume an integer (C-8, AC-004).

### 3.2 Non-Functional Requirements

- **SEC-001** `InstansiScope` remains in force on every instansi-scoped query. No `withoutGlobalScope`/`withoutInstansiScope` call may be introduced by this feature (CONSTITUTION Prinsip IV; CONSTRAINTS §3 rule 6).
- **SEC-002** Agency coverage is enforced **explicitly** by the read model for an agency-bound viewer (`performance_data.instansi_id = $user->instansi_id`; `assessments` restricted through `performance_data.instansi_id`; `reports` by their own `instansi_id`), so the
  counts stay correct even when no authenticated user is bound at service-call time. This is what makes the S2 unit test meaningful (§6.2).
  **Canonical population (C-6):** an `Assessment` whose parent `PerformanceData` row is soft-deleted is **not** pending work and leaves the queue. The agency-bound form already excludes it (its
  `whereHas('performanceData', …)` inherits the parent's `SoftDeletes` scope), so the cross-agency form must exclude it too — by using `->whereHas('performanceData')` with an empty closure rather than a
  bare `->pending()->count()`. Both viewer states therefore always count the same population and AC-011's viewer-consistency invariant is falsifiable (AC-036).
- **SEC-003** The `period` query parameter is untrusted input. It is validated against the five-key whitelist, must be a scalar string, and is never interpolated into SQL or into a `route()` parameter before validation (AC-005, AC-007).
- **SEC-004** No new permission, role, gate, or policy is introduced. No viewer gains access beyond `admin.dashboard` holders and `Super Admin` (FEAT-008; PRD §3.2 "explicitly unchanged").
- **CON-001** PHP `^8.3`, Laravel 12, Blade + Bootstrap 5.3 CDN + `public/css/modern-sakip.css`. No new Composer or npm dependency. The Vite/Tailwind pipeline must not be assumed to render (ARCHITECTURE §6).
- **CON-002** No migration and no schema change (PRD §7.6). CONSTRAINTS §1 "Database safety" is unaffected.
- **CON-003** Server-side domain-query budget per landing render: **≤ 6** statements touching `performance_data`, `assessments`, `reports`, `instansis`, `audit_logs`; expected exact counts are in §4.4. No N+1 patterns.
- **CON-004** The period selector must function **without inline event handlers** (`onchange="…"` attributes are blocked in production by the `script-src 'self' 'nonce-…'` policy at `app/Http/Middleware/SecurityHeadersMiddleware.php`). A plain
  `<form method="GET">` with an explicit submit control is mandatory; the house idiom already exists (`resources/views/sakip/data-collection/index.blade.php:34`).
- **CON-005** Rendered Indonesian copy asserted by CI is frozen in §4.4. Any change to those strings is a contract change and must update the tests in the same commit.
- **CON-006** Every Blade `route()` reference must resolve to an existing named route, because `tests/Unit/ArchitectureGuardTest.php::every_blade_route_name_exists` walks `resources/views` (the same guard also enforces unique short class names in `app/`).
- **CON-007** Rendered-label assertions use the existing house idiom only: `$content = $response->getContent();` + `$this->assertStringContainsString(...)`, and for every negative criterion its exact
  counterpart `$this->assertStringNotContainsString(...)` on the same `$content` value (`tests/Feature/AuthenticationTest.php:341-346`; finding F-07 — the suite contains zero `assertSee` occurrences and
  no browser harness). No third assertion idiom and no HTML-parsing dependency are introduced.
- **CON-008** Floor-guard (`CONSTRAINTS.md` §3): no suppressed diagnostics, no skipped/incomplete tests, no weakened or deleted assertions, no lowered thresholds, no `phpunit.xml`/CI edits.

### 3.3 Reuse Obligations (do not reinvent)

| Existing capability | Location (verified) | Obligation |
| --- | --- | --- |
| Period key vocabulary | `SakipDashboardService::getDateRange()` (`app/Services/SakipDashboardService.php:608-649`, `protected`) | Becomes an adapter over `ReportingPeriod`; its `[Carbon, Carbon]` return contract is preserved exactly (mutable `Carbon` instances) so its five call sites (`:67, :87, :256, :392, :444`) are untouched |
| Queue definitions | `PerformanceData::submitted()`, `Assessment::pending()`, `Report::submitted()` | Reuse the model scopes; never re-declare status literals inline in queries |
| Year scopes | `ForYearTrait::scopeForCurrentYear` | Delegate to `ReportingPeriod::activeYear()` (REQ-014) |
| Deep-link filters | `DataCollectionController@index` (`?validation_status=`, `?period=` exact, `:164-173`), `AssessmentController@index` (`?status=`, `?period=` **year**, `:77-95`), `ReportController@index` (`?status=`, `?type=`, `?period=` exact, `?category=`, `:82-95`) | Send only parameters a target honours (§4.3) |
| Layout shell | `layouts/modern.blade.php:91-92` badge markup, `.stat-card`, `.card-accent-warning`, `.page-header` in `public/css/modern-sakip.css` | Reuse the components; author no new visual generation |
| Test fixtures | `PerformanceDataFactory` (`submitted()`, `forInstansi()`, `forPeriod()`), `AssessmentFactory` (`pending()`, `forPerformanceData()`), `ReportFactory` (`forInstansi()`), `InstansiFactory`, `UserFactory` | Build S2/S3 fixtures with these factories; `TestCase::$seed = true` seeds roles/permissions |

## 4. Interfaces & Data Contracts

### 4.1 Value-Object Contracts (seam S1, `app/Support/`)

```php
<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Periode Pelaporan — the annual, quarterly, or monthly window that scopes a triage figure.
 * Single source of truth for "what does this period mean" (CONTEXT.md: Periode Pelaporan).
 */
final readonly class ReportingPeriod
{
    public const DEFAULT_KEY = 'current_year';

    /** @var list<string> */
    public const KEYS = ['current_year', 'current_quarter', 'last_quarter', 'current_month', 'last_month'];

    private function __construct(
        public string $key,
        public CarbonImmutable $start, // inclusive, at 00:00:00
        public CarbonImmutable $end,   // inclusive, at 23:59:59
    ) {}

    /** Unknown, empty, null, or non-listed key resolves to DEFAULT_KEY. Never throws. */
    public static function fromKey(?string $key, ?CarbonImmutable $now = null): self;

    public static function default(?CarbonImmutable $now = null): self;

    /**
     * Anchor instant (REQ-015, C-7 precedence rule):
     *   - when $now is provided, it is authoritative and the config seam is IGNORED (deterministic unit tests);
     *   - when $now is null, now() is used and, if config('sakip.reporting.active_year') is not null,
     *     its (int) year replaces the clock's year.
     */
    public static function anchor(?CarbonImmutable $now = null): CarbonImmutable;

    /** Calendar year of the anchor (REQ-015). Always an int: the config value is cast with (int) at read time. */
    public static function activeYear(?CarbonImmutable $now = null): int;

    /** Indonesian label: 'Tahun 2026' | 'Triwulan III 2026' | 'September 2026'. */
    public function label(): string;

    /** True for current_month and last_month: the range collapses into exactly one YYYY-MM value. */
    public function isSingleMonth(): bool;

    /** @return array{0: string, 1: string} YYYY-MM bounds for performance_data.period (string(7)). */
    public function performancePeriodRange(): array;
}
```

**Label rules (frozen copy, CON-005):** year → `Tahun {Y}`; quarter → `Triwulan {I|II|III|IV} {Y}`; month → the Indonesian month name via `Carbon::locale('id')->translatedFormat('F')` plus ` {Y}` (house idiom for `translatedFormat`:
`AdminDashboardController.php:30`). `Triwulan` uses roman numerals because Carbon's `id` locale exposes no quarter token.

```php
<?php

namespace App\Support;

/** Cakupan Instansi — the agency coverage of a figure or screen (CONTEXT.md). */
enum TriageScope: string
{
    case CrossAgency = 'cross_agency'; // Semua Instansi
    case Agency = 'agency';            // the viewer's own agency, named on screen
    case Unassigned = 'unassigned';    // Instansi Belum Ditetapkan

    /** The two canonical CONTEXT.md labels; null for Agency, whose label is the agency name. */
    public function defaultLabel(): ?string; // 'Semua Instansi' | 'Instansi Belum Ditetapkan' | null
}
```

```php
<?php

namespace App\Support;

/** Immutable read-model result for one landing render (seam S2 return contract). */
final readonly class AdminTriageSummary
{
    public const PERIOD_INDEPENDENT_BASIS_LABEL = 'Tidak dibatasi periode';

    public function __construct(
        public ReportingPeriod $period,
        public string $periodLabel,              // === $period->label()
        public TriageScope $scope,
        public string $scopeLabel,               // canonical label or the agency name
        public int $verificationCount,           // Antrean Verifikasi, period-scoped
        public int $assessmentCount,             // Antrean Asesmen, NOT period-scoped (A1)
        public int $reportCount,                 // Antrean Laporan, NOT period-scoped (D9)
        public string $verificationUrl,          // named route + filters per §4.3
        public string $assessmentUrl,
        public string $reportUrl,
    ) {}
}
```

**Enforced invariants (asserted at S2):** `$periodLabel === $period->label()`; `$scopeLabel === $scope->defaultLabel() ?? <agency name>`; `scopeLabel === 'Semua Instansi'` **iff** `$scope === TriageScope::CrossAgency` (F-03); `$scope === Unassigned` implies all
three counts are `0`; every URL is an absolute URL produced by `route()` for a registered named route.

### 4.2 Service Contract (seam S2, `app/Services/AdminTriageService.php`)

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Support\AdminTriageSummary;
use App\Support\ReportingPeriod;

final class AdminTriageService
{
    /** Filter value honoured by AssessmentController@index and matching Assessment::pending(). */
    private const ASSESSMENT_PENDING_STATUS = 'pending';

    /**
     * Resolve the whole triage read model for one viewer over one period.
     * Performs at most five domain queries (§4.4) and never writes.
     */
    public function summaryFor(User $user, ReportingPeriod $period): AdminTriageSummary;

    /**
     * Antrean Verifikasi count for one viewer; null period means ReportingPeriod::default().
     * Single source shared by the landing figure and the Phase-2 sidebar badge (US-005).
     */
    public function verificationCountFor(User $user, ?ReportingPeriod $period = null): int;
}
```

**Scope resolution (exact rule, in this order):**

1. `$user->hasRole(SystemRoles::SUPER_ADMIN)` → `TriageScope::CrossAgency`, `scopeLabel = 'Semua Instansi'`, no `instansi_id` restriction on any count.
2. else `$user->instansi_id !== null` → `TriageScope::Agency`, `scopeLabel = <nama_instansi>` (resolved by the label-only `Instansi::withTrashed()->whereKey($user->instansi_id)->value('nama_instansi')`, one query — ASSUMPTION A7), all counts restricted to that `instansi_id`. If the name resolves to
   `null`, the viewer is treated as state 3 instead so that no label is ever empty.
3. else → `TriageScope::Unassigned`, `scopeLabel = 'Instansi Belum Ditetapkan'`, **all three counts are `0` and no count query is executed** (the default-deny semantics of `InstansiScope` are stated on screen rather than silently producing an empty unscoped view).

**Count queries (the only allowed forms):**

```php
// Antrean Verifikasi — period-scoped by YYYY-MM range, index-friendly (REQ-006)
$range = $period->performancePeriodRange(); // ['2026-01', '2026-12']
PerformanceData::query()->whereBetween('period', $range)->submitted()->count();          // + ->where('instansi_id', $id) when agency-bound

// Antrean Asesmen — NOT period-scoped (A1); coverage via performance_data (F-01)
// Canonical population (C-6): an assessment whose parent performance_data row is soft-deleted is not pending work.
Assessment::query()->pending()->whereHas('performanceData')->count();
// when agency-bound, use the single coverage form below — it applies the parent's SoftDeletes scope AND the coverage:
//   Assessment::query()->pending()
//       ->whereHas('performanceData', fn ($q) => $q->where('performance_data.instansi_id', $id))
//       ->count();

// Antrean Laporan — NOT period-scoped (D2/D9)
Report::query()->submitted()->count();                                                    // + ->where('instansi_id', $id) when agency-bound
```

**Prohibited in this service:** raw status literals in the three count queries (use the model scopes), `whereYear(...)` for period scoping, any period or date filter on the assessment and report counts (A1, D9), any `withoutGlobalScope`/`withoutInstansiScope` call, any write, any cache call
(D6), any `now()` call outside `ReportingPeriod`, and any second period vocabulary. **Status-value sourcing (A-4):** the assessment filter value is held in the single named constant
`ASSESSMENT_PENDING_STATUS = 'pending'` rather than in a literal inside a query, which satisfies the reuse obligation of PRD §7.2. It is deliberately **not** read from `App\Constants\AssessmentStatus`,
because that class exposes no `PENDING` member (verified: DRAFT, SUBMITTED, IN_REVIEW, APPROVED, REJECTED, REVISED), and `App\Constants\Status::PENDING` belongs to a different domain vocabulary. The report
status is likewise taken from the model scope `Report::submitted()`; `App\Constants\ReportStatus` is **not** used, because the report figure's filter value must match the value the target controller
accepts on `?status=` and the model scope is the single authority for it.

**Deep links** are produced with `route()` using the named routes and the §4.3 parameter rules. `verificationCountFor()` delegates to the same query builder as `summaryFor()` (single implementation; the Phase-2 badge and the landing figure must never diverge).

### 4.3 HTTP & Deep-Link Contract

**Landing request**

| Element | Contract |
| --- | --- |
| Method / URI | `GET /admin/dashboard` (`routes/web.php:205`) |
| Route name | `admin.dashboard` |
| Middleware | `auth`, `can:admin.dashboard`, `throttle:60,1` (route group, `routes/web.php:201`) — the only gate (REQ-013) |
| Controller | `App\Http\Controllers\Admin\AdminDashboardController@index` (no `$this->middleware()` call) |
| Query parameter | `period` = one of `ReportingPeriod::KEYS`; absent/invalid/non-string → `ReportingPeriod::DEFAULT_KEY` |
| Successful response | `200` with `resources/views/admin/dashboard.blade.php` |
| Denied response | `403` for an authenticated viewer without `admin.dashboard` and without `Super Admin` (US-003 scenario 2) |
| Writes | none (no POST/PUT/DELETE, no CSRF surface) |

**Deep-link construction rules** (`route()` first, then the parameters below; never string concatenation)

| Figure | Named route | Always sent | Conditionally sent | Never sent |
| --- | --- | --- | --- | --- |
| Antrean Verifikasi | `sakip.data-collection.index` | `validation_status=submitted` | `period=YYYY-MM` (the exact `performance_data.period` value) **iff** `$period->isSingleMonth()` | `period` for year/quarter selections; any `instansi` parameter |
| Antrean Asesmen | `sakip.assessments.index` | `status=pending` (value of `ASSESSMENT_PENDING_STATUS`) | — | `period` for **every** selection (A1: the figure is period-independent, and the target's filter reads a calendar year); any `instansi` parameter |
| Antrean Laporan | `sakip.reports.index` | `status=submitted` | — | `period` for **every** selection (D9: report periods are quarter-coded free-form values, so no honest calendar value exists) |

**Expressibility rule (PRD §7.5 item 2, ratified; narrowed by the A1 decision):** a period parameter is sent only when the target's own filter can express the selected *Periode Pelaporan* range with the target's semantics — exact `YYYY-MM` for the data-collection index, which is the **only**
target able to express the selection. Otherwise the link degrades to the status-filtered index (US-004 scenario 2). Degradation must never produce a broken link, a wrong parameter name, or a 404 caused by a parameter the target ignores.

**Target-frame disclosure (C-4, `[ASSUMPTION]`).** `AssessmentController@index` applies `whereYear('created_at', Carbon::now()->year)` (`:53`, `:60`) **before** any request filter, so the assessment
target can only ever show a calendar-year window while the figure is labelled `Tidak dibatasi periode`. This specification **accepts** that divergence in Phase 1 — the count is the honest triage signal
and the anchor still reaches its queue — but it must be asserted rather than assumed: AC-037 and AC-038 pin the accepted behaviour and §9.1 records it as D-S7. If the product owner instead requires the
pair to agree, the decision is recorded as an open question (A9).

**Target-coverage disclosure (C-5, `[ASSUMPTION]`).** The verification index derives its coverage from `performance_indicators.instansi_id` (`app/Http/Controllers/Sakip/DataCollectionController.php:84-95`)
and has **no `Super Admin` branch**, while the landing figure counts `performance_data.instansi_id`. For the cross-agency viewer the two paths disagree and the queue is empty. This specification accepts
and records that defect for Phase 1 (AC-038, D-S8) and does not modify the target controller (§1.1 out of scope).

**No scope parameter exists.** All three indexes derive coverage from the authenticated viewer (`InstansiScope` plus their own controller-level filters; verified: none of the three `index` methods reads an instansi query parameter).

### 4.4 Render Contract, Copy & Query Budget (seam S3)

The landing exposes stable machine-checkable handles so that every CI-enforced PRD §6 metric is assertable with the house content idiom (CON-007). Required elements:

| Handle | Element | Required content |
| --- | --- | --- |
| `data-triage-region` | the **single** `<section>` that wraps the whole triage block (scope indicator, period selector, all three figures, attention strip, empty-period sentence) | **This element is the triage region and the only boundary the negative assertions of AC-033 are scoped to (C-1).** It must be a `<section>` carrying all three region attributes, so the region is extractable from `$content` with one regex before the forbidden strings are asserted absent |
| `data-triage-period="{key}"` | the same triage `<section>` | the resolved `ReportingPeriod` key |
| `data-triage-scope="{scope}"` | the same triage `<section>` | `cross_agency` \| `agency` \| `unassigned` |
| `data-triage-period-label` | element | exactly `ReportingPeriod::label()` (`Tahun 2026`, `Triwulan III 2026`, `September 2026`) |
| `data-triage-scope-label` | element | exactly `Semua Instansi` \| the agency name \| `Instansi Belum Ditetapkan` |
| `data-triage-period-select` | `<select name="period">` inside `<form method="GET" action="{{ route('admin.dashboard') }}">` | five `<option>` values in `ReportingPeriod::KEYS` order with the resolved key marked `selected`; a visible submit control labelled `Terapkan`; no inline event handler (CON-004) |
| `data-triage-figure="{verification\|assessment\|report}"` | `<a href="{deep link}">` | inner content contains, in one anchor: the canonical figure name, the formatted count, and the figure's basis label — the period label for **Antrean Verifikasi**, `Tidak dibatasi periode` for **Antrean Asesmen** and **Antrean Laporan** — so the accessible name always states the figure's basis (PRD §5.3) |
| `data-triage-attention` | attention strip container | rendered only when at least one count is greater than `0`; absent from the HTML when all counts are `0` |
| `data-triage-signal="{verification\|assessment\|report}"` | one signal per non-empty queue | a count sentence plus exactly one named action link |
| `data-triage-empty` | empty-period sentence | rendered only when all counts are `0` |

**Frozen Indonesian copy (CON-005):**

| Purpose | Exact string |
| --- | --- |
| Figure 1 name | `Antrean Verifikasi` |
| Figure 2 name | `Antrean Asesmen` |
| Figure 3 name | `Antrean Laporan` |
| Period-scoped basis label | the `ReportingPeriod::label()` value (identical to `data-triage-period-label`) |
| Period-independent basis label | `Tidak dibatasi periode` (figures 2 and 3 — **Antrean Asesmen** and **Antrean Laporan** — in Phase 1) |
| Selector field label | `Periode Pelaporan` |
| Selector submit control | `Terapkan` |
| Scope indicator prefix | `Cakupan:` (the scope label follows) |
| Empty-period sentence | `Belum ada pekerjaan tertunda pada periode ini.` |
| Signal action — verification | `Tinjau Antrean Verifikasi` |
| Signal action — assessment | `Tinjau Antrean Asesmen` |
| Signal action — report | `Tinjau Antrean Laporan` |

**Long agency names (PRD §5.3, delivered 2026-09-25 under review finding `SPEC-B-06`).** The scope chip carries `triage-scope-chip` — `max-width: 49ch` (the 9-character `Cakupan: ` prefix plus the 40-character
name threshold), `overflow: hidden`, `text-overflow: ellipsis`, `white-space: nowrap` — and exposes the untruncated name through `title`, because a shortened label must never become the only available value. The
contract is asserted at S3 (`AdminTriageLandingTest::test_long_agency_name_is_wired_for_ellipsis_truncation`); the rendered ellipsis itself needs a browser and therefore belongs to the deferred manual set of PRD §6.

**Removed from the landing (Phase 1):** the `Aktivitas Login (7 Hari)` telemetry figure (D7/REQ-011) and the `Tervalidasi (<bulan>)` and `Indikator Kinerja` inventory cards (ASSUMPTION A2). The recent-activity table and the quick-actions panel remain as secondary
content and are unchanged by this specification.

**Domain-query budget (CON-003).** Counted as statements whose SQL text mentions `performance_data`, `assessments`, `reports`, `instansis`, or `audit_logs`; `users`, `roles`, `permissions`, `sessions`, and `migrations` are excluded so the assertion stays deterministic
under Spatie permission checks.

| Viewer state | Phase 1 expected | Phase 2 expected (+1 badge composer query) | Composition (Phase 1) |
| --- | --- | --- | --- |
| Cross-agency (`Super Admin`) | 4 | 5 | 3 counts + 1 recent-activity list |
| Agency-bound | 5 | 6 | 3 counts + 1 agency-name lookup + 1 recent-activity list |
| Unassigned | 1 | **1** — not 2 (v1.4): the badge short-circuits before issuing a statement, so Phase 2 adds nothing | 0 counts (short-circuit) + 1 recent-activity list |
| Hard ceiling | **≤ 6** in every state and every phase | | |

Each count is one statement; `whereHas('performanceData')` compiles into the same statement, never an extra query. Zero N+1 patterns. `[Assumed — the exact numbers must be confirmed during the RED step; if a genuine framework statement appears inside the counted
table set, the ticket records it in the test comment and adjusts the exact expectation while keeping the ≤ 6 ceiling.]`

**Delivered measurement (v1.4).** The shipped values are **`5 / 6 / 1`**, asserted exactly per viewer state in `tests/Feature/AdminTriageLandingTest.php::test_domain_query_count_matches_the_expectation_for_each_viewer_state` with the `≤ 6` ceiling alongside it. The
unassigned column is `1`, not the `2` this table originally predicted: `AdminTriageService::verificationCountFor()` returns `0` for an unassigned viewer before touching the database
(`app/Services/AdminTriageService.php:69-71`), so the badge contributes no statement to that state. Taken literally, the pre-v1.4 §9.4 obligation 3 would have written a false assertion. Recorded in
`docs/review/code-review-admin-triage-landing-2026-09-25.md` as finding `SPEC-B-04`.

### 4.5 Controller & Phase-2 Badge Composer Contracts

**Controller (Phase 1, replaces `Admin/AdminDashboardController.php`)**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminTriageService $triageService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $rawPeriod = $request->query('period');                 // untrusted (SEC-003)
        $period = ReportingPeriod::fromKey(is_string($rawPeriod) ? $rawPeriod : null);

        $summary = $this->triageService->summaryFor($user, $period);
        $recentLogs = AuditLog::with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', [
            'summary' => $summary,
            'recentLogs' => $recentLogs,
        ]);   // no `pendingDataCount`: the layout-owned composer below owns the badge (v1.4)
    }
}
```

- The constructor contains **no** `$this->middleware(...)` call (REQ-013). Authorization stays at the route.
- The controller performs no counting, no label building, and no period arithmetic; it only validates the query parameter envelope and binds the view.
- **Badge ownership (v1.4 — replaces the Phase-1 "pendingDataCount continuity" clause).** `layouts/modern.blade.php:91-92` reads `isset($pendingDataCount)`, but the variable is produced **only** by the composer below, from the first
  release. The earlier plan of a controller-owned Phase-1 variable was abandoned during implementation because it cannot satisfy US-005: the badge must render on *every* page that uses the layout, and a controller variable reaches only its own
  page. The consequence is recorded openly rather than smoothed over — between ticket T4 (`df57eb8`) and T10 (`7e8642e`) no `layouts.modern` page rendered a badge at all, and no test could see it because the S4 seam did not exist until Phase 2.
  The delivered state (HEAD onward) is the composer-only one, and AC-023 … AC-026 now pin it. `docs/review/code-review-admin-triage-landing-2026-09-25.md` finding `SPEC-B-01` records the deviation in full.

**Phase-2 sidebar badge composer (`app/View/Composers/SidebarQueueBadgeComposer.php`, new directory)**

```php
<?php

namespace App\View\Composers;

use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SidebarQueueBadgeComposer
{
    public function __construct(
        private readonly AdminTriageService $triageService,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $user = $this->request->user();
        if ($user === null) {
            return; // no authenticated viewer: no badge, no query
        }

        $rawPeriod = $this->request->query('period');
        $period = ReportingPeriod::fromKey(is_string($rawPeriod) ? $rawPeriod : null);

        $view->with('pendingDataCount', $this->triageService->verificationCountFor($user, $period));
    }
}
```

- Registered once in `AppServiceProvider::boot()`, next to the existing composer (`:180`): `View::composer('layouts.modern', SidebarQueueBadgeComposer::class);`. Only `layouts.modern` renders the badge today (verified), so no other layout is claimed.
- The Blade guard stays `@if(isset($pendingDataCount) && $pendingDataCount > 0)` — no zero-value badge (US-005 scenario 2), no markup change.
- Period basis: the request's valid `period` when present, else `ReportingPeriod::default()` (ASSUMPTION A5) — identical semantics on the landing and on every other `layouts.modern` page, which is what US-005 scenario 1 and scenario 3 require.
- `verificationCountFor()` is the same service method the landing figure uses; the badge must never re-implement the count or the scope filter.

### 4.6 Data Storage Schema (read-only)

No DDL is added, altered, or dropped (CON-002). The landing reads existing columns only:

| Table | Columns read | Index used |
| --- | --- | --- |
| `performance_data` | `instansi_id`, `period` (`string(7)`, `YYYY-MM`), `status`, `deleted_at` | `idx_perf_data_instansi_period` (`2026_01_23_add_performance_data_indexes.php:18`), `idx_perf_data_status` (`:24`) |
| `assessments` | `performance_data_id`, `status`, `deleted_at` | `idx_assessments_data_status` (`2026_01_23_add_assessments_indexes.php`), plus the unique index on `performance_data_id` (`2025_10_14_080004_create_assessments_table.php:43`) |
| `reports` | `instansi_id`, `status`, `deleted_at` | `reports.instansi_id` and `reports.status` (`2025_10_14_080006_create_reports_table.php:44,47`) |
| `instansis` | `id`, `nama_instansi`, `deleted_at` | primary key (the table name is `instansis` — `2025_08_05_120351_create_instansis_table.php:14`) |
| `audit_logs` | `action`, `created_at`, `user_id` (existing recent-activity list) | existing indexes |

Soft deletes are respected by Eloquent's global `SoftDeletes` scopes on `PerformanceData`, `Assessment`, and `Report` — **not** on `AuditLog`, which is a plain `Model` with no `SoftDeletes`
(`app/Models/AuditLog.php:16`, corrected in v1.1 / C-9). The specification forbids adding `withTrashed()` to any count. The **only** permitted `withTrashed()` call in this feature is the
label-only agency-name lookup in §4.2 (ASSUMPTION A7), which never participates in a count.

## 5. Acceptance Criteria & Testable Behaviors

Every criterion below is authoritative for the RED step and states its seam: **S1** = `ReportingPeriod` unit, **S2** = `AdminTriageService` unit, **S3** = HTTP `GET /admin/dashboard`, **S4** = HTTP sidebar-badge render (Phase 2). All criteria use seeded fixtures
through the existing factories; no criterion depends on wall-clock time (the clock is injected at S1 and fixed at S2–S4 via `Carbon::setTestNow()` or the `active_year` config seam where a boundary matters).

### 5.0 Canonical Fixture Matrix (shared Given for S2 — C-3)

Every S2 criterion resolves its Given to this single matrix; no S2 test may invent its own dataset. The clock is frozen at `2026-09-24` and the selected period is `current_year` unless the criterion says
otherwise. Each row records the exact factory state that must be applied — a fixture created without the stated state is a test defect, not an implementation defect.

| # | Fixture | Exact factory call | Purpose |
| --- | --- | --- | --- |
| F-1 | Agency A | `InstansiFactory::create(['nama_instansi' => 'Dinas A'])` | Agency-bound viewer target |
| F-2 | Agency B | `InstansiFactory::create(['nama_instansi' => 'Dinas B'])` | Cross-agency surplus |
| F-3 | A data, inside period (2 rows) | `PerformanceDataFactory::count(2)->submitted()->forInstansi($a)->forPeriod('2026-03')` | `verificationCount` for A |
| F-4 | B data, inside period (3 rows) | `PerformanceDataFactory::count(3)->submitted()->forInstansi($b)->forPeriod('2026-05')` | Cross-agency surplus |
| F-5 | A data, outside period (1 row) | `PerformanceDataFactory::submitted()->forInstansi($a)->forPeriod('2025-03')` | Proves F-3 is period-scoped |
| F-6 | A pending assessments (3 rows) | `AssessmentFactory::count(3)->pending()->forPerformanceData($f3->first()->id)` | `assessmentCount` for A — pinned parent, never the factory default |
| F-7 | B pending assessment (1 row) | `AssessmentFactory::pending()->forPerformanceData($f4->first()->id)` | Cross-agency surplus |
| F-8 | Trashed-parent assessment (1 row) | `AssessmentFactory::pending()->forPerformanceData($f12->id)`; asserted **after** F-12 is deleted | AC-036 canonical population |
| F-9 | A submitted report (1 row) | `ReportFactory::submitted()->forInstansi($a)` | `reportCount` for A |
| F-10 | A non-submitted report (1 row) | `ReportFactory::pending()->forInstansi($a)` | Proves F-9 is status-filtered |
| F-11 | B submitted reports (2 rows) | `ReportFactory::count(2)->submitted()->forInstansi($b)` | Cross-agency surplus |
| F-12 | Trashed parent data (1 row) — defines the `$f12` referenced by F-8 | `PerformanceDataFactory::submitted()->forInstansi($a)->forPeriod('2026-04')`, then `$f12->delete()` **before** any assertion | Proves a soft-deleted `PerformanceData` leaves **all three** queues: it owns no report, and its child assessment is excluded by the canonical population (A8/D-S9) |

Expected matrix (all three queues, all three viewer states): cross-agency `5 / 4 / 3`, agency-bound A `2 / 3 / 1`, agency-bound B `3 / 1 / 2`, unassigned `0 / 0 / 0`. **F-12 is soft-deleted before
assertions**, so it is absent from the verification queue (A stays `2`, cross-agency stays `5`), from the assessment queue (its child F-8 is excluded — A8/D-S9) and from the report queue (it owns no
report). The numbers above are therefore correct **only with that deletion**, and AC-036 is the criterion that asserts it.

### 5.1 US-001 — Comparable figures for one reporting period (FEAT-001)

- **AC-001 (S1)** Given the clock is `2026-09-24`, When `ReportingPeriod::fromKey('current_year', $now)`, Then `key === 'current_year'`, `start === 2026-01-01 00:00:00`, `end === 2026-12-31 23:59:59`, `label() === 'Tahun 2026'`, and `performancePeriodRange() === ['2026-01', '2026-12']`.
- **AC-002 (S1)** Given any of `null`, `''`, `'not-a-key'`, `'2026'`, or `'CURRENT_YEAR'`, When `fromKey()` is called, Then it resolves to the default `current_year` and never throws.
- **AC-003 (S1)** Given the clock is `2026-09-24`, When resolving `current_quarter`, `last_quarter`, `current_month`, and `last_month`, Then the ranges are `2026-07-01…2026-09-30`, `2026-04-01…2026-06-30`, `2026-09-01…2026-09-30`, `2026-08-01…2026-08-31` with labels `Triwulan III 2026`, `Triwulan II 2026`, `September 2026`, `Agustus 2026`, and `isSingleMonth()` is `true` only for the two month keys while `isYearScoped()` is `true` only for `current_year`.
- **AC-004 (S1)** Given `config()->set('sakip.reporting.active_year', '2025')` — written as the **string the seam actually produces** (`env()` never yields an `int`) and with no `$now` argument — When `activeYear()` and `fromKey('current_year')` are resolved, Then both yield the **integer** `2025` (`assertSame(2025, …)`, so the `(int)` cast of REQ-015 fails when it is removed); and Given the config key is `null`, Then both yield `2026` (REQ-015, AC-039 covers the precedence half).
- **AC-039 (S1, C-7)** Given `config('sakip.reporting.active_year')` is set **and** an explicit `$now` of `2026-09-24` is supplied, When `anchor($now)`, `activeYear($now)` and `fromKey('current_year', $now)` are resolved, Then the injected clock wins and the configured year is **ignored** (year `2026`, range `2026-01…2026-12`); Given the config is set and `$now` is `null`, Then the configured year wins. The precedence rule of §4.1 is therefore asserted in both directions, and an implementation with the reverse precedence fails this criterion.
- **AC-005 (S3)** Given performance data in two calendar years, When a cross-agency viewer requests `GET /admin/dashboard` with no query string, Then the response is `200`, contains `data-triage-period="current_year"`, contains the label `Tahun 2026` in `data-triage-period-label`, the verification figure counts only that year's records, and the assessment and report figures each carry the label `Tidak dibatasi periode`.
- **AC-006 (S3)** Given `?period=not-a-key`, When the landing is requested, Then the response is `200` and identical in period and basis labels to the default request (no error page, no partial render).
- **AC-007 (S3)** Given `?period[]=current_month` (array input), When the landing is requested, Then the response is `200` and the resolved period is the default (SEC-003).
- **AC-008 (S3)** Given `?period=last_quarter`, When the landing is requested twice, Then both responses contain `data-triage-period="last_quarter"` and the quarter label, and both render identical figure values for the same dataset (US-001 scenario 3).

### 5.2 US-002 — Explicit agency scope on every figure (FEAT-002, F-01, F-03)

- **AC-009 (S2)** Given the canonical fixture matrix of §5.0 and an agency-bound viewer for A, When `summaryFor($userA, $period)`, Then `verificationCount === 2` (F-5, the out-of-period row, is excluded), `assessmentCount === 3` (F-6 only — pinned parents, canonical population), `reportCount === 1` (F-9 only — F-10 and F-11 are excluded), `scopeLabel === 'Dinas A'`, and `scope === TriageScope::Agency`.
- **AC-010 (S2)** Given a non-`Super Admin` viewer with `instansi_id === null`, When `summaryFor($viewer, $period)`, Then `scope === TriageScope::Unassigned`, `scopeLabel === 'Instansi Belum Ditetapkan'`, and all three counts are `0` although data exists for other agencies.
- **AC-011 (S2)** Given the canonical fixture matrix of §5.0 and the same three viewers, When each calls `summaryFor()`, Then the counts are exactly cross-agency `5 / 4 / 3`, agency-bound A `2 / 3 / 1`, and unassigned `0 / 0 / 0`; `scopeLabel` is `Semua Instansi`, `Dinas A`, and `Instansi Belum Ditetapkan` respectively; and for **every one of the three queues** the cross-agency count equals the sum of the two agency-bound counts (the invariant that AC-036's canonical population makes falsifiable).
- **AC-012 (S2)** Given any summary, When asserted as an invariant, Then `scopeLabel === 'Semua Instansi'` iff `scope === TriageScope::CrossAgency`, and `scope === TriageScope::Unassigned` implies three zero counts (F-03).
- **AC-013 (S3)** Given an agency-bound viewer and a `Super Admin`, When both request the landing, Then the agency-bound response contains `data-triage-scope="agency"` with the agency name in `data-triage-scope-label`, while the `Super Admin` response contains `data-triage-scope="cross_agency"` and `Semua Instansi`.
- **AC-014 (S3)** Given an authenticated non-`Super Admin` viewer with `instansi_id === null` holding `admin.dashboard`, When the landing is requested, Then the response contains `data-triage-scope="unassigned"`, `Instansi Belum Ditetapkan`, three zero figures, and the string `Semua Instansi` appears nowhere in the response body.

### 5.3 US-003 — Delegated administrators can reach the landing (FEAT-008, finding C1)

- **AC-015 (S3)** Given a verified user holding the `admin.dashboard` permission and **not** the `Super Admin` role, When `GET /admin/dashboard` is requested, Then the response is `200` and contains the three figure handles. *(This is the legitimate RED: today it returns `403` because of `AdminDashboardController.php:14`.)*
- **AC-016 (S3)** Given a verified user holding neither `admin.dashboard` nor `Super Admin`, When `GET /admin/dashboard` is requested, Then the response is `403` and the body contains none of `Antrean Verifikasi`, `Semua Instansi`, or an agency name (US-003 scenario 2).
- **AC-017 (S3)** Given the permitted user of AC-015, When the landing is requested, Then the response is not a redirect and contains the triage handles — proving the route-level and controller-level decisions agree, since the controller declares no middleware of its own (US-003 scenario 3).

### 5.4 US-004 — One click from a figure into its queue (FEAT-004, D9)

- **AC-018 (S3)** Given `?period=current_year`, When the landing renders, Then the verification anchor's `href` contains `validation_status=submitted` and does **not** contain `period=`; Given `?period=current_month`, Then the same anchor contains `period=<YYYY-MM>` for the selected month.
- **AC-019 (S3)** Given each of the five period keys in turn, When the landing renders, Then the assessment anchor's `href` contains `status=pending` and never contains `period=` (A1, §4.3).
- **AC-020 (S3)** Given each of the five period keys in turn, When the landing renders, Then the report anchor's `href` contains `status=submitted` and never contains `period=` (D9).
- **AC-021 (S3)** Given a `Super Admin` viewer and each figure's `href` with its query string stripped, When that path is requested, Then the status is `200` or `302` — every link resolves to a named route and never 404s or 500s (US-004 scenario 2).
- **AC-022 (S1)** Given each of the five period keys, When the deep-link period parameter is derived, Then it is present **only** for the verification target and **iff** `isSingleMonth()`; the assessment and report targets never receive one (§4.3 expressibility rule, A1, D9).

### 5.5 US-005 — The queue badge follows the administrator everywhere (FEAT-007, Phase 2)

- **AC-023 (S4)** Given a `Super Admin` viewer (the pre-agreed S4 viewer of §6.1 — it satisfies both `admin.dashboard` and `sakip.dashboard.view` via `Gate::before`) and a non-empty verification queue, When two pages owned by two different controllers render `layouts.modern` (`sakip.dashboard`, which returns `sakip.dashboard.index`, and `admin.dashboard`), Then both responses contain `sidebar-link-badge` with the same integer as the landing's verification figure for the same scope and period.
- **AC-024 (S4)** Given the same `Super Admin` viewer and an empty verification queue, When either `layouts.modern` page renders (`sakip.dashboard` and `admin.dashboard`), Then neither response contains a `sidebar-link-badge` element (no zero badge).
- **AC-025 (S2)** Given a viewer and a period, When `verificationCountFor($user, $period)` is compared with `summaryFor($user, $period)->verificationCount`, Then the two are identical (single count implementation).
- **AC-026 (S4)** Given the same `Super Admin` viewer and a valid `period` query parameter on a non-landing `layouts.modern` page (`sakip.dashboard`), When the badge renders, Then its value equals the landing's verification figure for that same period; Given no parameter, Then it equals the default-period figure (ASSUMPTION A5).

### 5.6 US-006 — No figure claims a period basis it does not have (FEAT-003, F-02)

- **AC-027 (S3)** Given any period selection, When the landing renders, Then the assessment and report anchors' inner content each contain their canonical figure name and `Tidak dibatasi periode`, and contain none of the period labels (`Tahun <Y>`, `Triwulan <I|II|III|IV> <Y>`, month name + year) of the selected period.
- **AC-028 (S3)** Given datasets in two calendar years, When the viewer switches the period, Then the verification count changes accordingly while the assessment and report counts stay identical and remain labelled `Tidak dibatasi periode`.
- **AC-029 (S3)** Given the selected period, When the landing renders, Then each of the three figure names is rendered exactly once inside its own `data-triage-figure` anchor, the verification anchor contains the selected period label, and the assessment and report anchors do not (accessible-name rule, PRD §5.3).

### 5.7 US-007 — An empty period is explained, not blank (FEAT-005, D3)

- **AC-030 (S3)** Given no pending verification records in the selected period and no pending assessments or submitted reports at all, When the landing renders, Then each figure renders `0` with its own basis label, `data-triage-attention` is **absent** from the HTML, and `data-triage-empty` is present containing `Belum ada pekerjaan tertunda pada periode ini.`
- **AC-031 (S3)** Given pending work in exactly one queue, When the landing renders, Then exactly one `data-triage-signal` element is present, its handle matches the non-empty queue, and the other two queues appear only as zero-valued figures.
- **AC-032 (S3)** Given an empty period adjacent to a non-empty period, When the viewer switches between them, Then the strip appears and disappears accordingly, each figure equals its own basis count (the verification figure follows the selected period; the assessment and report figures are unmoved), and no value carries over from the previous period.

### 5.8 FEAT-006 & cross-cutting criteria

- **AC-033 (S3)** Given any landing render, When the body is inspected, Then (a) `substr_count($content, 'data-triage-figure=') === 3`; (b) the triage region extracted with `preg_match('/<section[^>]*data-triage-region[^>]*>(.*?)<\/section>/s', $content, $m)` is non-empty; and (c) the forbidden strings `Aktivitas Login (7 Hari)` and `Tervalidasi` are absent **from `$m[1]`**, asserted with `assertStringNotContainsString`. The sidebar string `Indikator Kinerja` is deliberately **not** asserted: `resources/views/layouts/modern.blade.php:86` renders it on every page and the layout shell is out of scope (§1.1 and the ASSUMPTION A2 carve-out).
- **AC-034 (S3)** Given a permitted viewer, When the landing renders, Then the observed count of counted domain statements equals the §4.4 expectation for that viewer state and never exceeds `6` (CON-003).
- **AC-035 (S1 + S3)** Given the whole suite, When `php artisan test`, `./vendor/bin/pint --test --no-interaction`, and `php artisan test --testsuite=Unit` are executed, Then all pass and the Unit suite stays under its runtime floor (`CONSTRAINTS.md` §1). If the environment has no PHP toolchain, the gate is reported **unverified** (§7.4) — never assumed green, and the Unit-suite runtime stays an open measurement because S2 is the second DB-backed test class in `tests/Unit`.
- **AC-036 (S2)** Given F-8 (a pending assessment whose parent `PerformanceData` row is soft-deleted) alongside F-6 and F-7, When the cross-agency and the agency-bound A summaries are both resolved, Then `assessmentCount` excludes F-8 in **both** states (cross-agency `4`, agency-bound A `3`) and the "cross-agency = sum of the two agency counts" invariant holds for the assessment queue exactly as AC-011 requires (C-6, ASSUMPTION A8).
- **AC-037 (S3)** Given the §5.0 matrix written through the HTTP layer for an agency-bound viewer of A, When the landing renders and the verification anchor's `href` and the assessment anchor's `href` are each requested in the same session, Then the verification queue page contains the rows the figure counted and the assessment queue page contains the rows the assessment figure counted — with an exact row-count comparison when the figure is `≤ 15` (the targets paginate at 15) and a containment assertion otherwise; the test comment must state which of the two it asserts (C-4, C-5 — the figure↔target agreement that AC-018 and AC-021 never asserted). **Scope (D-5):** agreement is asserted **for the §5.0 matrix only**, whose assessment rows carry `created_at` inside the frozen current year; an assessment created in a prior year is deliberately **outside** this criterion, because D-S7 accepts the frame divergence for the assessment queue.
- **AC-038 (S3)** Given a cross-agency (`Super Admin`) viewer and the §5.0 matrix, When the landing renders, Then the verification figure is `5` **and** the verification anchor is still rendered as a single anchor whose `href` returns `200` or `302` — never `404`/`500` — even though `DataCollectionController@index` has no cross-agency branch and therefore renders an empty queue. The accepted divergence is recorded as D-S8 in §9.1 and restated in the test comment, so the empty queue is a documented, asserted behaviour rather than a silent surprise (C-5, ASSUMPTION A9).

## 6. Test Automation Strategy & Pre-Agreed Test Seams (TDD Blueprint)

### 6.1 Pre-Agreed Test Seams Matrix

| # | Public boundary (seam) | Test file (new) | Level | Required cases |
| --- | --- | --- | --- | --- |
| **S1** | `App\Support\ReportingPeriod` (pure value object) | `tests/Unit/Support/ReportingPeriodTest.php` | Unit — no HTTP, no DB, no auth | AC-001, AC-002, AC-003, AC-004, AC-022, AC-039, plus: the **precedence case** asserted in both directions (config set **and** an explicit `$now` → the injected clock wins; config set **and** `$now === null` → the config wins); every key resolves inside the anchor year; `performancePeriodRange()` is lexicographically ordered `YYYY-MM → YYYY-MM`; `start` is at `00:00:00` and `end` at `23:59:59` of the resolved window |
| **S2** | `App\Services\AdminTriageService::summaryFor()` / `verificationCountFor()` | `tests/Unit/Services/AdminTriageServiceTest.php` | Unit with real DB (`RefreshDatabase`, factories), **no `actingAs()`** | AC-009, AC-010, AC-011, AC-012, AC-025, AC-036, plus: a **period-invariance case** proving the assessment and report counts are identical for `current_year`, `last_quarter`, and `current_month` over the same dataset (A1, D9); a soft-deleted-agency case proving the label still resolves and the counts stay agency-bound (ASSUMPTION A7). Every case builds its fixtures from the §5.0 canonical matrix. **The former "not narrowed by ambient auth" case is removed (C-2):** with no authenticated user `InstansiScope` is a no-op, so that case is unsatisfiable at S2 and now lives at S3 |
| **S3** | `GET /admin/dashboard` (HTTP route boundary) | `tests/Feature/AdminTriageLandingTest.php` | HTTP feature (`actingAs`, `RefreshDatabase`, seeded permissions) | AC-005 … AC-008, AC-013 … AC-022, AC-027 … AC-034, AC-037, AC-038, plus the **ambient-auth invariant moved here (C-2)**: a `Super Admin` render's three figures must equal a direct `summaryFor()` call made with no authenticated user, so the invariant is falsifiable rather than tautological, and the long-agency-name markup contract (TC-072, review `SPEC-B-06`) |
| **S4** | Sidebar badge render on `layouts.modern` (view rendering boundary) | `tests/Feature/SidebarQueueBadgeTest.php` | HTTP feature on **two** routes owned by two controllers, with the `Super Admin` viewer pre-agreed in §5.5 | AC-023, AC-024, AC-026 (Phase 2 only) |

### 6.2 Mocking & Isolation Boundaries

- **Real implementation (never mocked):** the database (SQLite `:memory:` per `phpunit.xml:25`), all Eloquent models and their global scopes, `InstansiScope`, the `RefreshDatabase` + `$seed = true` baseline
  (`tests/TestCase.php`), Spatie roles/permissions, the Blade view, the router, and the `AdminTriageService`/`ReportingPeriod` under test.
- **No mocks are permitted at any seam of this feature.** The slice has no third-party integration (no payment gateway, no external notification, no mail), so there is no legitimate mock boundary. `Mockery`/`$this->mock()` must not appear in the four seam files.
- **S1 must not touch the DB or auth:** S1 is a pure function seam; a query or an `actingAs()` there would hide the boundary being specified.
- **S2 must not use `actingAs()`:** the service receives the viewer explicitly and applies the agency restriction explicitly (SEC-002). Running S2 without an authenticated user exercises the explicit-coverage path directly; running it *with* auth would make the test pass for the wrong reason. **Correction (C-2):** because `app/Models/Scopes/InstansiScope.php:24-26` is a documented no-op when no user is authenticated, S2 can prove nothing about *ambient auth*; that invariant is asserted at **S3** instead (see below), and no S2 case may claim it.
- **S3 owns the ambient-auth invariant (C-2):** a `Super Admin` landing render must yield three figures identical to a direct `summaryFor()` call issued with **no** authenticated user. Both halves are observable, so the invariant can fail — unlike the removed S2 case, which could not.
- **S3/S4 must drive HTTP through the router** (`$this->get(route(...))`) rather than instantiating the controller; authorization is part of what they specify.
- **Fixtures:** use `PerformanceDataFactory` (`submitted()`, `forInstansi()`, `forPeriod()`), `AssessmentFactory` (`pending()`, `forPerformanceData()`), `ReportFactory` (`forInstansi()`), `InstansiFactory`, and `UserFactory`; create roles/permissions with `firstOrCreate` +
  `givePermissionTo` exactly as `tests/Feature/ReportIndexRendersTest.php:19-27` and `tests/Feature/SakipDashboardAccessTest.php:22-25` do. Never hand-write rows that a factory state already expresses.

### 6.3 Seam Idioms (pre-agreed, house style)

**Content assertion (CON-007, finding F-07):**

```php
$response = $this->actingAs($viewer)->get(route('admin.dashboard'));
$response->assertOk();

$content = $response->getContent();
$this->assertStringContainsString('data-triage-figure="verification"', $content);
$this->assertStringContainsString('Antrean Verifikasi', $content);
$this->assertStringContainsString('Tahun '.$now->year, $content);
```

For assertions scoped to a single anchor (AC-029), extract it from `$content` with one regular expression over the rendered handle before asserting — for example `preg_match('/<a[^>]*data-triage-figure="report"[^>]*>(.*?)<\/a>/s', $content, $m)` and then assert on `$m[1]`.
No HTML-parsing dependency, no browser harness, no screenshot helper is introduced.

**Negative content assertion (C-1, AC-033)** — the paired idiom is `assertStringNotContainsString(...)` on the same `$content`, and on the extracted triage region where the criterion scopes it. Never `assertDontSee()`, never an HTML parser:

```php
preg_match('/<section[^>]*data-triage-region[^>]*>(.*?)<\/section>/s', $content, $m);
$this->assertNotSame('', $m[1] ?? '', 'the triage region must be extractable from the response');
$this->assertStringNotContainsString('Aktivitas Login (7 Hari)', $m[1]);
$this->assertStringNotContainsString('Tervalidasi', $m[1]);
```

**Domain-query count (CON-003; no such idiom exists in the repository yet, so this specification pre-agrees it):**

```php
DB::enableQueryLog();
$response = $this->actingAs($viewer)->get(route('admin.dashboard'));
$response->assertOk();

$domainTables = ['performance_data', 'assessments', 'reports', 'instansis', 'audit_logs'];
$count = collect(DB::getQueryLog())
    ->filter(fn (array $query) => Str::contains($query['query'], $domainTables))
    ->count();
DB::disableQueryLog();

$this->assertSame(4, $count);   // §4.4 expectation for the cross-agency viewer
$this->assertLessThanOrEqual(6, $count);
```

`DB::enableQueryLog()`/`disableQueryLog()` wrap the request only; the count is asserted **exactly** for the viewer state (not merely as an upper bound) so the assertion cannot silently rot, and the `≤ 6` ceiling is asserted alongside it.

### 6.4 Coverage Obligations

- Every REQ-001…REQ-015 maps onto at least one AC in §5 and at least one required case in §6.1; REQ-014's `scopeForCurrentYear` delegation needs one focused case (assert the scope yields the configured active year when `sakip.reporting.active_year` is set).
- `tests/Unit/ArchitectureGuardTest.php` runs as-is: new short class names (`ReportingPeriod`, `TriageScope`, `AdminTriageSummary`, `AdminTriageService`, `SidebarQueueBadgeComposer`) are unique in `app/`, and every new Blade `route()` name exists.
- Floor-guard: no test may be skipped, marked incomplete, or weakened to pass. **Correction (v1.1 / correction 8):** `grep -rn 'admin.dashboard' tests/` returns **no hits** — no test covers `GET /admin/dashboard` today, so AC-015 is a brand-new RED with no incumbent assertion to replace. (The earlier wording, "the previous landing assertion (the 403 defect) is replaced", described a test that does not exist.) Nothing is deleted to make room for AC-015.
- The four audit-driven criteria are part of the obligation set: **AC-036** (S2, canonical assessment population — C-6), **AC-037** (S3, figure↔target agreement, scoped to §5.0 — C-4/C-5), **AC-038** (S3, accepted cross-agency divergence — C-5) and **AC-039** (S1, `anchor()` precedence — C-7). They must appear in the `/tdd-checklist` inventory matrix.
- Existing tests that must stay GREEN: `SakipDashboardAccessTest` (it covers `sakip.dashboard` only, and its status/redirect assertions are unaffected by the Phase-2 badge), `ReportIndexRendersTest`, `DataCollectionControllerTest`, `TargetTenantIsolationTest`, `EvidenceUpdateIsolationTest`, `RateLimitingTest`, `RedirectFlowTest`.

## 7. Project Structure & Executable Commands

### 7.1 Files Created (Phase 1)

| Path | Purpose | Notes |
| --- | --- | --- |
| `app/Support/ReportingPeriod.php` | Periode Pelaporan resolver (seam S1) | **New directory** `app/Support/`; update `docs/ARCHITECTURE.md` §5/§6 in the same change |
| `app/Support/TriageScope.php` | Cakupan Instansi enum | Holds the two canonical label strings |
| `app/Support/AdminTriageSummary.php` | Read-model return contract | `PERIOD_INDEPENDENT_BASIS_LABEL` lives here |
| `app/Services/AdminTriageService.php` | Triage read model (seam S2) | Thin, read-only, no cache, no auth coupling |
| `tests/Unit/Support/ReportingPeriodTest.php` | S1 cases | New sub-directory, mirroring `tests/Unit/Services/` |
| `tests/Unit/Services/AdminTriageServiceTest.php` | S2 cases | No `actingAs()` |
| `tests/Feature/AdminTriageLandingTest.php` | S3 cases | HTTP criteria, render contract, deep links, query budget |

### 7.2 Files Modified (Phase 1)

| Path | Change |
| --- | --- |
| `app/Http/Controllers/Admin/AdminDashboardController.php` | Rewrite `index()` to delegate to the service; inject the service; **delete** the phantom `can:access-admin-dashboard` middleware (AC-015 is the RED for this) |
| `resources/views/admin/dashboard.blade.php` | Three-figure triage block with the §4.4 handles and frozen copy; three-signal attention strip; empty-period sentence; period selector (GET form, no inline handler); remove telemetry and inventory cards; keep the recent-activity table and quick actions |
| `config/sakip.php` | Add the `active_year` key **inside the existing `reporting` block** (`config/sakip.php:103-116`) — `env('SAKIP_ACTIVE_YEAR', null)`, read as `(int)` at its single call site (REQ-015, C-8). No new top-level block and no new block name |
| `app/Services/SakipDashboardService.php` | `getDateRange()` becomes an adapter over `ReportingPeriod`; return type stays `array{0: Carbon, 1: Carbon}` (mutable `Carbon::instance(...)`) so its five call sites stay untouched |
| `app/Models/Scopes/ForYearTrait.php` | `scopeForCurrentYear` delegates to `ReportingPeriod::activeYear()` (REQ-014) |
| `app/Models/Scopes/ForYearScope.php` | **Delete** (dead `ForYearScope` class plus unused `ForYear` trait; zero `use` sites verified) |
| `docs/ARCHITECTURE.md` | Record `app/Support/` (and `app/View/Composers/` in Phase 2) in §5 and §6 (Living Architecture Map Mandate) |

### 7.3 Files Created / Modified (Phase 2 only)

| Path | Change |
| --- | --- |
| `app/View/Composers/SidebarQueueBadgeComposer.php` | New composer (seam S4); new directory `app/View/Composers/` |
| `app/Providers/AppServiceProvider.php` | Register `View::composer('layouts.modern', SidebarQueueBadgeComposer::class)` beside the existing composer (`:180`) |
| `app/Http/Controllers/Admin/AdminDashboardController.php` | Drop the `pendingDataCount` view variable in the same commit as the composer |
| `tests/Feature/SidebarQueueBadgeTest.php` | S4 cases |
| `docs/ARCHITECTURE.md` | Record `app/View/Composers/` |

### 7.4 Executable Commands

```bash
# Focused RED->GREEN cycles (one seam at a time)
php artisan test --filter=ReportingPeriodTest
php artisan test --filter=AdminTriageServiceTest
php artisan test --filter=AdminTriageLandingTest
php artisan test --filter=SidebarQueueBadgeTest        # Phase 2

# Full quality gates (mirrors .github/workflows/ci.yml; CONSTRAINTS.md §2)
composer validate --strict --no-check-all
./vendor/bin/pint --test --no-interaction
php artisan config:clear && php artisan test
php artisan test --testsuite=Unit                      # runtime floor < 10s target / < 20s hard

# Container fallback when no local PHP toolchain is available
make up && make test && make lint
```

Do not claim any gate passed unless the command completed successfully; a gate blocked by toolchain or network availability is reported as **unverified** (`CONSTRAINTS.md` §2).

## 8. Implementation Guardrails (Three-Tier System)

**Always do**

- Write the failing test at the named seam first, run it, and observe the expected failure reason before writing production code (CONSTITUTION Prinsip I).
- Reuse `PerformanceData::submitted()`, `Assessment::pending()`, `Report::submitted()`, the existing factories, and the existing CSS components.
- Run the focused seam test, then `./vendor/bin/pint --test --no-interaction`, then the full suite before each commit; keep the suite GREEN through Phase 1 before starting Phase 2.
- Keep the controller thin: validate the query envelope, delegate, bind the view.
- Use the canonical `CONTEXT.md` terms and the frozen Indonesian copy verbatim (§4.4).
- Update `docs/ARCHITECTURE.md` when `app/Support/` (Phase 1) and `app/View/Composers/` (Phase 2) are introduced.

**Ask first**

- Changing any string in the §4.4 copy table — it is a CI contract.
- Deviating from the **resolved A1 decision** (the assessment figure is period-independent) or from **ASSUMPTION A2** (removing the two inventory cards), which still needs product-owner confirmation before the Plan phase.
- Touching any shared file beyond the §7.2/§7.3 lists, including `AssessmentStatus`, `Status`, or any policy.
- Introducing any dependency, any new permission, or any caching layer (D6 forbids caching in Phase 1).

**Never do**

- Bypass `InstansiScope` (`withoutGlobalScope`, `withoutInstansiScope`, manual tenancy `whereRaw`) — CONSTRAINTS §3 rule 6.
- Send a deep-link parameter a target does not honour, or build a link by string concatenation instead of `route()`.
- Re-declare status literals inline in the count queries, or introduce a second period vocabulary or a second "current year" implementation.
- Add `whereYear('period', ...)` to the report or assessment counts, present the report or assessment figure as period-scoped, or invent a period value for either deep link (A1, D2/D9, US-006).
- Add an inline event handler to the selector (CON-004) or rely on the Vite/Tailwind pipeline.
- Add suppressions (`@phpstan-ignore`, `// @noinspection`, …), skip or weaken tests, lower a `CONSTRAINTS.md` threshold, or edit `phpunit.xml`/CI to pass.
- Stage the pre-existing working-tree dirt recorded in memory (unrelated `AdminService`, `AdminController`, `modern-sakip.css`, `.mimosa/hook-state/` changes) as part of this feature.
- Assert the absence of `Indikator Kinerja` anywhere in the landing body, or rename the sidebar link to make such an assertion pass — the layout shell is out of scope and only the triage region is asserted (C-1).
- Count the assessment queue with a different population per viewer state: both forms must carry `whereHas('performanceData')` so the soft-deleted-parent rule of §4.2 and AC-036 hold identically (C-6).
- Claim the ambient-auth invariant at S2 (impossible without `actingAs()`), or leave AC-037/AC-038 unasserted because the divergence is "known" — an accepted divergence must still be a tested one (C-2, C-5).

## 9. Rationale, Context & Architecture Decisions

### 9.1 Decisions taken by this specification

| # | Decision | Rationale | Rejected alternatives |
| --- | --- | --- | --- |
| D-S1 | Assessment figure is period-independent and labelled `Tidak dibatasi periode` — **ratified by the product owner on 2026-09-24** | `Assessment` has no period column; its only anchor is `created_at` and the target's own filter reads a calendar year, so any period claim would be a claim the data cannot support. An explicit period-independent label is honest, keeps one period vocabulary, and is the variant FEAT-003 already permits | `created_at` bounded by the selected range (exact, but a creation-date basis the PRD only permitted after Spec confirmation — declined); literal `whereYear('created_at', $year)` (exact for the year key only, over-broad for quarters and months) |
| D-S2 | Period resolver as a `final readonly` value object in `app/Support/` with an injectable clock and a config seam | Pure, unit-testable with zero DB or auth; the `active_year` seam satisfies D1 without touching call sites; the value object is the single place that knows `performance_data.period` is `YYYY-MM` | a service (heavier, no benefit); a model trait (cannot be unit-tested purely); leaving `getDateRange()` and the year traits duplicated |
| D-S3 | Report figure stays period-independent with the explicit label `Tidak dibatasi periode` (D2/D9) | `reports.period` is free-form and quarter-coded in the existing suite, so no honest calendar mapping exists; the explicit label satisfies the Periode honesty rule in `CONTEXT.md` | any range mapping (unverifiable); any period label (misleading — forbidden by US-006) |
| D-S4 | Deep-link period parameter only where the target's filter can express the selection | The three targets have three different filter semantics (exact `YYYY-MM`, year, exact quarter-coded string); sending what a target cannot honour would show false-empty queues | sending `period` unconditionally (broken or empty results); sending nothing at all (loses the §5.2 step-4 requirement wherever it *is* expressible) |
| D-S5 | Sidebar badge via a `layouts.modern` view composer that resolves the period from the request | Removes the inverted coupling documented as C5 while keeping badge value and figure value identical, including on non-default periods | extending the `View::composer('*')` closure (unscoped, runs on every view); keeping the controller variable (C5 unfixed) |
| D-S6 | Query count asserted as an exact per-viewer value plus a `≤ 6` ceiling | PRD §6 marks the query count as CI-enforced, yet the repository has no such idiom; asserting the exact value prevents silent rot while the ceiling encodes the PRD budget | `≤ 6` alone (weak, rots silently) |
| D-S7 | The assessment figure stays **period-independent** even though its target is year-bounded (`AssessmentController.php:53, :60`) — `[ASSUMPTION]`, C-4 | The figure's job is to state how much assessment work exists, not to mirror a target whose `whereYear('created_at', …)` predates any request filter. The divergence is asserted (AC-037, AC-038) and disclosed in §4.3 instead of hidden, so a reader is never told the pair agrees when it does not | Scoping the figure to the target's year (a basis `Assessment` does not carry — forbidden by US-006); silently keeping the mismatch unasserted (the pre-v1.1 state) |
| D-S8 | For the cross-agency viewer the verification figure is rendered while its target queue is empty — `[ASSUMPTION]`, C-5 | `DataCollectionController@index:84-95` derives coverage from `performance_indicators.instansi_id` with no `Super Admin` branch; repairing it is a change to an existing controller outside this slice (§1.1). Rendering the figure with an asserted, documented degradation (AC-038) is honest and keeps the slice read-only on other controllers | Gating or hiding the figure for `Super Admin` (violates REQ-009 and hides real work); fixing the target inside this slice (scope creep on an out-of-scope controller) |
| D-S9 | The canonical assessment population excludes assessments whose parent `PerformanceData` is soft-deleted — `[ASSUMPTION]`, C-6 | Without it the cross-agency count and the agency-bound count describe different populations, so AC-011's invariant would be untestable and the two viewer states incomparable. A trashed performance-data row is not pending work | Counting trashed-parent assessments in both states (a `withTrashed()` carve-out forbidden by §4.6); leaving the asymmetry unstated (the pre-v1.1 state) |

### 9.2 ADR verdict

No ADR is written. Each decision above is reversible (a resolver, a label, a parameter rule, a composer), none would surprise a new engineer, and each had a normal engineering answer rather than a paradigm trade-off — the Triple Gate
(`.agents/standards/ADR-FORMAT.md`) requires all three criteria and is unmet. This matches the verdict recorded in `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md` §4.

### 9.3 Traceability

| PRD item | Requirements | Acceptance criteria |
| --- | --- | --- |
| FEAT-001 / US-001 | REQ-001, REQ-002, REQ-003, REQ-015 | AC-001…AC-008 |
| FEAT-002 / US-002 | REQ-004, SEC-002 | AC-009…AC-014 |
| FEAT-003 / US-006 | REQ-005, REQ-006, REQ-007, REQ-008 (A1 ratified 2026-09-24) | AC-005, AC-027…AC-029 |
| FEAT-004 / US-004 | REQ-009 | AC-018…AC-022 |
| FEAT-005 / US-007 | REQ-010 | AC-030…AC-032 |
| FEAT-006 | REQ-011 | AC-033 |
| FEAT-007 / US-005 | REQ-012, D-S5 | AC-023…AC-026 |
| FEAT-008 / US-003 | REQ-013, SEC-004 | AC-015…AC-017 |
| PRD §6 metrics | CON-003, CON-005, CON-007 | AC-034, AC-035 |
| PRD §7.5 item 3 (C2) | REQ-014 | §6.1 S1/S2 case list |
| PRD §7.5 item 4 (C5) | REQ-012 | AC-023…AC-026 |
| Audit C-4 / C-5 (figure↔target) | REQ-007, REQ-009, A9, D-S7, D-S8 | AC-037, AC-038 |
| Audit C-6 (canonical population) | SEC-002, A8, D-S9 | AC-036 |
| Audit C-7 (anchor precedence) | REQ-015, D-S2 | AC-039 |
| Audit C-1 / C-2 / C-8 / C-9 | A2 removal-scope carve-out, CON-007, REQ-015, §4.6 | AC-033 (rescoped), §6.1 S2/S3 rows, AC-004, §6.3 negative idiom |
| Audit Iteration-2 refinements (D-1, D-2, D-5, D-4) | REQ-015, A8, D-S7 | AC-039, AC-004, AC-037 (scope clause), §5.0 F-12 with AC-036 |
| Review v1.4 (`SPEC-B-01`…`SPEC-B-04`) | REQ-015, §4.4, §4.5, §9.4 | AC-028 (pinned by `test_switching_period_moves_only_the_verification_figure`), §4.4 `data-triage-period-select` (TC-051), §4.4 delivered budget `5 / 6 / 1`, §4.5 badge ownership |

### 9.4 Post-implementation obligations

1. Update `docs/ARCHITECTURE.md` §5/§6 for `app/Support/` (Phase 1) and `app/View/Composers/` (Phase 2) — Living Architecture Map Mandate.
2. **DISCHARGED BY RE-SCOPE (v1.5).** The three manually verified metrics (above-the-fold placement at 1280×800, the five-participant ≤10 s usability check, the p95 < 500 ms measurement) were never produced, and no harness in this
   repository can produce them. On 2026-09-25 the product owner re-scoped them to `[Assumed / Backlog]` — the decision of record is `docs/prd/prd-admin-triage-landing.md` §6, and it is reflected in the review artifact
   (`docs/review/code-review-admin-triage-landing-2026-09-25.md` §7) that also carries obligation 4's record.
3. **DISCHARGED (v1.4).** The §4.4 exact query expectations are updated to the measured **`5 / 6 / 1`** in the same cycle as the composer (`7e8642e`); the numbers are asserted per viewer state, not predicted. The instruction's original target of
   `5 / 6 / 2` was wrong for the unassigned state, whose badge short-circuits without issuing a statement — see §4.4 "Delivered measurement (v1.4)" and review finding `SPEC-B-04`.
4. Record the three accepted decisions of the v1.1 remediation — D-S7 (assessment figure/target frame divergence), D-S8 (empty cross-agency verification target) and D-S9 (canonical assessment population) — in the Phase-1 review artifact. If the product owner resolves ASSUMPTION A9 differently, amend this Spec and its ACs **before** Phase 2 starts; the divergences are accepted, not forgotten. **Discharged 2026-09-25** by
   `docs/review/code-review-admin-triage-landing-2026-09-25.md` (§5.2 records that AC-037/AC-038 assert them as positive expectations).
5. No change to `CONTEXT.md`, `docs/adr/`, or the memory file is required by this specification — the seven canonical terms already exist and no decision meets the Triple Gate.
