---
name: execution-plans
description: Create, catalogue, maintain, or orchestrate staged ExecPlans for complex features and significant refactors. Use for plan documents, `docs/planlist.md`, PLAN IDs, staged implementation, planning reviews, parent PRs, child stage PRs, implementation, independent review, or resuming an existing plan.
---

# Execution Plans

Use one of two workflows:

1. **Authoring** creates or revises the durable plan and stage map.
2. **Orchestration** executes an existing plan. Never recreate it merely because execution was requested.

## Load Only The Reference For The Current Role

Do not read every reference.

| Current role or action | Read |
|---|---|
| Create a master plan | Copy `assets/master-plan.md`, then read `references/master-plan.md` |
| Catalogue, revise, or validate a master plan | `references/master-plan.md` |
| Create a stage document | Copy `assets/stage-document.md`, then read `references/stage-document.md` |
| Revise or validate a stage document | `references/stage-document.md` |
| Start, resume, monitor, or complete orchestration | `references/controller.md` |
| Warm the next likely stage-planning thread | `references/planning-warmup.md` |
| Conduct a stage planning review or scope decision | `references/stage-planning.md` and `references/stage-document.md` |
| Run a dispatch-time freshness check for an approved stage | `references/freshness-gate.md` and `references/stage-document.md` |
| Implement an approved stage or correct its code | `references/stage-implementation.md` |
| Independently review or re-review a stage | `references/stage-review.md` |

The controller reads the reference for the next gate immediately before dispatching or accepting that gate. Implementation and review agents receive the current stage document and handoff; they do not load authoring, planning, or controller references.

## Stable Records

- `docs/planlist.md`: ID-and-title index only.
- `docs/plans/PLAN-1234.md`: authoritative master plan.
- `docs/plans/PLAN-1234/stages/STAGE-01.md`: authoritative detailed stage record.
- Parent and child PR bodies: short operational dashboards linking to those documents.

Use IDs `PLAN-1234`: uppercase, zero-padded, monotonic, never reused or renumbered. Resolve exact IDs, bare numbers, and unique title phrases case-insensitively.

## Universal Gates

- Make every stage one small, coherent, independently implementable component with one implementation agent, one child PR, and focused verification. Split it before implementation if that boundary fails.
- Treat E2E readiness as mandatory for every stage and the complete plan. Identify affected complete journeys, assess existing scenarios, and own any required changes or named deferred execution gate.
- Keep approved scope hard. Implementation and review must not expand into audits, cleanup, refactors, or adjacent fixes. Route material expansion back through planning and human approval.
- Carry context through the current stage document and compact handoffs. Trust settled decisions and valid evidence until a recorded invalidation condition fires; reread relevant sources when context is missing, stale, contradicted, or transferred.
- Require explicit handoff delivery. A separate task is not complete until its handoff reaches the controller and delivery is confirmed.
- Keep only one substantive stage-planning task active across the plan. One next-stage thread may perform bounded preparation-only warm-up concurrently; it cannot ask decisions, edit plan documents, or become authoritative until activated. A focused freshness gate for an already approved stage is also a read-only compatibility audit, not planning.
- Keep implementation and independent-review tasks available for their stage's correction loops until merge, then close or archive tasks no longer needed.
- Keep the controller open while any owned task, handoff, stage PR, or gate remains active, queued, waiting, or unresolved. Report material events only and monitor quietly with compact state.
- Keep plan documents self-contained enough for a fresh agent to resume from the working tree without chat history. Self-contained means necessary context once, not repeated narrative.

## Assurance Ownership

| Concern | Owning gate |
|---|---|
| Product decisions, scope, dependencies, interfaces, and planned acceptance | Stage planning |
| Compatibility of an approved stage with a changed upstream baseline | Focused freshness gate |
| Code changes and candidate-scoped test evidence | Implementation |
| Correctness and scope judgment on the final diff | Independent review |
| Handoff receipt, state coherence, dispatch, and cleanup | Controller |
| Cross-stage journeys and deferred obligations | Final plan validation |

Each gate consumes the earlier owner's result. Do not repeat that work merely for confidence. Reopen it only when evidence is missing, contradictory, stale for a relevant path, or a recorded invalidation condition fires.

## Readiness

Use exactly:

- `not ready`: planning, authorization, scope, dependency, or a material decision remains unresolved.
- `provisionally ready`: planning and approval are complete; only a named upstream or scheduling freshness gate remains.
- `ready`: the remaining gate passed and implementation may start.

A compatible provisional delta promotes automatically. A contained upstream mismatch keeps the stage provisional behind a revised named gate. Return to the human only when the delta invalidates an approved decision, contract, scope, or validation requirement.

## Model Routing

| Work | Model | Reasoning |
|---|---|---|
| Initial overall plan and stage map | `gpt-5.6-sol` | `Ultra` |
| Controller/orchestrator | `gpt-5.6-sol` | `high` |
| Planning-thread warm-up | `gpt-5.6-sol` | `xhigh` |
| Stage planning and human approval | `gpt-5.6-sol` | `xhigh` |
| Focused dispatch-time freshness gate | `gpt-5.6-sol` | `xhigh` |
| Laravel implementation | `gpt-5.6-sol` | `high` |
| Independent implementation review | `gpt-5.6-sol` | `xhigh` |

Use the nearest available configuration only when the exact setting is unavailable, and report the fallback to the controller before relying on its output.
