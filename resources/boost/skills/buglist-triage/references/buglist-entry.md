# Buglist Entry Format

Use only when touching `docs/buglist.md`.

## Shape

Keep `docs/buglist.md` as the canonical short index. One issue equals one bullet:

```md
- [Bug: `BUG-260511-001`] **P2 - Short title:** Concise description. See `docs/investigations/BUG-260511-001.md`.
```

Add direct prerequisites at the end when needed:

```md
- [Bug: `BUG-260511-003`] **P2 - Downstream recalculation uses stale totals:** Concise description. See `docs/investigations/BUG-260511-003.md`. Depends on: `BUG-260511-002`.
```

Mark a bug that must be resolved before a release or milestone with `Release blocker:`:

```md
- [Bug: `BUG-260511-003`] **P2 - Downstream recalculation uses stale totals:** Concise description. See `docs/investigations/BUG-260511-003.md`. Implementation: Ready. Release blocker: `v2.0 go-live`. Depends on: `BUG-260511-002`.
```

Entry type is usually `Bug`, `Risk`, or `Watch`.

## ID Convention

Follow the project's documented bug ID convention when one exists. Check project guidance and existing bug IDs before assigning a new ID. Keep existing IDs stable even when the project later changes its convention.

Use `BUG-{YYMMDD}-{NNN}` as the default when the project does not define an override. Use the creation date and the next three-digit number for that date. Before using an ID, search `docs/buglist.md` and `docs/investigations` for existing IDs with the same date prefix. Use the next number after the highest existing one, or `001` when none exist.

A project may add a stable scope segment when it helps distinguish independently managed areas. For example, a modular project may define `BUG-{MODULE}-{YYMMDD}-{NNN}`, such as `BUG-SAL-260811-001` for its Sales module. Follow the project's documented module codes and sequencing rules; do not invent or introduce scoped IDs solely because the project has modules.

## Sections

```md
## Active Issues

### Inventory Allocation

- [Bug: `BUG-260511-001`] **P1 - Fulfilled quantity can exceed reserved quantity after reroute exhaustion:** Reservation totals may become lower than fulfilled quantities after partial fulfilment. See `docs/investigations/BUG-260511-001.md`.

## Under PR Agent Control
```

Group by status, then domain when the app has domains. Otherwise group by module, workflow, feature, or affected area. Sort within each group by priority, then likely blast radius.

## Dependencies

Use `Depends on:` to record the direct bugs that must be resolved first. List multiple IDs separated by commas:

```md
Depends on: `BUG-260511-001`, `BUG-260511-002`.
```

- Record only direct prerequisites. If bug 3 depends on bug 2 and bug 2 depends on bug 1, bug 3 lists only bug 2.
- Keep one canonical direction. Do not also store `Blocks:` on the prerequisite; derive blocked bugs by searching dependency references.
- Point only to stable IDs that still exist in `docs/buglist.md`, including entries under `## Under PR Agent Control`.
- Do not create a self-dependency or any edge that forms a direct or transitive cycle.
- Mirror the same direct IDs in the linked investigation's `Depends on:` metadata and explain each edge under `## Dependencies`. Update the buglist first because it is canonical.
- Use dependencies only for required ordering. Put related bugs without an ordering constraint in the investigation's `## Related` section.
- Treat priority and visual ordering as separate from dependency order. The resolution controller follows prerequisites before dependants regardless of their headings or priorities.

## Implementation Readiness

Use one of these exact markers after review:

```md
Implementation: Ready.
Implementation: In progress.
Implementation: In review.
```

- `Ready` means the user has approved the expected behaviour and scope and has requested implementation. It does not mean dependencies are clear.
- Leave a ready bug queued when any direct or transitive prerequisite remains unresolved. The dependency graph explains the wait; do not add a separate `Blocked` implementation state.
- Replace `Ready` with `In progress` when the controller starts the implementation subagent, then with `In review` when independent review starts.
- If no implementation was requested or the decision remains open, omit the marker. Never infer readiness from `Status: Confirmed`, priority, release-blocker status, or the absence of open technical questions.
- Mirror the marker in the linked investigation. Only the controller may add or change it after receiving the decision, implementation, or review handoff.
- If later evidence invalidates the approved behaviour or scope, remove the marker and return the bug to a decision side task.

