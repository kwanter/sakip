# Product Requirements Document: Evidence Integrity Locking (Bukti Terkunci)

**Status:** Draft
**Version:** 1.0
**Date:** 2026-10-25
**Author:** TDD Product Manager
**Target Technical Spec:** `/spec/spec-evidence-integrity-locking.md`
**Target Quality Gate:** `/tdd-clarify`

---

## 1. Product Overview

### 1.1 Summary

SAKIP's audit guarantee is broken on one path today: a data collector can
silently destroy **all** evidence attached to a submitted or validated
performance record by submitting an edit with an empty evidence list — the
files' bytes are physically deleted immediately, inside the database
transaction, with no status precondition and without consulting the
admin-only evidence-deletion policy. This feature makes evidence on
`submitted`/`validated` records **locked (Bukti Terkunci)**: collector edits
are refused while a record is locked, evidence removal is authorized per
document, and physical file cleanup happens only after the owning database
transaction commits. It also repairs the validation-result contract on the
same path so a *failed* validation never again writes `status='submitted'`.

### 1.2 Strategic Goals

- **Business Goals:**
  - Preserve the audit integrity of the SAKIP pipeline: no evidence byte may
    disappear from a submitted/validated record without an authorized,
    audited action (close the last open security finding from audit cycle
    v1.0, `REQ-006`).
  - Keep the evidence trail consistent with the `AuditLog` — denials leave no
    successful-mutation audit row; authorized removals do.
- **User Goals:**
  - As a Data Collector, I can still correct **my own draft/returned** records
    and manage their evidence with predictable behavior.
  - As an auditor/assessor, the evidence I validated is the evidence that
    remains when I or anyone else later opens the record.
- **Non-Goals (Out of Scope):**
  - Building an unlock/re-validation workflow (the transitions exist in the
    domain vocabulary; wiring the unrouted validation cycle is a future cycle).
  - Fixing the broken `store()` write path (`indicator_id`/`target_value`/
    `performance_percentage` column mismatch — plan v2.0 RISK-808).
  - Adding any new `performance_data.status` enum value (v1.0 CON-005).
  - Changing evidence **visibility/download** (hardened in a prior cycle) —
    locking concerns mutations only.
  - Any schema migration (pure code/policy/behavior change).

---

## 2. Domain Vocabulary (Aligned with `CONTEXT.md`)

- **Bukti Terkunci**: evidence attached to a `submitted`/`validated` record;
  collectors cannot mutate or remove it — only a genuine validation
  transition (or `sakip.admin` authority) may change it.
  (`_Avoid_: frozen evidence, readonly evidence, bukti beku`)
- **Status Dapat Edit**: `draft` and `rejected` — the only statuses in which
  an owning collector may still edit values and evidence attachments.
  (`_Avoid_: open status, status terbuka`)
- **Cleanup Setelah Commit**: physical file deletion scheduled only after the
  owning database transaction commits; a failed transaction preserves bytes
  and rows in a diagnosable state. (`_Avoid_: deferred delete, async delete`)

---

## 3. User Personas & Roles

### 3.1 Personas

- **Pengumpul Data (Data Collector)**: instansi-bound verified user holding
  `enter-and-submit-data-records` and `edit-own-data-submissions`; creates
  records, uploads evidence, fixes drafts and returned (`rejected`) records.
- **Penilai/Auditor (Assessor/Auditor)**: consumes submitted records and their
  evidence; relies on validated evidence remaining present.
- **Administrator SAKIP (`sakip.admin`)**: the only role whose policy may
  remove evidence outside the owning-draft flow.

### 3.2 Role-Based Access Control

- **Data Collector**: edit values/evidence only on own records in
  **Status Dapat Edit**; removing own draft evidence is part of editing that
  draft. `[ASSUMPTION-1]` No new permission is introduced for draft-evidence
  removal — authority derives from the existing update policy (tenant +
  `edit-own-data-submissions` + creator/submitter) plus **Status Dapat Edit**.
- **Assessor/Auditor**: read/download evidence; no mutation rights.
- **`sakip.admin`**: retains `EvidenceDocumentPolicy::delete` authority
  (DEC-006 keeps this restrictive; unchanged).

---

## 4. Functional Requirements & Feature Scope

- **FEAT-001 (Status-Gated Record Editing)** (Priority: High)
  Editing a performance record (values or evidence attachments) is refused
  while its status is `submitted` or `validated` (**Bukti Terkunci**).
  Refusal is an authorization denial (HTTP 403) with an Indonesian message
  stating the record is locked. `[DECISION]` 403 over 422: a locked record is
  an authorization-semantics refusal per project convention CON-002, and it
  fires before payload validation (KB P-01).
