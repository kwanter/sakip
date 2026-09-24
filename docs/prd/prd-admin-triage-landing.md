# Product Requirements Document: Admin Triage Landing (Landing Triage)

**Status:** Draft
**Version:** 1.0
**Date:** 2026-09-24
**Author:** TDD Product Manager
**Target Technical Spec:** `/spec/spec-admin-triage-landing.md`
**Target Quality Gate:** `/tdd-clarify`

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

- Make HQ supervision defensible: every figure on the landing carries a stated reporting period and a stated agency scope, so it can be quoted in an official report.
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

These terms are the canonical vocabulary for this feature. They are **proposed** in discovery draft §6 and are not yet ratified into `CONTEXT.md`; for this feature they
carry exactly the following meanings.

- **Periode Pelaporan**: the annual, quarterly, or monthly window that scopes every triage figure.
  (`_Avoid_: "periode", "periode data", "bulan berjalan", "date range"`)
- **Cakupan Instansi**: the explicit statement of which `Instansi` a figure covers — all agencies (HQ) or exactly one.
  (`_Avoid_: "tenant", "tenant filter", "wilayah"`)
- **Antrean Verifikasi**: records awaiting a decision at a workflow step; canonical instance is `PerformanceData` with status `submitted`.
  (`_Avoid_: "inbox", "pending list", "tugas saya"`)
- **Antrean Asesmen**: `Assessment` records with status `pending`.
  (`_Avoid_: "antrean lain", "menunggu"`)
- **Antrean Laporan**: `Report` records with status `submitted`.
  (`_Avoid_: "antrean lain", "menunggu"`)
- **Landing Triage**: the HQ landing whose purpose is to direct attention before administration.
  (`_Avoid_: "dashboard admin", "beranda admin", "overview"` — "dashboard admin" collides with the agency-side SAKIP dashboards)

**Ratification note for `/tdd-clarify`:** the six terms above are the PRD's first authoritative use. `CONTEXT.md` has deliberately **not** been created yet
(lazy creation); creating it requires explicit user approval, and its absence must not be treated as an ambiguity in this PRD.

---

## 3. User Personas & Roles

### 3.1 Personas

- **Rina — HQ Administrator** (`Super Admin`): owns the platform, affiliated with no single agency, accountable for cross-agency data health. Arrives after login
  wanting to know what is stuck and with whom. Moderate technical literacy; lives in the admin panel daily and will quote these numbers in briefings.
- **Bayu — Delegated Administrator**: agency staff granted the `admin.dashboard` permission. Supervises and verifies his own agency's submissions. Low tolerance for
  ambiguity about whose data he is looking at, because his agency is the only one he may act on.
- **Dea — Agency Officer** (`Assessor`, `Auditor`, `Data Collector`): explicitly **not** a user of this landing. Her surfaces are the SAKIP-side dashboards. Listed to
  bound scope: this feature must not change what she sees.

### 3.2 Role-Based Access Control

- **Entry rule:** access is governed by exactly one gate — `admin.dashboard` (`AppServiceProvider.php:93-94`), which Super Admins satisfy through `Gate::before`
  (`:88-90`) and delegated administrators satisfy through the seeded permission (`RolesAndPermissionsSeeder.php`). The second, phantom gate currently applied by the
  controller is a defect (discovery draft §3, C1) and must be removed, not preserved as an alternative policy.
- **Scope rule:** a viewer carrying an `instansi_id` sees **only** their own agency's figures (existing `InstansiScope` behaviour is preserved, never bypassed); a viewer
  without one sees **all** agencies and is labelled `Semua Instansi`.
- **Explicitly unchanged:** no new permission is introduced, and no user gains access they were not intended to have. Restoring delegated administrators' access is a
  defect fix, not a policy widening — the PRD forbids widening it further while fixing C1.

---

## 4. Functional Requirements & Feature Scope

- **FEAT-001 (Reporting-period dimension)** (Priority: High)
  - The landing exposes the current *Periode Pelaporan* and lets the viewer change it among year (default), quarter, and month.
  - The selected period is visible on the page at all times, in Indonesian, next to the figures it scopes.
  - Period resolution is one shared implementation; the feature must not introduce a second interpretation of "current period" anywhere.

- **FEAT-002 (Explicit instansi scope)** (Priority: High)
  - The landing always displays a *Cakupan Instansi* indicator: `Semua Instansi` for HQ viewers, and the agency name for agency-bound viewers.
  - The indicator is never hidden, even when only one agency exists in the system, because its absence is what makes today's figures ambiguous.

- **FEAT-003 (Three queue counts on one period)** (Priority: High)
  - Three figures are shown, each computed on the *same* selected period range: **Antrean Verifikasi** (`PerformanceData` status `submitted`), **Antrean Asesmen**
    (`Assessment` pending), and — subject to the constraint below — **Antrean Laporan** (`Report` status `submitted`).
  - **Constraint carried from discovery (draft §7, assumption 2, now evidenced):** only `PerformanceData` has a real period column (`performance_data.period`, `YYYY-MM`).
    The only existing period-based assessment filter in the codebase derives the year from `created_at` (`AssessmentController.php:91-92`), and no equivalent was found
    for reports. Therefore: verification and assessment figures are period-scoped in Phase 1; the **report figure is not period-scoped in Phase 1** and must either be
    presented as an explicitly labelled non-period figure or deferred — `/tdd-clarify` and `/tdd-spec` must resolve which, and the PRD forbids presenting a fabricated
    period basis for it.
  - No figure is presented without its period and scope context.

