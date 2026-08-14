# Stage Document

Read this reference only when creating, revising, or validating a stage document.

## Contents

- Location and skeleton
- Section ownership
- Canonical state fields
- Reviewed plan shape
- Living execution sections
- Compactness rules
- Readiness, freshness, and handoffs

## Location And Template

Copy `assets/stage-document.md` to `docs/plans/<PLAN-ID>/stages/STAGE-<NN>.md`, then replace its identifiers, title, link, date, and original assignment. Do not recreate the skeleton from memory or copy it out of this reference.

Keep the template's section order. Expand only the active section; future sections remain one status sentence.

## Canonical State Fields

- `Status`: `Unplanned`, `Planning`, `Provisionally ready`, `Ready`, `In progress`, `In review`, `Complete`, or `Blocked`.
- `Implementation readiness`: `not ready`, `provisionally ready`, or `ready`.
- `Current phase`: `Not started`, `Stage planning review`, `Awaiting freshness gate`, `Awaiting implementation`, `Implementation`, `Independent review`, `Controller closure`, or `Complete`.

`Status` is the overall lifecycle, implementation readiness authorizes dispatch, and current phase identifies where work is happening. `Blocked` is a status, not a phase; retain the phase in which the blocker occurred.

## Section Ownership

- Overall plan author: `Original Stage Assignment`.
- Stage planning task: `Repository Orientation`, `Reviewed Plan`, planning decisions, and planning discoveries.
- Controller: top-level metadata, accepted handoffs, progress, final evidence, retrospective, and closure.
- Implementation and review agents: return handoffs; they do not edit the stage document unless the controller explicitly grants that narrow ownership.

The planning task may edit only its own stage document. It returns proposed master-plan or other-stage changes to the controller as suggestions.

## Reviewed Plan Shape

Use these subsections in order once review starts:

1. `Outcome and Scope`: observable outcome, included work, exclusions, and failure behaviour.
2. `Plan of Work`: short prose sequence naming the modules or files to change and what each change accomplishes.
3. `Interfaces and Dependencies`: direct dependencies, inherited contracts, affected interfaces, and grouped relevant paths.
4. `Validation, E2E, and Recovery`: exact commands, expected results, affected complete journeys, existing coverage decision, required scenario changes and owner, environment assumptions, and safe retry or rollback.
5. `Readiness and Freshness`: baseline, readiness, remaining gate, automatic promotion, reapproval and invalidation conditions, latest relevant delta, small-stage result, open questions, and outside-stage suggestions.
6. `Planning Handoff`: gate result, guided-review completion, user approval, changed sections, controller action, and delivery receipt.

Write plain prose and use short lists only where they improve scope or validation. Put material choices and rationale once in `Decision Log`; do not repeat them in the handoff.

## Living Sections

- `Progress`: short timestamped checklist of meaningful milestones and the next executable step. Do not log routine commands or monitoring.
- `Surprises & Discoveries`: unexpected evidence that changes implementation, validation, risk, or later work. Leave `None.` otherwise.
- `Implementation Record`: starting parent, implementation and candidate commits, compact changed-path-to-evidence map, focused tests, E2E evidence or deferred gate, static analysis/CI, deviations, gaps, and delivery receipt.
- `Independent Review`: reviewed candidate, scope result, independent checks, E2E readiness, reused or invalidated evidence, findings, correction coverage, result, and delivery receipt.
- `Outcomes & Retrospective`: delivered behaviour versus original purpose, remaining obligations, and lessons useful to later stages.
- `Controller Closure`: disposition, PR/merge, master-plan updates, outside-stage suggestions, E2E obligations, task cleanup, and closure evidence.

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

Use `Affected complete journeys: None — <evidence>` only when the stage truly cannot change an observable journey. Missing infrastructure is an environmental gap, not proof of readiness; name the scenario, expected result, owner, and later gate.

Use `Must read`, `Read if needed`, and `Do not reread` only when they materially reduce downstream loading. Pair relevant paths with exact invalidation conditions. Replace an old delta summary with the latest material delta rather than accumulating history.

Every handoff records the destination controller task and successful receipt. `Pending` or `Blocked — <reason>` means the gate is incomplete.