- **FEAT-002 (Authorized Per-Document Evidence Removal)** (Priority: High)
  Within an authorized edit of a record in **Status Dapat Edit**, removing an
  evidence document from the retained list removes exactly that document —
  each removal passes the evidence-deletion authorization; bulk wiping by
  omission (`existing_files: []`) on non-editable statuses is impossible by
  FEAT-001, and a removal attempt on evidence the actor is not authorized to
  remove is denied without touching bytes.
- **FEAT-003 (Cleanup Setelah Commit)** (Priority: High)
  Physical deletion of an authorized removal's file bytes is scheduled to run
  only after the owning transaction commits. A rolled-back transaction leaves
  both the row and the bytes intact and diagnosable (path recorded); a
  committed transaction cleans only the files whose removal was authorized.
- **FEAT-004 (Validation-Result Contract Repair)** (Priority: Medium)
  The validation result contract on the update path is read correctly, and
  the record advances to `submitted` **only** when validation succeeds; a
  failed validation keeps the record editable-status-safe (`draft`) and
  stores the errors for the collector.
- **FEAT-005 (Audit & Denial UX)** (Priority: Medium)
  Denied locked-edit attempts produce no successful-mutation `AuditLog` row;
  authorized removals do produce their audit row. All denial messages shown
  to collectors are in Bahasa Indonesia (e.g. "Data telah divalidasi; bukti
  terkunci.").

---

## 5. User Experience & Flows

### 5.1 First-Time User Flow & Entry Points

Entry points unchanged: the data-collection edit form (`sakip.data-collection`
update route). When a collector opens a record in `submitted`/`validated`
status, the editable form is replaced by a read-only view with the lock
message; the edit action itself is server-refused even if the UI is bypassed
(the UI is convenience, the server is the contract).

### 5.2 Core Experience & Happy Path

1. Collector opens their own `draft` record with two evidence files attached.
2. Collector removes one file from the retained list and saves.
3. Transaction commits; the removed file's bytes are cleaned after commit;
   the record, the retained evidence, and a new audit row are consistent.

### 5.3 Edge Cases & UI/UX Highlights

- Locked record + crafted direct request → 403, Indonesian message, zero
  byte/row changes, no success audit row.
- Removal whose transaction later fails → bytes and row survive; the failure
  is diagnosable (error logged with the file path); no partial state.
- Rollback does not resurrect deleted bytes — which is exactly why FEAT-003
  moves deletion after commit.

---

## 6. Success Metrics & Performance Budgets

- **User-Centric Metrics:** 100% of collector attempts to mutate evidence on
  locked records are refused with an understandable Indonesian message (asserted
  as the message string in tests).
- **Business Metrics:** zero evidence bytes lost through the collector edit
  path on `submitted`/`validated` records — enforced as a permanent regression
  suite assertion (byte-level, sentinel contents), closing audit finding
  `REQ-006`.
- **Technical Metrics & Constraints (per `CONSTRAINTS.md`):** no schema
  migration; full-suite runtime stays within the SLA (target < 25.0 s /
  floor < 30.0 s — new tests use the seam-light fake-storage pattern);
  Pint clean; all existing tenant-isolation and floor-guard tests remain
  green; 0 skipped tests.

---

## 7. Technical Considerations (Input for Specification Architect)

- **Integration Boundaries:** `DataCollectionController::update` (public seam),
  `PerformanceDataPolicy` (status precondition), `EvidenceDocumentPolicy::delete`
  (per-document authority), `DataValidationService` result contract, private
  `local` disk (`Storage::fake` in tests), `AuditLog`.
- **Transaction/Storage Consistency:** DB-rollback cannot restore file bytes —
  cleanup must be scheduled after commit; a failed cleanup must retain the
  path for controlled retry (DEC-006).
- **Data Privacy & Retention:** evidence files stay on the private disk only
  (prior hardening); deletion authority follows the restrictive policy.
- **Scalability Risks:** none material (internal edit path); after-commit
  cleanup is O(removed files) within the request lifecycle.
- **Test-seam discipline:** guards precede validation (KB P-01); dual-denial
  403/404 conventions apply where route-model binding filters first (KB P-02);
  unique sentinel names per test because the suite shares one `:memory:`
  connection (KB P-05).

---

## 8. User Stories & Executable Acceptance Scenarios (BDD Mandate)

### US-001: Evidence on locked records cannot be mutated by collectors
**As a** Pengumpul Data,
**I want to** be refused when editing a record that is `submitted`/`validated`,
**So that** the evidence trail I submitted for audit remains exactly what the
auditor validated.

#### Acceptance Criteria (Given-When-Then)
- **Scenario 1: Happy Path (editable record still editable)**
  - **Given** a `draft` performance record owned by verified collector C
    (holding `edit-own-data-submissions`) with two evidence documents whose
    sentinel bytes exist on the private disk
  - **When** C submits a valid scalar edit keeping both files retained
  - **Then** the edit succeeds (2xx), both files' bytes and rows remain, and a
    mutation audit row exists
- **Scenario 2: Boundary / Validation Error (locked record wipe attempt)**
  - **Given** the same record now `validated` with the same two evidence files
  - **When** C submits the edit with `existing_files: []`
  - **Then** the response is **403** containing "terkunci", both evidence
    rows exist, both sentinel byte sets still exist on disk, no
    successful-mutation audit row exists, and the record status is unchanged
- **Scenario 3: State Transition & Persistence**
  - **Given** a `rejected` (returned) record owned by C
  - **When** C edits it successfully
  - **Then** the record remains editable-status and its retained evidence is
    preserved (returned records are **Status Dapat Edit**, not locked)

### US-002: Draft evidence removal is authorized and exact
**As a** Pengumpul Data,
**I want to** remove one specific evidence file from my own draft,
**So that** I can correct mistakes without affecting any other file.

#### Acceptance Criteria (Given-When-Then)
- **Scenario 1: Happy Path (single authorized removal)**
  - **Given** C's `draft` record with evidence files E1, E2 (sentinel bytes on
    private disk)
  - **When** C saves the edit retaining only E1's id
  - **Then** the transaction commits, E2's row and bytes are removed (after
    commit), E1 is fully intact, and an audit row records the removal
