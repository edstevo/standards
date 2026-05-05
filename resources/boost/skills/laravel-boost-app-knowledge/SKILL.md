---
name: laravel-boost-app-knowledge
description: Create and maintain Laravel Boost application knowledge skills in `.ai/skills/*/SKILL.md` so future agents understand complex modules, workflows, integrations, LLM schemas, cross-file architecture, app conventions, key files, and key tests. Use when adding or refactoring stable application behavior that future agents must not rediscover.
license: MIT
metadata:
  domain: laravel
  role: specialist
  scope: documentation
  triggers: Laravel Boost, .ai skills, app knowledge, application architecture, module documentation, workflow documentation, integration contracts, LLM schemas, agent memory, boost:update
---

# Laravel Boost App Knowledge

Use this skill when complex application knowledge should be preserved for future agents.

Laravel Boost custom app skills live in `.ai/skills/{skill-name}/SKILL.md`. They are compiled into `.agents` when `php artisan boost:update` runs.

Never edit `.agents` directly. Write source skills in `.ai/skills`.

## Default Choice

Prefer app skills over always-loaded guidelines.

- Use `.ai/skills/*/SKILL.md` for module, workflow, integration, LLM, and cross-file architecture knowledge that is needed only for matching work.
- Use `.ai/guidelines/*` only for short, cross-cutting conventions every agent needs upfront.
- Keep app knowledge close to the code change. Update the relevant skill in the same PR as the behavior change.

## When To Create Or Update

Create or update an app skill when you add, refactor, or discover stable knowledge about:

- module responsibilities and boundaries
- workflows, jobs, events, observers, or state transitions
- integrations, API contracts, webhooks, queues, or external side effects
- LLM prompts, schemas, tools, structured outputs, or guaranteed response shapes
- data flow across models, services, UI, queues, and external systems
- key files and key tests future agents must inspect before making related changes
- repeated project conventions that are too specific for package-level guidelines
- facts the agent missed at first that would have prevented mistakes, reduced rework, or sped up the task if they had been documented

Do not create app skills for one-off implementation notes, temporary bugs, historical commentary, or information that is obvious from one nearby file.

## App Skill Structure

Use these sections unless the app already has a stricter local convention:

```md
---
name: module-or-workflow-name
description: Use when working on [specific module/workflow/integration], including [important triggers and terms].
---

# Module Or Workflow Name

## Purpose

What this part of the app does and why it exists.

## When To Use

The tasks, files, routes, models, events, jobs, UI areas, or keywords that should trigger this skill.

## Architecture Map

The main moving pieces and how they fit together.

## Key Files

The files future agents should inspect first.

## Data Flow

How data enters, moves through, and leaves this module or workflow.

## Invariants And Contracts

Rules that must stay true, including API contracts, event payloads, state transitions, LLM schemas, and guaranteed output shapes.

## Testing And Verification

The key tests, fakes, factories, scenario builders, commands, or manual checks that prove this area still works.

## Update Triggers

The code or behavior changes that require this skill to be updated.
```

## Writing Rules

- Inspect the code before writing. Skill content must be grounded in current implementation.
- Separate confirmed facts from inference. Do not present guesses as rules.
- Keep language concise and stable. Prefer current behavior, contracts, and invariants over implementation history.
- Do not include secrets, credentials, private tokens, customer data, or environment-specific values.
- Link concepts to key files and tests, but avoid long inventories that become stale.
- Remove or revise obsolete guidance when behavior changes.
- If you discover app knowledge late in a task that would have prevented problems or made the work faster, add it to the relevant app skill for the next agent.
- If the skill grows large, split stable detail into directly linked `references/` files under the skill.

## LLM And Structured Output Rules

When an app uses an LLM, document the contract the code relies on:

- prompt or tool responsibility
- schema file or DTO that defines the response shape
- guaranteed fields, enum values, and nullability
- validation or parsing boundary
- fallback behavior for genuinely invalid responses
- tests that prove the schema and parser behavior

If a schema or structured output contract exists, rely on that contract. Do not add broad defensive branches that guess impossible response shapes.

## Checklist

- [ ] App skill source lives under `.ai/skills/{skill-name}/SKILL.md`.
- [ ] `.agents` is not edited directly.
- [ ] The skill has trigger-rich frontmatter.
- [ ] Purpose, usage, architecture, files, flow, contracts, tests, and update triggers are covered.
- [ ] LLM schemas or integration contracts are documented when relevant.
- [ ] The skill is updated in the same PR as the related code change.
