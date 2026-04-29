---
name: github-issue-brief-refinement
description: Review a GitHub issue or rough implementation brief, explore the codebase to identify real impact, and rewrite the issue into a concise implementation-ready brief with explicit open questions, affected areas, and key tests. Use when work begins from a GitHub issue URL or a thin brief that is not yet codebase-grounded.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: planning
  triggers: GitHub issue URL, review brief, improve brief, refine issue, concise implementation brief, affected areas, key tests, codebase impact
---

# GitHub Issue Brief Refinement

Use this skill when implementation work starts from a GitHub issue or a rough brief that still needs to be turned into an implementation-ready description.

## Goal

Produce a concise, codebase-grounded brief inside GitHub, not in a local markdown handoff.

The improved issue should:
- use only the seven brief headings listed below
- explain the current implementation and target behavior without repeating the same point under different labels
- identify the likely impact on the codebase as affected areas
- separate confirmed impact from informed inference
- ask the smallest set of questions needed to remove scope ambiguity
- fold acceptance criteria into `Target behavior` and edge cases into `Risks`
- finish with `Affected areas` and `Key tests`

## When to use

Use this skill when:
- the user shares a GitHub issue URL and asks for the brief to be reviewed or improved
- the issue is too thin to implement safely
- the likely change spans multiple files or layers and needs impact mapping first
- the team wants the refined brief written back into GitHub before coding starts

Do not use this skill when:
- the issue is already implementation-ready and the user wants coding to begin immediately
- the user only wants a local private note and does not want GitHub updated

## Workflow

1. Read the issue first.
   - Extract the goal, constraints, acceptance criteria, linked context, screenshots, logs, and any prior discussion.
   - Identify missing details, contradictions, and assumptions that could change the implementation shape.

2. Explore the codebase before rewriting the brief.
   - Search for the issue's domain terms, UI labels, route names, model names, errors, and integration names.
   - Trace the likely change through the smallest useful set of files.
   - Follow both implementation paths and existing tests so the impact list and test list are grounded in the codebase.
   - Label every impact point as either confirmed by code or inferred from surrounding structure.

3. Decide how to update GitHub.
   - Prefer updating the issue body when it is a thin brief and the original content can be preserved inside a clearer structure.
   - If rewriting the body would erase useful history, add a GitHub comment titled `Refined implementation brief` instead.
   - Do not move the work into a local markdown file.

4. Refine the brief.
   - Rewrite it using only these headings, in this order: `Current implementation`, `Target behavior`, `Open questions`, `Scope`, `Risks`, `Affected areas`, `Key tests`.
   - Fold desired outcomes and acceptance criteria into `Target behavior`.
   - Fold non-goals, assumptions, dependencies, and sequencing concerns into `Scope`.
   - Fold edge cases, regression concerns, and uncertainty into `Risks`.
   - Put files, modules, routes, jobs, integrations, data stores, and tests impacted by the work under `Affected areas`.
   - Keep recommendations concrete and concise. Do not invent detailed solutions where the code only supports a likely direction.
   - Avoid repeating the same detail across sections; mention it once in the section where it will help implementation most.

5. Ask clarifying questions when needed.
   - Ask only the questions that materially affect scope, architecture, data shape, or target behavior.
   - Put those questions both in the GitHub update and in the final response if they remain unresolved.

6. Finish with a structured final response.
   - End with `Affected areas` and `Key tests`.
   - Prefer absolute file paths for files.
   - Prefer the smallest confidence-building test set rather than a broad wishlist.

## Recommended issue structure

Use this structure when rewriting or commenting on the brief:

- Current implementation
- Target behavior
- Open questions
- Scope
- Risks
- Affected areas
- Key tests

Do not add separate `Problem statement`, `Desired outcome`, `Acceptance criteria`, `Edge cases`, `Codebase impact`, `Scope and non-goals`, or `Dependencies` sections. Condense that content into the seven headings above.

## Output rules

- Be explicit when something is inferred rather than confirmed.
- Preserve useful original context rather than replacing it with a cleaner but narrower rewrite.
- Do not hide uncertainty. If the codebase does not answer a key question, surface it plainly.
- Keep the wording tight enough to live directly in the GitHub issue.
- Use short paragraphs or compact bullets. Keep each section focused on implementation-critical detail only.
