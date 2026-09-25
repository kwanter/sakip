# 🔍 TDD Clarification Report [Review Iteration 2]

<!-- markdownlint-disable -->

**Target Document:** `spec/spec-admin-triage-landing.md` (v1.1, status Draft — remediation of Iteration 1)  
**Readiness Score:** 89/100  
**Status:** Good Enough (>= 80)  
**Audit Date:** 2026-09-24  
**Interrogator:** TDD Clarification Analyst

**Score Breakdown (Quality Gate Rubric):**

- **Completeness (max 40):** 37 — All ten mandatory corrections of Iteration 1 landed, and they landed *substantively*: a canonical fixture matrix (§5.0), three new acceptance criteria (AC-036…AC-038),
  three new recorded decisions (D-S7…D-S9), two new assumptions (A8, A9), and two new normative clauses in §4.1/REQ-015. Deduction: the precedence rule added to `ReportingPeriod::anchor()` is not asserted
  by any criterion and is absent from the S1 case list (D-1); AC-004 cannot exercise the new `(int)` cast (D-2); F-8's trashed-parent fixture is referenced but never defined (§5.0, D-4).
- **Clarity & Testability (max 30):** 25 — AC-033 is now deterministic and region-scoped, AC-009/AC-011 are anchored to one matrix, and the negative-assertion idiom is named. Deduction: AC-037 reads as a
  general figure↔target agreement rule while D-S7 accepts the opposite for the assessment queue (D-5); the §6.3 needle list still says `instansi` while CON-003/§4.4 now say `instansis` (D-3); the
  §1.2 bullet list mixes tight and loose formatting (D-7); A4's cross-reference points at §7.1 instead of §4.3 (D-6).
- **Alignment & Constraints (max 30):** 27 — `instansis` and the `AuditLog` (no `SoftDeletes`) corrections are applied, the layout-shell carve-out is recorded where the implementer will read it, and
  `CONTEXT.md` vocabulary is honoured throughout the new text. Deduction: REQ-007 still says the assessment figure counts "every pending assessment, restricted only by *Cakupan Instansi*", which no longer
  matches the canonical-population decision it now depends on (D-8).
- **Critical Flaw Veto:** **Not triggered.** No criterion is unsatisfiable, and no two normative statements contradict *within the same scope* — AC-037's Given scopes it to §5.0, so the D-S7 tension is
  latent rather than actual. The eight items in §3 (plus one process note) are precision gaps that must close before the Plan is executed, but none of them blocks the phase. This is the decisive difference from Iteration 1, whose
  C-1 could never have gone green.

---

## 1. 🚨 Critical Findings (Untestable Blockers)

**None.** Every acceptance criterion in v1.1 is satisfiable as written, and the four seams each have at least one criterion that can fail for the right reason:

- AC-033 now fails only when the triage block really still renders a forbidden string (the sidebar carve-out makes it satisfiable).
- AC-009/AC-011/AC-036 fail deterministically against the §5.0 matrix (the factory defaults that made them luck-dependent are pinned).
- AC-037/AC-038 fail when the figure→target relationship regresses, and they are scoped by their Givens.
- AC-004 still fails when the year seam or its cast regresses — but see D-2: it cannot fail *for the cast itself*.

The eight remaining gaps are recorded in §3 with the correction each needs, and the three that touch a *new* normative clause (D-1, D-2, D-5) are repeated in §4 as the priority refinements.

## 2. 🧩 Resolved Items & Pre-Agreed Boundaries

Every Iteration-1 correction was re-verified against the v1.1 text in this session. No item was found unapplied.

