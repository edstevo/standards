---
name: buglist-triage
description: Exclusive workflow skill for the entire tracked-bug lifecycle. Always use this skill, and no other workflow skill, when creating or recording bugs; reviewing, reconciling, investigating, grouping, de-duplicating, prioritising, or deciding them; implementing or independently reviewing fixes; merging fixes; or removing resolved bugs from `docs/buglist.md`. Also use for linked investigations, user-led bug decisions, subagent implementation and review, cleanup, risks, watch-list entries, and PR Agent bug refs.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: documentation
  triggers: buglist, bug list, reconcile the buglist, reconcile buglist, resolve bug, work through bugs, review bug fix, docs/buglist.md, known bugs, risks, watch-list, investigation docs, PR Agent bug refs, Under PR Agent Control
---

# Buglist Triage

Use this skill to keep `docs/buglist.md` useful as the canonical index and control surface for known bugs, risks, watch items, and PR Agent handoffs.

## Exclusive Workflow Ownership

Use this skill, and only this skill, as the workflow authority whenever the primary task is to create, record, review, reconcile, investigate, decide, fix, review a fix for, merge, hand off, close, or remove a tracked bug.

Do not activate or substitute another bug, issue-triage, debugging, planning, PR-prompt, code-review, merge, or resolution workflow skill. In particular, do not activate `pr-agent-prompts` for bug work; draft any PR Agent handoff through this skill's own reference.

Allow implementation and review subagents to load project-required language, framework, architecture, domain, coding-style, testing, documentation, or security guidance only as technical support. Do not let supporting guidance replace this skill or change its bug IDs, user decision gate, control state, independent-review gate, merge gate, or cleanup rules.

## Load References

- Load `references/buglist-entry.md` when adding, editing, reviewing, grouping, or moving entries in `docs/buglist.md`.
- Load `references/investigation-file.md` only when creating or editing a linked `docs/investigations/{BUG-ID}.md` file.
- Load `references/bug-resolution.md` when reviewing bugs with the user one by one, deciding whether an entry is a bug, implementing an approved fix through a subagent, independently reviewing it, merging it, or completing post-merge cleanup.
- Load `references/pr-agent-handoff.md` only when selecting bug refs for PR Agent, drafting handoff refs, or moving entries under `## Under PR Agent Control`.

Load the narrowest reference that fits the task. Do not load investigation or PR Agent handoff guidance for a simple buglist entry edit.

## Core Rule

`docs/buglist.md` is the index and control surface, not the full case file.

Keep entries short enough to scan, group, prioritise, de-duplicate, and hand over. Put deeper evidence, reproduction notes, root cause analysis, implementation scope, incidents, and normal app/domain behaviour in linked documents.

## Buglist Workflow

- [ ] Load only the reference needed for the current task.
- [ ] Read `docs/buglist.md` before changing tracked bugs or risks.
- [ ] Search existing buglist entries and linked investigation docs for the same or overlapping bug before creating a new entry.
- [ ] Keep stable bug IDs and never renumber existing entries.
- [ ] Group by status, then domain when the app has domains; otherwise group by module, workflow, feature, or affected area.
- [ ] Keep each entry to one concise bullet.
- [ ] Create or link a deeper document when the bug needs more context than fits cleanly in one short bullet.
- [ ] Delete linked `docs/investigations/{BUG-ID}.md` files when removing fixed buglist entries.
- [ ] Move PR Agent-owned entries under `## Under PR Agent Control` only after the user says they have been handed off.

## Reconcile Mode

When the user asks to "reconcile the buglist", treat it as an audit-and-alignment pass only. Load `references/buglist-entry.md` and `references/investigation-file.md`; do not load PR Agent handoff guidance unless the user separately asks for PR Agent work.

Do:
- Review every active bug, risk, and watch entry for duplicates, overlaps, stale wording, wrong priority, and wrong grouping.
- Check linked `docs/investigations/{BUG-ID}.md` files exist, match the buglist entry, and include the right evidence, cause, scope, and open questions.
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

When the user asks to work through bugs, resolve a bug, implement an agreed fix, or review and merge that fix, load `references/bug-resolution.md` and follow its decision, implementation, independent review, merge, and cleanup gates. Work on one bug at a time unless the user explicitly requests safe parallel work.

Do not treat resolution mode as reconcile mode. Resolution mode may inspect implementation behaviour and may remove or transfer an entry, but only after the user decides its disposition or an independently reviewed fix has been merged.

## Where Detail Belongs

- `docs/buglist.md`: canonical short entry, priority, ownership/control state, and outward links.
- Domain docs, when present: normal expected behaviour and stable invariants.
- Investigation docs: evidence, reproduction, suspected or confirmed cause, investigation and likely fix scope boundaries, uncertainty, affected files, and related bugs.
- PR Agent prompts: implementation plan and acceptance context for one coherent PR.
- Incidents or postmortems: production event timelines, impact, response, and follow-up.

Before adding a long explanation to `docs/buglist.md`, decide whether it should instead become a linked investigation doc, domain documentation update, PR Agent prompt, test case, incident note, or postmortem.
