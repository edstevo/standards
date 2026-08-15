# Focused Freshness Gate

Read this reference only for the dispatch-time compatibility check of a stage whose planning and user approval are already complete.

## Classification And Boundary

Use a short-lived `gpt-5.6-sol` `xhigh` task named `PLAN-1234 Stage 01 Freshness`.

This is a read-only audit, not renewed planning. It does not occupy the plan's single substantive planning-task slot and may run while another stage is being planned. It must not conduct guided review, ask the user questions, reopen settled decisions, redesign the stage, edit documents or code, implement a correction, or inspect unrelated stages.

The controller may run freshness gates for different approved stages concurrently. Never run two for the same stage.

## Minimum Context

Receive only:

- the approved current stage document and its review baseline;
- recorded upstream assumptions and invalidation conditions;
- the named prerequisite's final commit and handoff;
- changed paths, contracts, and decisions since the baseline; and
- the controller task ID.

Compare that delta with the approved stage contract. Trust all unaffected planning decisions and evidence.

## Result

Return exactly one disposition:

- `Compatible`: no approved assumption or contract is materially affected. Recommend automatic promotion to `ready` with no user reapproval.
- `Upstream correction required`: the approved stage remains valid, but a contained upstream defect or mismatch must be corrected first. Keep `provisionally ready`, name the correction and replacement freshness gate, and do not seek user reapproval unless the correction would change an approved decision.
- `Approved plan invalidated`: the delta changes an approved outcome, scope, contract, dependency, or validation requirement. Recommend `not ready` and identify only the affected decisions for the substantive planning task and user.

Do not use uncertainty alone to claim invalidation. Report missing evidence precisely so the controller can supply it without restarting planning.

## Controller Handoff

Send this compact handoff explicitly and confirm receipt:

```text
Plan and stage: <ID and title>
Stage document: <path>
Baseline checked: <approved baseline>
Upstream result: <commit and handoff>
Relevant delta: <paths, contracts, or decisions checked>
Disposition: <Compatible | Upstream correction required | Approved plan invalidated>
Reason: <concise compatibility or conflict explanation>
Remaining gate: <none | exact correction and replacement check | substantive planning decision>
User reapproval: <not required | precise invalidated decisions>
Recommended controller action: <promote | keep provisional and route correction | return affected decisions to planning>
```

The controller owns all stage-document and readiness updates. If delivery fails, report the failure rather than treating the gate as complete.
