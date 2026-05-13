# PR Agent Handoff

Use only when grouping buglist entries for PR Agent or moving handed-off entries under `## Under PR Agent Control`.

## Pick A Cluster

- Choose bugs sharing a domain when the app has domains; otherwise choose by module, workflow, invariant, lifecycle transition, integration payload, or failing test area.
- Include every bug ID that should be removed or updated when the PR closes.
- Avoid bundling unrelated P1/P2 issues just because they are nearby.
- If open questions would change implementation, keep them in the PR Agent prompt. If the user answers them, update the prompt and set `Open questions` to `None`.

## Prompt Refs

Put refs near the top of the PR Agent prompt:

```md
Buglist refs:
- `BUG-260511-001` - Short title
- `BUG-260511-002` - Short title
```

Add this cleanup rule to the prompt:

```md
When this PR fully fixes a buglist entry, remove it from `docs/buglist.md` and delete its linked `docs/investigations/{BUG-ID}.md` file when one exists. If it only partially fixes it, keep the same bug ID and update the wording.
```

## Control State

When the user says the prompt has been handed to PR Agent:

- Move referenced entries to `## Under PR Agent Control`.
- Keep the original bug IDs.
- Group by domain when the app has domains; otherwise by module, workflow, feature, or affected area.
- Add the owning issue or PR reference when known.

When the PR closes, remove fixed entries and their linked investigation files. Move remaining risk back to `## Active Issues` with the same ID and updated wording.
