---
title: "Explanation: Why the Admin Triage Landing Is Shaped This Way"
version: 1.0
date_created: 2026-09-26
last_updated: 2026-09-26
status: Active
quadrant: Explanation (Diátaxis)
upstream_spec: spec/spec-admin-triage-landing.md (v1.5, §9 decisions D-S1…D-S9)
upstream_review: docs/review/code-review-admin-triage-landing-2026-09-25.md (v1.0)
upstream_architecture: docs/ARCHITECTURE.md (§10 seams S1–S4)
generated_by: /tdd-generate-docs
---

<!-- markdownlint-disable -->

# Explanation: Why the Admin Triage Landing Is Shaped This Way

This document discusses the reasoning behind the Admin Triage Landing: the trade-offs taken, the alternatives rejected, and the deliberate omissions. It contains no steps and no instructions —
for the task recipes see `docs/how-to/`, for the factual contract see `docs/reference/ref-admin-triage-landing.md`.

## 1. From Inventory to Triage

The HQ landing used to report *inventory*: how much data exists, how many users logged in, how many indicators are registered. An administrator arriving at the page could read every number and still not
know what to do first, because inventory does not rank work. The feature reframed the page as a **triage** surface that answers one question — *what needs my attention first, for which agency, in which
reporting period* — with three figures that each describe a queue and each lead to it.

That reframing is why the login telemetry and the two inventory cards (the validated-data card and the indicator card) left the triage block: neither is an attention signal, and neither is a figure
whose basis the product requires stating. The recent-activity table stayed, because it answers a different, secondary question ("what just happened?") without competing for the figure row.

Three queues, not one, for a simple reason: the platform has three approval steps, and a landing that exposes only the first makes the other two discoverable only by accident.

## 2. Why Every Figure States Its Basis

`Periode Pelaporan` in `CONTEXT.md` carries a rule, not just a definition: *a figure not limited to such a window must state that limitation on screen*. That single sentence drives the most visible design
decision of this feature — the basis label under each figure.

The verification figure is genuinely period-scoped: `performance_data.period` is a `string(7)` `YYYY-MM` column, so a month, a quarter or a year resolves to an exact, honest range. The assessment
figure is not. An `Assessment` carries no period column at all; its only temporal anchor is `created_at`, and the assessment index's own `?period=` filter reads a **calendar year**. The report figure is
not either: `reports.period` is free-form and seeded quarter-coded, so any calendar mapping would be an invention.

Three options existed for those two figures: bound them by a range the data cannot support, drop them, or state their limitation. The product owner ratified the third — the figures stay
period-independent and carry the explicit label `Tidak dibatasi periode` (decision **A1**, recorded 2026-09-24). Labelling is not a cosmetic compromise: it turns an implicit claim into an explicit one,
which is exactly what the glossary rule demands and what a figure quoted in an official report needs.

The rejected alternatives are worth remembering, because they are the ones a future maintainer will be tempted by:

| Temptation | Why it was rejected |
| --- | --- |
| `whereYear('created_at', $year)` on the assessment count | exact only for the year key; over-broad for quarters and months, so the label would become wrong for four of five keys |
| Presenting the assessment or report figure as period-scoped | a claim the underlying data cannot support — forbidden by `CONTEXT.md` |
| Removing the "awkward" figures | they are the queues an HQ administrator must see; hiding work is worse than labelling it |

## 3. Why the Three Figures Have Three Relationships to the Period

The period selector scopes what the data can honestly support and nothing more. A user switching from `Tahun 2026` to `September 2026` sees the verification figure move, and the other two stay put —
and both keep saying `Tidak dibatasi periode`.

That asymmetry is deliberate and now pinned by a test of its own (TC-045 / AC-028): a period switch moves only the verification figure, and the other two figures keep both their value and their label.
Without that pin, a future template change could bind a period label to the assessment figure and every other test would still pass — the honesty contract is exactly the thing that would have broken.

## 4. Why Each Deep Link Carries Different Parameters

The three targets have three different filter vocabularies:

- `DataCollectionController@index` accepts an exact `YYYY-MM` — it can express a month selection exactly.
- `AssessmentController@index` reads a calendar **year** from `?period=`.
- `ReportController@index` reads its own quarter-coded string from `?period=`.

Sending one `period` parameter to all three would therefore be wrong twice over: for the assessment figure it would imply a window the figure explicitly disclaimed, and for the report figure it would send
a value the target's vocabulary does not share with `performance_data.period`. The rule that emerged (decision **D-S4**) is: send a parameter only where the target's filter can express the selection.
Today that means `period` reaches exactly one target, and only when the selection collapses to a single month.

The alternative — sending nothing anywhere — was rejected too: for the verification queue the filter genuinely exists, and losing it would push the administrator back into searching an unfiltered list,
which is the behaviour this feature exists to remove.

## 5. Why Accepted Divergences Are Asserted, Not Hidden

Three known imperfections ship with this feature. The team's decision was not to hide them but to **assert** them, so a reader of the test suite learns the truth at the same moment the suite protects it.

- **D-S7 — the assessment figure and its target disagree about time.** The figure counts all pending assessments; the target index hardcodes the current calendar year for its default query. Making them
  agree would require either scoping the figure to a basis the data does not carry or rewriting another controller's default. The divergence is disclosed in the reference and pinned by TC-054.
- **D-S8 — for a cross-agency viewer the verification figure can be non-zero while its queue renders empty.** `DataCollectionController@index` derives coverage from `performance_indicators.instansi_id`
  and has no Super Admin branch. The repair belongs to that controller's own slice. Until then, TC-055 asserts the figure, the reachable link, and the empty queue as positive expectations — a reader
  cannot be surprised by a behaviour the suite states out loud.
