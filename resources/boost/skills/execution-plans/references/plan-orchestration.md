# ExecPlan Orchestration

Use this reference only when orchestrating, executing, resuming, or completing an existing plan.

## Contents

- Keep the main task as controller
- Use official names
- Establish or resume the parent PR
- Plan one stage with the user
- Implement through a child PR
- Require independent review
- Close each stage before planning the next
- Complete the parent plan

## Keep The Main Task As Controller

- Resolve the requested entry from `docs/planlist.md`, then read its `docs/plans/<PLAN-ID>.md` master document and current stage document. Read a completed stage document only when the current stage names it as a direct dependency or its handoff links a required contract or decision. Do not load every stage by default.
- Treat the main task as controller and orchestrator, and run it with `gpt-5.6-sol` at `high`. It owns plan state, branches, PRs, task dispatch, handoffs, stage transitions, and final completion. If the task mechanism cannot provide the exact configuration, use the nearest available setting and record the fallback before dispatching work.
- Keep the controller open while any planning chat, implementation subagent, review subagent, or stage PR it started remains active or awaits the user.
- Do not recreate, renumber, or silently redesign the plan. Record required changes through the plan's decision and revision mechanisms.
- If the plan is `Draft`, explain what prevents orchestration and return it to the separate authoring process. Start or resume when it is `Ready to orchestrate`, `In progress`, `Blocked`, or `In review` as appropriate.

## Use Official Names

For `PLAN-1234`, use:

| Item | Official name |
|---|---|
| Controller task | `PLAN-1234 Orchestrator` |
| Parent branch | `codex/plan-1234-<short-slug>` |
| Parent PR | `[PLAN-1234] <plan title>` |
| Stage planning chat | `PLAN-1234 Stage 01 Planning` |
| Stage implementation task | `PLAN-1234 Stage 01 Implementation` |
| Stage independent review | `PLAN-1234 Stage 01 Review` |
| Detailed stage document | `docs/plans/PLAN-1234/stages/STAGE-01.md` |
| Stage branch | `codex/plan-1234-stage-01` |
| Stage PR | `[PLAN-1234/S01] <stage title>` |

Preserve the full four-digit plan ID and two-digit stage number. Use one implementation task per stage. Do not vary the planning-chat name because the controller may need to resume it for a scope decision.

## Establish The Parent PR

The parent branch is the integration branch for the whole plan, and the parent PR targets the repository's normal default or user-specified target branch.

Before creating anything, search for an existing parent PR in this order:

1. Follow the PR link recorded in `Parent PR` when present.
2. Search open PRs for the exact plan ID in the title, such as `[PLAN-1234]`.
3. Search open PRs and remote branches for the official parent branch prefix `codex/plan-1234-`.
4. Check closed and merged PRs with that plan ID when no open match exists, so a completed or abandoned execution is not accidentally duplicated.

When exactly one active parent PR exists, treat it as the execution home. Fetch and check out its head branch, update it safely from its remote head, then reread the master plan, current stage document, and context explicitly referenced by its latest handoff. The plan documents on the parent branch are authoritative; the PR body is the operational dashboard. Repair stale `Parent PR`, status, current-stage, or dashboard metadata instead of creating another PR.

When several plausible parent PRs exist, stop and ask the user which execution is authoritative. When the only match is merged or closed, inspect its outcome and plan status; resume or create a new execution only with user confirmation. Never silently create a duplicate parent PR.

If `Parent PR: Not created`:

1. Confirm the target branch and current repository state.
2. Create `codex/plan-1234-<short-slug>` from the target branch.
3. Update the plan to `Status: In progress`, set `Parent PR: Pending creation`, and set `Current stage` to the first stage's planning state. Commit this transition so the branch differs from its base.
4. Push the branch and open a draft parent PR titled `[PLAN-1234] <plan title>`.
5. Make the PR body link to the authoritative plan document and summarize the current stage and child PRs without copying the full plan.
6. Replace `Parent PR: Pending creation` with the PR link, then commit and push that metadata update.

If a parent PR already exists, verify that it is open, its head is the recorded plan branch, and its base is the intended target. Repair stale plan metadata before dispatching stage work. Do not change a PR base casually because doing so can invalidate its review context.

Keep the parent PR draft while stages remain. Never merge or close it while a stage PR or delegated task remains active.

Commit controller-owned plan status, decisions, and stage-document updates directly to the parent plan branch. Keep implementation code on the stage branch. Give implementers and reviewers the current stage document and preceding handoff rather than the complete plan history. Synchronize the stage branch only when parent updates affect its code or recorded verification context.

## Plan One Stage With The User

Review stages in dependency order, but allow the next stage's planning chat to run while its prerequisite is being implemented or reviewed. Treat any user approval made before a prerequisite's reviewed result is merged as provisional. A provisionally approved stage must not enter implementation until every prerequisite completes and the focused final delta gate passes.

For the current stage:

