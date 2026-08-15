# Stage Document

Read this reference only when creating, revising, or validating a stage document.

## Contents

- Location and skeleton
- Section ownership
- Canonical state fields
- Compactness rules
- Readiness, freshness, and handoffs

## Location And Template

Copy `assets/stage-document.md` to `docs/plans/<PLAN-ID>/stages/STAGE-<NN>.md`, then replace its identifiers, title, link, date, and original assignment. Do not recreate the skeleton from memory or copy it out of this reference.

Keep the template's section and field order. Replace placeholders only when their phase becomes active; do not add narrative to future phases.

## Canonical State Fields

- `Status`: `Unplanned`, `Planning`, `Provisionally ready`, `Ready`, `In progress`, `In review`, `Complete`, or `Blocked`.
- `Implementation readiness`: `not ready`, `provisionally ready`, or `ready`.
- `Current phase`: `Not started`, `Stage planning review`, `Awaiting freshness gate`, `Awaiting implementation`, `Implementation`, `Independent review`, `Controller closure`, or `Complete`.

`Status` is the overall lifecycle, implementation readiness authorizes dispatch, and current phase identifies where work is happening. `Blocked` is a status, not a phase; retain the phase in which the blocker occurred.

## Section Ownership

- Overall plan author: `Original Stage Assignment`.
- Stage planning task: `Repository Orientation`, `Reviewed Plan`, planning decisions, and planning discoveries.
- Controller: top-level metadata, accepted freshness results and readiness promotion, accepted handoffs, progress, final evidence, retrospective, and closure.
- Implementation and review agents: return handoffs; they do not edit the stage document unless the controller explicitly grants that narrow ownership.

The planning task may edit only its own stage document. It returns proposed master-plan or other-stage changes to the controller as suggestions.

Complete every applicable field supplied by the asset. Use plain prose and short lists only where they improve scope or validation. Put each material choice and rationale once in `Decision Log`; do not restate it in receipts or later phases.

## Compactness Rules

- Give each fact one canonical home; later sections reference it.
- Keep only the current reviewed plan, latest material delta, and commit-scoped evidence.
- List only paths, symbols, tests, and commands needed to implement, verify, or invalidate the stage; group related paths.
- Keep guided-review proof to `Guided review`, `User approval`, and `Handoff delivery`.
- Do not repeat outcome, scope, E2E duties, decisions, or approach inside receipts, progress, discoveries, or messages.
- Use `None` instead of explanatory prose when nothing exists.
- Keep revision notes to one line per material document change.
- Reject a document that is duplicated, not independently resumable, or contains discovery history with no downstream use.

## Readiness And Freshness

A provisionally ready stage has no open decisions and waits only for an exact freshness gate. Record `Remaining gate`, `Automatic promotion: Yes`, and `User reapproval required: No — unless <material invalidation condition>`.

The freshness task is read-only. It returns its baseline and result to the controller, which records `Latest relevant delta`, the freshness handoff receipt, and any readiness change. A contained upstream mismatch may keep the stage provisionally ready behind a revised named gate; only invalidation of the approved stage returns it to substantive planning and human approval.

Use `Affected complete journeys: None — <evidence>` only when the stage truly cannot change an observable journey. Missing infrastructure is an environmental gap, not proof of readiness; name the scenario, expected result, owner, and later gate.

Use `Must read`, `Read if needed`, and `Do not reread` only when they materially reduce downstream loading. Pair relevant paths with exact invalidation conditions. Replace an old delta summary with the latest material delta rather than accumulating history.

Every handoff records the destination controller task and successful receipt. `Pending` or `Blocked — <reason>` means the gate is incomplete.