- **D-S9 — an assessment whose parent performance-data row is soft-deleted is not pending work.** Without this rule, the cross-agency count and the agency-bound count would describe different
  populations, and the invariant "cross-agency equals the sum of the agencies" would be untestable. TC-020 and TC-021 pin it for both viewer states.

The philosophy is the same in all three cases: an accepted defect that is asserted is a documented behaviour; an accepted defect that is not asserted is folklore that the next change will silently break.

## 6. Why the Badge Belongs to the Layout

The sidebar badge shows the same number as the landing's verification figure, on every page that renders `layouts.modern`. The obvious implementation — have the dashboard controller pass
`pendingDataCount` to the layout — is the one the Spec initially prescribed and the delivery deliberately replaced (decision **D-S5**).

The reason is coupling direction. The layout renders on dozens of pages owned by dozens of controllers; a controller-supplied variable would make the badge the landing's property rendered on other pages'
behalf, and every non-landing page would show no badge at all. A `layouts.modern` view composer inverts that: the layout owns its sidebar data, resolves the period from the request, and asks the service
for one count. The badge and the landing figure then share a single implementation, so they cannot drift.

The delivery also recorded the cost of getting there: because the Phase-1 controller stopped passing the variable before the composer existed, no `layouts.modern` page rendered a badge for a five-commit
window. No test could see it — the badge seam only existed from Phase 2 — which is precisely why the review treated it as a process finding (`SPEC-B-01`) and why the Spec was amended to state the
delivered ownership instead of a continuity promise the delivery never kept.

## 7. Why One Active-Year Source, and Why the Seam Is Validated

Two implementations of "the current year" existed: `ForYearTrait::scopeForCurrentYear` called `date('Y')` directly, while the dashboard service computed its own ranges. Duplicated clocks drift — a pinned
reporting year would move one and not the other. The fix (REQ-014) is a single source: `ForYearTrait` delegates to `ReportingPeriod::activeYear()`, and the dashboard's date-range helper became an adapter
over the same resolver, so its five call sites were not touched.

Making the year configurable (`SAKIP_ACTIVE_YEAR`) introduced a subtler risk. An environment variable is untrusted input like any other, and `(int) 'abc'` is `0`. A typo in a deployment file could
therefore have re-pointed every year-scoped query in the application at year 0 — silently, because nothing in the suite probed the seam with a malformed value. The resolution kept the decision inside the
resolver: the configured value must be a whole number inside `1970…9999`, and anything else falls back to the clock. Validating inside `config/sakip.php` instead was rejected because the resolver stays the
single decision point and remains unit-testable at S1 without a config file.

Both facts are visible in the same place today: the documentation states the precedence, and TC-071 sweeps the rejected shapes.

## 8. Why the Triage Region Is the Unit of Assertion

The feature removes two strings from the triage block, and the layout shell renders one of them — `Indikator Kinerja` — as its own sidebar link. A body-wide negative assertion would therefore have been
impossible to satisfy without either renaming the sidebar link (a layout change outside this slice) or weakening the assertion (a floor-guard violation). The resolution was to define the triage block as an
explicit region and scope every negative assertion to it: the removed copy must be absent **inside the region**, and the layout's identical string must still be present **outside** it.

This is a small decision with a general lesson: a test can only assert the boundary it can name, so making the boundary explicit in the markup (`data-triage-region`) is what made a previously unassertable
requirement assertable.

## 9. What Was Deliberately Left Undone

Documentation that lists only what was built is incomplete. The following were consciously not done, and each has a named owner or a recorded reason:

| Not done | Reason |
| --- | --- |
| Charts, trends and anomaly detection | HQ triage needs counts and links first; anomaly detection needs its own rule design (idea I2) |
| Bulk actions, inline approvals | they duplicate the queue indexes and expand the write path; rejected as a discovery option |
| Caching (D6) | a caching decision needs defined invalidation; deferred to a later phase, never to be smuggled in |
| Repairing the cross-agency verification queue (D-S8) | it belongs to `DataCollectionController`, outside this read-only slice |
| Renaming or granting `manage-sakip` (finding F-1) | the badge sits inside a permission granted nowhere, so the section is effectively Super-Admin-only; the layout shell is out of scope here |
| The three manual metrics (above-the-fold at 1280×800, five-participant usability, p95 < 500 ms) | no harness in this repository can produce them; re-scoped to `[Assumed / Backlog]` by the product owner |
| A coverage gate | Measured 2026-09-26 (PCOV): **8.8 %** line over `app/` against a ≥ 75 % floor — unmet at project scale, while this feature's slice sits at 91.8–100 %. Branch coverage remains **UNVERIFIED** (Xdebug absent). Re-scoping the threshold is a pending product-owner decision (retro A5), never a silent edit |

## 10. How to Read This Documentation Set

| Question | Quadrant | Document |
| --- | --- | --- |
| *What exactly does the landing expose, field by field?* | Reference | `docs/reference/ref-admin-triage-landing.md` |
| *Why is the assessment figure not period-scoped?* | Explanation | this document |
| *How do I pin the reporting year in a deployment?* | How-to | `docs/how-to/how-to-configure-the-active-reporting-year.md` |
| *How do I add a fourth queue figure?* | How-to | `docs/how-to/how-to-add-a-queue-figure-to-the-triage-landing.md` |
| *How do I see the three viewer states with my own eyes?* | Tutorial | `docs/tutorials/tutorial-observe-the-triage-figures.md` |
| *What must the code satisfy, and how is it verified?* | Spec + Checklist | `spec/spec-admin-triage-landing.md`, `docs/checklist/checklist-admin-triage-landing.md` |
