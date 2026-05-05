---
name: process-documentation
description: Create and maintain concise Laravel process and workflow documentation, including Mermaid flowcharts, strict entry-point semantics, key rules, state transitions, edge cases, key files, key tests, and aligned class or method docblocks. Use when workflow behavior, jobs, observers, integrations, state transitions, or process docs change.
license: MIT
metadata:
  domain: laravel
  role: specialist
  scope: documentation
  triggers: process docs, workflow docs, Mermaid flowchart, flowchart TB, state transition, observer, job workflow, integration workflow, webhook entry point, class docblocks
---

# Process Documentation

Use this skill when workflow behavior changes or when process documentation needs to be created, repaired, or reviewed.

## Non-negotiable Rule

If code changes workflow behavior, update all affected process knowledge in the same change:

- process docs pages
- Mermaid flowcharts
- key files and key tests lists
- important rules, invariants, state transitions, and edge cases
- class and method docblocks in key implementation files

## Load References

- Load `references/process-doc-template.md` when creating a new process doc or restructuring an existing one.
- Load `references/mermaid-flowchart-rules.md` when adding or changing a flowchart.

## Process Doc Standard

Docs are the digestible source of truth, not long manuals.

Prefer:
- short sections
- clear headings
- bullets
- flowcharts where they reduce explanation
- key rules and invariants
- links to deeper references when detail would bloat the doc

Avoid:
- long prose
- duplicated explanations
- historical commentary
- generic Laravel or PHP advice
- documenting obvious code
- huge all-in-one process manuals

For every section, ask: would a future agent likely get this wrong without this information? If no, cut or shorten it.

## Required Process Doc Content

Each workflow doc should cover:

- purpose
- strict entry points
- `flowchart TB` Mermaid diagram when it clarifies the workflow
- key rules, state transitions, invariants, and important edge cases
- data flow across models, jobs, observers, queues, UI, and integrations
- failure modes, retries, idempotency, and stop conditions
- key files
- key tests
- update triggers

## Flowchart Rules Summary

- Use Mermaid `flowchart TB`.
- Use meaningful swimlanes grouped by model, integration, and key job/decision process.
- Treat inbound webhooks as separate entry points.
- Do not draw expected webhooks as deterministic children of outbound API calls.
- Keep node labels short and verb-led.
- Show important decisions, integration calls, observer/job dispatches, and terminal outcomes.

Load `references/mermaid-flowchart-rules.md` for shape syntax and detailed rules.

## Documentation Update Checklist

When workflow code changes:

- [ ] Update the relevant process doc page.
- [ ] Update flowchart nodes and edges.
- [ ] Ensure entry nodes are strict entry points.
- [ ] Update narrative, key rules, edge cases, failure modes, and retry behavior.
- [ ] Update key files when implementation files changed.
- [ ] Update key tests when coverage changed or needs adding.
- [ ] Ensure names match actual code: jobs, events, observers, model methods, integrations.
- [ ] Update class-level docs in changed key classes, especially jobs, actions, observers, and workflow coordinators.
- [ ] Update non-obvious method docs so they align with the process narrative.
- [ ] Activate `skill-maintenance` if the workflow's app skill or references also need updating.
