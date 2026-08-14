# Master Plan Authoring

Read this reference only when cataloguing, creating, or materially revising a master ExecPlan.

## Contents

- Catalogue and metadata
- Required master-plan sections
- Stage decomposition
- E2E and final acceptance
- Authoring completion gate

## Catalogue

Keep `docs/planlist.md` as a searchable index:

```markdown
# Plan List

- [PLAN-1234 — Revolut integration](plans/PLAN-1234.md)
```

List only the plan ID, title, and link. Sort by ID unless the repository establishes another order. Allocate the next unused four-digit ID; never reuse or renumber one.

## Master Document

Copy `assets/master-plan.md` to `docs/plans/<PLAN-ID>.md`, then replace its identifiers, title, placeholders, stages, and date. Do not recreate the skeleton from memory or copy it out of this reference.

Use statuses `Draft`, `Ready to orchestrate`, `In progress`, `Blocked`, `In review`, and `Complete`. Authoring does not create a branch or PR unless the user explicitly combines authoring and orchestration.

## Required Sections

### Purpose / Big Picture

Explain the user-visible outcome, why it matters, how to observe it, and the relevant repository concepts in plain language.

### Stages

Give every stage a two-digit number, action-oriented title, one coherent outcome, direct dependencies, and a link to its stage document. Keep each entry in the compact shape supplied by the asset.

Use stage states `Unplanned`, `Planning`, `Provisionally ready`, `Ready`, `In progress`, `In review`, `Complete`, and `Blocked`.

Split a stage when it combines independently useful outcomes, unrelated interfaces or validation strategies, multiple implementation owners, or a PR that cannot be reviewed as one component. Prefer additive migrations or an explicit prototype stage when they reduce risk.

### Progress

Track stage-level transitions and integrated validation only. Use timestamped checkboxes and split partial work into completed and remaining items.

### Surprises & Discoveries

Record only findings that affect multiple stages or the overall plan. Put stage-local findings in the stage document.

### Decision Log

Record only cross-stage or plan-level decisions:

```markdown
- Decision: <decision>
  Rationale: <why>
  Date/Author: <date and actor>
```

### Validation and Acceptance

Describe how to exercise the completed system, the exact commands and working directory, and the observable result. Include environmental assumptions and safe alternatives where needed.

### End-To-End Readiness

Maintain one plan-wide map of complete user or system journeys:

```markdown
| Complete journey | Affected stages | Existing E2E coverage | Required change | Final evidence |
|---|---|---|---|---|
| Connect and refresh an account | 01, 03 | `tests/e2e/connect-account.*` | Add refresh-expiry path in Stage 03 | Pending |
```

Assign each required scenario change to the stage that owns the behaviour. If execution must wait, name its owner and executable final gate. Do not complete the plan with an unresolved E2E obligation.

### Interfaces and Dependencies

Describe the stage dependency graph and contracts shared across stages. Keep detailed files, symbols, and implementation contracts in their owning stage documents.

### Artifacts and Notes

Keep only evidence needed to verify or resume the overall plan. Do not paste full logs or diffs.

### Outcomes & Retrospective

At completion, compare the achieved behaviour with the original purpose and record remaining gaps and lessons useful to future work.

### Revision Notes

Append one dated line for each material revision and its reason. Update the affected section as well; the notes are not a second copy of the plan.

## Authoring Completion Gate

Create the master document, index entry, and one skeleton document for every defined stage together. Keep the plan `Draft` until it is self-contained, decomposed into small stages, has a complete-journey map, and defines observable final acceptance. Then mark it `Ready to orchestrate`.
