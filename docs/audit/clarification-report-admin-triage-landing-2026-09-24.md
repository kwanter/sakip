# 🔍 TDD Clarification Report [Review Iteration 1]

<!-- markdownlint-disable -->

**Target Document:** `docs/prd/prd-admin-triage-landing.md`  
**Readiness Score:** 82/100  
**Status:** Good Enough (>= 80)  
**Audit Date:** 2026-09-24  
**Interrogator:** TDD Clarification Analyst

**Score Breakdown (Quality Gate Rubric):**

- **Completeness (max 40):** 35 — All eight functional requirements, seven user stories with three BDD scenarios each, four declared target seams, and the §7 integration, reuse,
  privacy, and scalability sections are present. Deduction: three §7.5 items (URL parameter naming, single-source-of-truth mechanism, view-composer mechanism) are delegated to
  the Spec without a default, and the report queue's deep-link path had no pre-agreed boundary before this session.
- **Clarity & Testability (max 30):** 26 — After F-05 every §6 metric carries an explicit enforcement mode, and figures, period, and scope are all concrete. Residual deduction:
  the p95 < 500 ms budget has no harness in this repository and therefore stays a manual measurement.
- **Alignment & Constraints (max 30):** 21 — The resolved requirements now agree with the codebase (`InstansiScope` default-deny semantics, `reports.period` as a free-form
  `string(20)`, the `ReportController@index` filter set, the existing model scopes and indexes). Deduction: the document still physically contains three stale statements
  (FEAT-003, FEAT-004) plus one stale phase allocation (§9), and §2 still claims its vocabulary is unratified although `CONTEXT.md` now exists.
- **Critical Flaw Veto:** None — the two fundamental contradictions found (F-01, F-03) were resolved by explicit product decisions inside this session.

---

## 1. 🚨 Critical Findings (Untestable Blockers)

None. No untestable blocker remains after this iteration: every acceptance criterion in §8 now maps onto one of the declared seams. The mandatory *text* corrections that follow
from this session's resolutions are listed in §4 so the authoring agent can apply them without re-interrogating the document.

---

## 2. 🧩 Resolved Items & Pre-Agreed Boundaries

### F-03 — Scope semantics for a non-Super-Admin viewer (contradicted immutable code semantics)

- **Original ambiguity:** §3.2 — "a viewer without one sees **all** agencies and is labelled `Semua Instansi`".
- **Evidence:** `app/Models/Scopes/InstansiScope.php:11-18` documents default-deny semantics ("null instansi sees nothing") and marks them immutable. The promised behaviour is
  impossible for a delegated administrator without an agency assignment: that viewer would have seen three zeros under a `Semua Instansi` label, i.e. a screen that misinforms.
- **Resolution & boundary:** `Semua Instansi` is reserved for a Super Admin. An agency-bound delegated administrator sees exactly their own agency. A delegated administrator
  without an agency assignment sees the explicit `Instansi Belum Ditetapkan` state with zero counts — never the `Semua Instansi` label.
- **Target seam:** HTTP `GET /admin/dashboard` (label and count assertions for both viewer types), plus the triage read model for the count boundary.

### F-01 — Agency coverage for the assessment queue

- **Original ambiguity:** FEAT-003 requires all figures to share one period and §3.2 requires agency coverage, but `app/Models/Assessment.php` declares no `instansi_id` and no
  global scope — `create_assessments_table.php:20,24,43` creates only `performance_data_id`, `assessed_by`, and a unique index on `performance_data_id`.
- **Resolution & boundary:** assessment coverage is derived through `performance_data.instansi_id`, keeping tenancy single-sourced; the join is index-backed by the existing
  unique constraint on `performance_data_id`.
- **Target seam:** triage read-model unit test — an agency-bound viewer sees only assessments whose performance data belongs to their agency.

### F-02 — Report figure period treatment

- **Original claim:** FEAT-003 — "no equivalent was found for reports"; FEAT-004 — "Report-index filtering was **not** evidenced".
- **Evidence falsifying both:** `reports.period` exists as `string(20)` with a dedicated index (`create_reports_table.php:28,46`), and `ReportController@index` already accepts
  `?status=`, `?type=`, `?period=` (exact match), and `?category=`. Decisively, the existing suite seeds `'period' => '2024-Q1'` (`tests/Feature/ReportIndexRendersTest.php:34`),
  so real report periods are **quarter-coded strings**, not the `YYYY-MM` used by `performance_data.period`.
- **Resolution & boundary:** ratified decision D2 stands, now on correct grounds — the report figure is **not** period-scoped in Phase 1 and is labelled period-independent under
  US-006. Report period values are free-form and their distribution cannot be audited without database access, so any range mapping would be unverifiable.
- **Target seam:** HTTP render assertion that the report figure carries no period label (US-006 scenario 2).