| # | Iteration-1 correction | Status | Evidence now in `spec/spec-admin-triage-landing.md` |
| --- | --- | --- | --- |
| 1 | **C-1** rescope AC-033 | **RESOLVED** | AC-033 is now a three-part criterion (`substr_count === 3`; the `data-triage-region` `<section>` extracts non-empty; the two forbidden strings absent **from `$m[1]`**). §4.4 pins `data-triage-region` as the *only* negative-assertion boundary, §1.2 A2 records the sidebar carve-out, and §8's "Never do" list forbids asserting `Indikator Kinerja` or renaming the link |
| 2 | **C-2** resolve the S2/S3 contradiction | **RESOLVED** | §6.1's S2 row explicitly removes the ambient-auth case with the reason (`InstansiScope` is a no-op without auth) and moves it to S3; §6.2 adds a dedicated **S3 owns the ambient-auth invariant (C-2)** bullet stating both halves are observable |
| 3 | **C-3** rewrite the S2 Given as a matrix | **RESOLVED** | New §5.0 with F-1…F-11 (each row names the exact factory state), a frozen clock (`2026-09-24`), and the expected 3×3 matrix. AC-009 and AC-011 both resolve their Given to §5.0, and AC-011 now asserts all three counts for all three viewer states plus the sum invariant |
| 4 | **C-4 / C-5 / C-6** add the missing ACs | **RESOLVED (2 follow-ups → D-4, D-5)** | §4.3 gains explicit *Target-frame* and *Target-coverage* disclosures; §1.2 gains A8/A9; §9.1 gains D-S7/D-S8/D-S9; AC-036 (canonical population), AC-037 (figure↔target agreement) and AC-038 (accepted cross-agency divergence) exist and are wired into §6.1 S2/S3 and §6.4 |
| 5 | **C-7** define `anchor()` precedence | **PARTIAL → D-1** | §4.1's docblock now states the rule verbatim ("when `$now` is provided, it is authoritative and the config seam is IGNORED"). But no acceptance criterion asserts it and the S1 case list does not name it |
| 6 | **C-8** config seam wording and type | **PARTIAL → D-2** | REQ-015 now says **key inside the existing `reporting` block** (`:103-116`), `env('SAKIP_ACTIVE_YEAR', null)`, and the `(int)` cast at its single read site; §7.2 matches. AC-004 was not updated, so the cast itself remains unexercised |
| 7 | **C-9** correct §4.6 | **RESOLVED (1 follow-up → D-3)** | §4.6's table row is now `instansis` with `deleted_at` and the migration reference; the `SoftDeletes` paragraph now excludes `AuditLog` explicitly; CON-003 and §4.4 both list the needle as `instansis` |
| 8 | §6.4 replaced-assertion claim | **RESOLVED** | §6.4 now records the `grep` result ("no hits"), states AC-015 is a brand-new RED, and keeps `SakipDashboardAccessTest` in the must-stay-GREEN list with the reason its status-only assertions are unaffected |
| 9 | CON-007 / §6.3 negative idiom | **RESOLVED** | CON-007 names `assertStringNotContainsString(...)` as the paired idiom, and §6.3 carries a worked snippet including the `assertNotSame('', $m[1] ?? '')` guard |
| 10 | Name the S4 viewer | **RESOLVED** | AC-023, AC-024 and AC-026 all name the `Super Admin` viewer and the `Gate::before` reason; §6.1's S4 row points at §5.5 |

**Iteration-1 §2 boundaries re-confirmed (no drift):** the `403` RED is still legitimate (controller `middleware()` exists and `access-admin-dashboard` occurs once), the single `admin.dashboard` gate is
unchanged, the three queue scopes and route names are unchanged, `whereHas('performanceData')` is still implementable, and the factory states the Spec demands all exist. Nothing in the v1.1 remediation
invalidated them.

## 3. ⚠️ Assumed / Auto-Resolved / Out of Scope (The 20% Tail)

None of the items below blocks the phase; each is a precision gap introduced or exposed by the v1.1 remediation. Items **D-1, D-2 and D-5** touch a *new* normative clause and are therefore also listed in §4.

