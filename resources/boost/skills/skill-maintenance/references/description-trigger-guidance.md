# Description And Trigger Guidance

Load this when creating, renaming, or tuning a skill's frontmatter.

## Description Rules

- Write the description as "Use when..." guidance.
- Focus on user intent and task triggers, not implementation details.
- Include real terms an agent may see: model names, workflow names, integrations, route areas, UI labels, jobs, events, schemas.
- Mention adjacent cases where the user may not name the domain directly.
- Keep it specific enough to avoid false activation.
- Keep it under 1024 characters.

## Good Shape

```yaml
description: Use when working on fulfillment routing, including routing jobs, fulfillment order state transitions, SendCloud shipment creation, routing exceptions, and tests that prove routing decisions.
```

## When To Revise

Revise names, descriptions, and triggers when:

- agents miss the skill during relevant work
- the skill activates for unrelated work
- the module or workflow has been renamed
- a skill was split into focused skills
- new important trigger terms appear in the codebase

Avoid adding one-off keywords from a single failure. Generalize the category of work instead.
