# Bug Resolution Workflow

Use when working through buglist entries with the user from initial review through disposition, implementation, independent review, merge, and cleanup.

## Keep The Main Task As Controller

- Treat the main task as the controller and orchestrator for the full bug lifecycle. It selects bugs, starts side tasks, receives their handoffs, authorises each next stage, and owns final completion.
- Keep the controller open while any decision task, implementation subagent, or review subagent it started is still running or waiting for the user. Do not report the controller task as complete, close it, or stop waiting while delegated work remains active.
- Work on one bug at a time unless the user explicitly requests safe parallel work.
- When choosing among similar priorities, rotate across modules or application areas and avoid files, invariants, or workflows touched by other in-flight fixes. Do not delay a materially higher-priority bug merely to rotate areas.
- Start a dedicated user-facing decision side task for the selected bug. A side task may also be described as a side chat; its role is to review and decide the bug with the user, not to implement it. Do not substitute an internal-only subagent that cannot converse with the user.
- Give the decision task the bug ID and direct it to `docs/buglist.md`, the linked investigation, stable project documentation, and relevant implementation paths. Do not give it a predetermined conclusion.

## Review And Decide In The Side Task

The decision side task must remain read-only. It may inspect code, tests, documentation, history, logs, or runtime behaviour, but it must not edit files, implement a fix, start an implementation agent, merge changes, remove the buglist entry, or delete its investigation.

Read `docs/buglist.md` and the bug's linked investigation before discussing the bug with the user. Explain these separately in simple English:

1. What the bug report claims is wrong and what evidence supports it.
2. What the bug report says should happen instead.
3. What the current code and stable project documentation say, when inspected.
4. What behaviour the decision task independently recommends and why.

Call out missing evidence, contradictions, and uncertainty. Do not silently replace the reported expected behaviour with a new interpretation.

Formulate an explicit decision with the user. Ask them to confirm or correct a short decision statement that records:

- the disposition: not a bug, confirmed as written, corrected expected behaviour, or future roadmap;
- the approved expected behaviour in plain English;
- the approved scope and important boundaries; and
- whether the user is asking the controller to implement a fix now.

Do not treat an explanation or recommendation as approval. Continue the side-task discussion until the user's decision is explicit, or report that the decision remains unresolved.

## Pass The Decision To The Controller

After the user decides, send the controller a concise handoff containing:

```text
Bug: <BUG-ID>
Disposition: <not a bug | confirmed as written | corrected expected behaviour | future roadmap>
Approved behaviour: <plain-English decision>
Scope and boundaries: <approved scope>
Implementation requested: <yes | no>
Open questions or constraints: <none or explicit list>
```

The side task stops after sending the handoff. It must not act on the decision itself. If the user has not decided, keep the decision task open and tell the controller what remains unresolved; the controller must also remain open.

## Controller Applies The User's Disposition

Verify that the handoff contains the user's explicit decision. If it is incomplete or ambiguous, return it to the decision task instead of inferring approval.

- **Not a bug:** Treat the user's answer as a product decision. Update stable behaviour documentation when useful, then remove the buglist entry and delete its linked investigation file.
- **Confirmed as written:** Preserve the approved expected behaviour and scope, updating the investigation only when needed for implementation.
- **Corrected expected behaviour:** Update the investigation and any affected stable behaviour documentation before implementation so the approved expectation is authoritative.
- **Future roadmap:** Move the requirement to the project's established roadmap or backlog when one exists, retaining a link to the original bug ID when useful. Remove the buglist entry and linked investigation only after the future work has a durable destination; otherwise keep the entry and ask where it should live.

Do not implement until the decision handoff confirms both the intended behaviour and that the user asked for the fix.

## Implement Through A Separate Subagent

When the handoff records that the user asked for implementation:

- Spawn a new implementation subagent for that one bug. Never reuse the decision side task as the implementer.
- Give it the bug ID, the user-approved expected behaviour, scope boundaries, investigation, relevant project guidance, and required tests and documentation.
- Require it to inspect current behaviour, implement the fix, run focused and proportionate broader tests, and update relevant stable documentation or changelog material.
- Do not let the implementation subagent merge, remove the buglist entry, delete the investigation, or approve its own work.
- Keep the controller open and wait for implementation to finish before starting review.

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
