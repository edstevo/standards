---
name: buglist-triage
description: Exclusive workflow skill for the entire tracked-bug lifecycle. Always use this skill, and no other workflow skill, when creating or recording bugs; reviewing, reconciling, investigating, grouping, de-duplicating, prioritising, deciding, linking dependencies, marking bugs ready for implementation, or marking go-live and release-blocking bugs; implementing or independently reviewing fixes; merging fixes; or removing resolved bugs from `docs/buglist.md`. Also use for ordered review pipelines, implementation readiness, release gates, dependency ordering, linked investigations, controller-led side-task decisions, separate subagent implementation and review, cleanup, risks, watch-list entries, and PR Agent bug refs.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: documentation
  triggers: buglist, bug list, reconcile the buglist, reconcile buglist, resolve bug, work through bugs, review bug fix, ready for implementation, implementation ready, bug dependency, go-live bug, release blocker, release gate, docs/buglist.md, known bugs, risks, watch-list, investigation docs, PR Agent bug refs, Under PR Agent Control
---

# Buglist Triage

Use this skill to keep `docs/buglist.md` useful as the canonical index and control surface for known bugs, risks, watch items, and PR Agent handoffs.

## Exclusive Workflow Ownership

Use this skill, and only this skill, as the workflow authority whenever the primary task is to create, record, review, reconcile, investigate, decide, fix, review a fix for, merge, hand off, close, or remove a tracked bug.

Do not activate or substitute another bug, issue-triage, debugging, planning, PR-prompt, code-review, merge, or resolution workflow skill. In particular, do not activate `pr-agent-prompts` for bug work; draft any PR Agent handoff through this skill's own reference.

Allow decision, implementation, and review side tasks or subagents to load project-required language, framework, architecture, domain, coding-style, testing, documentation, or security guidance only as technical support. Do not let supporting guidance replace this skill or change its bug IDs, approved scope, dependency graph, implementation readiness, release gates, user decision gate, control state, independent-review gate, merge gate, or cleanup rules.

## Load References

- Load `references/buglist-entry.md` when adding, editing, reviewing, grouping, or moving entries in `docs/buglist.md`. Load it together with `references/investigation-file.md` when adding, changing, redirecting, or clearing dependencies.
- Load `references/investigation-file.md` only when creating or editing a linked `docs/investigations/{BUG-ID}.md` file.
- Load `references/bug-resolution.md` when orchestrating a side task that reviews a bug and decides it with the user, receiving its decision handoff, implementing an approved fix through a separate subagent, independently reviewing it, merging it, or completing post-merge cleanup.
- Load `references/pr-agent-handoff.md` only when selecting bug refs for PR Agent, drafting handoff refs, or moving entries under `## Under PR Agent Control`.

Load the narrowest reference that fits the task. Dependency edits are the exception because their canonical buglist edge and investigation explanation must change together. Do not load investigation or PR Agent handoff guidance for other simple buglist entry edits.

## Core Rule

`docs/buglist.md` is the index, dependency graph, release gate, and control surface, not the full case file.

Keep entries short enough to scan, group, prioritise, de-duplicate, and hand over. Put deeper evidence, reproduction notes, root cause analysis, implementation scope, incidents, and normal app/domain behaviour in linked documents.

## Buglist Workflow

- [ ] Load only the reference needed for the current task.
- [ ] Read `docs/buglist.md` before changing tracked bugs or risks.
- [ ] Search existing buglist entries and linked investigation docs for the same or overlapping bug before creating a new entry.
- [ ] Keep stable bug IDs and never renumber existing entries.
- [ ] Record only direct prerequisites as `Depends on` bug IDs; reject missing targets, self-dependencies, and dependency cycles.
- [ ] Mirror those direct prerequisites in the linked investigation and explain why each must be resolved first.
- [ ] Mark a bug `Implementation: Ready` only after the user approves its behaviour and scope and requests implementation; readiness may coexist with unresolved dependencies.
- [ ] Record a `Release blocker:` target only when the user or an authoritative release plan requires that bug before a named release or milestone.
- [ ] Group by status, then domain when the app has domains; otherwise group by module, workflow, feature, or affected area.
- [ ] Keep each entry to one concise bullet.
- [ ] Create or link a deeper document when the bug needs more context than fits cleanly in one short bullet.
- [ ] Delete linked `docs/investigations/{BUG-ID}.md` files when removing fixed buglist entries.
- [ ] Move PR Agent-owned entries under `## Under PR Agent Control` only after the user says they have been handed off.