- **D-1 — the `anchor()` precedence rule has no criterion.** The rule is stated in §4.1 ("when `$now` is provided, it is authoritative and the config seam is IGNORED") and listed in the assumptions, but
  AC-004 only covers "config set, no `$now`" and "config `null`, no `$now`", and §6.1's S1 row ("AC-001, AC-002, AC-003, AC-004, AC-022, plus …") names no precedence case. An implementation with the
  *opposite* precedence would pass every criterion in the document.
  - **Handling:** `[Assumed / Out of Scope]` — add one S1 criterion ("Given `config('sakip.reporting.active_year')` is set **and** an explicit `$now` is supplied, Then the injected clock wins and the
    configured year is ignored") and name it in the S1 row. This is the one gap that can hide a wrong implementation.
- **D-2 — the `(int)` cast of the configuration value is unexercised.** REQ-015 now mandates the cast because `env()` yields a string, but AC-004 still says
  `config('sakip.reporting.active_year') === 2025` — an integer literal. Setting the config to an integer makes the cast a no-op, so the clause cannot fail.
  - **Handling:** `[Assumed / Out of Scope]` — state AC-004's config write form (`'2025'`, the string the seam actually produces) and assert the type as well as the value (`assertIsInt` /
    `=== 2025`) so the cast is falsifiable.
- **D-3 — the query-count needle list contradicts itself.** CON-003 and §4.4 now name `instansis`, but §6.3's pre-agreed idiom still reads
  `$domainTables = ['performance_data', 'assessments', 'reports', 'instansi', 'audit_logs'];`. The assertion still passes (substring match), which is exactly why the drift would go unnoticed; it also
  over-matches any future table whose name contains `instansi`.
  - **Handling:** `[Assumed / Auto-Resolved]` — align the §6.3 snippet to the normative list (`instansis`) so the document speaks one vocabulary.
- **D-4 — F-8's trashed parent is referenced but never created.** §5.0 row F-8 uses `forPerformanceData($trashedData->id)` "after `$trashedData->delete()`", yet no row defines `$trashedData`, its agency,
  its period, or its status; and the matrix never states the consequence that follows from A8/D-S9: a soft-deleted `PerformanceData` must be absent from **all three** queues, so A's expectations stay
  `2 / 3 / 1` and the cross-agency expectations stay `5 / 4 / 3`.
  - **Handling:** `[Assumed / Out of Scope]` — add the parent row (e.g. `PerformanceDataFactory::submitted()->forInstansi($a)->forPeriod('2026-04')`, deleted before assertions) and state the
    all-three-queues exclusion explicitly, because that exclusion is what makes AC-036's numbers meaningful.
- **D-5 — AC-037 reads as a general agreement rule while D-S7 accepts the opposite.** AC-037 requires the assessment queue page to contain the rows the assessment figure counted; D-S7 records that the
  figure is deliberately *not* limited to the target's calendar-year frame. The Given scopes AC-037 to the §5.0 matrix (whose assessment rows carry `created_at` inside the frozen year), so the criterion is
  satisfiable — but nothing says so, and an implementer could "restore" agreement by scoping the figure, violating D-S7 under test pressure.
  - **Handling:** `[Assumed / Out of Scope]` — add one clause: agreement is asserted **for the §5.0 matrix only**, and a prior-year assessment is explicitly outside AC-037's scope because D-S7 accepts
    that divergence.
- **D-6 — A4's cross-reference is wrong.** A4 says the link "degrades to the status-filtered index per §7.1", but §7.1 is "Files Created (Phase 1)"; the degradation rule lives in §4.3.
  - **Handling:** `[Assumed / Auto-Resolved]` — repoint the reference to §4.3. (Iteration 1 missed this one; it is cosmetic but the document is otherwise reference-exact.)
- **D-7 — §1.2's bullet list mixes tight and loose formatting.** A2…A7 and the two "CLARIFICATION NEEDED" bullets are contiguous, while A8 and A9 are separated by blank lines. Valid markdown, but it
  breaks the "consistent list and structural formatting" rule this project enforces on generated documents.
  - **Handling:** `[Assumed / Auto-Resolved]` — normalise the whole §1.2 list to one style.
- **D-8 — REQ-007's wording no longer matches its own decision.** REQ-007 says the assessment figure "counts every pending assessment, restricted only by *Cakupan Instansi*", but A8/SEC-002/D-S9 add a
  second restriction (the canonical population excludes soft-deleted parents). The requirement text and the decision table now describe different rules.
  - **Handling:** `[Assumed / Auto-Resolved]` — extend REQ-007 to "restricted only by *Cakupan Instansi* **and the canonical population of §4.2**".
