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

Use stable IDs in the form `PLAN-1234`: uppercase `PLAN-` followed by a zero-padded, monotonically increasing four-digit number. Find the highest existing number in `docs/planlist.md` and `docs/plans/`, then allocate the next number. Never reuse or renumber an ID. Keep the filename ID-only so title changes do not rename it.

Resolve user references case-insensitively. Accept the exact ID, a bare number, or a unique title phrase; for example, `PLAN-1234`, `plan 1234`, and `the Revolut plan` may all resolve to the same document. Ask the user only when a title phrase matches more than one plan.

## Author Plans Separately

When creating a plan:

- Inspect the repository and formulate the design before declaring it ready.
- Prefer the strongest intelligence model available at Ultra reasoning, or the future equivalent maximum reasoning tier, when model selection is available for a dedicated authoring task.
- Create or update the index entry, authoritative master plan, and one detailed document for every defined stage together.
- Keep `Status: Draft` until the plan is self-contained, staged, and demonstrably executable; then use `Status: Ready to orchestrate`.
- Do not create a branch or PR merely because the plan was authored. Orchestration owns that transition unless the user explicitly combines both processes.

## Orchestrate Existing Plans

When the user says `orchestrate PLAN-1234` or refers to a unique plan title:

- Resolve and read the existing plan; do not create a replacement.
- Make the main task the long-lived controller and follow `references/plan-orchestration.md` literally.
- Search for an existing parent PR before creating branches or PRs. If one exists, check out its head branch and reread the latest master and stage documents from that branch; create the parent integration branch and draft PR only when no matching parent PR exists.
- Plan each stage with the user in a dedicated Ultra side chat. That chat returns the approved stage plan to the controller and never implements it.
- Implement the approved stage through separate Extra High subagents and a child stage PR.
- Require a separate Extra High review before merging the stage PR into the parent plan branch.
- Update the authoritative master plan and current stage document after every stage with progress, decisions, discoveries, scope movement, and evidence before starting the next stage-planning chat.
- Keep the controller open while any planning chat, implementation subagent, review subagent, or stage PR it owns remains active.

## Preserve The Outcome

Every ExecPlan must remain self-contained enough that a novice can understand the intended journey from the plan document and can implement any stage marked ready using only the plan and working tree. Future stages may remain provisional, but their purpose, ordering, dependencies, and contribution to final acceptance must remain clear.

Require observable behaviour, exact verification commands, and expected results. Define unfamiliar terms in plain English. Record why the design changed, not only what changed.