- **FEAT-004 (Deep links into the queues)** (Priority: High)
  - Each figure links to the queue it describes, pre-filtered to the same period and scope where the target already supports it.
  - **Verified capability:** `?validation_status=` on the data-collection index (`Sakip/DataCollectionController.php:168-171`) and `?status=` / `?period=` on the
    assessment index (`AssessmentController.php:77-92`). Report-index filtering was **not** evidenced, so the report figure's link target must be confirmed by
    `/tdd-spec` rather than assumed.

- **FEAT-005 (Attention strip carries all three signals)** (Priority: Medium)
  - The conditional attention strip surfaces a signal per non-empty queue (three distinct signals), preserving today's behaviour of appearing only when work exists.
  - Each signal exposes a single named action in Indonesian; the strip is not a dashboard-in-dashboard.

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
- **First-load default:** *Periode Pelaporan* = the current reporting year; *Cakupan Instansi* = `Semua Instansi` for HQ viewers, or the viewer's agency name.

The self-explanatory requirement is deliberate: the current page is documented as having "no definition anywhere for the ambiguous metrics a Super Admin is expected to
act on" (impeccable critique #10), and that is precisely what this landing must stop doing.

### 5.2 Core Experience & Happy Path

1. The administrator lands on `/admin/dashboard`; period defaults to the current reporting year and the scope chip states the scope in plain Indonesian.
2. Three figures render — Antrean Verifikasi, Antrean Asesmen, and the report figure (see FEAT-003 for its period constraint) — each carrying the same period and scope.
3. The attention strip appears above the figures only when at least one queue is non-empty, showing one signal per non-empty queue with a single named action.
4. The administrator activates a figure or a strip action; the corresponding queue opens pre-filtered to the same period and status.
5. The administrator changes the period using the selector; all figures recompute on the identical range, and the selected period is reflected in the URL so the view can
   be bookmarked and shared with a colleague.

Step 5 is a product requirement, not an implementation detail: a shareable, period-addressed view is what makes these numbers usable in supervision meetings.

### 5.3 Edge Cases & UI/UX Highlights

- **Empty period:** all three figures show `0` with the period label; the attention strip is hidden; a short Indonesian sentence states that the period has no pending
  work. The page must never present an empty region without explanation.
- **Mixed emptiness:** only non-empty queues appear in the attention strip. A zero queue is legitimate information in the figure row, but is not an "attention" signal.
- **Unauthorized viewer:** a user without `admin.dashboard` who is not a Super Admin receives a 403 and must not see a partially rendered landing.
- **Agency-bound viewer:** sees only their own agency's figures; the chip names the agency. Cross-agency leakage is a defect, never a design option.
- **HQ viewer with no agency affiliation:** sees all agencies under the `Semua Instansi` label — the label matters most for this persona, because their data is the union
  of every agency.
