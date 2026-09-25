# Product Requirements Document: Admin Triage Landing (Landing Triage)

**Status:** Draft (v1.1 — clarification corrections applied, pending product-owner re-approval)
**Version:** 1.1
**Date:** 2026-09-24
**Author:** TDD Product Manager
**Target Technical Spec:** `/spec/spec-admin-triage-landing.md`
**Target Quality Gate:** `/tdd-clarify` (Iteration 1 complete: `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md`)

> **Remediation note (v1.1):** this revision applies the four mandatory text corrections listed in §4 of the clarification report — (1) FEAT-003 report-period evidence replaced with the
> verified fact, (2) FEAT-004 deep-link contract stated, (3) §2 and §3.2 aligned with the ratified `CONTEXT.md` and the F-03 scope semantics, (4) §6 and §9 carry per-metric enforcement
> modes and the corrected phase allocation. **No ratified product decision was changed**; this revision only aligns the document with the decisions recorded on 2026-09-24.

> Upstream input: `docs/discovery/idea-admin-triage-landing.md` (Phase 0, verdict GO). Findings **C1–C7** with `file:line` evidence live in that draft §3 and are
> referenced here, never restated. Architectural facts live in `docs/ARCHITECTURE.md`; quality bars in `CONSTRAINTS.md`; product intent in `PRODUCT.md`.

---

## 1. Product Overview

### 1.1 Summary

SAKIP's HQ landing (`GET /admin/dashboard`) must become a **triage surface**: on arrival, a platform administrator answers *"what needs my attention first, for which
agency, in which reporting period"* without leaving the page. Today the page reports inventory rather than attention, mixes three time dimensions in one row of cards,
never states whose data it shows, and exposes only one of the platform's three approval queues.

This PRD defines the **WHAT** for the first vertical slice: a period-scoped, instansi-explicit read model rendered as a triage landing by an HQ administrator, with each
figure deep-linking into the queue it describes. Interface contact: `/admin/dashboard`.

### 1.2 Strategic Goals

**Business Goals**

- Make HQ supervision defensible: every figure on the landing carries a stated agency scope and a stated period basis — a period label wherever a verified period anchor exists, and an
  explicit period-independent label where none does — so it can be quoted in an official report.
- Shorten the time from login to the first corrective action for administrators who supervise data collection across agencies.
- Establish one authoritative vocabulary for reporting periods, replacing duplicated and drifting implementations (`docs/discovery/idea-admin-triage-landing.md` §3, C2).

**User Goals**

- See, in one screenful, how much work is waiting at each of the platform's approval queues.
- Know instantly whether the numbers cover all agencies or only the viewer's own.
- Reach the specific queue pre-filtered, instead of landing on an unfiltered list and searching again.

**Non-Goals (Out of Scope)**

- **Audit-anomaly detection.** Named in `PRODUCT.md` but requires its own rule design; deferred to idea **I2**.
- Charts, trends, and visualisations. The agency-side SAKIP dashboard already owns charts; HQ triage needs counts and links first.
- Bulk actions and inline approvals. Rejected in discovery Option C: it duplicates `sakip.data-collection.index` and expands write-path scope.
- Layout-shell defects (duplicate H1, inert header search, permanent notification dot, token/dark-mode cleanup, mobile `.page-header` margins).
- Backlog items **F1** (2 skipped rate-limit tests) and **F2** (63 PHPUnit-12 deprecations) — both are *HOW* concerns with their own routes.
- Any change to the workflow states themselves (`draft → submitted → validated → approved`) or to tenant-isolation semantics.

## 2. Domain Vocabulary (Aligned with `CONTEXT.md`)

The vocabulary of this feature is **ratified** in `CONTEXT.md`, which exists as of 2026-09-24 and is versioned (`!/CONTEXT.md` in `.gitignore`). This PRD uses exactly those seven
canonical terms and no others; the definitions below quote that glossary.

> [!NOTE]
> `CONTEXT.md` is the single source of truth for these definitions. If this section and `CONTEXT.md` ever diverge, `CONTEXT.md` wins and this PRD must be corrected.

- **Periode Pelaporan**: the annual, quarterly, or monthly window that scopes a triage figure. A figure not limited to such a window must state that limitation on screen.
  (`_Avoid_: "periode", "periode data", "bulan berjalan", "date range", "rentang waktu"`)
- **Cakupan Instansi**: the agency coverage of a figure or screen — either exactly one agency, or every agency in the system.
  (`_Avoid_: "tenant", "tenant scope", "scope instansi", "wilayah"`)
- **Semua Instansi**: the coverage label shown to a viewer entitled to figures that are not limited to any single agency.
  (`_Avoid_: "all tenants", "global scope", "lintas instansi", "semua unit"`)
