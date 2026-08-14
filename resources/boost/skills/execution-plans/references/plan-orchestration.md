# ExecPlan Orchestration

Use this reference only when orchestrating, executing, resuming, or completing an existing plan.

## Contents

- Keep the main task as controller
- Use official names
- Establish or resume the parent PR
- Plan one stage with the user
- Optimize the critical path without weakening gates
- Implement through a child PR
- Require independent review
- Close each stage and dispatch the next ready stage
- Complete the parent plan

## Keep The Main Task As Controller

- Resolve the requested entry from `docs/planlist.md`, then read its `docs/plans/<PLAN-ID>.md` master document and current stage document. Read a completed stage document only when the current stage names it as a direct dependency or its handoff links a required contract or decision. Do not load every stage by default.
- Treat the main task as controller and orchestrator, and run it with `gpt-5.6-sol` at `high`. It owns plan state, branches, PRs, task dispatch, handoffs, stage transitions, and final completion. If the task mechanism cannot provide the exact configuration, use the nearest available setting and record the fallback before dispatching work.
- Keep the controller open while any planning chat, implementation subagent, review subagent, or stage PR it started remains active or awaits the user.
- Keep orchestration as the controller's primary role for the lifetime of the plan. When a human asks a question, answer it clearly, apply any resulting decision to the plan when relevant, and then resume orchestration from the current state. A question, status request, or brief discussion does not pause orchestration, transfer ownership, or turn the controller into a general-purpose side chat unless the human explicitly changes or stops the task.
- After responding to the human, recheck active delegated work, pending gates, readiness transitions, CI or PR state, and the next controller action. Continue waiting or dispatching as appropriate; do not end the controller merely because the immediate question was answered.
- While delegated work runs, continue safe controller work: provisionally plan later stages, plan independent stages, prepare controller-owned documentation, monitor CI, and prepare the next focused delta gate. Never start a dependent implementation before its prerequisite merges and its delta gate passes.
- Treat receipt of every required handoff as a controller gate. Do not infer delivery from a side task reaching a final answer, and do not advance the stage until the controller has actually received the handoff through the correct channel.
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

Preserve the full four-digit plan ID and two-digit stage number. Maintain only one active implementation task and one active independent review task per stage, and keep both available through correction cycles until the stage merges. Resume them by default; use a replacement only under the correction-context rules below. Do not vary the planning-chat name because the controller may need to resume it for a scope decision.

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

Commit controller-owned plan status, decisions, and stage-document updates directly to the parent plan branch. Keep implementation code on the stage branch. Give implementers and reviewers the current stage document and preceding handoff rather than the complete plan history.

Record the exact parent commit from which each stage branch starts. While implementation or review is active, synchronize that branch with the parent only when a parent change touches a relevant path, changes an inherited contract or decision, creates a merge conflict, or invalidates recorded verification. Unrelated documentation, status, or other-stage changes may wait until stage closure; their existence alone must not trigger a merge, rebase, test run, or renewed review.

## Plan One Stage With The User

Review stages in dependency order, but allow the next stage's planning chat to run while its prerequisite is being implemented or reviewed. A stage may become `Provisionally ready` before a named upstream result merges only when its design is complete, every material decision is accepted, it is small and isolated, and no open question remains. It must not enter implementation until the named condition clears and the focused final delta gate passes.

For the current stage:

- Set its state to `Planning` in the plan document.
- Create or resume the dedicated user-facing side chat titled exactly `PLAN-1234 Stage 01 Planning`.
- Use `gpt-5.6-sol` at `xhigh`. This is the pre-stage/subplan review gate; `Ultra` is reserved for the separate overall initial plan and stage-map creation process. If the task mechanism cannot provide the exact configuration, use the nearest available setting and tell the controller which fallback applied.
- Give it the master plan's concise stage map, the current stage document, direct dependency documents, explicitly relevant contracts and decisions, relevant project guidance, and repository access. Do not give it every completed stage or a predetermined conclusion.
- Keep it read-only. It may investigate and discuss but must not edit files, implement, create PRs, start implementers, or merge anything.
- Require it to use the user-facing decision-brief method below and formulate the stage decision with the user.
- Require it to verify that the stage is one small, isolated subplan with one coherent outcome, one component boundary, one implementation agent, one child PR, and focused verification. Ask: `Can one implementation agent implement, verify, and hand off this outcome in one focused working session and one understandable PR?` If not, formulate the split with the user and return separate proposed stages instead of approving an oversized stage. Treat separate models, workflows, interfaces, independently useful outcomes, or unrelated validation strategies as strong evidence that a split is required.
- Require it to identify every complete user or system journey the stage can affect, inspect the relevant existing E2E scenarios, decide whether they remain sufficient, and include any required scenario addition or adjustment in the approved stage scope. If execution must wait for a later stage or environment, name that gate and owner explicitly.

