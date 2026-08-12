# Investigation File Format

Use only for linked files like `docs/investigations/BUG-260511-001.md`.

Investigation files explain the issue. They are not PR prompts or architecture docs.

`docs/buglist.md` remains canonical for dependency edges. Mirror its direct `Depends on:` IDs here so the case file is understandable on its own, then explain why each prerequisite must finish first.

## Writing Checklist

- [ ] Confirm the buglist entry exists or create/update it first.
- [ ] Use the same bug ID in the filename, title, and `Buglist ref`.
- [ ] State the investigation status: `Suspected`, `Confirmed`, `Resolved`, or `Needs reproduction`.
- [ ] Mirror `Implementation: Ready`, `Implementation: In progress`, or `Implementation: In review` from the buglist when present; omit it when implementation is not approved.
- [ ] Add `Depends on: None` or the exact direct prerequisite IDs from the buglist entry.
- [ ] When dependencies exist, include a `## Dependencies` section explaining the ordering constraint and what must be true before this bug can proceed.
- [ ] Include only headings that help explain this issue.
- [ ] Separate observed behaviour from expected behaviour.
- [ ] Add reproduction steps when known; write `Unknown` only when reproduction is not yet known.
- [ ] Include a `## Scope` section that states the investigation and likely fix boundary.
- [ ] Make `## Scope` explicit enough to act as the implementation contract: state the required outcome, permitted affected areas, and material exclusions.
- [ ] Link affected files, tests, issues, PRs, or incidents only when they are useful evidence.
- [ ] Mark uncertain facts as suspected.
- [ ] Keep task checklists, acceptance criteria, and detailed implementation plans out of this file.

## Example

```md
# BUG-260511-001 - Fulfilled quantity can exceed reserved quantity after reroute exhaustion

Buglist ref: `BUG-260511-001`
Status: Suspected
Priority: P1
Implementation: Ready
Depends on: `BUG-260510-001`

## Dependencies

- `BUG-260510-001` must be resolved first because it establishes the reservation totals this release path consumes. This bug can proceed after that fix has merged and its buglist cleanup is complete.

## Observed Behaviour

- Fulfilled quantity can remain higher than reserved quantity after reroute exhaustion releases allocations.

## Expected Behaviour

- Reservation totals should not drop below fulfilled quantities for the same order line.

## Scope

- Investigate and fix reservation release behaviour after partial fulfilment and reroute exhaustion.
- Do not redesign reroute selection, reservation accounting, or historical order correction unless the evidence shows this bug cannot be fixed within the release path.

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

- `## Dependencies` when `Depends on` is not `None`
- `## Observed Behaviour`
- `## Expected Behaviour`
- `## Scope`
- `## Reproduction`
- `## Evidence`
- `## Suspected Cause`
- `## Confirmed Cause`
- `## Open Questions`
- `## Related`

Use fewer headings when the investigation is simple.

## Dependency Rules

- List only direct prerequisites, matching the buglist entry exactly and in the same order. Use `Depends on: None` when there are none.
- For each prerequisite, explain in simple English why it must finish first and the condition that clears the dependency. Do not merely repeat its title.
- Do not copy transitive prerequisites into this file. A bug that depends on bug 2 does not also list bug 1 merely because bug 2 depends on bug 1.
- Do not maintain a reverse `Blocks:` list here. Find dependants from the canonical buglist graph when needed.
- Keep related-but-independent bugs under `## Related`; a shared area or root cause is not automatically a dependency.
- When investigation evidence suggests a new, removed, or reversed dependency, update `docs/buglist.md` first after applying its cycle and target validation, then mirror the approved graph here.

## Implementation Readiness

- Mirror the buglist's exact `Implementation:` value when present. The buglist remains canonical.
- Treat `Ready` as an approved implementation queued by the controller. It may still be waiting for the dependencies described below.
- Do not use investigation status as a substitute: `Status: Confirmed` describes confidence in the bug, while `Implementation: Ready` records the user's implementation decision.
- Do not add or change readiness from investigation evidence alone. If evidence changes approved behaviour or scope, return the bug to user decision.

## Final Check

- [ ] The file explains evidence, reproduction, cause, or uncertainty that would not fit cleanly in `docs/buglist.md`.
- [ ] `Depends on:` exactly matches the buglist entry, and every listed prerequisite has a clear rationale and completion condition under `## Dependencies`.
- [ ] `Implementation:` matches the buglist when present and is not used to hide unresolved dependencies.
- [ ] The `## Scope` section makes the boundary of the investigation and likely fix explicit without becoming a PR plan.
- [ ] The scope distinguishes the smallest necessary fix and focused verification from unapproved audits, refactors, cleanup, and adjacent bug fixes.
- [ ] The buglist entry stays short and links to this file.
- [ ] The investigation does not duplicate the buglist as a second index.
- [ ] The investigation does not read like a PR Agent prompt, architecture doc, or implementation plan.