- **Instansi Belum Ditetapkan**: the state of a viewer account that carries no agency assignment and is therefore entitled to no agency-scoped data.
  (`_Avoid_: "semua instansi", "instansi kosong", "instansi null", "instansi global"`)
- **Antrean Verifikasi**: the performance-data records awaiting a decision at the verification step of the reporting workflow.
  (`_Avoid_: "inbox", "pending list", "tugas saya", "daftar tunggu"`)
- **Antrean Asesmen**: the assessments awaiting their first decision, under the same agency coverage as the performance data they assess.
  (`_Avoid_: "antrean lain", "menunggu", "pending asesmen"`)
- **Antrean Laporan**: the reports awaiting HQ review before publication.
  (`_Avoid_: "antrean lain", "menunggu", "pending laporan"`)

**Feature name (not a glossary term).** *Landing Triage* names this feature's landing surface and appears in the title and phase names of this PRD only. It is deliberately **not**
ratified into `CONTEXT.md`, which governs domain concepts rather than screen names.
(`_Avoid_: "dashboard admin", "beranda admin", "overview"` — "dashboard admin" collides with the agency-side SAKIP dashboards)

**Ratification status:** no glossary work is pending for this feature. The three `Cakupan Instansi` states used below encode clarification finding **F-03**: `Semua Instansi` is available
only to a viewer entitled to cross-agency data, and `Instansi Belum Ditetapkan` is the explicit state of a delegated administrator without an agency assignment.

---

## 3. User Personas & Roles

### 3.1 Personas

- **Rina — HQ Administrator** (`Super Admin`): owns the platform, affiliated with no single agency, accountable for cross-agency data health. Arrives after login
  wanting to know what is stuck and with whom. Moderate technical literacy; lives in the admin panel daily and will quote these numbers in briefings.
- **Bayu — Delegated Administrator**: agency staff granted the `admin.dashboard` permission. Supervises and verifies his own agency's submissions. Low tolerance for
  ambiguity about whose data he is looking at, because his agency is the only one he may act on. A delegated administrator may also carry no agency assignment at all; in that case the
  landing must state that state explicitly rather than implying cross-agency access (§3.2 state 3, finding F-03).
- **Dea — Agency Officer** (`Assessor`, `Auditor`, `Data Collector`): explicitly **not** a user of this landing. Her surfaces are the SAKIP-side dashboards. Listed to
  bound scope: this feature must not change what she sees.

### 3.2 Role-Based Access Control

- **Entry rule:** access is governed by exactly one gate — `admin.dashboard` (`AppServiceProvider.php:93-94`), which Super Admins satisfy through `Gate::before`
  (`:88-90`) and delegated administrators satisfy through the seeded permission (`RolesAndPermissionsSeeder.php`). The second, phantom gate currently applied by the
  controller is a defect (discovery draft §3, C1) and must be removed, not preserved as an alternative policy.
- **Scope rule — three distinct viewer states (clarification finding F-03):**
  1. **Cross-agency viewer** (a `Super Admin`, who satisfies every ability through `Gate::before` and carries no agency binding): sees all agencies and is labelled `Semua Instansi`.
  2. **Agency-bound delegated administrator** (`instansi_id` set, holds `admin.dashboard`): sees **only** their own agency's figures and is labelled with that agency's name. Existing
     `InstansiScope` behaviour is preserved and never bypassed.
  3. **Delegated administrator with no agency assignment** (`instansi_id` null): sees the explicit `Instansi Belum Ditetapkan` state with zero counts. This viewer is **never** labelled
     `Semua Instansi`, because `InstansiScope` is default-deny (`app/Models/Scopes/InstansiScope.php:11-18`) — an unscoped viewer sees nothing, so a `Semua Instansi` label would
     promise data the screen cannot show.
- **Explicitly unchanged:** no new permission is introduced, and no user gains access they were not intended to have. Restoring delegated administrators' access is a
  defect fix, not a policy widening — the PRD forbids widening it further while fixing C1.

---

## 4. Functional Requirements & Feature Scope

- **FEAT-001 (Reporting-period dimension)** (Priority: High)
  - The landing exposes the current *Periode Pelaporan* and lets the viewer change it among year (default), quarter, and month.
  - The selected period is visible on the page at all times, in Indonesian, next to the figures it scopes.
  - Period resolution is one shared implementation; the feature must not introduce a second interpretation of "current period" anywhere.

