---
name: github-issue-briefs
description: Create or refine a GitHub issue into a pr-agent-ready implementation brief by reading the request, inspecting this repository, identifying affected areas and key tests, surfacing open questions, and writing the result into GitHub. Use before adding pr-agent-ready to new, thin, ambiguous, or multi-stage issues.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: planning
  triggers: create issue, refine issue, improve brief, implementation brief, pr-agent-ready, staged issue, affected areas, key tests, open questions
---

# GitHub Issue Briefs

Use this skill when work needs to become a clear GitHub issue before pr-agent implements it.

## Goal

Create or refine a concise, codebase-grounded brief inside GitHub.

The final issue should:
- be implementation-ready for pr-agent
- use only the eight brief headings listed below
- explain current implementation and target behavior without repetition
- include a Plan section with the single-stage or multi-stage implementation checklist
- identify likely codebase impact as affected areas
- separate confirmed impact from informed inference
- ask only questions that materially affect implementation
- fold acceptance criteria into Target behavior
- fold edge cases and uncertainty into Risks
- finish with Affected areas and Key tests

Do not add pr-agent-ready yourself. Always get confirmation from a human that the brief is ready before the pr-agent-ready label is added.

## When To Use

Use this skill when:
- creating a new GitHub issue from a rough request
- rewriting a thin or ambiguous issue
- preparing an issue for pr-agent-ready
- splitting work into staged PRs
- the likely change spans multiple files, layers, models, jobs, UI, or integrations
- codebase impact needs to be mapped before implementation begins

Do not use this skill when:
- the issue is already implementation-ready and the user wants coding to begin immediately
- the user only wants a private local note and does not want GitHub updated

## Workflow

1. Establish the source request.
    - If creating a new issue, extract the goal, constraints, desired behavior, non-goals, examples, screenshots, logs, and known context from the user request.
    - If refining an existing issue, read the issue first and preserve useful original context.
    - Identify contradictions, missing details, and assumptions that could change implementation shape.

2. Inspect the codebase before writing the brief.
    - Search for domain terms, UI labels, route names, model names, errors, jobs, actions, observers, integrations, database tables, and existing tests.
    - Trace the smallest useful implementation path.
    - Check sibling files and existing tests so affected areas and key tests are grounded in real code.
    - Label impact as confirmed when directly seen in code, or inferred when based on surrounding structure.

3. Decide whether the work should be single-stage or multi-stage.
    - Use a single issue brief when the work can be implemented safely in one PR.
    - Use staged work when the implementation naturally needs multiple PRs, when risk is lower with ordered slices, or when later work depends on earlier merged foundations.
    - Each stage must be independently implementable and reviewable.
    - Stage briefs must be concrete enough for pr-agent to implement using only the current unchecked stage.

4. Write or update the GitHub issue.
    - If creating a new issue, create it in the target proprietary repository.
    - If refining an existing thin issue, prefer updating the issue body.
    - If rewriting the body would erase useful history, add a comment titled Refined implementation brief.
    - Do not move the brief into a local markdown handoff.

5. Use exactly these headings, in this order:
    - Current implementation
    - Target behavior
    - Open questions
    - Scope
    - Plan
    - Risks
    - Affected areas
    - Key tests

6. Fill the sections.
    - Current implementation: what the code does now, with confirmed vs inferred details.
    - Target behavior: the desired behavior and acceptance criteria.
    - Open questions: only questions that affect scope, architecture, data shape, sequencing, or user-visible behavior.
    - Scope: included work, non-goals, dependencies, assumptions, and sequencing constraints.
    - Plan: a concrete checklist of implementation work. Break the work into manageable chunks; use one checklist for single-stage work, or a staged checklist when the work should be split across multiple PRs.
    - Risks: edge cases, regressions, data concerns, operational risks, and uncertainty.
    - Affected areas: files, modules, models, routes, jobs, observers, integrations, database tables, and tests likely affected.
    - Key tests: the smallest confidence-building test set.

7. Write the Plan section.

The Plan section is always required.

For single-stage work, use a normal checklist:

```md
- [ ] First concrete implementation step
- [ ] Second concrete implementation step
- [ ] Update or add the relevant tests
```

For multi-stage work, put the stage checklist in the Plan section and use this exact stage block format:

```md
<!-- pr-agent-stages:start -->
- [ ] Stage 1: Short stage title
  Concrete implementation brief for stage 1.

- [ ] Stage 2: Short stage title
  Concrete implementation brief for stage 2.
<!-- pr-agent-stages:end -->
```

Rules for stages:
- Put stages inside the Plan section.
- Keep each stage brief scoped to one PR.
- Do not include closing keywords that would close the parent issue from a stage PR.
- Make dependencies explicit, for example "depends on Stage 1 being merged".
- Do not create vague stages such as "Clean up" or "Finish remaining work".

## pr-agent Readiness

Never add pr-agent-ready yourself.

Only recommend that a human adds pr-agent-ready when:
- Open questions is None.
- the target behavior is specific enough to implement
- scope and non-goals are explicit
- affected areas are grounded in code inspection
- key tests are listed
- the Plan section breaks the implementation into clear checklist items
- staged work, if present, has independently implementable stage briefs

Before automation can begin:
- ask the human to confirm that the issue is ready for pr-agent
- wait for the human to add pr-agent-ready, or explicitly tell you to add it
- if questions remain, do not recommend pr-agent-ready

If questions remain:
- do not add pr-agent-ready
- add or keep needs-human-review if automation should not proceed
- tag the human if configured or requested

## Output Rules

- Be explicit when something is inferred rather than confirmed.
- Preserve useful original context.
- Do not invent detailed solutions where the code only supports a likely direction.
- Keep wording tight enough to live directly in a GitHub issue.
- Use short paragraphs or compact bullets.
- Avoid duplicate sections beyond the eight required headings.
- Do not add separate Problem statement, Desired outcome, Acceptance criteria, Edge cases, Codebase impact, Implementation plan, Scope and non-goals, or Dependencies sections. Condense that content into the eight headings.
