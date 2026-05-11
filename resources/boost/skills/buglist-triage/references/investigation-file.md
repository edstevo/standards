# Investigation File Format

Use only for linked files like `docs/investigations/BUG-260511-001.md`.

Investigation files explain the issue. They are not PR prompts or architecture docs.

## Writing Checklist

- [ ] Confirm the buglist entry exists or create/update it first.
- [ ] Use the same bug ID in the filename, title, and `Buglist ref`.
- [ ] State the investigation status: `Suspected`, `Confirmed`, `Resolved`, or `Needs reproduction`.
- [ ] Include only headings that help explain this issue.
- [ ] Separate observed behaviour from expected behaviour.
- [ ] Add reproduction steps when known; write `Unknown` only when reproduction is not yet known.
- [ ] Link affected files, tests, issues, PRs, or incidents only when they are useful evidence.
- [ ] Mark uncertain facts as suspected.
- [ ] Keep implementation scope and acceptance criteria out of this file.

## Example

```md
# BUG-260511-001 - Fulfilled quantity can exceed reserved quantity after reroute exhaustion

Buglist ref: `BUG-260511-001`
Status: Suspected
Priority: P1

## Observed Behaviour

- Fulfilled quantity can remain higher than reserved quantity after reroute exhaustion releases allocations.

## Expected Behaviour

- Reservation totals should not drop below fulfilled quantities for the same order line.

## Reproduction

- Create an order line with reserved stock.
- Partially fulfil it.
- Exhaust reroute attempts and release the remaining allocation.

## Evidence

- `app/.../ReleaseAllocation.php`: releases the remaining reservation without checking fulfilled quantity.
- `tests/.../RerouteExhaustionTest.php`: missing partial-fulfilment coverage.

## Cause

- Suspected: the release path treats reserved quantity as fully releasable after partial fulfilment.

## Open Questions

- None.
```

## Useful Headings

- `## Observed Behaviour`
- `## Expected Behaviour`
- `## Reproduction`
- `## Evidence`
- `## Suspected Cause`
- `## Confirmed Cause`
- `## Open Questions`
- `## Related`

Use fewer headings when the investigation is simple.

## Final Check

- [ ] The file explains evidence, reproduction, cause, or uncertainty that would not fit cleanly in `docs/buglist.md`.
- [ ] The buglist entry stays short and links to this file.
- [ ] The investigation does not duplicate the buglist as a second index.
- [ ] The investigation does not read like a PR Agent prompt, architecture doc, or implementation plan.
