# ExecPlan Format

Use this reference when creating, cataloguing, reading, or revising an ExecPlan.

## Contents

- Catalogue plans
- Start every plan with metadata
- Maintain the master plan
- Maintain plan-wide and stage-level E2E readiness
- Decompose the plan into small subplans
- Create a document for every stage
- Record reusable handoffs and fresh validation evidence
- Apply formatting rules

## Catalogue Plans

Keep `docs/planlist.md` as an index, not a status dashboard or full specification. Use one linked entry per plan:

```markdown
# Plan List

- [PLAN-1234 — Revolut integration](plans/PLAN-1234.md)
- [PLAN-1235 — Subscription reconciliation](plans/PLAN-1235.md)
```

Sort by plan ID unless the repository already establishes another stable order. Do not copy status, current stage, PR metadata, or plan prose into the list.

## Start Every Plan With Metadata

Store each master plan at `docs/plans/<PLAN-ID>.md` and start it with:

```markdown
# PLAN-1234 — Revolut integration

Status: Ready to orchestrate

Summary: Integrate Revolut account and transaction data.

Parent PR: Not created

Current stage: Not started
```

Use these plan statuses:

- `Draft`
- `Ready to orchestrate`
- `In progress`
- `Blocked`
- `In review`
- `Complete`

Use `Parent PR: Not created` before orchestration. Once created, record a Markdown link to the parent PR. Make `Current stage` human-readable, such as `Stage 01 — Planning`, `Stage 01 — In progress`, or `Stage 01 — Complete; Stage 02 not started`.

## Maintain The Master Plan

Keep the master plan concise. It owns the overall purpose, stage map, cross-stage state, dependencies and decisions, final acceptance, and integrated evidence. After the metadata, maintain these sections.

### Purpose / Big Picture

Explain why the work matters, what someone can do afterwards, how to observe it, and how the relevant files, modules, and concepts fit together. Define every non-obvious term. At completion, add what was achieved, what remains, and what was learned.

### Stages

Describe the intended stages, their order, direct dependencies, outcomes, and final contribution. Treat each stage as one subplan. Give every stage a two-digit number and action-oriented title. A future stage remains `Unplanned` until its planning chat; after complete planning it may become `Provisionally ready` while a named freshness gate remains.

Use these stage states:

- `Unplanned`
- `Planning`
- `Provisionally ready`
- `Ready`
- `In progress`
- `In review`
- `Complete`
- `Blocked`

Track implementation authorization separately with one canonical field:

- `Implementation readiness: not ready` means substantive planning or authorization is incomplete or blocked. Examples include unresolved user decisions, open questions, an oversized stage, an unresolved technical prerequisite, or a material contract conflict. More planning or user input is required.
- `Implementation readiness: provisionally ready` means the design is complete, the user accepted every material decision, the stage is small and isolated, and no open question remains. Implementation waits only for a named upstream change or scheduling delay and its focused freshness or delta gate.
- `Implementation readiness: ready` means every remaining focused gate passed and implementation dispatch is authorized.

Do not use `provisionally ready` for generally incomplete work. A named active upstream stage qualifies only when its expected outcome is sufficiently settled to complete this stage's planning; an unknown prerequisite outcome or unresolved conflict remains `not ready`.

For example, if every decision for `PLAN-0001 Stage 04` is settled and no technical prerequisite is missing, but scheduling requires one relevant-path delta check, record `Implementation readiness: provisionally ready`, name that exact check under `Remaining gate`, use `Automatic promotion: Yes`, and require no user reapproval unless the check finds the recorded material conflict.

### Decompose Into Small Subplans

Make every stage the smallest useful component that can be implemented, verified, reviewed, and merged independently while leaving the repository coherent. A valid stage must:

- deliver one coherent outcome;
- have a narrow, explicit component boundary;
- be understandable and implementable by one implementation agent;
- fit in one focused child PR;
- have focused tests or another independent verification signal; and
- state direct dependencies on other stages instead of absorbing their work.

If a proposed stage spans several components, needs multiple implementation agents, contains independently deliverable outcomes, or would produce a broad mixed PR, split it into separately numbered stage documents before marking any part `Ready`. Do not use an arbitrary line or file limit; use implementation, review, and verification isolation as the size test.

Before marking a stage `Ready`, ask whether one implementation agent can implement, verify, and hand off its outcome in one focused working session and one understandable PR. Split stages that combine separate models, workflows, interfaces, independently useful outcomes, or unrelated validation strategies.

Summarize and link every stage in this form:

```markdown
### Stage 01 — Connect Revolut accounts

Status: Planning

Details: [STAGE-01](PLAN-1234/stages/STAGE-01.md)

Outcome: A user can connect and refresh a Revolut account.

Depends on: None
```

Prefer additive migrations and temporary parallel paths when they keep tests passing and reduce risk. Use a small explicit prototype stage when feasibility is uncertain, with promotion or rejection criteria.

### Progress