### Run A Guided Decision Review

Use this protocol in every user-facing planning or scope-decision review, including a resumed review caused by scope movement or a material delta conflict. It does not apply to an independent implementation reviewer, which reviews the approved contract and code without reopening settled product decisions or questioning the user.

1. Start with a short orientation. State the proposed outcome, why it matters, the current position, the important boundaries, and one representative real-world example.
2. Build and maintain a private decision register with separate entries for inherited decisions, verified facts, genuinely open choices, risks, failure cases, and exclusions. Use it to guide the conversation; do not make the user read internal bookkeeping.
3. Ask exactly one genuine decision question per turn, in plain English. Before asking, explain the consequences and tradeoffs and give a realistic example. Never require the user to interpret code, schemas, hashes, or internal names to answer.
4. If the user asks for clarification, answer it first, then repeat only the still-pending question. Do not introduce another decision in the same turn.
5. Record each answer immediately as settled. Do not reopen it unless new evidence directly contradicts the basis on which it was accepted.
6. If a new issue appears, add it to the decision register, preserve the current pending question, and resume the ordered sequence without losing earlier answers.
7. After the final open decision is answered, present one complete user-facing decision brief. Include all outcomes, material decisions, examples, failure cases, risks, exclusions, stage splits, and readiness consequences.
8. Ask for explicit approval only after the user has seen that complete brief.
9. After approval, create the separate technical execution handoff and deliver it to the controller. Do not use the technical handoff as the user review.

For a resumed review, ask only about the new or invalidated decisions. Carry forward every settled decision that remains supported by current evidence.

### Separate The User Review From The Execution Handoff

Whenever the user must review or approve a plan, design, stage, policy, workflow, interface, or technical decision, first provide a user-facing decision brief. Do not present the formal execution handoff as the review.

Write the review in plain English at the user's level of technical knowledge:

- Lead with the proposed outcome and why it matters.
- Organize the review around the decisions the user is being asked to approve. Choose headings that fit the subject; do not force topic-specific headings from an unrelated example.
- Explain what will happen, who or what is affected, important boundaries, failure cases, and observable consequences.
- Preserve every material rule, tradeoff, risk, exclusion, and unresolved question.
- Use short sentences and one idea per bullet.
- Define unfamiliar terms on first use.
- Describe behaviour and consequences before implementation mechanics.
- Do not make the user interpret repository paths, class names, schemas, enums, internal identifiers, commit hashes, validation commands, context-routing instructions, or handoff metadata unless one is itself a material decision.
- When exact technical detail matters, state its plain-English meaning first, followed by the exact detail in a short technical note.
- Avoid both extremes: do not replace the review with a high-level summary, and do not bury the decisions inside an engineering specification.
- End with what is settled, what remains open, and exactly what approval or choice is needed.

Use progressive disclosure:

1. Give the short orientation, then work through one open decision at a time.
2. Answer clarification requests and expand individual areas without adding a second decision question.
3. After the last decision, present the complete plain-English decision brief.
4. Ask for explicit approval of that complete brief.
5. After approval, produce the full structured execution handoff separately.

The execution handoff must retain every precise interface, path, identifier, validation requirement, risk, assumption, readiness field, invalidation condition, and context-routing instruction. Readability in the user review must never remove required detail from the durable handoff.

Before sending a review, check:

- Can the user understand it on the first read?
- Is it clear what they are approving?
- Are all material consequences and boundaries visible?
- Are internal implementation details included only where they affect the decision?
- Is the detailed handoff separate?

If any answer is no, rewrite the review before sending it.

The following structure is an execution record for agents and the controller. It must not be copied verbatim into the user-facing review.

The planning chat returns:

```text
Plan: PLAN-1234
Stage: 01 — <title>
Outcome: <observable result>
Included scope: <approved work>
Explicit exclusions: <boundaries>
Affected complete journeys: <journeys or none with evidence-based rationale>
Existing E2E coverage assessment: <scenarios inspected and sufficiency result>
Required E2E scenario changes: <additions or adjustments | none with rationale>
E2E readiness disposition: <existing coverage sufficient | scenario changes in scope | deferred to named owner/gate | blocked>
Prerequisites: <completed or unresolved dependencies>
Interfaces and affected areas: <paths, systems, contracts>
Component boundary: <the single isolated subcomponent>
Implementation approach: <focused steps for one implementation agent>
Validation and acceptance: <commands and observations>
Risks and constraints: <known concerns>
User decisions: <explicit approvals>
Open questions: <none or unresolved list>
Guided decision review: <complete | incomplete>
Final decision brief approved: <yes | no>
Small isolated subplan: <yes | no; if no, proposed split>
Implementation readiness: <not ready | provisionally ready | ready>
Remaining gate: <exact focused gate | none>
Automatic promotion: <yes | no>
User reapproval required: <no | no unless the precise invalidation condition occurs | yes and the precise conflict>
Validated code baseline: <parent commit inspected>
Validated upstream stages: <stage IDs and merge commits>
Provisional upstream assumptions: <none | exact outcomes, contracts, and decisions expected from active prerequisites>
Compatibility result: <passed | blocked>
Inherited contracts and decisions: <only context implementation must preserve>
Relevant paths: <paths whose upstream change invalidates this gate>
Invalidation conditions: <specific events requiring renewed planning>
```

## Deliver Every Delegated Handoff Explicitly

Codex side tasks are separate peer tasks. Their final answers are not automatically returned to the controller merely because they were created from a delegation.

When a delegation or prompt contains `<codex_delegation source_thread_id="…">`, a `source_thread_id`, controller task ID, or equivalent return-task identifier, treat that identifier as the required delivery destination:

1. Finish the gate handoff in the side task.
2. Use the task-messaging tool to send the complete handoff explicitly to that controller task ID.
3. Check that the tool call succeeded and record the destination and successful delivery in the side task.
4. Only then report the gate task as complete.

Do not treat a user-facing planning or review task like an internal subagent with automatic parent delivery. Do not rely on the handoff remaining visible only in the side task, and do not ask the human to relay it manually when task messaging is available.

If the messaging tool is unavailable, the destination is invalid, or delivery fails, do not declare completion. Preserve the handoff in the side task, report `Handoff delivery: blocked` with the controller task ID and exact failure, and retry when possible. The controller must remain open and must not authorize the next gate until delivery succeeds.

Internal subagents may return results through their native parent channel when that mechanism actually supplies the result to the controller. The completion requirement is successful controller receipt, not use of one particular tool. When `source_thread_id` identifies a separate Codex task, explicit task messaging is mandatory.

The planning chat owns the semantic dependency and cross-stage compatibility check. It stops only after the handoff has been delivered successfully to the controller and must not implement its decision. The controller writes the received result into `Gate Handoffs > Planning To Implementation`, verifies explicit user approval, updates every affected plan section, records the delivery receipt, and appends a revision note.

Before accepting a planning handoff, the controller verifies that:

- `Guided decision review` is `complete`;
- the review began with the required orientation;
- each open decision was discussed one at a time with its consequences and an example;
- settled answers were preserved unless contradictory evidence invalidated them;
- the final decision brief covered every material outcome, boundary, failure case, risk, exclusion, stage split, and readiness consequence;
- the stage's affected complete journeys and E2E coverage decision are explicit, with required scenario work in scope or assigned to a named later gate;
- the user explicitly approved that complete brief; and
- the separate technical handoff was delivered successfully.

If any item is missing, return the handoff to the planning task for completion. Do not authorize implementation from an incomplete guided review.

Classify readiness literally:

- Use `not ready` when a substantive user decision, open question, stage split, technical prerequisite, or material conflict remains. Set `Automatic promotion: No` and state what further planning or user input is required.
- Use `provisionally ready` only when planning is otherwise complete and implementation waits solely for a named upstream result or scheduling-time freshness check. Record that exact check under `Remaining gate`, the expected outcomes under `Provisional upstream assumptions`, `Automatic promotion: Yes`, and `User reapproval required: No — unless <precise invalidation condition>`. Set the stage status to `Provisionally ready`.
- Use `ready` only when all prerequisites and focused gates have passed, compatibility passed, `Small isolated subplan: yes`, and the E2E assessment is complete with any required scenario work included or assigned to an explicit executable gate. Set the stage status to `Ready`, `Remaining gate: None`, and authorize implementation dispatch.

Split and renumber an oversized stage before implementation. Never encode a provisionally ready stage as merely not ready.