- Set its state to `Planning` in the plan document.
- Create or resume the dedicated user-facing side chat titled exactly `PLAN-1234 Stage 01 Planning`.
- Use `gpt-5.6-sol` at `xhigh`. This is the pre-stage/subplan review gate; `Ultra` is reserved for the separate overall initial plan and stage-map creation process. If the task mechanism cannot provide the exact configuration, use the nearest available setting and tell the controller which fallback applied.
- Give it the master plan's concise stage map, the current stage document, direct dependency documents, explicitly relevant contracts and decisions, relevant project guidance, and repository access. Do not give it every completed stage or a predetermined conclusion.
- Keep it read-only. It may investigate and discuss but must not edit files, implement, create PRs, start implementers, or merge anything.
- Require it to explain material choices in simple English and formulate the stage decision with the user.
- Require it to verify that the stage is one small, isolated subplan with one coherent outcome, one component boundary, one implementation agent, one child PR, and focused verification. If not, formulate the split with the user and return separate proposed stages instead of approving an oversized stage.

The planning chat returns:

```text
Plan: PLAN-1234
Stage: 01 — <title>
Outcome: <observable result>
Included scope: <approved work>
Explicit exclusions: <boundaries>
Prerequisites: <completed or unresolved dependencies>
Interfaces and affected areas: <paths, systems, contracts>
Component boundary: <the single isolated subcomponent>
Implementation approach: <focused steps for one implementation agent>
Validation and acceptance: <commands and observations>
Risks and constraints: <known concerns>
User decisions: <explicit approvals>
Open questions: <none or unresolved list>
Small isolated subplan: <yes | no; if no, proposed split>
Approval state: <final | provisional>
Validated code baseline: <parent commit inspected>
Validated upstream stages: <stage IDs and merge commits>
Provisional upstream assumptions: <none | exact outcomes, contracts, and decisions expected from active prerequisites>
Compatibility result: <passed | blocked>
Inherited contracts and decisions: <only context implementation must preserve>
Relevant paths: <paths whose upstream change invalidates this gate>
Invalidation conditions: <specific events requiring renewed planning>
Ready for implementation: <yes | no>
```

The planning chat owns the semantic dependency and cross-stage compatibility check. It stops after the handoff and must not implement its decision. The controller writes the result into `Gate Handoffs > Planning To Implementation`, verifies explicit user approval, updates every affected plan section, and appends a revision note. When a prerequisite is still active, record its expected outcome, contracts, and decisions under `Provisional upstream assumptions` and set the stage to `Provisionally approved`. Mark the stage `Ready` only when the handoff is final, every prerequisite is complete, compatibility passed, and `Small isolated subplan: yes`. Split and renumber an oversized stage before implementation.

The controller updates `docs/plans/PLAN-1234/stages/STAGE-01.md` from the handoff and keeps its concise master-plan summary and link aligned. If a stage document is missing, repair the plan structure before implementation. Do not let the planning chat edit either document itself.

## Finalize Provisional Approval With A Delta Gate

When the last active prerequisite of a `Provisionally approved` stage completes, run the pre-stage planning gate again with `gpt-5.6-sol` at `xhigh`, but limit it to the delta between:

- the recorded provisional upstream assumptions; and
- the prerequisite's final merge commit, `Review To Controller` handoff, changed contracts, decisions, and relevant-path diff.

Do not repeat the full stage review, reread unrelated completed stages, or reopen decisions that the final prerequisite result did not affect.

If the final upstream result satisfies the provisional assumptions, update the validated baseline and upstream merge commits, record `Final delta result: Compatible` and `User reapproval required: No`, change `Approval state` to `Final`, and mark the stage `Ready` without asking the user to approve it again.

If the delta changes or contradicts the approved scope, outcome, interface, dependency, validation, or inherited decision, record `Final delta result: Conflict` and `User reapproval required: Yes`. Resume the existing `PLAN-1234 Stage 01 Planning` chat, explain only the material differences in simple English, and obtain explicit user sign-off before updating the stage and marking it `Ready`. Do not dispatch implementation while that decision remains open.

## Carry Context Forward Without Repeating Discovery

Treat a completed gate as authoritative for the concern it owns:

- The pre-stage planning gate owns upstream dependency analysis, inherited-contract discovery, and cross-stage compatibility.
- The implementation agent owns the approved code change and focused tests. It may inspect code needed to implement the stage, but must not repeat the planning gate's upstream audit or compatibility analysis.
- The implementation reviewer owns independent review of the approved stage diff and its verification. It must not recreate the planning investigation merely because it is a new agent.
- The controller owns handoff persistence and freshness checks.

Before dispatching implementation or review, the controller checks the recorded code baseline, upstream merge commits, and relevant paths. Replacing a provisional prerequisite hash with its final merge hash triggers the focused delta gate above, not a full review. A final handoff is invalid only when a relevant upstream path, inherited contract, recorded decision, dependency result, or upstream stage changed; a merge or rebase exposed a conflict; or new evidence directly contradicts the certification. Plan-document status commits and unrelated parent-branch changes do not invalidate it.

