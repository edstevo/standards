---
name: execution-plans
description: Create, catalogue, maintain, and orchestrate self-contained ExecPlans for complex features and significant refactors. Use when drafting or revising a plan document, maintaining `docs/planlist.md`, looking up a plan by ID or title, or when the user asks to orchestrate, execute, resume, or review a staged plan through a controller, planning side chats, implementation subagents, independent review, a parent PR, and stage PRs.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: planning
  triggers: ExecPlan, execution plan, plan list, plan document, PLAN ID, orchestrate plan, execute plan, resume plan, pull request plan, PR plan, complex feature, significant refactor, design doc, milestone, living plan, staged plan
---

# Execution Plans

Use this skill for two separate processes:

1. **Plan authoring:** create or revise a durable ExecPlan and its plan-list entry.
2. **Plan orchestration:** execute an existing plan through staged planning, implementation, review, and pull requests.

Do not recreate or substantially redesign an existing plan merely because the user asks to orchestrate it.

## Load References

- Load `references/plan-format.md` whenever creating, cataloguing, reading, or revising an ExecPlan.
- Load both `references/plan-format.md` and `references/plan-orchestration.md` whenever the user asks to orchestrate, execute, resume, or complete an existing plan.

## Use Stable Plan Records

Maintain three required layers:

- `docs/planlist.md` is the short, searchable index. It contains only a linked plan ID and title for each active or retained plan.
- `docs/plans/PLAN-1234.md` is the authoritative master plan. It contains plan metadata, the overall purpose, a concise linked stage map, cross-stage progress and decisions, final validation, and overall evidence.
- `docs/plans/PLAN-1234/stages/STAGE-01.md` is the required detailed document for Stage 01. Every stage has one; the master plan links to it and retains only the stage's state, purpose, dependencies, and outcome.

Treat every stage as a subplan. Each subplan must be a small, isolated, independently implementable component of the overall plan with one coherent outcome, one implementation agent, one child PR, and focused verification. Split an oversized stage before it can become `Ready`.

Carry context between gates through the current stage document's versioned handoffs. The pre-stage gate owns cross-stage compatibility and dependency discovery. Implementation and review must trust that certification and must not repeat it unless a recorded invalidation condition occurs.

Optimize the critical path without weakening gates. Treat each stage handoff as the minimum context package, with explicit reading boundaries, settled decisions, unresolved assumptions, commit-scoped evidence, and invalidation rules. Keep the stage's implementation agent and independent reviewer available until the stage merges, reuse them for in-scope correction cycles while their handoffs remain valid, and avoid synchronizing unrelated parent changes into active stage work.

Allow a later stage to become `Provisionally ready` while a named upstream change or scheduling delay remains. Use the canonical readiness states `not ready`, `provisionally ready`, and `ready`; never present incomplete planning and completed-but-awaiting-freshness-check planning as equivalent. After the named condition clears, run a focused `xhigh` delta gate. Promote a compatible stage to `Ready` automatically without repeating planning or requesting approval; return to the user only when the delta creates a material conflict.

Use stable IDs in the form `PLAN-1234`: uppercase `PLAN-` followed by a zero-padded, monotonically increasing four-digit number. Find the highest existing number in `docs/planlist.md` and `docs/plans/`, then allocate the next number. Never reuse or renumber an ID. Keep the filename ID-only so title changes do not rename it.

Resolve user references case-insensitively. Accept the exact ID, a bare number, or a unique title phrase; for example, `PLAN-1234`, `plan 1234`, and `the Revolut plan` may all resolve to the same document. Ask the user only when a title phrase matches more than one plan.

## Author Plans Separately

When creating a plan:

- Inspect the repository and formulate the design before declaring it ready.
- Use `gpt-5.6-sol` at `Ultra` for overall initial plan creation and the initial stage/subplan map.
- Break the work into the smallest useful subplans that can be implemented and reviewed independently while leaving the repository coherent. Record dependencies instead of combining dependent components into one large stage.
- Create or update the index entry, authoritative master plan, and one detailed document for every defined stage together.
- Keep `Status: Draft` until the plan is self-contained, staged, and demonstrably executable; then use `Status: Ready to orchestrate`.
- Do not create a branch or PR merely because the plan was authored. Orchestration owns that transition unless the user explicitly combines both processes.

## Orchestrate Existing Plans

When the user says `orchestrate PLAN-1234` or refers to a unique plan title:

- Resolve and read the existing plan; do not create a replacement.
- Make the main task the long-lived `gpt-5.6-sol` `high` controller and follow `references/plan-orchestration.md` literally.
- Keep orchestration as the controller's primary role. Answer human questions when they arise, then immediately return to coordinating active gates, agents, PRs, readiness, and stage transitions unless the human explicitly changes or stops the orchestration request.
- Search for an existing parent PR before creating branches or PRs. If one exists, check out its head branch and reread the latest master and stage documents from that branch; create the parent integration branch and draft PR only when no matching parent PR exists.
- Run each pre-stage planning and approval gate with the user in a dedicated `gpt-5.6-sol` `xhigh` side chat. That chat returns the approved stage plan to the controller and never implements it. When its delegation supplies a controller `source_thread_id`, require the side chat to send the handoff explicitly to that task with the task-messaging tool and confirm delivery before declaring completion; never assume a separate Codex task reports back automatically.
- Implement each approved stage through one `gpt-5.6-sol` `high` subagent and one child stage PR. Keep that implementation agent available for in-scope review corrections until the stage merges.
- Require a separate `gpt-5.6-sol` `xhigh` reviewer before merging the stage PR into the parent plan branch. Prefer the same independent reviewer for focused re-review after corrections while its prior evidence remains valid.
- Update the authoritative master plan and current stage document after every stage with progress, decisions, discoveries, scope movement, and evidence before advancing or finalizing the next stage.
- Pass each agent only the current stage document, the preceding gate handoff, and explicitly referenced context. Do not make every agent reread the full master plan or all completed stages.
- Treat handoff delivery as part of every delegated gate. A side task must not report success until its required handoff reached the controller; on missing tooling or failed delivery, keep the handoff available and report the precise delivery blocker.
- Preserve test and review evidence that still covers the candidate commit and relevant paths. Do not merge unrelated parent changes, rerun unaffected checks, or repeat broad discovery merely because another commit exists.
- Provisionally plan safe later work while a stage is active, then immediately dispatch the next ready dependant after a compatible delta gate and durable stage closure.
- Keep the controller open while any planning chat, implementation subagent, review subagent, or stage PR it owns remains active.

## Preserve The Outcome

Every ExecPlan must remain self-contained enough that a novice can understand the intended journey from the plan document and can implement any stage marked ready using only the plan and working tree. Future stages may remain provisional, but their purpose, ordering, dependencies, and contribution to final acceptance must remain clear.

Require observable behaviour, exact verification commands, and expected results. Define unfamiliar terms in plain English. Record why the design changed, not only what changed.

## Use The Required Model Routing

| Work | Model | Reasoning |
|---|---|---|
| Overall initial plan and stage/subplan map creation | `gpt-5.6-sol` | `Ultra` |
| Plan controller/orchestrator | `gpt-5.6-sol` | `high` |
| Pre-stage/subplan planning and approval gate | `gpt-5.6-sol` | `xhigh` |
| Laravel implementation | `gpt-5.6-sol` | `high` |
| Implementation review | `gpt-5.6-sol` | `xhigh` |

Treat these as the default execution settings, not suggestions to choose a cheaper or faster model. If the task mechanism cannot provide the exact model or reasoning level, use the nearest available configuration and tell the controller which fallback applied before relying on its output.
