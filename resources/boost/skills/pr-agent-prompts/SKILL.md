---
name: pr-agent-prompts
description: Create concise prompts for PR Agent from rough feature, bug, or refactor requests. Use when a user needs a short codebase-grounded prompt with current implementation, target behavior, out-of-scope work, and open questions, without creating or updating GitHub issues.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: planning
  triggers: PR Agent, pr-agent, prompt, brief prompt, issue prompt, current implementation, target behavior, scope, out of scope, open questions
---

# PR Agent Prompts

Use this skill to turn a rough request into a short prompt for PR Agent.

## Rules

- Do not create, update, comment on, or label GitHub issues.
- Inspect only enough code to ground `Current implementation`.
- Mark uncertain facts as suspected.
- Keep the prompt concise. PR Agent expands it into the full issue.
- Do not add plan, risks, affected areas, or key tests sections.

## Output

Use exactly these headings, in this order:

```md
## Current implementation

What happens today. Include known UI flows, URLs, jobs, labels, routes, models, logs, screenshots, or examples. Mark anything uncertain as suspected.

## Target behavior

What should happen instead. Include acceptance criteria, examples of correct behavior, and anything that must not happen.

## Scope

What is not in scope for this issue. Use `None` if there are no known exclusions.

## Open questions

Only list known product or technical decisions that would change the implementation. Use `None` if there are no known blockers.
```

## Checklist

- [ ] Extract the requested change and constraints.
- [ ] Inspect relevant code, docs, routes, UI labels, jobs, models, logs, tests, or examples only as needed.
- [ ] Write the four-section prompt.
- [ ] Use `None` for scope when there are no known exclusions.
- [ ] Use `None` for open questions when there are no known blockers.
