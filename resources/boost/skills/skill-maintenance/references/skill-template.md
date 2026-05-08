# Skill Template

Load this when creating or restructuring an app skill.

```md
---
name: focused-module-or-workflow
description: Use when working on [specific module/workflow/integration], including [important trigger terms].
---

# [Skill Name]

## Purpose

What this skill helps the agent do.

## When To Use

Specific tasks, files, routes, models, events, jobs, UI areas, integrations, or keywords that should trigger this skill.

## Read First

- `docs/domains/[domain]/[topic].md` when [specific condition]
- `docs/processes/[workflow].md` when [specific condition]
- `docs/integrations/[provider].md` when [specific condition]
- `references/[deeper-detail].md` when [specific condition]

## Workflow Checklist

- [ ] Inspect the source of truth.
- [ ] Update implementation.
- [ ] Update tests.
- [ ] Update docs, references, or docblocks.
- [ ] Run validation.

## Invariants And Gotchas

Non-obvious rules, contracts, state transitions, schema guarantees, and mistakes future agents might make.

## Validation Loop

Commands, tests, scripts, or manual checks to run until passing.

## Update Triggers

Changes that require this skill to be updated, renamed, split, or pruned.
```

## Template Rules

- Keep `SKILL.md` short and action-oriented.
- Link to exact docs as source of truth.
- Do not use `docs/README.md` as read-first material unless the task is docs navigation.
- Do not duplicate docs content in the skill.
- Move deep detail into references.
- Keep the skill focused on one coherent module, workflow, integration, or responsibility.