## Release Blockers

Use `Release blocker:` to mark a bug that must be resolved before a specific release, launch, or go-live milestone. Prefer the project's exact release identifier. When none exists, use the user's exact milestone name such as `go-live`; use a moving label such as `next release` only until a stable name or version is known.

```md
Release blocker: `v2.0 go-live`.
```

- Treat this marker as an explicit release gate, not another severity. A `P2` may block a release, while a `P0` does not automatically belong to a particular release.
- Add, change, or remove the marker only from an explicit user decision or an authoritative release plan. Do not infer it from priority alone.
- Allow more than one target when necessary, separated by commas.
- Treat every transitive prerequisite of a marked bug as part of that release's required resolution path without copying the `Release blocker:` marker onto those prerequisites.
- Leave unrelated, unmarked bugs in the backlog while working toward the target unless the user expands the release scope.
- Do not declare a target ready while any matching blocker or prerequisite remains unresolved. The user must explicitly re-scope or waive a blocker if it will not be fixed.

## Entry Checklist

- Stable ID following the project convention, or the default date-sequence convention, and entry type.
- Priority: `P0`, `P1`, `P2`, or `P3`.
- Short title and concise description.
- Affected area from the heading or description.
- `Implementation:` marker when the bug is ready, in progress, or in independent review.
- `Release blocker:` target when the bug is explicitly required before a release or milestone.
- Direct `Depends on:` IDs when prerequisites exist, with every target present and the graph acyclic.
- Owner or PR Agent reference when relevant.
- Link to deeper context when needed.

## Priority Guide

- `P0`: blocks install, deployment, or core operation.
- `P1`: broken admin workflow, data loss, or high-risk incorrect data.
- `P2`: important correctness, safety, or coverage gap.
- `P3`: low-risk cleanup, stale comments, naming, or docblocks.

## Rules

- Never renumber existing IDs.
- Do not rewrite existing IDs when adopting or discovering a different project convention.
- Before adding a new bug, search for an existing same or overlapping bug; update the existing entry when it is the same issue.
- Do not always create a new entry.
- When closing any fixed bug, first find every dependant. Delete the fixed bug's linked investigation, clear its ID from every dependant's buglist and investigation `Depends on:` field, update each affected `## Dependencies` section, then start every newly unblocked ready dependant according to `bug-resolution.md`. Treat that scan and dispatch as part of closing the fixed bug.
- When merging duplicate entries, redirect dependencies from the removed ID to the retained canonical ID in both the buglist and investigations, then remove duplicates and any self-dependency created by the redirect.
- Do not silently remove a dependency because its prerequisite was classified as not a bug or moved to future roadmap work; follow the disposition rules in `bug-resolution.md`.
- Do not silently remove or retarget a release blocker during reconciliation, reprioritisation, or cleanup; require an explicit release-scope decision unless the bug was fully resolved and removed.
- Keep `Implementation:` markers aligned between the buglist and investigation. Do not mark a bug ready merely because a prerequisite completed.
- Treat `In progress` or `In review` without matching active controller work as stale control state. Report it instead of guessing whether to resume, reset, or remove it.
- Split mixed issues instead of hiding multiple risks in one bullet.
- Do not copy investigation detail, PR prompts, or incident timelines into the buglist.

For reconcile-only requests, stay inside the reconcile scope from `SKILL.md`: fix duplicates, overlaps, stale wording, priorities, grouping, dependency integrity, release-marker formatting, and linked investigation alignment. Report a stale or ambiguous release target instead of changing release scope. Do not guess how to break a dependency cycle, remove fixed entries, delete investigation files, move PR Agent entries, split mixed entries into new IDs, or inspect implementation code unless the user separately asks for that work.
