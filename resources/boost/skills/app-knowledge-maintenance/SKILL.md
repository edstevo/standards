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

## Load References

- Load `references/knowledge-layout.md` when adding, splitting, pruning, or reorganizing app docs or app skills.
- Load it when you see duplicated docs/skills, a broad `docs/README.md`, or one domain skill without comparable coverage elsewhere.

## Knowledge Layers

- `AGENTS.md` / `.ai/guidelines`: short always-loaded rules every agent needs.
- `docs`: source of truth for stable app behavior, organized into focused directories.
- `.ai/skills/*/SKILL.md`: short execution playbooks for modules, workflows, integrations, or conventions.
- `.ai/skills/*/references`: conditional detail loaded only when needed.
- Key class and method docblocks: implementation intent for non-obvious jobs, actions, observers, transitions, and workflow internals.

Do not edit compiled `.agents` files directly. Update source files in `.ai`, `docs`, and code.

## Use The Right Skill

- Activate `process-documentation` when workflow docs, process docs, Mermaid flowcharts, key files/tests, or implementation docblocks may need updating.
- Activate `skill-maintenance` when app skills, skill descriptions, triggers, references, scripts, checklists, or validation loops may need updating.

Use both when the code change affects a workflow and the agent-facing playbook for that workflow.

## Token Budget

- Keep always-loaded guidance tiny.
- Keep `SKILL.md` action-oriented; move deep detail into references.
- Keep `docs/README.md` as a map, not a second source of truth.
- Link to exact docs or references; do not duplicate their explanations.
- Split large docs by directory: domains, processes, integrations, contracts, or LLM behavior.

For every section, ask: would a future agent likely get this wrong without this information? If no, cut or shorten it.

## Post-change Checklist

After meaningful code changes, run this checklist before finishing:

- [ ] Did stable app behavior change?
- [ ] Did a workflow, state transition, queue job, observer, integration, API contract, LLM schema, or domain invariant change?
- [ ] Is there an existing docs page that should be updated?
- [ ] Is there an existing app skill that should be updated?
- [ ] Should a focused skill be created, renamed, split, or pruned?
- [ ] Would creating one domain skill leave similar domains undocumented for no good reason?
- [ ] Should deeper material move into a skill `references/` file?
- [ ] Did any skill duplicate a docs page or ask agents to load a broad README unnecessarily?
- [ ] Did key files or key tests change?
- [ ] Does a Mermaid flowchart need updating?
- [ ] Do class or method docblocks now misrepresent the implementation?
- [ ] Did you discover durable knowledge future agents should not need to rediscover?

If yes to any item, update the relevant docs, skills, references, and docblocks in the same change.
