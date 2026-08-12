# ExecPlan Format

Use this reference when creating, cataloguing, reading, or revising an ExecPlan.

## Contents

- Catalogue plans
- Start every plan with metadata
- Maintain the master plan
- Create a document for every stage
- Apply formatting rules

## Catalogue Plans

Keep `docs/planlist.md` as an index, not a status dashboard or full specification. Use one linked entry per plan:

```markdown
# Plan List

- [PLAN-1234 — Revolut integration](plans/PLAN-1234.md)
- [PLAN-1235 — Subscription reconciliation](plans/PLAN-1235.md)
```

Sort by plan ID unless the repository already establishes another stable order. Do not copy status, current stage, PR metadata, or plan prose into the list.

## Start Every Plan With Metadata

Store each master plan at `docs/plans/<PLAN-ID>.md` and start it with:

```markdown
# PLAN-1234 — Revolut integration

Status: Ready to orchestrate

Summary: Integrate Revolut account and transaction data.

Parent PR: Not created

Current stage: Not started
```

Use these plan statuses:

- `Draft`
- `Ready to orchestrate`
- `In progress`
- `Blocked`
- `In review`
- `Complete`

Use `Parent PR: Not created` before orchestration. Once created, record a Markdown link to the parent PR. Make `Current stage` human-readable, such as `Stage 01 — Planning`, `Stage 01 — In progress`, or `Stage 01 — Complete; Stage 02 not started`.

## Maintain The Master Plan

Keep the master plan concise. It owns the overall purpose, stage map, cross-stage state, dependencies and decisions, final acceptance, and integrated evidence. After the metadata, maintain these sections.

### Purpose / Big Picture

Explain why the work matters, what someone can do afterwards, how to observe it, and how the relevant files, modules, and concepts fit together. Define every non-obvious term. At completion, add what was achieved, what remains, and what was learned.

### Stages

Describe the intended stages, their order, direct dependencies, outcomes, and final contribution. Give every stage a two-digit number and action-oriented title. A future stage may be provisional until its planning chat, but its purpose and prerequisites must be clear.

Use these stage states:

- `Unplanned`
- `Planning`
- `Ready`
- `In progress`
- `In review`
- `Complete`
- `Blocked`

Summarize and link every stage in this form:

```markdown
### Stage 01 — Connect Revolut accounts

Status: Planning

Details: [STAGE-01](PLAN-1234/stages/STAGE-01.md)

Outcome: A user can connect and refresh a Revolut account.

Depends on: None
```

Each stage must be independently verifiable and leave the repository in a coherent state. Prefer additive migrations and temporary parallel paths when they keep tests passing and reduce risk. Use an explicit prototype stage when feasibility is uncertain, with promotion or rejection criteria.

### Progress

Track stage-level progress and final integrated validation only. Put implementation chunks in the relevant stage document. Use checkboxes only in `Progress`. Keep completed items checked with a timestamp and short outcome when useful. Split partially completed work into a checked item and a remaining unchecked item.

### Surprises & Discoveries

Record only discoveries that affect more than one stage or the overall plan. Keep stage-local findings in the stage document. Include concise evidence where useful.

### Decision Log

Record cross-stage and plan-level decisions in this form:

```markdown
- Decision: <what was decided>
  Rationale: <why>
  Date/Author: <date and actor>
```

Include stage-boundary changes, changed dependencies, and decisions affecting more than one stage or final acceptance. Keep stage-local decisions and scope movement in the stage document, promoting them here only when they affect the wider plan.

### Validation and Acceptance

Describe how to exercise the completed system and what a human should observe. Give exact commands, working directories, inputs, outputs, relevant test names, and environmental assumptions. Prefer observable behaviour over internal implementation claims.

### Artifacts and Notes

Keep compact integrated evidence that helps someone verify or resume the plan. Keep stage-only evidence in the stage document. Do not paste full logs or large diffs.

### Interfaces and Dependencies

Describe the overall stage dependency graph and interfaces shared across stages. Put detailed files, symbols, libraries, services, and contracts in the stage that owns them.

### Revision Notes

Append a dated note after every material master-plan revision describing what changed and why. Do not use revision notes instead of updating the affected sections.

## Create A Document For Every Stage

Create a document for every stage, including simple and provisional stages. Use `docs/plans/<PLAN-ID>/stages/STAGE-<NN>.md`; for example, `docs/plans/PLAN-1234/stages/STAGE-01.md`. Do not add stage documents to `docs/planlist.md` or give them separate plan IDs.

Use this standard structure:

```markdown
# PLAN-1234 Stage 01 — Connect Revolut accounts

Status: Planning

Parent plan: [PLAN-1234](../../PLAN-1234.md)

Stage PR: Not created

## Outcome

Explain the independently observable result this stage must produce.

## Scope

### Included

State the approved work.

### Excluded

State the hard boundaries.

## Prerequisites, Interfaces, and Dependencies

Record prerequisite stages, affected paths, services, contracts, libraries, and symbols.

## Implementation Slices

Describe one coherent slice or several explicitly independent slices.

## Progress

- [ ] Record concrete work and its verification signal.

## Surprises & Discoveries

Record stage-local findings with concise evidence.

## Decision Log

Record stage-local decisions, including approved scope movement.

## Validation and Acceptance

Give exact commands, inputs, expected outputs, and observable acceptance.

## Artifacts and Notes

Keep compact stage evidence and handoff material.

## Revision Notes

Record each material stage-document change and why it was made.
```

A provisional stage document must at least state its purpose, dependencies, and known boundaries. Before the stage becomes `Ready`, record its observable acceptance, included scope, explicit exclusions, prerequisites, affected interfaces, implementation slices, validation commands, risks, and user-approved decisions.

The planning chat returns the approved stage content, but the controller creates and updates the file. The master plan remains authoritative for overall status, stage order, dependencies, cross-stage decisions, and final acceptance. Each stage document is authoritative for that stage's detailed scope and execution record. Immediately reflect any stage change that affects another stage or the overall plan in the master plan.

## Formatting Rules

Write normal Markdown. Use prose for explanations and reserve checklists for `Progress`. Use fenced blocks only for commands, transcripts, payloads, diffs, or small code excerpts.

Use full repository-relative paths and precise symbol names. Repeat every assumption needed to resume. Do not rely on prior chats, inaccessible documents, or unexplained external guidance. Link relevant prior work, but summarize all context required by the plan.

The master plan and all linked stage documents are the source of truth before and during orchestration. Parent and stage PR bodies are operational dashboards: link to and summarize the documents without becoming divergent copies.