The controller updates `docs/plans/PLAN-1234/stages/STAGE-01.md` from the handoff and keeps its concise master-plan summary and link aligned. If a stage document is missing, repair the plan structure before implementation. Do not let the planning chat edit either document itself.

## Finalize Provisional Readiness With A Delta Gate

When the named upstream condition for a `Provisionally ready` stage clears—or the stage reaches its recorded scheduling-time freshness point—run the pre-stage planning gate again with `gpt-5.6-sol` at `xhigh`, but limit it to the delta between:

- the recorded provisional upstream assumptions; and
- the prerequisite's final merge commit, `Review To Controller` handoff, changed contracts, decisions, and relevant-path diff.

Do not repeat the full stage review, reread unrelated completed stages, or reopen decisions that the final prerequisite result did not affect.

If the final upstream result satisfies the provisional assumptions, update the validated baseline and upstream merge commits, record `Final delta result: Compatible`, `Implementation readiness: ready`, `Remaining gate: None`, `Automatic promotion: Yes`, and `User reapproval required: No`. Mark the stage `Ready` and dispatch it when scheduled without asking the user to approve it again.

If the delta changes or contradicts the approved scope, outcome, interface, dependency, validation, or inherited decision, record `Final delta result: Conflict`, `Implementation readiness: not ready`, `Automatic promotion: No`, and `User reapproval required: Yes — <precise conflict>`. Resume the existing `PLAN-1234 Stage 01 Planning` chat, explain only the material differences in simple English, and obtain explicit user sign-off before updating the stage and marking it `Ready`. Do not dispatch implementation while that decision remains open.

When user sign-off is required, resume the guided decision review for the conflicting or newly open decisions only. Do not repeat orientation or questions for unaffected settled decisions. The final decision brief must still show the complete resulting outcome, but may identify unchanged decisions concisely as carried forward.

## Carry Context Forward Without Repeating Discovery

Treat a completed gate as authoritative for the concern it owns:

- The pre-stage planning gate owns upstream dependency analysis, inherited-contract discovery, and cross-stage compatibility.
- The implementation agent owns the approved code change and focused tests. It may inspect code needed to implement the stage, but must not repeat the planning gate's upstream audit or compatibility analysis.
- The implementation reviewer owns independent review of the approved stage diff and its verification. It must not recreate the planning investigation merely because it is a new agent.
- The controller owns handoff persistence and freshness checks.

Before dispatching implementation or review, the controller checks the recorded code baseline, upstream merge commits, and relevant paths. Replacing a provisional prerequisite hash with its final merge hash triggers the focused delta gate above, not a full review. A final handoff is invalid only when a relevant upstream path, inherited contract, recorded decision, dependency result, or upstream stage changed; a merge or rebase exposed a conflict; or new evidence directly contradicts the certification. Plan-document status commits and unrelated parent-branch changes do not invalidate it.

When the handoff remains valid, downstream agents must trust it and start their own gate's work. Do not announce or perform another broad discovery pass. When a final handoff is invalid, return to `PLAN-1234 Stage 01 Planning`, refresh only the affected certification and user decisions, and then resume downstream work.

Treat the current stage handoffs as its minimum context package. Before dispatch, give every agent three explicit reading lists:

- `Must read`: the current handoff and exact files needed to perform this gate;
- `Read only if needed`: narrowly relevant supporting context to open only when a named condition occurs; and
- `Do not reread`: completed stages, settled investigations, or broad repository material already certified by an earlier gate.

Record settled decisions separately from unresolved assumptions. Agents must preserve settled decisions and must not reopen them unless new evidence directly conflicts. Pair relevant paths and inherited contracts with an invalidation map stating exactly which handoff section, decision, or evidence must be refreshed if each item changes; otherwise trust it.

When the parent branch advances, the controller supplies a short delta summary containing the old and new parent commits, changed paths, affected contracts or decisions, triggered invalidation rules, and evidence that remains valid. Do not ask a downstream agent to rediscover this delta.

## Optimize The Critical Path Without Weakening Gates

Every stage still requires an approved small scope, cleared prerequisites, one implementation owner, one child PR, focused validation, and an independent review. Optimize waiting and repetition, never those gates.

### Reuse Correction Context