- **Long agency names:** the chip must degrade gracefully rather than reflow the header.
- **Language and codes:** every label is Indonesian; raw machine codes must not leak into the interface (impeccable critique #2).
- **Accessibility:** each figure is a link whose accessible name includes its period; the scope indicator is text, never colour alone; contrast stays at WCAG AA.

---

## 6. Success Metrics & Performance Budgets

**User-Centric Metrics**

- First meaningful figure is visible without scrolling at a 1280×800 viewport.
- **Zero** figures render without both a period and a scope context (target: 0 of 3 ambiguous — today's baseline is 3 of 3).
- The triage question is answerable without navigation: 0 required clicks to *understand* the state, 1 click to *act* on it.
- In a five-participant usability check, the largest queue is identified within 10 seconds of first view.

**Business Metrics**

- Every figure on the landing is quotable in an official report because its period and scope are stated on screen.
- HQ administrators reach a corrective action from login without visiting an unfiltered list first.

**Technical Metrics & Constraints** (aligned with `CONSTRAINTS.md` — no new gate is introduced by this feature)

- Server-side query count for the landing ≤ 6 (three queue counts, scope resolution, period metadata, recent activity); no N+1 patterns.
- Page render p95 < 500 ms against the seeded dataset; the period-scoped queries must use existing indexes (`idx_perf_data_instansi_period`, `idx_targets_year`).
- The existing quality gates remain GREEN: `php artisan test`, `./vendor/bin/pint --test --no-interaction`, Unit suite under its runtime floor.
- Accessibility: WCAG AA contrast retained for body text and figure labels.

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
| Queue filtering | `?validation_status=` (`DataCollectionController.php:168-171`), `?status=`/`?period=` (`AssessmentController.php:77-92`) | Reuse these parameters for deep links |
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
- Report/assessment tables have no `instansi_id`-indexed period access path verified for this use; the Spec must confirm the query plan or exclude the affected figure.

### 7.5 Open Questions the Spec Must Resolve

1. **Report period anchor** — unverified. Confirm the semantically correct column, or implement the "labelled non-period figure" variant permitted by FEAT-003.
2. **Period addressing in the URL** — required by §5.2 step 5 for shareability; the Spec chooses the parameter name and range semantics.
3. **Single source of truth for periods** — the Spec decides the mechanism (value object, service, or consolidated trait) and how the duplicated year-filtering
   implementations are retired (discovery draft §3, C2).
4. **Deep-link parameter for the report queue** — confirm whether the report index supports filtering; if not, the Spec proposes the smallest change that makes the link
   honest, or the link is dropped from Phase 1.
5. **View-composer ownership of the sidebar badge** — confirm the mechanism and its effect on other layouts that embed the same sidebar.

### 7.6 Constraints the Spec Must Honour

- No destructive or data-losing migration; the mapping from a period to `performance_data.period` (`YYYY-MM` string) versus `targets.year` (integer) is defined **once**.
- `InstansiScope` must remain in force for every instansi-scoped query; no bypass outside an authorised path covered by a test.
- Exactly one gate governs access (FEAT-008); no additional permission is created.
- Floor-guard rules in `CONSTRAINTS.md` §3 apply: no suppressed diagnostics, no skipped tests, no weakened assertions, no lowered thresholds.

### 7.7 Decision Log (ratified by the product owner, 2026-09-24)

The four open decisions handed over by the discovery draft §7 were resolved by default when this PRD was drafted, and were then **explicitly accepted by the product
owner** on 2026-09-24.

| ID | Decision | Ratified answer |
| --- | --- | --- |
| D1 | Active reporting year | Calendar year derived from the server clock, behind a configuration seam so fiscal-year semantics can be added later without touching call sites |
| D2 | Period anchor for the other queues | Assessment uses the existing `created_at`-year precedent and is flagged for Spec confirmation; the report figure is **not** period-scoped in Phase 1 (US-006 governs its presentation) |
| D3 | Empty period | Every figure shows `0` with its period label, the attention strip is hidden, and one Indonesian sentence explains that the period is empty |
| D4 | Attention-strip composition | Three signals — one per non-empty queue |
| D5 | HQ viewer without agency affiliation | Labelled `Semua Instansi` |
| D6 | Caching | No caching in Phase 1; the decision is revisited against measured timings |
| D7 | Login-telemetry figure | Removed from the figure row |

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
  - **Given** performance data existing in two different reporting periods, and the viewer is an HQ administrator
  - **When** the viewer opens `/admin/dashboard` without specifying a period
  - **Then** the current reporting year is selected, and all three figures are computed over exactly that year's range, each displayed together with its period label

- **Scenario 2: Boundary / Validation Error**
  - **Given** the viewer supplies an unrecognised period value (for example a malformed or unsupported key)
  - **When** the landing is requested with that value
  - **Then** the landing falls back to the default reporting year and remains renderable — it never returns an error page or an unscoped figure

- **Scenario 3: State Transition & Persistence**
  - **Given** the viewer has changed the period to a previous quarter
  - **When** the page has finished rendering
  - **Then** the selected period is reflected in the URL, and reloading that URL reproduces the identical figures for the identical period

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
  - **Given** a viewer with no agency affiliation (an HQ account)
  - **When** the landing renders
  - **Then** the scope indicator reads `Semua Instansi`, and the figures count records across every agency

- **Scenario 3: State Transition & Persistence**
  - **Given** agency-bound and HQ viewers in the same system
  - **When** both open the landing in the same period
  - **Then** the agency-bound viewer's figures never include another agency's records, while the HQ viewer's figures equal the sum across agencies

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
  - **Then** the data-collection queue opens pre-filtered to the submitted status, and the records shown belong to the selected period and scope

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

> Context: only `performance_data.period` is a verified period column; report/assessment period scoping is unresolved (FEAT-003, §7.5 item 1).

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
  - **Then** each figure shows `0` with its period label, the attention strip is not rendered, and a short Indonesian sentence states that the period has no pending work

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

- **Phase 1 (MVP Vertical Slice):** the period dimension, the explicit agency scope, the two verifiable queue counts with their deep links, and the single-gate authorization fix.
  This slice is independently demonstrable: an administrator sees comparable, scoped figures for a chosen period and reaches the corresponding filtered queues.
  Covers FEAT-001, FEAT-002, FEAT-003 (verification and assessment), FEAT-004 (supported targets), FEAT-008, and US-001 through US-004 plus US-006.
- **Phase 2 (Edge Cases & Polish):** the three-signal attention strip, removal of the telemetry figure, the layout-owned badge, honest empty-period copy, and the report figure's
  resolution under US-006. Covers FEAT-005, FEAT-006, FEAT-007, the remaining FEAT-004 target, and US-005 with US-007.
- **Phase 3 (Deferred — needs its own cycle):** period-scoped report figures once an anchor is verified, a caching decision with defined invalidation, and any trend visualisation.
  These are explicitly outside this PRD's acceptance scope.