- **Scenario 2: Boundary (actor not authorized for the document)**
  - **Given** a same-instansi collector D (no ownership of the record, holds
    no removal authority)
  - **When** D attempts the same edit
  - **Then** the request is denied (403) and neither E1 nor E2 changes
- **Scenario 3: State Transition & Persistence (foreign evidence id ignored)**
  - **Given** C's draft edit payload referencing a foreign record's evidence
    id in the retained list
  - **When** the edit is processed
  - **Then** no foreign evidence document is affected and C's own retained
    semantics apply only to C's record's documents

### US-003: Physical cleanup happens only after commit
**As an** Auditor,
**I want to** file deletions to occur only after the database transaction
succeeds,
**So that** a failed edit never leaves records without their evidence files.

#### Acceptance Criteria (Given-When-Then)
- **Scenario 1: Happy Path (commit then clean)**
  - **Given** an authorized removal of E2 inside a transaction
  - **When** the transaction commits
  - **Then** E2's bytes are deleted from the private disk after commit and its
    row is gone
- **Scenario 2: Boundary (transaction fails)**
  - **Given** the same removal where the transaction later rolls back
  - **When** the rollback completes
  - **Then** E2's row and sentinel bytes both still exist and the failure is
    diagnosable (logged path)
- **Scenario 3: State Transition & Persistence (cleanup failure is safe)**
  - **Given** an after-commit cleanup that itself fails
  - **Then** the database state remains committed-and-correct and the orphaned
    path is recorded for controlled retry — never silently swallowed

### US-004: Validation failures never advance status
**As a** Pengumpul Data,
**I want to** failed validations to keep my record in an editable state with
visible errors,
**So that** I can fix the data instead of it silently entering the audit queue.

#### Acceptance Criteria (Given-When-Then)
- **Scenario 1: Happy Path (clean validation advances)**
  - **Given** C's `draft` record edit whose validation succeeds
  - **When** the edit completes
  - **Then** the record status becomes `submitted` and no validation errors are stored
- **Scenario 2: Boundary (failed validation)**
  - **Given** an edit whose validation reports errors
  - **When** the edit completes
  - **Then** the record does **not** become `submitted`; it remains in
    **Status Dapat Edit** and the errors are stored for display
- **Scenario 3: State Transition & Persistence**
  - **Given** any edit of a locked-status record
  - **When** validation would run
  - **Then** FEAT-001's refusal fires first (guard-before-validation) — the
    validation contract is never reached on a locked record

---

## 9. Milestones & Suggested Phasing

- **Phase 1 (MVP Vertical Slice):** FEAT-001 + FEAT-002 + FEAT-003 — the
  lock, authorized removal, and after-commit cleanup land as one slice
  (US-001..US-003) with byte-level regressions.
- **Phase 2 (Contract & Polish):** FEAT-004 + FEAT-005 — validation-contract
  repair, denial copy, audit-row assertions (US-004), plus the read-only
  locked-record view message.

---

## 10. Open Items for `/tdd-clarify`

- `[ASSUMPTION-1]` Draft-evidence removal derives from existing update
  authority (no new permission). Alternative: a dedicated delegated
  capability.
- `[DECISION]` Locked-edit refusal = 403 (authorization semantics, CON-002,
  guard-before-validation). Confirm at clarify.
- `[ASSUMPTION-2]` The unrouted validation cycle stays out of scope; a locked
  record becomes editable again only through a genuine future validation
  workflow (not built here).
- `[ASSUMPTION-3]` Pre-rollout read-only inventory of collectors who
  currently edit `submitted` records (rollout communication, not a code gate).