- Use fresh agents where independence or a new work boundary matters: the initial planning chat for a stage, the first implementation of that stage, and its initial independent review. Reuse existing agents only for continuity within that stage, such as clarified planning or scope decisions, in-scope corrections, and focused re-review.
- Keep the implementation agent available until the stage PR merges. Send every in-scope review correction back to that same agent by default because it already owns the implementation context.
- Start a replacement implementer only when the original is unavailable, the approved stage scope changed materially, or its handoff became invalid. Never run two implementation agents for the same stage concurrently.
- Keep the independent reviewer available through corrections. Prefer the same reviewer for re-review so independence from implementation remains intact while review context is preserved.
- On re-review, inspect the correction, confirm it resolves the finding, check the correction for new problems, and rerun only affected or invalidated verification. Do not repeat the full first review while its evidence remains valid.
- Require a fresh full review when a correction materially changes the stage design or invalidates most previous review evidence. The reviewer must always remain different from the implementation agent.

When any agent must be replaced, give its replacement a compact handoff containing the current branch, starting parent and candidate commits, approved outcome and exclusions, settled decisions, unresolved assumptions, completed and remaining work, changed paths, failing or pending tests, evidence still valid, triggered invalidations, environmental gaps, and known traps. Do not make the replacement reconstruct current state from the full history.

### Use A Validation Ladder

During implementation:

1. Run the smallest relevant test while developing.
2. Run the complete focused stage suite before review.
3. Add or adjust the E2E scenarios required by the stage's approved readiness assessment, and run them at the earliest gate where the complete journey is executable.
4. Run changed-path static analysis and formatting once the candidate is ready.
5. Run specialist database, browser, or external-service CI only when local checks cannot prove the required behaviour or project rules require it.

During independent review, rerun proportionate focused verification independently; do not default to the whole repository suite. At plan completion, run the integrated or full-plan validation. Require a full repository suite for an individual stage only when its risk, shared surface, or project rules genuinely justify it.

### Preserve Fresh Evidence

- Record the exact commit and paths covered by every validation result.
- Invalidate evidence only when a later commit changes covered production behaviour, the test itself, a shared dependency used by that behaviour, or relevant configuration or schema.
- Preserve unaffected evidence across corrections. Documentation-only, plan-status-only, and unrelated-path commits do not invalidate passing implementation tests or completed review work.
- Record invalidated evidence and remaining environmental verification explicitly instead of silently rerunning everything.

### Reduce Branch And CI Churn

Prefer this stage push cadence: push once to create the draft PR, develop and validate locally, push one locally green review candidate, then push only review corrections. Do not require a push after every internal implementation slice unless the user requests that visibility.

Where existing repository controls permit it, cancel superseded CI runs, avoid duplicate equivalent workflows on both `push` and `pull_request`, and use path filters for specialist checks. Do not change shared CI configuration merely to accelerate one stage unless that change is inside the approved scope. A newer commit does not by itself require rerunning unaffected workflows.

## Implement Through A Child PR

For a ready stage:

1. Mark the stage `In progress` on the parent plan branch, commit, and push that state.
2. Create `codex/plan-1234-stage-01` from the latest parent plan branch and record that exact commit as `Starting parent commit`.
3. Push the initial stage branch and open a draft stage PR targeting the parent plan branch, titled `[PLAN-1234/S01] <stage title>`. Link the parent PR, master plan, and stage document; copy only the approved stage scope and acceptance criteria needed for review.
4. Start a separate implementation subagent named `PLAN-1234 Stage 01 Implementation`.
5. Use `gpt-5.6-sol` at `high` for Laravel implementation.
6. Give the implementer the approved outcome and exclusions, exact starting commit, inherited contracts, relevant paths, invalidation conditions, affected complete journeys, E2E coverage assessment and required scenario work, current stage document, `Planning To Implementation` handoff, and only the linked project guidance needed for implementation. Tell it the upstream compatibility result is certified and must not be reinvestigated unless an invalidation condition is observed.

One stage document, one implementation subagent, and one child PR form the subplan boundary. If implementation requires multiple independent agents or produces independently mergeable components, stop and split the stage through its planning gate instead of coordinating a large mixed stage.

Keep that implementation subagent available until the stage merges. When independent review returns an in-scope defect, send the finding and affected evidence back to the same agent. Replace it only under the correction-context rules above.

Treat approved scope as a hard boundary. Implementers must avoid open-ended audits, opportunistic refactors, adjacent fixes, broad cleanup, unrelated dependency changes, and unapproved redesign. They may make only the smallest enabling changes, focused tests, and required documentation needed for the approved outcome.

