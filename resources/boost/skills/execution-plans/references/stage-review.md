# Independent Stage Review

Read this reference only when dispatching, performing, or repeating independent review of an implemented stage.

## Controller Dispatch

Mark the stage `In review` and start a different task named `PLAN-1234 Stage 01 Review` with `gpt-5.6-sol` at `xhigh`. Provide the current stage document, planning certification, implementation handoff, changed-path-to-evidence map, exact candidate commit, and complete stage diff. Do not provide unrelated stage history.

Keep this reviewer available through corrections until merge. It must remain independent from implementation.

## Review Boundary

Compare the complete stage diff with the approved outcome, scope, exclusions, interfaces, documentation, validation, and acceptance. Confirm:

- the implementation is correct and complete;
- the PR remains one small isolated subplan;
- no scope escape, unrelated cleanup, or redesign occurred;
- affected complete journeys and existing E2E coverage were assessed correctly;
- required E2E changes are present with current evidence or a valid named later gate; and
- focused verification is proportionate and reproducible.

Form an independent judgment from the complete diff, but do not equate independence with repeating every earlier command. Reuse exact-candidate implementation evidence when its scope and result are clear. Run the smallest independent checks needed for high-risk behaviour, suspected findings, missing evidence, or reviewer conclusions; do not automatically rerun the entire focused suite.

Do not repeat planning discovery, conduct an open-ended repository audit, question the user, reopen settled product decisions, implement fixes, or approve expanded scope.

Fail the gate for defects, missing paths, invalid evidence, incomplete E2E duties, or scope escape. Send in-scope corrections to the controller for the existing implementer. Send material expansion to planning and human approval.

## Review Handoff

Return only:

```text
Reviewed candidate: <commit>
Scope result: <within approved plan | findings>
Independent verification: <commands and results>
E2E readiness: <passed | findings | exact remaining gate>
Evidence reused or invalidated: <concise result>
Findings: <none | actionable list>
Correction coverage: <initial review | focused re-review>
Review result: <passed | failed>
```

Deliver it explicitly to the controller and confirm receipt. The controller persists it in `Independent Review`; do not reconstruct the planning or implementation narrative.

## Correction Review

Use the same reviewer for a focused re-review while independence and prior evidence remain valid. Inspect the correction, confirm the finding is resolved, check for new problems in the affected area, and rerun only invalidated checks. Require a fresh full review only when the correction materially changes the design or invalidates most prior evidence.

Do not pass a stage until the exact merge candidate has passed. Do not merge or edit the stage document yourself.
