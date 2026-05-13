# Buglist Entry Format

Use only when touching `docs/buglist.md`.

## Shape

Keep `docs/buglist.md` as the canonical short index. One issue equals one bullet:

```md
- [Bug: `BUG-260511-001`] **P2 - Short title:** Concise description. See `docs/investigations/BUG-260511-001.md`.
```

Entry type is usually `Bug`, `Risk`, or `Watch`.

ID format is `BUG-{YYMMDD}-{NNN}`. Use the creation date and the next three-digit number for that date. Before using an ID, search `docs/buglist.md` and `docs/investigations` for existing IDs with the same date prefix. Use the next number after the highest existing one, or `001` when none exist. Do not encode domain/module names in IDs.

## Sections

```md
## Active Issues

### Inventory Allocation

- [Bug: `BUG-260511-001`] **P1 - Fulfilled quantity can exceed reserved quantity after reroute exhaustion:** Reservation totals may become lower than fulfilled quantities after partial fulfilment. See `docs/investigations/BUG-260511-001.md`.

## Under PR Agent Control
```

Group by status, then domain when the app has domains. Otherwise group by module, workflow, feature, or affected area. Sort within each group by priority, then likely blast radius.

## Entry Checklist

- Stable date-sequence ID and entry type.
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
- Before adding a new bug, search for an existing same or overlapping bug; update the existing entry when it is the same issue.
- Do not always create a new entry.
- When removing a fixed bug from `docs/buglist.md`, delete its linked `docs/investigations/{BUG-ID}.md` file when one exists.
- Split mixed issues instead of hiding multiple risks in one bullet.
- Do not copy investigation detail, PR prompts, or incident timelines into the buglist.