When the handoff remains valid, downstream agents must trust it and start their own gate's work. Do not announce or perform another broad discovery pass. When a final handoff is invalid, return to `PLAN-1234 Stage 01 Planning`, refresh only the affected certification and user decisions, and then resume downstream work.

## Implement Through A Child PR

For a ready stage:

1. Mark the stage `In progress` on the parent plan branch, commit, and push that state.
2. Create `codex/plan-1234-stage-01` from the latest parent plan branch.
3. Open a draft stage PR targeting the parent plan branch, titled `[PLAN-1234/S01] <stage title>`. Link the parent PR, master plan, and stage document; copy only the approved stage scope and acceptance criteria needed for review.
4. Start a separate implementation subagent named `PLAN-1234 Stage 01 Implementation`.
5. Use `gpt-5.6-sol` at `high` for Laravel implementation.
6. Give the implementer the current stage document, its `Planning To Implementation` handoff, and only the linked project guidance needed for implementation. Tell it the upstream compatibility result is certified and must not be reinvestigated unless an invalidation condition is observed.

One stage document, one implementation subagent, and one child PR form the subplan boundary. If implementation requires multiple independent agents or produces independently mergeable components, stop and split the stage through its planning gate instead of coordinating a large mixed stage.

Treat approved scope as a hard boundary. Implementers must avoid open-ended audits, opportunistic refactors, adjacent fixes, broad cleanup, unrelated dependency changes, and unapproved redesign. They may make only the smallest enabling changes, focused tests, and required documentation needed for the approved outcome.

When implementation finishes, require a compact handoff containing the implementation commits, changed paths, tests and evidence, scope deviations, and new discoveries. The controller records it in `Gate Handoffs > Implementation To Review`; do not ask the reviewer to reconstruct this context from the full history.

If materially broader work is required, stop and return:

```text
Scope expansion required for: PLAN-1234 Stage 01
Blocked outcome: <what cannot finish>
Evidence: <why>
Proposed movement: <specific added or changed scope>
Impact if declined: <narrow alternative or remaining gap>
Work completed: <concise status>
```

The controller resumes `PLAN-1234 Stage 01 Planning` with `gpt-5.6-sol` at `xhigh`, explains the proposed movement to the user, records the decision, updates scope and acceptance in the stage document and every affected master-plan section, and only then authorizes continued implementation. The implementer must not approve or perform the expansion first.

## Require Independent Review

After implementation and focused validation finish:

- Mark the stage `In review`.
- Start a different subagent named `PLAN-1234 Stage 01 Review` using `gpt-5.6-sol` at `xhigh`.
- Give it the current stage document, the planning certification, the `Implementation To Review` handoff, and the stage diff. Do not give it unrelated completed-stage documents.
- Require it to compare the complete stage diff with the approved outcome, scope, exclusions, interfaces, tests, documentation, and acceptance criteria.
- Require it to confirm the PR still represents only the approved small, isolated subplan and has not accumulated another component or outcome.
- Require it to run proportionate verification independently and report commands and results.
- Fail the gate for regressions, incomplete paths, missing evidence, or any scope escape. Do not turn review into an open-ended audit.
- Do not let the reviewer implement fixes or approve expanded scope. Return in-scope corrections to implementation; route broader changes through the `gpt-5.6-sol` `xhigh` planning chat.

Require the reviewer to return the review result, exact commits reviewed, independent verification, and findings. The controller records this in `Gate Handoffs > Review To Controller` and uses it for stage closure without repeating the review.

Do not merge a failed stage. Mark it `In progress`, correct it, then require another independent review pass.

## Close Each Stage Before Planning The Next

After review passes:

1. Confirm the reviewed commits are exactly what the stage PR will merge.
2. Merge the stage PR into the parent plan branch using project conventions.
3. Update the stage document with checked progress, decisions, discoveries, approved scope movement, and validation evidence. Update the master plan with the concise stage outcome and every cross-stage or overall effect.
4. Mark the stage `Complete`, update `Current stage`, and append a revision note.
5. Update the parent PR's short stage/child-PR summary and push all plan updates.
6. Finalize any provisionally approved dependent stage through its focused delta gate. Open a new planning chat only for the next stage that has not already been provisionally reviewed.

Do not consider a stage closed until its plan update is durable on the parent branch.

## Complete The Parent Plan

After all stages are complete:

- Set the plan to `In review` and run combined validation against the plan's final acceptance criteria.
- Require an independent `gpt-5.6-sol` `xhigh` review of the integrated parent diff when project rules or risk justify it.
- Update `Purpose / Big Picture` with the achieved result, remaining gaps, and lessons learned; finalize evidence and revision notes.
- Set `Status: Complete`, set `Current stage: Complete`, and make the parent PR ready for review.
- Do not merge the parent PR into its target branch or release the result unless the user explicitly requested that final action or established project workflow clearly grants that authority.

The controller may finish only when no delegated work remains and the parent PR accurately reflects the completed or precisely blocked state.