Track stage-level progress and final integrated validation only. Put implementation chunks in the relevant stage document. Use checkboxes only in `Progress`. Keep completed items checked with a timestamp and short outcome when useful. Split partially completed work into a checked item and a remaining unchecked item.

### Surprises & Discoveries

Record only discoveries that affect more than one stage or the overall plan. Keep stage-local findings in the stage document. Include concise evidence where useful.

### Decision Log

Record cross-stage and plan-level decisions in this form:

```markdown
- Decision: <what was decided>
  Rationale: <why>
  Date/Author: <date and actor>
```

Include stage-boundary changes, changed dependencies, and decisions affecting more than one stage or final acceptance. Keep stage-local decisions and scope movement in the stage document, promoting them here only when they affect the wider plan.

### Validation and Acceptance

Describe how to exercise the completed system and what a human should observe. Give exact commands, working directories, inputs, outputs, relevant test names, and environmental assumptions. Prefer observable behaviour over internal implementation claims.

### End-To-End Readiness

Maintain the plan-wide map of complete user or system journeys. For each journey, record the stages that affect it, the existing end-to-end (E2E) scenario, any required addition or adjustment, and the final evidence. Update this map whenever a stage changes behaviour, scope, dependencies, or acceptance.

```markdown
| Complete journey | Affected stages | Existing E2E coverage | Required change | Final evidence |
|---|---|---|---|---|
| Connect and refresh an account | 01, 03 | `tests/e2e/connect-account.*` | Add refresh-expiry path in Stage 03 | Pending |
```

Do not defer all E2E thinking to final plan validation. A stage may record that no E2E change is required only after assessing its affected complete journeys and explaining why existing scenarios remain sufficient. If a journey cannot run until a later stage or environment is available, name the owning stage or final verification gate and preserve that obligation in the master plan.

### Artifacts and Notes

Keep compact integrated evidence that helps someone verify or resume the plan. Keep stage-only evidence in the stage document. Do not paste full logs or large diffs.

### Interfaces and Dependencies

Describe the overall stage dependency graph and interfaces shared across stages. Put detailed files, symbols, libraries, services, and contracts in the stage that owns them.

### Revision Notes

Append a dated note after every material master-plan revision describing what changed and why. Do not use revision notes instead of updating the affected sections.

## Create A Document For Every Stage

Create a document for every stage, including simple and provisionally ready stages. Use `docs/plans/<PLAN-ID>/stages/STAGE-<NN>.md`; for example, `docs/plans/PLAN-1234/stages/STAGE-01.md`. Do not add stage documents to `docs/planlist.md` or give them separate plan IDs.

Use this standard structure:

```markdown
# PLAN-1234 Stage 01 — Connect Revolut accounts

Status: Planning

Parent plan: [PLAN-1234](../../PLAN-1234.md)

Stage PR: Not created

## Outcome

Explain the independently observable result this stage must produce.

## Scope

### Included

State the approved work.

### Excluded

State the hard boundaries.

## Prerequisites, Interfaces, and Dependencies

Record prerequisite stages, affected paths, services, contracts, libraries, and symbols.

## End-To-End Readiness

Affected complete journeys: Pending

Existing E2E coverage assessment: Pending

Required E2E scenario changes: Pending

E2E execution and evidence: Pending

Deferred E2E verification owner: None

## Gate Handoffs

### Context Routing

Must read by gate: Pending

Read only if needed by gate: Pending

Do not reread by gate: Pending

Settled decisions: Pending

Unresolved assumptions: None

Invalidation map: Pending

Latest parent delta summary: None

Controller task ID: Pending

### Planning To Implementation

Gate result: Pending

Guided decision review: Incomplete

Final decision brief approved: No

Implementation readiness: not ready

Remaining gate: Complete stage planning

Automatic promotion: No

User reapproval required: Yes — planning decisions remain open

Approved outcome: Pending

Explicit exclusions: Pending

Affected complete journeys: Pending

Existing E2E coverage assessment: Pending

Required E2E scenario changes: Pending

E2E readiness disposition: Pending

Validated code baseline: Pending

Starting parent commit: Pending

Validated upstream stages: Pending

Provisional upstream assumptions: None

Compatibility result: Pending

Final delta result: Pending

Inherited contracts and decisions: Pending

Relevant paths: Pending

Invalidation conditions: Pending

Handoff delivery: Pending

### Implementation To Review

Implementation commits: Pending

Validated candidate commit: Pending

Changed paths: Pending

Changed-path-to-test map: Pending

Evidence-covered paths: Pending

Focused tests and results: Pending

E2E scenarios added or adjusted: Pending

E2E results: Pending

Static analysis and formatting: Pending

CI evidence: Pending

Known environmental gaps: None

Remaining environmental verification: None

Invalidated evidence: None

Correction cycle: Initial candidate

Scope deviations: None

New discoveries: None

Handoff delivery: Pending

### Review To Controller

Review result: Pending

Reviewed commits: Pending

Evidence reused: Pending

Correction review coverage: Initial full review

Independent verification: Pending

E2E readiness review: Pending

Invalidated evidence: None

Remaining environmental verification: None

Findings: Pending

Handoff delivery: Pending

### Replacement Agent

Replacement handoff: Not required

## Implementation Approach

Describe the focused steps one agent will use to implement this subplan. If several independent slices appear, create separate stage documents instead.

## Progress

- [ ] Record concrete work and its verification signal.

## Surprises & Discoveries

Record stage-local findings with concise evidence.

## Decision Log

Record stage-local decisions, including approved scope movement.

## Validation and Acceptance

Give exact commands, inputs, expected outputs, and observable acceptance.

## Artifacts and Notes

Keep compact stage evidence and handoff material.

## Revision Notes

Record each material stage-document change and why it was made.
```