- **Process note — the Iteration-1 report has no `REMEDIATION STATUS` block.** The required remediation sequence (mental re-score → append a `REMEDIATION STATUS: RESOLVED` block to the original audit
  report → publish the calculation) was not executed when v1.1 was authored, so the Iteration-1 file still reads as an open audit.
  - **Handling:** `[Assumed / Out of Scope]` — the authoring agent (`/tdd-spec`) should append that block to
    `docs/audit/clarification-report-spec-admin-triage-landing-2026-09-24.md`. The Clarification Analyst does not edit another phase's artifact.

## 4. 📝 Next Steps

The Spec **passes the gate at 89/100**. The items below are refinements, not blockers. Items 1–3 close the three gaps that touch a *new* normative clause; item 4 protects the numbers AC-036 relies on.

1. **D-1 — add the precedence criterion.** One S1 case ("configured `active_year` set **and** an explicit `$now` supplied → the injected clock wins") plus its name in the §6.1 S1 case list.
2. **D-2 — make the cast falsifiable.** AC-004's Given must write the config the way the seam produces it (`'2025'`, a string) and assert both value and type, so `(int)` can fail when removed.
3. **D-5 — scope AC-037.** One clause: agreement is asserted **for the §5.0 matrix only**, and the prior-year assessment case is deliberately outside its scope because D-S7 accepts that divergence.
4. **D-4 — define the trashed parent in §5.0.** Add the parent's factory row and the explicit statement that a soft-deleted `PerformanceData` is absent from **all three** queues (A stays `2 / 3 / 1`,
   cross-agency stays `5 / 4 / 3`).
5. **D-3, D-6, D-7, D-8 — four one-line alignments.** `instansis` in the §6.3 snippet; A4 → §4.3; one list style in §1.2; REQ-007 extended with "and the canonical population of §4.2".
6. **Process — close the Iteration-1 trail.** The Spec agent should append the `REMEDIATION STATUS: RESOLVED` block to `docs/audit/clarification-report-spec-admin-triage-landing-2026-09-24.md` as the
   documented remediation protocol requires.

**Projected score after these six items:** Completeness ≈ 39/40, Clarity & Testability ≈ 29/30, Alignment ≈ 29/30 → **≈ 97/100** (mental calculation, not a re-audit; a further re-audit is optional at this
point because none of the items is a blocker).

**No new canonical term was agreed, and no ADR is required** — the v1.1 decisions (D-S7…D-S9) remain reversible, unsurprising and ordinary engineering calls under the Triple Gate
(`.agents/standards/ADR-FORMAT.md`).

> **User Decision Prompt.** The document has achieved a Readiness Score of **89/100**. It is testable and viable. Do you want to **PROCEED** to the next phase (`/tdd-checklist` for the test-case inventory
> matrix, then `/tdd-plan-tasks` for the tracer-bullet plan), or do you want to **REFINE** and apply the six refinements above first?

---

## 5. 🧭 Decision Log

- **2026-09-24 — user decision: REFINE.** The gate at 89/100 was accepted, and the user chose to refine before advancing. Handoff: `/tdd-spec` applies §4 items 1–6 to
  `spec/spec-admin-triage-landing.md` (as v1.2), then appends the `REMEDIATION STATUS: RESOLVED` block to `docs/audit/clarification-report-spec-admin-triage-landing-2026-09-24.md` per the remediation protocol.
  A third audit iteration is optional, because none of the remaining items is a blocker; D-1, D-2 and D-5 must be closed before the RED step regardless of whether Iteration 3 runs.
- **Priority order agreed for the refinements:** D-1 (missing precedence criterion) → D-2 (unexercised cast) → D-5 (AC-037 scope) → D-4 (undefined trashed parent) → D-3, D-6, D-7, D-8 (one-line alignments)
  → process note.
