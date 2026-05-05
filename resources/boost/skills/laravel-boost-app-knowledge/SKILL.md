---
name: laravel-boost-app-knowledge
description: Create and maintain Laravel Boost application knowledge skills in `.ai/skills/*/SKILL.md` so future agents understand complex modules, workflows, integrations, LLM schemas, cross-file architecture, app conventions, key files, and key tests. Use when adding or refactoring stable application behavior that future agents must not rediscover, and when shaping focused app skill descriptions, triggers, progressive disclosure, scripts, workflow checklists, validation loops, or splitting monolithic skills.
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
- Do not create one monolithic "app knowledge" skill. Break app knowledge down by module, workflow, integration, or other coherent responsibility.
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

## Workflow Checklist

For multi-step workflows, a checklist of the required sequence and validation gates.

## Invariants And Contracts

Rules that must stay true, including API contracts, event payloads, state transitions, LLM schemas, and guaranteed output shapes.

## Gotchas

Non-obvious facts that would prevent wrong assumptions, rework, or missed files.

## Testing And Verification

The key tests, fakes, factories, scenario builders, commands, manual checks, and validation loop that prove this area still works.

## Update Triggers

The code or behavior changes that require this skill to be updated.
```

## Skill Quality Rules

Spend context wisely:
- Add what the agent would not know without the skill: app conventions, domain procedures, non-obvious edge cases, contracts, and exact tools.
- Omit generic advice the agent already knows.
- Keep each skill a coherent unit of work. Split unrelated modules, workflows, or integrations into separate skills.
- Aim for moderate detail: enough to avoid mistakes, not an exhaustive manual.

Evolve skill boundaries:
- Treat skill names, descriptions, triggers, and sections as maintainable app knowledge, not permanent scaffolding.
- Rename a skill when the old name no longer matches the module or workflow.
- Rewrite the description and triggers when agents would miss the skill or activate it for the wrong work.
- Split an old skill into multiple focused skills when it starts covering unrelated modules, workflows, integrations, or conventions.
- Remove or redirect obsolete skills when the app no longer has the behavior they describe.

Use progressive disclosure:
- Keep `SKILL.md` focused on the instructions needed on every run.
- Move long examples, schemas, API notes, or deep workflow detail into directly linked `references/` files.
- Tell the agent exactly when to load each reference, for example: "Load `references/provider-webhooks.md` when changing webhook handling."

Optimize descriptions:
- Write the frontmatter `description` as "Use when..." guidance.
- Focus on user intent and task triggers, not internal implementation.
- Include adjacent trigger terms agents may see in real prompts.
- Keep the description specific enough to avoid false activation and under 1024 characters.

Use scripts when repeated logic should not be reinvented:
- Add scripts under `scripts/` when agents repeatedly parse, validate, transform, inspect, or generate the same kind of artifact.
- Document script usage in `SKILL.md` with relative paths from the skill root.
- Scripts should be non-interactive, support `--help`, produce useful errors, prefer structured output, and be idempotent where possible.

## Multi-step Workflow Checklists

For multi-step workflows, include an explicit checklist. This is required when order, dependencies, or validation gates matter.

```md
## Workflow Checklist

- [ ] Step 1: Inspect the source of truth.
- [ ] Step 2: Update the implementation.
- [ ] Step 3: Update related tests or fixtures.
- [ ] Step 4: Run the validation command.
- [ ] Step 5: Update this skill if new durable app knowledge was discovered.
```

Avoid vague checklist items such as "clean up" or "finish remaining work". Each item should be actionable and verifiable.

## Validation Loops

Every app skill for a fragile workflow should tell the agent how to validate and iterate:

1. Make the change.
2. Run the listed validation command, script, checklist, or targeted test.
3. If validation fails, read the failure, fix the issue, and run validation again.
4. Continue until validation passes or the blocker is explicit.

For risky or batch operations, prefer plan-validate-execute:
- create a plan or mapping
- validate it against the source of truth
- only then apply the change

## Writing Rules

- Inspect the code before writing. Skill content must be grounded in current implementation.
- Separate confirmed facts from inference. Do not present guesses as rules.
- Keep language concise and stable. Prefer current behavior, contracts, and invariants over implementation history.
- Do not include secrets, credentials, private tokens, customer data, or environment-specific values.
- Link concepts to key files and tests, but avoid long inventories that become stale.
- Remove or revise obsolete guidance when behavior changes.
- If you discover app knowledge late in a task that would have prevented problems or made the work faster, add it to the relevant app skill for the next agent.
- After completing complex work, review the affected app skills while the implementation context is fresh. Update, rename, split, or prune skills so future agents see the current module and workflow shape.
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
- [ ] The skill is scoped to a coherent module, workflow, integration, or responsibility instead of becoming a monolith.
- [ ] Skill names, descriptions, triggers, and boundaries still match the current app; old skills are renamed, rewritten, or split when needed.
- [ ] Affected app skills were reviewed after complex work was completed.
- [ ] Purpose, usage, architecture, files, flow, contracts, tests, and update triggers are covered.
- [ ] Multi-step workflows include an explicit checklist.
- [ ] Fragile workflows include a validation loop.
- [ ] Repeated parsing, validation, or generation logic is handled by a script when useful.
- [ ] LLM schemas or integration contracts are documented when relevant.
- [ ] The skill is updated in the same PR as the related code change.
