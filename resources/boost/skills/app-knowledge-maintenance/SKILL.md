---
name: app-knowledge-maintenance
description: Keep Laravel application knowledge self-maintaining after meaningful code changes. Use when a change affects stable app behavior, workflows, modules, integrations, API contracts, LLM schemas, domain invariants, docs, app skills, skill references, or implementation docblocks that future agents must rely on.
license: MIT
metadata:
  domain: laravel
  role: specialist
  scope: documentation
  triggers: app knowledge, documentation maintenance, meaningful code change, workflow changed, integration changed, LLM schema, domain invariant, docs update, skills update
---

# App Knowledge Maintenance

Use this skill to decide which source-of-truth docs, app skills, references, and docblocks must change after meaningful Laravel app work.

## Knowledge Layers

- `AGENTS.md` / `.ai/guidelines`: short always-loaded rules every agent needs.
- `docs`: concise source of truth for architecture, workflows, state transitions, integrations, key rules, and important edge cases.
- `.ai/skills/*/SKILL.md`: focused execution playbooks for modules, workflows, integrations, or conventions.
- `.ai/skills/*/references`: deeper supporting material loaded only when the skill says it is needed.
- Key class and method docblocks: implementation intent for non-obvious jobs, actions, observers, transitions, and workflow internals.

Do not edit compiled `.agents` files directly. Update source files in `.ai`, `docs`, and code.

## Use The Right Skill

- Activate `process-documentation` when workflow docs, process docs, Mermaid flowcharts, key files/tests, or implementation docblocks may need updating.
- Activate `skill-maintenance` when app skills, skill descriptions, triggers, references, scripts, checklists, or validation loops may need updating.

Use both when the code change affects a workflow and the agent-facing playbook for that workflow.

## Documentation Standard

Keep docs and skills complete enough to prevent wrong assumptions, but short enough for agents to load safely.

Prefer:
- short sections and clear headings
- bullet points over long prose
- flowcharts where they reduce explanation
- key rules, invariants, state transitions, and edge cases
- links to deeper references when needed

Avoid:
- long manuals
- duplicated explanations across docs and skills
- historical commentary
- generic Laravel or PHP advice
- documenting obvious code
- one huge process doc or one monolithic app skill

For every section, ask: would a future agent likely get this wrong without this information? If no, cut or shorten it.

## Post-change Checklist

After meaningful code changes, run this checklist before finishing:

- [ ] Did stable app behavior change?
- [ ] Did a workflow, state transition, queue job, observer, integration, API contract, LLM schema, or domain invariant change?
- [ ] Is there an existing docs page that should be updated?
- [ ] Is there an existing app skill that should be updated?
- [ ] Should a new focused skill be created, or should an existing one be renamed, split, or pruned?
- [ ] Should deeper material move into a skill `references/` file?
- [ ] Did key files or key tests change?
- [ ] Does a Mermaid flowchart need updating?
- [ ] Do class or method docblocks now misrepresent the implementation?
- [ ] Did you discover durable knowledge future agents should not need to rediscover?

If yes to any item, update the relevant docs, skills, references, and docblocks in the same change.

## Keep Boundaries Clean

- Docs are the digestible source of truth.
- Skills are execution playbooks that tell the agent how to work.
- References hold deeper supporting material.
- Docblocks explain non-obvious implementation intent.

Do not copy the same explanation into every layer. Link from skills to docs or references instead.