- **FEAT-002 (Explicit instansi scope)** (Priority: High)
  - The landing always displays exactly one *Cakupan Instansi* indicator, resolved to one of the three states in §3.2: `Semua Instansi`, the viewer's agency name, or `Instansi Belum
    Ditetapkan`.
  - The indicator is never hidden, even when only one agency exists in the system, because its absence is what makes today's figures ambiguous.
  - A viewer in the `Instansi Belum Ditetapkan` state must be able to distinguish "no work is pending" from "I have no agency assignment"; the indicator carries that distinction without
    a tooltip.

- **FEAT-003 (Three queue counts on one period)** (Priority: High)
  - Three figures are shown, scoped to the *same* selected period range wherever a verified period anchor exists: **Antrean Verifikasi** (`PerformanceData` status `submitted`), **Antrean
    Asesmen** (`Assessment` pending), and **Antrean Laporan** (`Report` status `submitted`, period-independent in Phase 1 — see the verified anchors below).
  - **Verified period anchors (clarification finding F-02, checked against the working tree on 2026-09-24):**
    - **Antrean Verifikasi** — `performance_data.period` is a real, indexed period column (`string(7)`, `YYYY-MM`); this figure is period-scoped.
    - **Antrean Asesmen** — `Assessment` has no period column; the only existing precedent derives the year from `created_at` (`app/Http/Controllers/Sakip/AssessmentController.php:91-92`).
      This figure is period-scoped on that year precedent, flagged for Spec confirmation (§7.5 item 1).
    - **Antrean Laporan** — `reports.period` **does exist** (`string(20)`, indexed; `2025_10_14_080006_create_reports_table.php`), but it is free-form: the migration comment reads
      "YYYY-MM format or custom period", and the existing suite seeds `'period' => '2024-Q1'` (`tests/Feature/ReportIndexRendersTest.php:34`), i.e. report periods are quarter-coded.
      No verified mapping exists from the landing's calendar range to that vocabulary, so the report figure is **deliberately not period-scoped in Phase 1** and is presented as an
      explicitly labelled period-independent figure governed by US-006. A fabricated period basis is forbidden.
  - No figure is presented without its *Cakupan Instansi* context; a figure without a verified period anchor carries an explicit period-independent label instead of a period label.

- **FEAT-004 (Deep links into the queues)** (Priority: High)
  - Each figure links to the queue it describes, pre-filtered to the same status and scope wherever the target already supports it.
  - **Verified capability (clarification finding F-02; endpoints and parameters checked in the working tree):**
    - Data-collection index (`/sakip/data-collection`): `?period=` (exact match on `performance_data.period`) and `?validation_status=`
      (`app/Http/Controllers/Sakip/DataCollectionController.php:164-173`).
    - Assessment index (`/sakip/assessments`): `?status=`, `?category=`, `?priority=`, and `?period=` interpreted as a **year** through `whereYear('created_at', …)`
      (`app/Http/Controllers/Sakip/AssessmentController.php:77-95`).
    - Report index (`/sakip/reports`): `?status=`, `?type=` (matches `report_type`), `?period=` (**exact match**) and `?category=`
      (`app/Http/Controllers/Sakip/ReportController.php:82-95`).
  - **Resolved deep-link contract (ratified 2026-09-24):**
    - **Antrean Verifikasi** and **Antrean Asesmen** links carry the status/category filter, plus a period parameter only where the target's filter semantics can express the selected
      *Periode Pelaporan* range (§7.5 item 2).
    - **Antrean Laporan** link carries `?status=submitted` and **no period parameter**: the report index matches `period` exactly and report periods are quarter-coded, so no honest value
      can be derived from the landing's calendar range — and US-006 forbids inventing one.
    - Every link targets a named route and degrades to the target's unfiltered index rather than to a broken link or a 404 (§7.1, US-004 scenario 2).

- **FEAT-005 (Attention strip carries all three signals)** (Priority: Medium — **allocated to Phase 1** by decision F-04, because §5.2 step 3 describes it as part of the happy path)
  - The conditional attention strip surfaces a signal per non-empty queue (three distinct signals), preserving today's behaviour of appearing only when work exists.
  - Each signal exposes a single named action in Indonesian; the strip is not a dashboard-in-dashboard.
  - When every queue is empty the strip is not rendered at all, and US-007's empty-period sentence takes its place.

- **FEAT-006 (Telemetry leaves the triage block)** (Priority: Medium)
  - The login-event count is removed from the figure row. Recent activity may remain as secondary content, but no telemetry may occupy a triage figure slot again
    (`PRODUCT.md` product principle #1).

- **FEAT-007 (Layout-owned queue badge)** (Priority: Medium)
  - The sidebar queue badge must be produced by the layout's own data source rather than by one controller's view variables, so that it appears consistently on every
    page rendered with that layout (discovery draft §3, C5).

- **FEAT-008 (Single authoritative authorization)** (Priority: High)
  - One gate governs access to the landing; the divergent second gate is removed. This requirement is deliberately a functional requirement, not a refactor note,
    because it changes observable behaviour for delegated administrators.

---

## 5. User Experience & Flows

### 5.1 First-Time User Flow & Entry Points

- **Entry points:** the post-login redirect for administrators (`routes/web.php:43`), the sidebar entry "Panel Admin", and the direct URL.
- **No onboarding wizard.** The page must explain itself through its labels: period, scope, and Indonesian figure names. A first-time viewer should not need a tooltip to
  learn that a figure is period-scoped — the period is written next to it.
- **First-load default:** *Periode Pelaporan* = the current reporting year; *Cakupan Instansi* = one of the three §3.2 states: `Semua Instansi` for a cross-agency viewer, the viewer's
  agency name for an agency-bound viewer, and `Instansi Belum Ditetapkan` for a delegated administrator with no agency assignment.

The self-explanatory requirement is deliberate: the current page is documented as having "no definition anywhere for the ambiguous metrics a Super Admin is expected to
act on" (impeccable critique #10), and that is precisely what this landing must stop doing.

### 5.2 Core Experience & Happy Path

1. The administrator lands on `/admin/dashboard`; period defaults to the current reporting year and the scope chip states the scope in plain Indonesian.
2. Three figures render — Antrean Verifikasi, Antrean Asesmen, and the report figure — each carrying the same *Cakupan Instansi*, and each carrying the selected *Periode Pelaporan*
   label where a verified period anchor exists (FEAT-003). The report figure carries no period label in Phase 1 (US-006).
3. The attention strip appears above the figures only when at least one queue is non-empty, showing one signal per non-empty queue with a single named action.
4. The administrator activates a figure or a strip action; the corresponding queue opens pre-filtered to the same period and status.
5. The administrator changes the period using the selector; all figures recompute on the identical range, and the selected period is reflected in the URL so the view can
   be bookmarked and shared with a colleague.

Step 5 is a product requirement, not an implementation detail: a shareable, period-addressed view is what makes these numbers usable in supervision meetings.

### 5.3 Edge Cases & UI/UX Highlights

- **Empty period:** every period-scoped figure shows `0` with its period label and the report figure shows `0` as a labelled period-independent figure; the attention strip is hidden; a
  short Indonesian sentence states that the period has no pending work. The page must never present an empty region without explanation.
- **Mixed emptiness:** only non-empty queues appear in the attention strip. A zero queue is legitimate information in the figure row, but is not an "attention" signal.
- **Unauthorized viewer:** a user without `admin.dashboard` who is not a Super Admin receives a 403 and must not see a partially rendered landing.
- **Agency-bound viewer:** sees only their own agency's figures; the chip names the agency. Cross-agency leakage is a defect, never a design option.
- **Cross-agency viewer:** sees all agencies under the `Semua Instansi` label — the label matters most for this persona, because their data is the union of every agency.
- **Viewer with no agency assignment:** sees `Instansi Belum Ditetapkan` with zero counts and an explanation of that state — never the `Semua Instansi` label (§3.2, finding F-03).
- **Long agency names:** the scope indicator truncates with an ellipsis once the agency name exceeds 40 characters, and the header neither wraps nor reflows at a 1280 px viewport
  (finding F-05 replaced "degrades gracefully" with this measurable threshold).
- **Language and codes:** every label is Indonesian; raw machine codes must not leak into the interface (impeccable critique #2).
- **Accessibility:** each period-scoped figure is a link whose accessible name includes its period; the scope indicator is text, never colour alone; contrast stays at WCAG AA.

---

## 6. Success Metrics & Performance Budgets

Every metric below declares its **enforcement mode** (decision F-05). Only CI-enforced metrics create a floor-guard obligation; manual metrics are verified by a reviewer and recorded in the
review artifact.

**User-Centric Metrics**

| Metric | Target | Enforcement mode |
| --- | --- | --- |
| First meaningful figure visible without scrolling at 1280×800 | Present in the first rendered figure row | **Manual** — this repository installs no browser harness; no screenshot or layout automation is introduced |
| Figures whose basis is not stated on screen | 0 of 3 ambiguous (today's baseline: 3 of 3). A figure's basis is stated when its *Cakupan Instansi* is shown and, for a period-scoped figure, its period label is shown; a period-independent figure must be labelled as such | **CI-enforced** — HTTP content assertions |
| Triage question answerable without navigation | 0 clicks to *understand* the state, 1 click to *act* on it | **CI-enforced** for the click count (each figure is an anchor to a named queue route); **manual** for comprehension |
| Largest queue identified within 10 seconds of first view | 4 of 5 participants | **Manual** — five-participant usability check, pass threshold 4 of 5 |

**Business Metrics**

- Every figure on the landing is quotable in an official report because its scope and period basis are stated on screen. **Enforcement mode: CI-enforced**, through the period/scope
  presence assertions above.
- HQ administrators reach a corrective action from login without visiting an unfiltered list first. **Enforcement mode: CI-enforced** — every figure and strip action resolves to a named,
  filtered queue route.

**Technical Metrics & Constraints** (aligned with `CONSTRAINTS.md` — no new gate is introduced by this feature)

- Server-side query count for the landing ≤ 6 (three queue counts, scope resolution, period metadata, recent activity); no N+1 patterns.
  **Enforcement mode: CI-enforced** by a deterministic query-count assertion in the landing's feature test.
- Page render p95 < 500 ms against the seeded dataset; the period-scoped queries must use existing indexes (`idx_perf_data_instansi_period`, `idx_targets_year`).
  **Enforcement mode: manual** measurement during review — no performance harness exists in this repository, so this budget is **not** a CI gate and creates no floor-guard obligation.
- The existing quality gates remain GREEN: `php artisan test`, `./vendor/bin/pint --test --no-interaction`, Unit suite under its runtime floor.
  **Enforcement mode: CI-enforced** (existing pipeline, `CONSTRAINTS.md` §2).
- Accessibility: WCAG AA contrast retained for body text and figure labels; each period-scoped figure's accessible name includes its period.
  **Enforcement mode: CI-enforced** for the accessible-name assertion (HTTP content assertion); **manual** for contrast measurement.

---

## 7. Technical Considerations (Input for Specification Architect)

### 7.1 Integration Boundaries

- The landing stays a **server-rendered, session-authenticated** page reached through the existing route chain (`routes/web.php` requiring `routes/web_sakip.php`). No new
  API surface, no client-side state store, no AJAX endpoint is required by this PRD.
- Deep links must target **named routes** only, and must degrade to the unfiltered queue if a filter parameter is unsupported — never to a broken link.
- The feature is **read-only**. It introduces no new state-changing action, therefore it must not add audit-logging obligations and must not alter workflow transitions.
- Presentation reuses the incumbent design system (`public/css/modern-sakip.css` components and the Bootstrap CDN pipeline). This PRD does not authorise a new visual
  generation, and the Tailwind/Vite pipeline must not be assumed to render.

### 7.2 Reuse Obligations (do not reinvent these)

| Existing capability | Where it lives | Obligation |
| --- | --- | --- |
| Period vocabulary (`current_year` default, quarter, month) | `SakipDashboardService::getDateRange()` (`:608-649`) | Reuse and consolidate onto one implementation; do not author a parallel vocabulary |
| Queue definitions | `PerformanceData::submitted()`, `Assessment::pending()`, `Report::submitted()` | Reuse the model scopes; do not re-declare status strings inline |
| Status constants | `app/Constants/Status.php`, `AssessmentStatus.php` | Prefer constants over magic strings |
| Queue filtering | `?validation_status=` / `?period=` (exact) (`DataCollectionController.php:164-173`); `?status=` / `?category=` / `?priority=` / `?period=` (year) (`AssessmentController.php:77-95`); `?status=` / `?type=` / `?period=` (exact) / `?category=` (`ReportController.php:82-95`) | Reuse these parameters for deep links; never invent a parameter a target does not honour |
| Index coverage | `idx_perf_data_instansi_period`, `idx_targets_year`, `idx_targets_indicator_year` | Period-scoped counts must be index-friendly |

### 7.3 Data Privacy & Retention

- The landing renders **aggregates only**. No personal data, no employee records, and no evidence-document content is exposed by this feature.
- Agency names are organisational data, displayed as scope context; they are already visible elsewhere in the application to the same audience.
- No new data is collected, stored, exported, or retained. Retention settings in `config/sakip.php` (`audit.retention_days`) are untouched.

### 7.4 Scalability Risks

- Each viewer triggers the landing's aggregate queries. Without caching this is N viewers × up to 6 queries; the Spec must validate this against expected concurrency and
  confirm index usage for the period-scoped counts before any optimisation is attempted.
- The existing SAKIP dashboard caches for 15 minutes with per-user keys while the config declares 5 minutes (discovery draft §3, C4). This feature must **not** silently
  add a third caching convention: either caching is deliberately skipped in Phase 1 or the caching decision is made explicitly, with invalidation defined.
- Assessment coverage is derived through `performance_data.instansi_id` (finding F-01), not from the assessment table, so there is no second tenancy path to validate; the join is backed
  by the existing unique index on the assessment's `performance_data_id`. The verification count is index-backed by `idx_perf_data_instansi_period`. The report figure is not period-scoped
  in Phase 1 (finding F-02) and therefore needs no period-covering index. The Spec must still confirm the query plan for the counts it keeps, or exclude the affected figure.

### 7.5 Open Questions the Spec Must Resolve

1. **Assessment period anchor** — `Assessment` has no period column; the only existing precedent derives the year from `created_at` (`AssessmentController.php:91-92`). The Spec either
   confirms that precedent explicitly, stating on screen that the assessment figure is scoped by submission year, or demotes the figure to the labelled period-independent variant
   permitted by FEAT-003.
2. **Period addressing in the URL and in deep links** — required by §5.2 step 5 for shareability; the Spec chooses the parameter name and range semantics. Two constraints are already fixed:
   a period parameter may be sent to a queue only when that queue's filter can express the selected range (the verification and report filters match `period` **exactly**; the assessment
   filter reads a **year**), and US-004 scenario 1's "belong to the selected period" clause is asserted only for targets that can express the selected period — every other link degrades
   per §7.1. `[Assumed — surfaced during v1.1 remediation; confirm with the product owner before the Spec freezes it]`
3. **Single source of truth for periods** — the Spec decides the mechanism (value object, service, or consolidated trait) and how the duplicated year-filtering implementations are retired
   (discovery draft §3, C2).
4. **View-composer ownership of the sidebar badge** — confirm the mechanism and its effect on other layouts that embed the same sidebar.

**Resolved by the 2026-09-24 clarification session — do not re-open:**

- **Report period treatment (finding F-02):** `reports.period` exists but is free-form and quarter-coded, so the report figure is not period-scoped in Phase 1 and is labelled
  period-independent (US-006).
- **Report deep link (finding F-02):** the report index does filter (`?status=` / `?type=` / `?period=` / `?category=`); the landing sends `?status=submitted` only, with no period
  parameter.

### 7.6 Constraints the Spec Must Honour

- No destructive or data-losing migration; the mapping from a period to `performance_data.period` (`YYYY-MM` string) versus `targets.year` (integer) is defined **once**.
- `InstansiScope` must remain in force for every instansi-scoped query; no bypass outside an authorised path covered by a test.
- Exactly one gate governs access (FEAT-008); no additional permission is created.
- Floor-guard rules in `CONSTRAINTS.md` §3 apply: no suppressed diagnostics, no skipped tests, no weakened assertions, no lowered thresholds.
- Rendered-label assertions follow the test suite's existing idiom — HTTP feature tests driving the request through `$this->get` / `actingAs` and asserting on response content and status.
  No second assertion idiom, browser harness, or screenshot helper is introduced for this feature (finding F-07).

### 7.7 Decision Log (ratified by the product owner, 2026-09-24)

The four open decisions handed over by the discovery draft §7 were resolved by default when this PRD was drafted and were **explicitly accepted by the product owner** on 2026-09-24. The
2026-09-24 clarification session then ratified the boundaries recorded below as **D8–D10** and corrected the wording of **D2, D3, and D5**; those three rows carry the corrected meaning, and
their v1.0 phrasing is superseded.

| ID | Decision | Ratified answer |
| --- | --- | --- |
| D1 | Active reporting year | Calendar year derived from the server clock, behind a configuration seam so fiscal-year semantics can be added later without touching call sites |
| D2 | Period anchor for the other queues | Assessment uses the existing `created_at`-year precedent and is flagged for Spec confirmation; the report figure is **not** period-scoped in Phase 1 (US-006 governs its presentation). Grounds verified 2026-09-24: `reports.period` exists but is free-form and quarter-coded (`2024-Q1` in the suite), so no honest calendar-range mapping exists (finding F-02) |
| D3 | Empty period | Every period-scoped figure shows `0` with its period label; the period-independent report figure shows `0` labelled as such; the attention strip is hidden; one Indonesian sentence explains that the period is empty |
| D4 | Attention-strip composition | Three signals — one per non-empty queue |
| D5 | Cross-agency labelling | `Semua Instansi` is reserved for a viewer entitled to cross-agency data; an agency-bound administrator is labelled with their agency; an administrator with no agency assignment sees `Instansi Belum Ditetapkan` with zero counts. This supersedes the v1.0 wording ("HQ viewer without agency affiliation → `Semua Instansi`"), which contradicted the default-deny `InstansiScope` (finding F-03) |
| D6 | Caching | No caching in Phase 1; the decision is revisited against measured timings |
| D7 | Login-telemetry figure | Removed from the figure row |
| D8 | Assessment agency coverage | Derived through `performance_data.instansi_id`, keeping tenancy single-sourced (finding F-01) |
| D9 | Report deep link | `?status=submitted` only, with no period parameter, because the report index matches `period` exactly against quarter-coded values (finding F-02) |
| D10 | Metric enforcement modes | CI-enforced: query count, period/scope presence per figure, accessible name, deep-link filtering. Manual with explicit criteria: above-the-fold placement, usability timing, p95 latency (finding F-05) |

**Consequence for downstream phases:** these are *ratified product decisions*, not open assumptions. `/tdd-clarify` may interrogate their **testability**, but re-opening a
decision requires an explicit reversal by the product owner.


---

## 8. User Stories & Executable Acceptance Scenarios (BDD Mandate)

### US-001: Comparable figures for one reporting period

**As a** HQ administrator (Rina),
**I want to** see the three queue figures computed over the same reporting period, with the period stated on screen,
**So that** I can compare them and quote them in an official report without wondering what window each number covers.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** performance data existing in two different reporting periods, and the viewer is a cross-agency administrator
  - **When** the viewer opens `/admin/dashboard` without specifying a period
  - **Then** the current reporting year is selected; the Antrean Verifikasi and Antrean Asesmen figures are computed over exactly that year's range and each is displayed with its period
    label; the report figure is displayed as a labelled period-independent figure that claims no period (FEAT-003, US-006)

- **Scenario 2: Boundary / Validation Error**
  - **Given** the viewer supplies an unrecognised period value (for example a malformed or unsupported key)
  - **When** the landing is requested with that value
  - **Then** the landing falls back to the default reporting year and remains renderable — it never returns an error page or an unscoped figure

- **Scenario 3: State Transition & Persistence**
  - **Given** the viewer has changed the period to a previous quarter
  - **When** the page has finished rendering
  - **Then** the selected period is reflected in the URL, and reloading that URL reproduces the identical figures for the identical period; the period-independent report figure is unchanged
    and remains labelled as such (US-006 scenario 2)

### US-002: Explicit agency scope on every figure

**As a** delegated administrator (Bayu),
**I want to** see plainly whether the figures cover all agencies or only my own,
**So that** I do not misread cross-agency totals as my own agency's workload.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** a viewer whose account is bound to an agency
  - **When** the landing renders
  - **Then** the scope indicator names that agency, and every figure counts only records belonging to that agency

- **Scenario 2: Boundary / Validation Error**
  - **Given** a delegated administrator whose account carries no agency assignment
  - **When** the landing renders
  - **Then** the scope indicator reads `Instansi Belum Ditetapkan`, every figure shows `0`, and the label `Semua Instansi` appears nowhere on the page (finding F-03 — `InstansiScope` is
    default-deny, so this viewer is entitled to no agency-scoped data)

- **Scenario 3: State Transition & Persistence**
  - **Given** three viewers in the same system — one entitled to cross-agency data, one bound to an agency, and one with no agency assignment — with work pending in the same period
  - **When** each of them opens the landing in that period
  - **Then** the cross-agency viewer's figures equal the sum across agencies under the `Semua Instansi` label, the agency-bound viewer's figures never include another agency's records and
    are labelled with their agency, and the unassigned viewer's figures are all `0` under the `Instansi Belum Ditetapkan` label

### US-003: Delegated administrators can actually reach the landing

**As a** delegated administrator (Bayu) holding the `admin.dashboard` permission,
**I want to** open the admin landing without being refused,
**So that** I can perform the supervision my role was granted.

> Context: this is the defect documented as C1 in the discovery draft §3. It is an existing, legitimate RED — the acceptance criteria below fail today.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** a user holding the `admin.dashboard` permission who is not a Super Admin, with no agency-bound restriction on accessing the admin area
  - **When** the user requests `/admin/dashboard`
  - **Then** the landing renders successfully and exposes the same figures, period, and scope affordances as it does for a Super Admin

- **Scenario 2: Boundary / Validation Error**
  - **Given** a user who holds neither the `admin.dashboard` permission nor the Super Admin role
  - **When** the user requests `/admin/dashboard`
  - **Then** the request is refused and no figure, period, or agency name is disclosed

- **Scenario 3: State Transition & Persistence**
  - **Given** a viewer whose access is governed by exactly one gate
  - **When** the landing evaluates access
  - **Then** the route-level decision and the controller-level decision agree for every user — a viewer permitted by the route is never refused afterwards

### US-004: One click from a figure into its queue

**As a** HQ administrator (Rina),
**I want to** open the queue a figure describes already filtered to that period and status,
**So that** I act on the right records instead of re-searching an unfiltered list.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** a non-empty verification queue in the selected period
  - **When** the administrator activates the verification figure
  - **Then** the data-collection queue opens pre-filtered to the submitted status, and the records shown belong to the selected period and scope for every target whose own filters can express
    that period (§7.5 item 2); where a target cannot express it, the queue opens status-filtered only and the link still resolves (scenario 2)

- **Scenario 2: Boundary / Validation Error**
  - **Given** a queue whose index page does not accept the filter the landing would send
  - **When** the administrator activates that figure
  - **Then** the link resolves to that queue's unfiltered index without error — an unsupported filter degrades, it never produces a broken link or a 404

- **Scenario 3: State Transition & Persistence**
  - **Given** the administrator has arrived at a filtered queue from the landing
  - **When** the administrator returns to the landing
  - **Then** the previously selected period is preserved, and the figure count matches the number of records the queue displays

### US-005: The queue badge follows the administrator everywhere

**As a** HQ administrator (Rina),
**I want to** see the pending-verification badge on any page that shows the sidebar,
**So that** the signal does not disappear the moment I navigate away from the landing.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** a non-empty verification queue
  - **When** the administrator opens a page other than the landing that renders the same sidebar
  - **Then** the badge is present and shows the same count as the landing's verification figure for the same scope

- **Scenario 2: Boundary / Validation Error**
  - **Given** an empty verification queue
  - **When** any page with the sidebar renders
  - **Then** no badge is shown, and no zero-value badge is rendered

- **Scenario 3: State Transition & Persistence**
  - **Given** the badge's data source is the layout rather than a single controller
  - **When** two different pages render the same sidebar in one session
  - **Then** both show the same count, and neither loses the badge by virtue of a different controller having rendered the page

### US-006: No figure claims a period basis it does not have

**As a** HQ administrator (Rina),
**I want to** be told plainly when a figure cannot be scoped to a period,
**So that** I never quote a number believing it covers a window it does not cover.

> Context: the report figure has no verified period anchor — `reports.period` is free-form and quarter-coded (finding F-02) — so it must not claim one. The assessment anchor rests on the
> `created_at`-year precedent and awaits Spec confirmation (FEAT-003, §7.5 item 1); this story's honesty rule applies to it as well if the Spec demotes it.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** the report figure cannot be period-scoped with a verified column
  - **When** the landing renders
  - **Then** that figure is either absent or explicitly labelled as not being limited to the selected period, and no period label is shown beside it

- **Scenario 2: Boundary / Validation Error**
  - **Given** a reviewer checks every rendered figure against the period it claims
  - **When** the period changes
  - **Then** each period-scoped figure changes accordingly, and any figure that does not change is visibly labelled as period-independent

- **Scenario 3: State Transition & Persistence**
  - **Given** the period anchor for a queue is later verified and scoped
  - **When** that figure becomes period-scoped
  - **Then** its period label appears in the same release that introduces the scoping — a figure is never left labelled as unscoped after it becomes scoped

### US-007: An empty period is explained, not blank

**As a** delegated administrator (Bayu),
**I want to** understand that a zero figure means no pending work in that period,
**So that** I do not mistake an empty queue for a broken page.

#### Acceptance Criteria (Given-When-Then)

- **Scenario 1: Happy Path (Standard Execution)**
  - **Given** no pending records in the selected period across all queues
  - **When** the landing renders
  - **Then** each period-scoped figure shows `0` with its period label, the report figure shows `0` as a labelled period-independent figure, the attention strip is not rendered, and a short
    Indonesian sentence states that the period has no pending work

- **Scenario 2: Boundary / Validation Error**
  - **Given** a period with pending work in exactly one queue
  - **When** the landing renders
  - **Then** the attention strip shows exactly one signal, and the other two queues appear only as zero-valued figures

- **Scenario 3: State Transition & Persistence**
  - **Given** a period that is empty and an adjacent period that is not
  - **When** the viewer switches between them
  - **Then** the strip appears and disappears accordingly, and each figure's value matches its own period without carry-over

---

## 9. Milestones & Suggested Phasing

- **Phase 1 (MVP Vertical Slice):** the period dimension, the explicit agency scope with its three viewer states, the three queue figures with their deep links, the three-signal attention strip
  with its honest empty-period copy, and the single-gate authorization fix. This slice is independently demonstrable: an administrator sees scoped figures for a chosen period, understands
  what is waiting, and reaches the corresponding filtered queues.
  Covers FEAT-001, FEAT-002, FEAT-003, FEAT-004, FEAT-005, FEAT-008, and US-001 through US-004 plus US-006 and US-007.
- **Phase 2 (Edge Cases & Polish):** removal of the telemetry figure, the layout-owned sidebar badge, and the contingency that the Spec demotes the assessment figure to the labelled
  period-independent variant permitted by FEAT-003. Covers FEAT-006, FEAT-007, and US-005.
- **Phase 3 (Deferred — needs its own cycle):** period-scoped report figures once a verified anchor exists, a caching decision with defined invalidation, and any trend visualisation.
  These are explicitly outside this PRD's acceptance scope.
