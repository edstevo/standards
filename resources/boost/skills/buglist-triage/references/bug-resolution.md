# Bug Resolution Workflow

Use when working through buglist entries with the user from initial review through disposition, implementation, independent review, merge, and cleanup.

## Select One Bug

- Read `docs/buglist.md` and the bug's linked investigation before discussing it.
- Work on one bug at a time unless the user explicitly requests safe parallel work.
- When choosing among similar priorities, rotate across modules or application areas and avoid files, invariants, or workflows touched by other in-flight fixes. Do not delay a materially higher-priority bug merely to rotate areas.

## Brief The User

Explain these separately:

1. What the bug report claims is wrong and the evidence it contains.
2. What the bug report says the expected behaviour is.
3. What the current code and stable project documentation say, when inspected.
4. What behaviour you independently think is correct and why.

Call out missing evidence, contradictions, and uncertainty. Do not silently replace the reported expected behaviour with your own interpretation. Wait for the user's disposition before implementing or removing anything.

## Apply The User's Disposition

- **Not a bug:** Treat the user's answer as a product decision. Update stable behaviour documentation when useful, then remove the buglist entry and delete its linked investigation file.
- **Confirmed as written:** Preserve the approved expected behaviour and scope, updating the investigation only when needed for implementation.
- **Corrected expected behaviour:** Update the investigation and any affected stable behaviour documentation before implementation so the approved expectation is authoritative.
- **Future roadmap:** Move the requirement to the project's established roadmap or backlog when one exists, retaining a link to the original bug ID when useful. Remove the buglist entry and linked investigation only after the future work has a durable destination; otherwise keep the entry and ask where it should live.

Do not implement until the user confirms the intended behaviour and asks for the fix.

## Implement Through A Subagent

When the user asks for implementation:

- Spawn a dedicated implementation subagent for that one bug.
- Give it the bug ID, the user-approved expected behaviour, scope boundaries, investigation, relevant project guidance, and required tests and documentation.
- Require it to inspect current behaviour, implement the fix, run focused and proportionate broader tests, and update relevant stable documentation or changelog material.
- Do not let the implementation subagent merge, remove the buglist entry, delete the investigation, or approve its own work.
- Wait for implementation to finish before starting review.

## Require Independent Review

After implementation finishes, spawn a different subagent to review the completed work. Do not reuse the implementation subagent as reviewer.

Require the reviewer to:

- Compare the complete change against the user-approved behaviour and scope.
- Inspect for regressions, incomplete paths, unrelated changes, and missing edge cases.
- Run the relevant tests independently and report the commands and results.
- Confirm that required stable behaviour documentation, technical documentation, and changelog material are accurate and complete.
- Return an explicit pass or actionable findings. Treat uncertain or unverified requirements as a failed gate.

Do not merge when review fails. Send findings back for correction, then require another independent review pass of the corrected work.

## Merge And Clean Up

After independent review passes:

- Confirm the target branch from project guidance or the user's instruction; use `main` only when it is the project's normal integration branch.
- Confirm the reviewed changes are the changes being merged and that unrelated work will not be included.
- Merge using the project's normal branch, pull request, and commit conventions.
- Only after the merge succeeds, remove the fixed entry from `docs/buglist.md` and delete its linked `docs/investigations/{BUG-ID}.md` file.
- Commit or otherwise land the cleanup on the target branch according to project convention, then verify the bug ID no longer appears as an active or controlled bug.

If the merge or cleanup cannot be completed safely, keep the bug tracked and report the exact blocker. If the fix is only partial, keep the same bug ID and update the entry and investigation to describe the remaining issue.
