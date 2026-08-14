# ExecPlan Controller

Read this reference only in the main orchestration task.

## Controller Role

The controller owns plan state, branches, PRs, dispatch, handoffs, readiness, stage transitions, cleanup, and completion. Orchestration remains its primary role. Answer human questions, apply relevant decisions, then return to active work unless the human explicitly stops or changes the request.

Remain open while any owned planning, implementation, review, handoff, gate, or stage PR is running, queued, waiting, or unresolved. Before finishing, confirm zero active delegated work and all required handoffs received.

Maintain a compact ledger containing each active task, gate, meaningful state or cursor, and expected handoff. Use bounded waits and compact snapshots. Report only human decisions, material blockers or conflicts, failed delivery, meaningful gate transitions, completed reviews or implementations, merges, and final readiness.

## Resolve Or Resume

1. Resolve the plan from `docs/planlist.md` by ID, number, or unique title.
2. Read the master plan and current stage document. Read a completed stage only when the current stage names it as a dependency or required contract.
3. If the plan is `Draft`, return it to authoring with the missing readiness work. Otherwise resume its recorded state; do not recreate or redesign it.
4. Search for an existing parent PR before creating one: recorded link, exact `[PLAN-1234]` title, official branch prefix, then closed or merged matches.
5. If one active match exists, use its head branch and authoritative plan documents. If several exist, ask the user. Do not silently duplicate a completed or abandoned execution.

When no parent PR exists, create `codex/plan-1234-<slug>` from the intended target, mark the plan `In progress`, commit that transition, push, and open draft PR `[PLAN-1234] <title>`. Link the master plan and summarize stages without copying it. Keep the parent PR draft while stage work remains.

## Official Names

| Item | Name |
|---|---|
| Controller | `PLAN-1234 Orchestrator` |
| Planning task | `PLAN-1234 Stage 01 Planning` |
| Implementation task | `PLAN-1234 Stage 01 Implementation` |
| Review task | `PLAN-1234 Stage 01 Review` |
| Parent branch | `codex/plan-1234-<slug>` |
| Stage branch | `codex/plan-1234-stage-01` |
| Parent PR | `[PLAN-1234] <title>` |
| Stage PR | `[PLAN-1234/S01] <stage title>` |

Keep one planning task across the plan, one implementation task per stage, and one independent reviewer per stage. Resume them for same-stage decisions or corrections while their context remains valid.

## Context Discipline

The current context package is the master plan, current stage document, latest applicable handoffs, dependency/readiness state, relevant commits or cursors, and invalidation conditions.

Trust this package while its baseline remains valid. Reread relevant sources or history when a handoff is missing or ambiguous, a relevant path or contract changed, contradictory evidence appears, an invalidation triggers, or an agent is replaced. Do not broadly rediscover settled work merely to conserve uncertainty.

Every dispatch gives three boundaries when useful: `Must read`, `Read if needed`, and `Do not reread`. When the parent advances, provide only the old/new commits, relevant changed paths, affected contracts, triggered invalidations, and still-valid evidence.

Never infer handoff receipt from task completion. Do not advance until explicit delivery succeeds.

Accept a completed gate by checking delivery, ownership boundaries, required fields, internal consistency, baseline freshness, and the recorded result. Do not repeat its repository investigation, dependency analysis, validation design, test execution, or diff review. Return it only for a missing requirement, contradiction, scope escape, stale relevant baseline, or triggered invalidation.

## Stage Loop

For each stage in dependency order:

1. Read `stage-planning.md` and dispatch or resume planning. Accept its stage-document delta and explicit handoff before changing readiness.
2. For a provisionally ready stage, run its named focused delta gate when the upstream condition clears. Promote automatically when compatible; return only material conflicts to planning and the human.
3. Read `stage-implementation.md`, create the child branch/PR, and dispatch one implementer when ready.
4. Read `stage-review.md` and dispatch a different independent reviewer for the validated candidate.
5. Route in-scope findings back to the same implementer and focused re-review back to the same reviewer. Route expansion to planning and human approval.
6. Merge only a passing reviewed candidate, persist evidence, update the stage and master plan, clean up no-longer-needed tasks, then dispatch newly ready dependants.

Only one planning task may be active at a time, but planning for a later stage may overlap implementation or review of an earlier stage. Never implement a dependant before its prerequisite merges and its focused gate passes.

Commit controller-owned plan state directly to the parent branch. Keep implementation code on child branches. Avoid synchronizing unrelated parent changes into active stage work; a new commit alone does not invalidate evidence.

## Stage Closure

After review passes:

1. Confirm the reviewed commit is the merge candidate.
2. Confirm E2E coverage is sufficient or a named scenario, expected result, owner, and executable later gate remain.
3. Merge the stage PR using repository conventions.
4. Persist implementation and review evidence in the stage document; update only cross-stage effects and the journey map in the master plan.
5. Mark the stage complete and push the durable controller update.
6. Close or archive planning, implementation, and review tasks no longer needed. Keep cleanup blockers in the ledger until resolved.
7. Check every dependant, run any newly unblocked delta gate, and dispatch the next ready stage without unnecessary idle time.
8. Update the parent PR's concise dashboard.

Do not close a stage until its record is durable and task cleanup is complete or precisely blocked.

Closure is a receipt and identity gate, not another technical review. Do not rerun tests or re-audit the diff when the reviewed candidate is unchanged and the review evidence remains valid.

## Plan Completion

After every stage closes, run integrated acceptance for cross-stage behaviour and the plan-wide E2E journey map. Resolve deferred obligations, but do not rerun every stage's focused suite unless integration changed or invalidated its evidence. Use an independent integrated review when project rules or risk require it. Record the achieved outcome, remaining gaps, lessons, and final evidence; then mark the plan complete and make the parent PR ready for review.

Do not merge the parent PR or release unless the user explicitly requested that final action or established workflow grants it. Finish the controller only after its final ledger confirms no active task, handoff, gate, or stage PR remains.