When implementation finishes, require a compact handoff containing the validated candidate commit, implementation commits, changed paths, a changed-path-to-test map, evidence-covered paths, focused tests and results, E2E scenarios added or adjusted, E2E results or a named remaining execution gate, static analysis and formatting, CI evidence, known environmental gaps, remaining environmental verification, invalidated evidence, correction cycle, scope deviations, and new discoveries. The controller records it in `Gate Handoffs > Implementation To Review`; do not ask the reviewer to reconstruct this context from the full history.

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
- Keep this reviewer available until the stage merges so it can perform focused re-review after corrections.
- Give it the current stage document, the planning certification, the `Implementation To Review` handoff, its changed-path-to-test map, and the stage diff. Do not give it unrelated completed-stage documents or ask it to reconstruct implementation history.
- Require it to compare the complete stage diff with the approved outcome, scope, exclusions, interfaces, tests, documentation, and acceptance criteria.
- Require it to confirm that the stage identified all affected complete journeys, judged existing E2E coverage reasonably, made every required scenario change, and supplied current evidence or a valid named remaining execution gate.
- Require it to confirm the PR still represents only the approved small, isolated subplan and has not accumulated another component or outcome.
- Require it to run proportionate verification independently and report commands and results.
- Fail the gate for regressions, incomplete paths, missing evidence, or any scope escape. Do not turn review into an open-ended audit.
- Do not let the reviewer implement fixes or approve expanded scope. Return in-scope corrections to implementation; route broader changes through the `gpt-5.6-sol` `xhigh` planning chat.

Require the reviewer to return the review result, exact commits reviewed, evidence reused, correction coverage, invalidated evidence, independent verification, E2E readiness review, remaining environmental verification, and findings. Require successful delivery to the controller under the explicit handoff-delivery gate above. The controller records the received result and delivery receipt in `Gate Handoffs > Review To Controller` and uses it for stage closure without repeating the review.

Do not merge a failed stage. Mark it `In progress`, return in-scope findings to the existing implementer, then return the correction to the existing independent reviewer for a focused pass. Start a replacement implementer or fresh full review only under the correction-context rules above.

## Close Each Stage And Dispatch The Next Ready Stage

After review passes:

1. Confirm the reviewed commits are exactly what the stage PR will merge.
2. Confirm the E2E assessment is resolved: existing coverage is demonstrably sufficient or every required scenario change is present and reviewed. Allow deferred execution only when its scenario, expected result, owner, and executable later gate are explicit.
3. Merge the stage PR into the parent plan branch using project conventions.
4. Persist the returned review handoff and update the stage document with checked progress, decisions, discoveries, approved scope movement, validation evidence, and the final E2E readiness disposition and evidence. Update the master plan with the concise stage outcome, every cross-stage or overall effect, and the current complete-journey coverage map.
5. Mark the stage `Complete`, update `Current stage`, append a revision note, then commit and push this closure as one controller-owned update so the review handoff and completed state are durable.
6. Immediately run the focused delta gate for every `Provisionally ready` dependant whose named condition just cleared. Promote each compatible dependant to `Ready` without asking the user again.
7. Dispatch the next ready stage in dependency order through the normal `In progress` transition. Open a new planning chat only for a stage that has not already reached `Provisionally ready` or `Ready`.
8. Update the parent PR's short stage/child-PR summary. This dashboard update may follow dispatch and must not create an avoidable idle gap in the next ready implementation.

Do not consider a stage closed until its plan update is durable on the parent branch.

## Complete The Parent Plan

After all stages are complete:

- Set the plan to `In review` and run integrated or full-plan validation against the plan's final acceptance criteria. Execute the plan-wide E2E journey map, confirm every affected complete journey has sufficient current coverage, and resolve every deferred E2E obligation. This is where broad cross-stage verification normally belongs.
- Require an independent `gpt-5.6-sol` `xhigh` review of the integrated parent diff when project rules or risk justify it.
- Update `Purpose / Big Picture` with the achieved result, remaining gaps, and lessons learned; finalize evidence and revision notes.
- Set `Status: Complete`, set `Current stage: Complete`, and make the parent PR ready for review.
- Do not mark the plan `Complete` while any journey lacks sufficient E2E coverage or any deferred E2E execution remains unresolved.
- Do not merge the parent PR into its target branch or release the result unless the user explicitly requested that final action or established project workflow clearly grants that authority.

The controller may finish only when no delegated work remains and the parent PR accurately reflects the completed or precisely blocked state.