## Reconcile Mode

When the user asks to "reconcile the buglist", treat it as an audit-and-alignment pass only. Load `references/buglist-entry.md` and `references/investigation-file.md`; do not load PR Agent handoff guidance unless the user separately asks for PR Agent work.

Do:
- Review every active bug, risk, and watch entry for duplicates, overlaps, stale wording, wrong priority, wrong grouping, invalid implementation states, stale release-blocker targets, and invalid or stale dependencies.
- Validate that dependency targets exist, no entry depends on itself, and the graph contains no cycles. Repair an edge only when its intended direction is clear; otherwise report the conflict for user decision.
- Check linked `docs/investigations/{BUG-ID}.md` files exist, match the buglist entry, and include the right implementation state, dependencies, dependency rationale, evidence, cause, scope, and open questions.
- Treat `docs/buglist.md` as canonical when dependency metadata drifts. Update the investigation to match unless evidence shows the graph itself is wrong; in that case formulate the graph correction with the user.
- Create or update missing/incomplete linked investigation files only enough to make the existing entry coherent; mark unknown facts as unknown or suspected.
- Merge duplicate entries when they describe the same root issue. Keep the oldest stable bug ID unless another ID is already the clearer canonical reference.

Do not:
- Remove entries just because they appear fixed.
- Delete linked investigation files.
- Move entries into or out of `## Under PR Agent Control`.
- Split mixed entries into new bug IDs.
- Update partially fixed issues unless the user asks for fixed/partial-fix cleanup.
- Inspect implementation code, tests, logs, or runtime behaviour unless the user explicitly asks for verification.

## Resolution Mode

When the user asks to work through bugs, resolve a bug, implement an agreed fix, or review and merge that fix, load `references/bug-resolution.md` and follow its controller, ordered review, readiness, implementation, independent review, merge, and cleanup gates.

The main task is the controller and orchestrator. Keep it open while any side task or subagent it started is still running or waiting for the user. Review and formulate the decision for each bug with the user in a dedicated user-facing side task titled exactly `<BUG-ID> Review`, such as `BUG-260810-002 Review`, then pass the explicit decision back to the controller. Do not use an internal-only subagent that cannot converse with the user for this decision stage. The decision side task is read-only and must not implement the fix. Only the controller may start a separate implementation subagent after it receives the decision handoff and the user has asked for implementation.

Treat the user-approved bug scope as a hard boundary for implementation and independent review. Implementation subagents must not perform open-ended audits, opportunistic refactors, adjacent fixes, broad cleanup, or other unapproved work. When materially broader work appears necessary, stop and return a scope-expansion proposal to the controller; obtain explicit user confirmation through the bug's review task before continuing. Independent reviewers must fail the gate when the change escapes the approved scope.

Review bugs one at a time in dependency order, but do not wait for an earlier bug's implementation to finish before reviewing the next bug. Mark each approved bug ready for implementation. A ready dependant waits in the queue until its prerequisites clear, then the controller starts it without asking for implementation approval again. Never implement connected bugs in parallel.

Treat dependant dispatch as part of closing every prerequisite bug. Before the controller finishes that closing step, find all bugs that depended on the closing bug, update their dependency metadata, and immediately start any ready dependant whose remaining prerequisites are clear.

When working toward a release or go-live milestone, resolve bugs marked `Release blocker:` for that target and all of their transitive prerequisites before unrelated bugs. Do not declare the release ready while any required bug remains unresolved unless the user explicitly removes or changes the gate.

Do not treat resolution mode as reconcile mode. Resolution mode may inspect implementation behaviour and may remove or transfer an entry, but only after the user decides its disposition or an independently reviewed fix has been merged.

## Where Detail Belongs

- `docs/buglist.md`: canonical short entry, priority, implementation readiness, release-blocker targets, direct dependencies, ownership/control state, and outward links.
- Domain docs, when present: normal expected behaviour and stable invariants.
- Investigation docs: mirrored implementation state, direct dependency IDs and their rationale, evidence, reproduction, suspected or confirmed cause, investigation and likely fix scope boundaries, uncertainty, affected files, and related bugs.
- PR Agent prompts: implementation plan and acceptance context for one coherent PR.
- Incidents or postmortems: production event timelines, impact, response, and follow-up.

Before adding a long explanation to `docs/buglist.md`, decide whether it should instead become a linked investigation doc, domain documentation update, PR Agent prompt, test case, incident note, or postmortem.
