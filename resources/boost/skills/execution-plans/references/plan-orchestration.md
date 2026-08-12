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

- Resolve the requested entry from `docs/planlist.md`, then read its `docs/plans/<PLAN-ID>.md` master document and linked stage documents completely.
- Treat the main task as controller and orchestrator. It owns plan state, branches, PRs, task dispatch, handoffs, stage transitions, and final completion.
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

Preserve the full four-digit plan ID and two-digit stage number. If several bounded implementation subagents are justified, append `01`, `02`, and so on to the implementation task name. Do not vary the planning-chat name because the controller may need to resume it for a scope decision.

## Establish The Parent PR

The parent branch is the integration branch for the whole plan, and the parent PR targets the repository's normal default or user-specified target branch.

Before creating anything, search for an existing parent PR in this order:

1. Follow the PR link recorded in `Parent PR` when present.
2. Search open PRs for the exact plan ID in the title, such as `[PLAN-1234]`.
3. Search open PRs and remote branches for the official parent branch prefix `codex/plan-1234-`.
4. Check closed and merged PRs with that plan ID when no open match exists, so a completed or abandoned execution is not accidentally duplicated.

When exactly one active parent PR exists, treat it as the execution home. Fetch and check out its head branch, update it safely from its remote head, then reread the master plan and every linked stage document from that branch before evaluating status or starting work. The plan documents on the parent branch are authoritative; the PR body is the operational dashboard. Repair stale `Parent PR`, status, current-stage, or dashboard metadata instead of creating another PR.

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

Commit controller-owned plan status, decisions, and stage-document updates directly to the parent plan branch. Keep implementation code on the stage branch. Ensure each implementer and reviewer reads the latest parent-branch plan state, and synchronize the stage branch when parent updates affect its code or verification context.

## Plan One Stage With The User

Plan stages sequentially. Do not start the next stage-planning chat until the preceding stage PR is merged into the parent branch and the plan document has been updated and closed for that stage.

For the current stage:

- Set its state to `Planning` in the plan document.
- Create or resume the dedicated user-facing side chat titled exactly `PLAN-1234 Stage 01 Planning`.
- Use the strongest intelligence model currently available at Ultra reasoning, or the future equivalent maximum reasoning tier. If the task mechanism cannot set model or reasoning explicitly, use the strongest available configuration and tell the controller which fallback applied.
- Give it the authoritative plan, completed-stage outcomes, current-stage purpose and prerequisites, relevant project guidance, and repository access. Do not give it a predetermined conclusion.
- Keep it read-only. It may investigate and discuss but must not edit files, implement, create PRs, start implementers, or merge anything.
- Require it to explain material choices in simple English and formulate the stage decision with the user.

The planning chat returns:

```text
Plan: PLAN-1234
Stage: 01 — <title>
Outcome: <observable result>
Included scope: <approved work>
Explicit exclusions: <boundaries>
Prerequisites: <completed or unresolved dependencies>
Interfaces and affected areas: <paths, systems, contracts>
Implementation slices: <bounded work packages or one coherent slice>
Validation and acceptance: <commands and observations>
Risks and constraints: <known concerns>
User decisions: <explicit approvals>
Open questions: <none or unresolved list>
Ready for implementation: <yes | no>
```

The planning chat stops after the handoff. It must not implement its decision. The controller verifies explicit user approval, updates every affected plan section, appends a revision note, and marks the stage `Ready` only when the handoff is complete and its prerequisites are clear.

The controller updates `docs/plans/PLAN-1234/stages/STAGE-01.md` from the handoff and keeps its concise master-plan summary and link aligned. If a stage document is missing, repair the plan structure before implementation. Do not let the planning chat edit either document itself.

## Implement Through A Child PR

For a ready stage:

1. Mark the stage `In progress` on the parent plan branch, commit, and push that state.
2. Create `codex/plan-1234-stage-01` from the latest parent plan branch.
3. Open a draft stage PR targeting the parent plan branch, titled `[PLAN-1234/S01] <stage title>`. Link the parent PR, master plan, and stage document; copy only the approved stage scope and acceptance criteria needed for review.
4. Start a separate implementation subagent named `PLAN-1234 Stage 01 Implementation`.
5. Use the strongest suitable coding model available at Extra High reasoning, or the future equivalent tier. Ultra is reserved for planning and user decisions unless explicitly required.
6. Give the implementer the approved outcome, exact included scope, exclusions, interfaces, implementation slice, acceptance, tests, and project guidance.

One stage PR is the integration boundary. Use multiple implementation subagents only for clearly independent, non-overlapping slices, give each an isolated task boundary, and keep the controller responsible for combining their work. Never parallelize dependent slices or let several agents make uncoordinated overlapping edits.

Treat approved scope as a hard boundary. Implementers must avoid open-ended audits, opportunistic refactors, adjacent fixes, broad cleanup, unrelated dependency changes, and unapproved redesign. They may make only the smallest enabling changes, focused tests, and required documentation needed for the approved outcome.

If materially broader work is required, stop and return:

```text
Scope expansion required for: PLAN-1234 Stage 01
Blocked outcome: <what cannot finish>
Evidence: <why>
Proposed movement: <specific added or changed scope>
Impact if declined: <narrow alternative or remaining gap>
Work completed: <concise status>
```

The controller resumes `PLAN-1234 Stage 01 Planning` at Ultra, explains the proposed movement to the user, records the decision, updates scope and acceptance in the stage document and every affected master-plan section, and only then authorizes continued implementation. The implementer must not approve or perform the expansion first.

## Require Independent Review

After implementation and focused validation finish:

- Mark the stage `In review`.
- Start a different subagent named `PLAN-1234 Stage 01 Review` using the strongest suitable coding/review model at Extra High reasoning, or the future equivalent tier.
- Require it to compare the complete stage diff with the approved outcome, scope, exclusions, interfaces, tests, documentation, and acceptance criteria.
- Require it to run proportionate verification independently and report commands and results.
- Fail the gate for regressions, incomplete paths, missing evidence, or any scope escape. Do not turn review into an open-ended audit.
- Do not let the reviewer implement fixes or approve expanded scope. Return in-scope corrections to implementation; route broader changes through the Ultra planning chat.

Do not merge a failed stage. Mark it `In progress`, correct it, then require another independent review pass.

## Close Each Stage Before Planning The Next

After review passes:

1. Confirm the reviewed commits are exactly what the stage PR will merge.
2. Merge the stage PR into the parent plan branch using project conventions.
3. Update the stage document with checked progress, decisions, discoveries, approved scope movement, and validation evidence. Update the master plan with the concise stage outcome and every cross-stage or overall effect.
4. Mark the stage `Complete`, update `Current stage`, and append a revision note.
5. Update the parent PR's short stage/child-PR summary and push all plan updates.
6. Only then open the next stage-planning chat.

Do not consider a stage closed until its plan update is durable on the parent branch.

## Complete The Parent Plan

After all stages are complete:

- Set the plan to `In review` and run combined validation against the plan's final acceptance criteria.
- Require an independent Extra High review of the integrated parent diff when project rules or risk justify it.
- Update `Purpose / Big Picture` with the achieved result, remaining gaps, and lessons learned; finalize evidence and revision notes.
- Set `Status: Complete`, set `Current stage: Complete`, and make the parent PR ready for review.
- Do not merge the parent PR into its target branch or release the result unless the user explicitly requested that final action or established project workflow clearly grants that authority.

The controller may finish only when no delegated work remains and the parent PR accurately reflects the completed or precisely blocked state.
