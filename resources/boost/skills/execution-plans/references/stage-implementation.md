# Stage Implementation

Read this reference only when dispatching, implementing, or correcting an approved stage.

## Controller Dispatch

For a ready stage:

1. Mark it `In progress` on the parent branch and persist that state.
2. Create `codex/plan-1234-stage-01` from the latest parent commit and record the starting commit.
3. Open draft child PR `[PLAN-1234/S01] <title>` targeting the parent branch. Link the plan and stage document; summarize scope and acceptance without copying them.
4. Start `PLAN-1234 Stage 01 Implementation` with `gpt-5.6-sol` at `high`.
5. Provide the current stage document, exact starting commit, approved planning handoff, relevant project guidance, and branch/PR details.

Keep one implementation agent for the stage and retain it through in-scope correction cycles until merge.

## Implementation Boundary

Implement only the approved reviewed plan. Do not perform open-ended audits, opportunistic refactors, adjacent fixes, broad cleanup, unrelated dependency changes, or redesign. Make only the smallest enabling production, test, migration, and documentation changes required by the approved outcome.

Trust the planning gate's dependency and compatibility certification unless a recorded invalidation condition fires. Inspect code needed to implement the stage, but do not repeat the upstream audit.

If implementation reveals materially broader required work, stop and return:

```text
Scope expansion required for: <plan and stage>
Blocked outcome: <what cannot finish>
Evidence: <why>
Proposed movement: <specific added or changed scope>
Impact if declined: <narrow alternative or remaining gap>
Work completed: <concise state>
```

Do not implement the expansion. The controller routes it to the planning task and human.

## Validation Ladder

1. Run the smallest relevant tests while developing when they provide useful feedback; do not rerun unchanged checks after every edit.
2. Run the complete focused stage suite once for each review candidate.
3. Add or adjust the approved E2E scenarios and run them at the earliest executable gate.
4. Run changed-path static analysis and formatting.
5. Use broader, specialist, or external-service validation only when risk, project rules, or an environmental gap requires it.

Record commands, outcomes, covered paths, and exact candidate commit. Preserve still-valid evidence; a new commit invalidates it only when it changes covered behaviour, tests, shared dependencies, configuration, or schema.

Move through the ladder proportionately. Do not rerun an earlier rung unless later changes invalidate it, and do not add broad verification that the reviewed plan, project rules, or observed risk does not require.

Prefer one push to create the PR, one locally green review candidate, and pushes for review corrections. Do not create branch or CI churn for unrelated parent changes.

## Implementation Handoff

Return only:

```text
Starting parent: <commit>
Implementation commits: <commits>
Validated candidate: <commit>
Changed paths and evidence: <compact path-to-test map>
Focused verification: <commands and results>
E2E evidence: <scenarios and results or exact deferred gate>
Static analysis and CI: <results>
Scope deviations: <none | approved deviation>
Discoveries: <none | material findings>
Environmental or evidence gaps: <none | exact gaps>
Correction cycle: <initial | correction number>
```

Deliver the handoff explicitly to the controller and confirm receipt. The controller persists it in `Implementation Record`; do not restate the reviewed plan.

## Corrections And Replacement

Return in-scope review findings to the same implementer. Change only the affected code and rerun invalidated checks. A replacement is allowed only when the original agent is unavailable, the approved scope changed materially, or most context was invalidated. Give a replacement the current branch, commits, stage document, completed and remaining work, failing tests, valid evidence, triggered invalidations, environmental gaps, and known traps.