A provisionally ready stage document must state its purpose, dependencies, boundaries, accepted scope, upstream assumptions, and the exact remaining freshness gate. Use `Status: Provisionally ready` and `Implementation readiness: provisionally ready` only when every material decision is settled, there are no open questions, the subplan is small and isolated, and implementation is withheld solely for that named gate. Record `Automatic promotion: Yes` and `User reapproval required: No — unless <precise invalidation condition>`. Before the stage becomes `Ready`, record its observable acceptance, included scope, explicit exclusions, prerequisites, affected interfaces, focused implementation approach, validation commands, risks, user-approved decisions, confirmation that it meets the small-subplan gate, its affected complete journeys, the sufficiency of existing E2E coverage, any required scenario work, and a final `Planning To Implementation` handoff.

Every stage must complete its E2E assessment, including stages whose implementation is internal or structural. Use `Affected complete journeys: None — <evidence-based rationale>` only when the stage truly cannot change an observable complete journey. Existing coverage may be accepted without changes when the stage document names the scenarios and explains why they remain sufficient. Missing local infrastructure is an environmental gap, not proof of readiness: record the scenario, expected result, remaining execution, and owning gate.

When revising an older stage that uses `Provisionally approved` or combines `Approval state: provisional` with `Ready for implementation: no`, migrate it to the canonical implementation-readiness fields. Do not preserve the ambiguous combination.

Treat `Gate Handoffs` as compact, versioned context, not duplicated narrative. `Context Routing` applies to every dispatched agent and keeps the reading boundaries, settled decisions, unresolved assumptions, parent delta, and invalidation map current. The planning handoff is the implementation context package: it records the approved outcome and exclusions plus only the upstream contracts, paths, commit references, and compatibility findings needed by this stage. The implementer records the candidate and implementation commits, changed-path-to-test map, changed and evidence-covered paths, focused validation, CI, invalidated evidence, environmental gaps, correction cycle, deviations, and discoveries. The reviewer records the exact commits reviewed, evidence reused or invalidated, correction coverage, independent verification, remaining environmental checks, result, and findings. Each downstream gate consumes the preceding handoff instead of recreating it.

Record the controller task ID when delegation supplies one. Set each `Handoff delivery` field to the destination task ID and successful delivery result only after the controller has actually received that handoff. Leave it `Pending` while work continues and use `Blocked — <exact failure>` when delivery fails. A gate is not complete while its required delivery is pending or blocked.

Use `Must read by gate`, `Read only if needed by gate`, and `Do not reread by gate` to control context loading separately for planning, implementation, review, and any replacement agent. Keep settled decisions distinct from assumptions that still need verification. Express the invalidation map as exact path or contract triggers and the context or evidence each trigger refreshes. When the parent advances, update `Latest parent delta summary` with both commits, changed paths, material contract or decision changes, triggered invalidations, and evidence that remains valid.

Complete `Replacement handoff` only when an agent must change. Record the current branch, starting parent and candidate commits, completed and remaining work, failing or pending tests, still-valid evidence, triggered invalidations, environmental gaps, and known traps. A replacement consumes this package instead of rereading the complete stage history.

Append or clearly version correction cycles instead of erasing their history. Tie each validation result to an exact commit and covered paths. A later commit invalidates that evidence only when it changes covered production behaviour, the test itself, a shared dependency used by that behaviour, or relevant configuration or schema. Preserve evidence across documentation-only, status-only, and unrelated-path commits.

The planning chat returns the approved stage content, but the controller creates and updates the file. The master plan remains authoritative for overall status, stage order, dependencies, cross-stage decisions, and final acceptance. Each stage document is authoritative for that stage's detailed scope and execution record. Immediately reflect any stage change that affects another stage or the overall plan in the master plan.

## Formatting Rules

Write normal Markdown. Use prose for explanations and reserve checklists for `Progress`. Use fenced blocks only for commands, transcripts, payloads, diffs, or small code excerpts.

Use full repository-relative paths and precise symbol names. Repeat every assumption needed to resume. Do not rely on prior chats, inaccessible documents, or unexplained external guidance. Link relevant prior work, but summarize all context required by the plan.

The master plan and all linked stage documents are the source of truth before and during orchestration. Parent and stage PR bodies are operational dashboards: link to and summarize the documents without becoming divergent copies.
