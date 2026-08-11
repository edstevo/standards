# Buglist Entry Format

Use only when touching `docs/buglist.md`.

## Shape

Keep `docs/buglist.md` as the canonical short index. One issue equals one bullet:

```md
- [Bug: `BUG-260511-001`] **P2 - Short title:** Concise description. See `docs/investigations/BUG-260511-001.md`.
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

## Entry Checklist

- Stable ID following the project convention, or the default date-sequence convention, and entry type.
- Priority: `P0`, `P1`, `P2`, or `P3`.
- Short title and concise description.
- Affected area from the heading or description.
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
- When removing a fixed bug from `docs/buglist.md`, delete its linked `docs/investigations/{BUG-ID}.md` file when one exists.
- Split mixed issues instead of hiding multiple risks in one bullet.
- Do not copy investigation detail, PR prompts, or incident timelines into the buglist.

For reconcile-only requests, stay inside the reconcile scope from `SKILL.md`: fix duplicates, overlaps, stale wording, priorities, grouping, and linked investigation alignment. Do not remove fixed entries, delete investigation files, move PR Agent entries, split mixed entries into new IDs, or inspect implementation code unless the user separately asks for that work.
