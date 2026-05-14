---
name: buglist-triage
description: Use when adding, editing, reviewing, reconciling, grouping, de-duplicating, prioritising, or moving known bugs, risks, and watch-list entries in `docs/buglist.md`, including linked investigation docs and PR Agent bug refs.
license: MIT
metadata:
  domain: workflow
  role: specialist
  scope: documentation
  triggers: buglist, bug list, reconcile the buglist, reconcile buglist, docs/buglist.md, known bugs, risks, watch-list, investigation docs, PR Agent bug refs, Under PR Agent Control
---

# Buglist Triage

Use this skill to keep `docs/buglist.md` useful as the canonical index and control surface for known bugs, risks, watch items, and PR Agent handoffs.

## Load References

- Load `references/buglist-entry.md` when adding, editing, reviewing, grouping, or moving entries in `docs/buglist.md`.
- Load `references/investigation-file.md` only when creating or editing a linked `docs/investigations/{BUG-ID}.md` file.
- Load `references/pr-agent-handoff.md` only when selecting bug refs for PR Agent, drafting handoff refs, or moving entries under `## Under PR Agent Control`.
- Activate `pr-agent-prompts` when turning a bug group into a local PR Agent prompt.

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

## Where Detail Belongs

- `docs/buglist.md`: canonical short entry, priority, ownership/control state, and outward links.
- Domain docs, when present: normal expected behaviour and stable invariants.
- Investigation docs: evidence, reproduction, suspected or confirmed cause, investigation and likely fix scope boundaries, uncertainty, affected files, and related bugs.
- PR Agent prompts: implementation plan and acceptance context for one coherent PR.
- Incidents or postmortems: production event timelines, impact, response, and follow-up.

Before adding a long explanation to `docs/buglist.md`, decide whether it should instead become a linked investigation doc, domain documentation update, PR Agent prompt, test case, incident note, or postmortem.
