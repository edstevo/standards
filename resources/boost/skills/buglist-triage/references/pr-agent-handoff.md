# PR Agent Handoff

Use only when grouping buglist entries for PR Agent or moving handed-off entries under `## Under PR Agent Control`.

Keep the handoff inside `buglist-triage`; do not activate a separate PR-prompt or issue-planning skill. Draft the prompt directly with concise `Current implementation`, `Target behavior`, `Scope`, and `Open questions` sections unless the project or user requires another format.

## Pick A Cluster

- Choose bugs sharing a domain when the app has domains; otherwise choose by module, workflow, invariant, lifecycle transition, integration payload, or failing test area.
- Do not hand off a bug while any direct or transitive `Depends on:` prerequisite remains unresolved. Hand off the earliest unmet prerequisite first and wait for its fix and cleanup before selecting the dependant.
- Never bundle bugs connected by a dependency path into the same PR Agent prompt.
- When preparing a release, select matching `Release blocker:` bugs and their prerequisite paths before unrelated bugs.
- Include every bug ID that should be removed or updated when the PR closes.
- Avoid bundling unrelated P1/P2 issues just because they are nearby.
- If open questions would change implementation, keep them in the PR Agent prompt. If the user answers them, update the prompt and set `Open questions` to `None`.

## Prompt Refs

Put refs near the top of the PR Agent prompt:

```md
Buglist refs:
- `BUG-260511-001` - Short title; release blocker: `v2.0 go-live`
- `BUG-260511-002` - Short title
```

Omit the release-blocker suffix when the bug has no `Release blocker:` marker.

Add this cleanup rule to the prompt:

```md
When this PR fully fixes a buglist entry, remove it from `docs/buglist.md`, remove its ID from every dependant's buglist and investigation `Depends on:` metadata, update their `## Dependencies` explanations, and delete the fixed bug's linked `docs/investigations/{BUG-ID}.md` file when one exists. If it only partially fixes it, keep the same bug ID, dependency edges, and release-blocker marker, and update the wording.
```

## Control State

When the user says the prompt has been handed to PR Agent:

- Move referenced entries to `## Under PR Agent Control`.
- Keep the original bug IDs.
- Preserve `Release blocker:` and `Depends on:` markers while the entry remains controlled.
- Group by domain when the app has domains; otherwise by module, workflow, feature, or affected area.
- Add the owning issue or PR reference when known.

When the PR closes, remove fixed entries, clear their IDs from dependant buglist entries and investigations, update affected dependency explanations, and delete their linked investigation files. Move remaining risk back to `## Active Issues` with the same ID, dependency and release markers, and updated wording.
