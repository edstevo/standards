# Planning Thread Warm-Up

Read this reference only when preparing the next likely stage-planning thread before its user review can begin.

## Boundary

Use the eventual `gpt-5.6-sol` `xhigh` planning task named `PLAN-1234 Stage 03 Planning`. Mark it as warm-up in the controller ledger.

Warm-up is bounded context preparation, not substantive planning. It does not consume the single planning-task slot. Keep at most one future stage warm at a time, and only when its position and dependencies are sufficiently stable.

The warm-up task may inspect the target stage document, concise master stage map, direct dependency documents and handoffs, relevant repository paths, and project guidance. It must not:

- ask the user questions or run guided review;
- settle decisions, recommend readiness, or produce a planning handoff;
- edit any plan document, source, test, branch, or PR;
- run tests or audit implementation correctness; or
- investigate unrelated completed or future stages.

Gather only enough context to identify the stage boundary, relevant contracts and paths, inherited decisions, likely open choices, and evidence that could become stale. Preserve that context in the same thread and then wait.

## Activation Delta

Activate the warmed thread only after the current substantive planning task has delivered its approved handoff and no other planning task remains active.

The controller supplies:

- the warm-up baseline;
- final approved plans and handoffs for earlier stages relevant to this stage;
- relevant master-plan changes; and
- changed paths, decisions, or contracts since warm-up.

Compare only that delta with the warmed context. Do not reread every previous stage or restart discovery.

- If still relevant, retain the context and begin guided planning immediately.
- If partly stale, refresh only affected facts, paths, assumptions, and likely questions before review.
- If the stage order, boundary, or prerequisite changed materially, stop and return the conflict to the controller instead of silently planning a different stage.

This activation check refreshes planning context; it does not authorize implementation or replace the later dispatch-time freshness gate against final upstream implementation.

## Warm-Up Receipt

Send only:

```text
Plan and stage: <ID and title>
Warm-up baseline: <commit or plan revision>
Context prepared: <boundaries, contracts, and relevant areas only>
Likely stale-if-changed items: <paths, decisions, or dependencies>
State: Warmed — awaiting activation
```

Do not paste the stage document or detailed discovery into the message. The controller keeps the task open and records the receipt in its ledger.