### F-04 — Attention-strip composition versus phase allocation

- **Original contradiction:** §5.2 step 3 (a Phase-1 happy path) describes a three-signal strip, while §9 allocates FEAT-005 to Phase 2.
- **Resolution & boundary:** FEAT-005 moves into Phase 1, and US-007 moves with it because the strip's behaviour during an empty period is part of its contract. §9 must be
  rewritten; §5.2 then needs no change, since the document already describes the intended end state.
- **Target seam:** HTTP render assertions for strip presence per non-empty queue, and for its absence during an empty period.

### F-05 — Enforceability of the §6 metrics

- **Original ambiguity:** "visible without scrolling at a 1280×800 viewport", "five-participant usability check … within 10 seconds", and "the chip must degrade gracefully".
- **Resolution & boundary:** §6 states an enforcement mode per metric. CI-enforced: query count, presence of period and scope context on every figure, accessible name includes
  the period, deep links arrive filtered. Manual verification with explicit criteria: above-the-fold figure placement and usability timing. "Degrade gracefully" is replaced by a
  measurable threshold — the chip truncates with an ellipsis beyond a stated length and the header does not wrap at 1280 px.
- **Target seam:** HTTP tests cover the CI-enforced set; the manual set is recorded as manual and therefore creates no floor-guard obligation. This repository installs no
  browser harness, so no screenshot or layout automation is introduced.

### F-07 — Content-assertion seam (house pattern)

- **Original gap:** `tests/` contains zero `assertSee` occurrences; the suite asserts with `assertOk()` / `assertStatus()` and string assertions, driving HTTP through
  `$this->get`, `$this->post`, and `actingAs` across ten files.
- **Resolution & boundary:** rendered-label assertions assert against response content using the vocabulary the suite already uses. The Spec pre-agrees this seam so that US-001
  and US-002 become testable without introducing a second assertion idiom.
- **Target seam:** HTTP feature test — the response content contains the period label and the scope label.

---

## 3. ⚠️ Assumed / Auto-Resolved / Out of Scope (The 20% Tail)

- **Report deep-link parameter set:** `[Assumed / Auto-Resolved]` — the deep link carries `?status=submitted` only. No period parameter is sent, because
  `ReportController@index` matches `period` by exact value and report periods are quarter-coded (`2024-Q1`) while the landing's period is a calendar range: a range cannot be
  expressed, and a fabricated value is forbidden by US-006.
- **Period parameter name in the URL:** `[Assumed / Out of Scope]` — §7.5 item 2 remains the Spec's decision; fixing a name here would duplicate Spec authority.
- **Single source of truth for periods:** `[Assumed / Out of Scope]` — §7.5 item 3 stays with the Spec. This session fixed only the *semantics*: a calendar reporting year behind a
  configuration seam, with quarter and month selectable.
- **View-composer mechanism for the sidebar badge:** `[Assumed / Out of Scope]` — §7.5 item 5 stays with the Spec.
- **p95 < 500 ms:** `[Assumed / Auto-Resolved]` — measured manually during review; no performance harness is introduced in Phase 1 and the budget is not treated as a CI gate.
- **`CONTEXT.md` versionability:** `[Assumed / Out of Scope]` — the file is currently captured by `.gitignore:27` (`*.md`). The remaining one-line patch is `!/CONTEXT.md`, which
  belongs to a governance initialisation action rather than to this clarification session.

---

## 4. 📝 Next Steps

**Mandatory text corrections for the authoring agent (`/tdd-prd`), required before `/tdd-spec`:**

1. **FEAT-003** — replace the falsified evidence sentence ("no equivalent was found for reports") with the verified fact: `reports.period` exists but is free-form and
   quarter-coded, therefore the report figure is deliberately not period-scoped in Phase 1.
2. **FEAT-004** — replace "Report-index filtering was **not** evidenced" with the verified filter set, and state the resolved deep-link contract: status only, no period parameter.
3. **§2 and §3.2** — record the `Cakupan Instansi` semantics resolved in F-03 (three distinct viewer states) and update the ratification note to point at the now-existing
   `CONTEXT.md`, which already carries the seven canonical terms.
4. **§9 and §6** — move FEAT-005 and US-007 into Phase 1, and add the per-metric enforcement mode decided in F-05.

**Then:** proceed to `/tdd-spec` with the corrected PRD, `@CONTEXT.md`, and the four target seams from §5 of the discovery draft. No ADR is required — none of the resolved
decisions meets the Triple Gate (each is reversible, would not surprise a new engineer, and was an ordinary product call). `CONTEXT.md` now carries seven canonical terms, so no
glossary work is pending for this feature.

---

> **User Decision Prompt:** The document has achieved a Readiness Score of 82/100. It is testable and viable. Do you want to **PROCEED** to the next phase, or do you want to
> **REFINE** and clarify further?
