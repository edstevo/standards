---
name: skill-maintenance
description: Create, revise, split, rename, and maintain focused Laravel Boost app skills in `.ai/skills/*/SKILL.md`, including trigger-rich descriptions, progressive disclosure references, scripts, workflow checklists, gotchas, invariants, validation loops, and update triggers. Use when app skills or skill references need to be created or kept current after code changes.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: documentation
  triggers: app skills, skill maintenance, SKILL.md, .ai/skills, skill description, skill triggers, progressive disclosure, skill references, skill scripts, validation loops
---

# Skill Maintenance

Use this skill to create and maintain focused app skills under `.ai/skills`.

Never edit compiled `.agents` output directly. Update `.ai/skills/{skill-name}/SKILL.md` and run the app's normal Boost update process when needed.

## Load References

- Load `references/skill-template.md` when creating or restructuring an app skill.
- Load `references/description-trigger-guidance.md` when writing, renaming, or tuning skill names, descriptions, and triggers.
- Load `references/scripts-and-validation.md` when a skill needs scripts, multi-step workflow checklists, validation loops, or plan-validate-execute guidance.

## Skill Boundaries

- Do not create one monolithic app knowledge skill.
- Prefer focused skills by module, workflow, integration, or coherent responsibility.
- Do not create one arbitrary domain skill while similar domains have no app knowledge coverage unless that domain is genuinely exceptional.
- Rename a skill when the old name no longer matches the module or workflow.
- Rewrite descriptions and triggers when agents would miss the skill or activate it for the wrong work.
- Split old skills when they start covering unrelated modules, workflows, integrations, or conventions.
- Remove or redirect obsolete skills when the behavior no longer exists.

## Context Budget

Spend context wisely:

- Add what the agent would not know without the skill.
- Omit generic Laravel, PHP, or framework advice.
- Keep `SKILL.md` concise and execution-focused.
- Link exact docs as source of truth; avoid `docs/README.md` unless the task is docs navigation.
- Do not repeat a docs page inside the skill.
- Move long examples, schemas, API details, and deep workflow notes into `references/`.
- Tell the agent exactly when to load each reference.

For every section, ask: would a future agent likely get this wrong without this information? If no, cut or shorten it.

## App Skill Checklist

Before finishing a skill change:

- [ ] Source lives under `.ai/skills/{skill-name}/SKILL.md`.
- [ ] `.agents` output was not edited directly.
- [ ] The skill is focused, not a monolith.
- [ ] Comparable domains/workflows are not left unevenly covered without a reason.
- [ ] The description is trigger-rich and under 1024 characters.
- [ ] `SKILL.md` is concise and points to references only when needed.
- [ ] Read-first docs are exact source docs, not broad README indexes.
- [ ] Gotchas, invariants, contracts, and update triggers are captured.
- [ ] Multi-step workflows include a checklist.
- [ ] Fragile workflows include a validation loop.
- [ ] Repeated parsing, validation, transformation, or generation logic uses a script when useful.
- [ ] Affected app skills were reviewed after complex work.

## LLM And Structured Output Skills

When a skill covers LLM behavior, document the contract the code relies on:

- prompt or tool responsibility
- schema file, DTO, or parser that defines the response shape
- guaranteed fields, enum values, and nullability
- validation boundary
- fallback behavior for genuinely invalid responses
- tests that prove schema and parser behavior

If a schema or structured output contract exists, rely on it. Do not teach agents to guess impossible response shapes with broad defensive branches.
