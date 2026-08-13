# Bug Resolution Workflow

Use when working through buglist entries with the user from initial review through disposition, implementation, independent review, merge, and cleanup.

## Keep The Main Task As Controller

- Treat the main task as the controller and orchestrator for the full bug lifecycle, and run it with `gpt-5.6-sol` at `high`. It selects bugs, starts side tasks, receives their handoffs, authorises each next stage, and owns final completion. If the exact configuration is unavailable, use the nearest available setting and record the fallback before dispatching work.
- Keep the controller open while any decision task, implementation subagent, or review subagent it started is still running or waiting for the user. Do not report the controller task as complete, close it, or stop waiting while delegated work remains active.
- Review one bug at a time in the selected order. Implementation and independent review for an earlier bug may run while the user reviews the next bug in its decision side task.
- When choosing among similar priorities, rotate across modules or application areas and avoid files, invariants, or workflows touched by other in-flight fixes. Do not delay a materially higher-priority bug merely to rotate areas.
- Let dependency order override priority and area rotation when building the review queue. Never implement bugs connected by a dependency path in parallel.
- Start a dedicated user-facing decision side task for the selected bug. Title it exactly `<BUG-ID> Review`, preserving the complete stable bug ID; for example, use `BUG-260810-002 Review`. Do not add the bug title, priority, status, or another suffix. If the task-creation mechanism cannot set the title atomically, rename it immediately before sending the review prompt.
- Inspect the model choices available at task-creation time. Bug planning always uses the strongest intelligence model available at Ultra reasoning, or the future equivalent maximum reasoning tier. Apply this to every `<BUG-ID> Review` task, including initial investigation, expected-behaviour decisions, scope formulation, and any later return caused by a proposed change to the approved scope. Prefer reasoning quality over speed or cost. Do not hard-code a model version: Sol Ultra is the current-style example, and a more capable successor becomes the default when available.
- If the task mechanism cannot set the model or reasoning effort explicitly, use the strongest available review-task configuration and tell the controller which limitation or fallback applied. Do not silently choose a cheaper or faster tier.
- A side task may also be described as a side chat; its role is to review and decide the bug with the user, not to implement it. Do not substitute an internal-only subagent that cannot converse with the user.
- Give the decision task the bug ID and direct it to `docs/buglist.md`, the linked investigation, stable project documentation, and relevant implementation paths. Do not give it a predetermined conclusion.

## Review Bugs In Order

- Build the review queue by recursively tracing `Depends on:` IDs. Review prerequisites before dependants; for bug 3 depending on bug 2 and bug 2 depending on bug 1, review bug 1, then bug 2, then bug 3.
- Finish the current bug's user decision and handoff before opening the next decision side task. Do not wait for its implementation, independent review, merge, or cleanup before continuing the review queue.
- Allow a dependant's investigation and decision to finish while its prerequisite implementation is active. The dependency constrains implementation order, not review order beyond reviewing the prerequisite first.
- If a dependency target is missing or the graph contains a cycle, stop that chain and formulate the graph correction with the user. Do not infer which edge to delete.

## Queue Ready Bugs

- After a decision handoff confirms the expected behaviour, scope, and implementation request, update the buglist and investigation, then mark both `Implementation: Ready`.
- Treat `Ready` as durable implementation authorisation. Do not ask the user again merely because the bug waited for a prerequisite.
- A ready bug is startable only when every direct and transitive prerequisite has completed disposition and cleanup. For a confirmed fix, that means independent review passed, the fix merged, and its dependency edge was cleared.
- Leave a ready dependant marked `Ready` while it waits. Continue reviewing later bugs in order.
- Re-scan ready bugs after every decision handoff, merge, cleanup, or dependency change. Start the earliest ready bug whose prerequisites are clear and whose files or invariants do not conflict with other in-flight work.
- When a prerequisite clears, pass its ready dependant through the focused readiness process below without waiting for another user prompt. Start it automatically when the approved scope remains current.
- If approved scope becomes stale because of a prerequisite's implementation, remove readiness and return the bug to its `<BUG-ID> Review` task instead of implementing stale instructions.

Example pipeline:

1. Review bug 1. After the user approves its fix, mark it ready and start implementation because it has no unmet prerequisite.
2. While bug 1 is being implemented, review bug 2. If approved, mark it ready; leave it queued because it still depends on bug 1.
3. Continue to review bug 3 after bug 2's decision. It may also become ready while waiting on bug 2.
4. As part of closing bug 1, clear bug 1 from bug 2's dependency metadata and start bug 2 immediately. Closing bug 2 later performs the same check and starts bug 3.

## Work The Release Gate First

When the user is preparing a release or go-live milestone:

- Identify every bug whose `Release blocker:` value matches the target.
- Add all direct and transitive prerequisites of those bugs to the required resolution path, even when the prerequisites are not themselves marked as release blockers.
- Review and queue the required path in dependency order. Among ready, startable bugs in that path, use review order, priority, and blast radius to choose the next implementation.
- Defer bugs outside the required path unless the user explicitly adds them to the release scope.
- Keep the controller open until every blocker has completed its disposition, implementation when required, independent review, merge, and cleanup.
- Report the release as blocked while any matching bug or prerequisite remains active, under PR Agent control, unresolved, or awaiting user decision.
- Do not waive, retarget, or remove a release gate implicitly. Formulate that release-scope decision with the user in a side task and pass it back to the controller.

## Review And Decide In The Side Task

The decision side task must remain read-only. It may inspect code, tests, documentation, history, logs, or runtime behaviour, but it must not edit files, implement a fix, start an implementation agent, merge changes, remove the buglist entry, or delete its investigation.

Read `docs/buglist.md` and the bug's linked investigation before discussing the bug with the user. Explain these separately in simple English:

1. What the bug report claims is wrong and what evidence supports it.
2. What the bug report says should happen instead.
3. Which bugs it directly depends on, why each must finish first, and whether those prerequisites are cleared.
4. What the current code and stable project documentation say, when inspected.
5. What behaviour the decision task independently recommends and why.

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
Prerequisites: <none | unresolved and cleared BUG IDs>
Release blocker: <none | release or milestone targets>
Implementation requested: <yes | no>
Decision baseline: <commit inspected and relevant working-tree state>
Relevant paths and contracts: <paths, interfaces, or invariants whose change could invalidate approval>
Invalidation conditions: <specific prerequisite outcomes or changes that require a readiness delta check>
Open questions or constraints: <none or explicit list>
```

The side task stops after sending the handoff. It must not act on the decision itself. If the user has not decided, keep the decision task open and tell the controller what remains unresolved; the controller must also remain open.

## Controller Applies The User's Disposition

Verify that the handoff contains the user's explicit decision. If it is incomplete or ambiguous, return it to the decision task instead of inferring approval.

If the bug is a release blocker, preserve that gate through disposition and implementation. A not-a-bug, future-roadmap, waiver, or re-targeting decision must explicitly say whether the current release remains blocked.

- **Not a bug:** Treat the user's answer as a product decision. Before removal, identify every dependant and formulate whether its dependency should be removed or its expected behaviour should change with the user. Then update stable behaviour documentation when useful, remove the approved dependency edges, remove the buglist entry, and delete its linked investigation file.
- **Confirmed as written:** Preserve the approved expected behaviour and scope, updating the investigation only when needed for implementation.
- **Corrected expected behaviour:** Update the investigation and any affected stable behaviour documentation before implementation so the approved expectation is authoritative.
- **Future roadmap:** Move the requirement to the project's established roadmap or backlog when one exists, retaining a link to the original bug ID when useful. If active bugs depend on it, keep it tracked until the user decides to move those dependants too, re-scope them so the dependency can be removed, or retain another durable blocker. Remove the buglist entry and linked investigation only after the future work and every dependency have durable destinations.

When a confirmed or corrected bug's handoff says `Implementation requested: yes`, persist the approved behaviour and scope, mark it `Implementation: Ready`, and advance to the next review. If it says `no`, do not mark it ready.

Clear any stale implementation marker when a bug is classified as not a bug or future roadmap, or when the user does not request implementation.

## Revalidate Only Changed Context Before Implementation

Treat the Ultra decision handoff as authoritative for the investigation, expected behaviour, and approved scope. Before implementation, the `gpt-5.6-sol` `high` controller checks its recorded baseline, prerequisite outcomes, relevant paths and contracts, and invalidation conditions.

- If no prerequisite result or relevant state changed after approval, keep the certification and dispatch implementation without another investigation or user prompt.
- If a prerequisite completed or relevant state changed, run a read-only `gpt-5.6-sol` `xhigh` readiness delta gate. Give it the approved decision handoff and only the prerequisite result, commits, contracts, and relevant-path diff that changed after approval.
- Require the delta gate to return `Compatible` or `Conflict`, the exact baseline checked, and a concise reason. It must not repeat the full bug investigation, reopen unaffected decisions, edit files, or implement the fix.
- On `Compatible`, update the certified baseline and dispatch implementation automatically without asking the user to approve it again.
- On `Conflict`, clear `Implementation: Ready`, resume the existing `<BUG-ID> Review` task at Ultra, explain the material difference in simple English, and obtain a human decision before restoring readiness.

The readiness delta gate checks freshness and compatibility only. It never replaces the Ultra bug investigation and user-decision task.

## Enforce The Approved Scope

Treat the approved behaviour and the investigation's `## Scope` section as the implementation contract.

The smallest necessary fix may include focused tests, required documentation or changelog updates, and minimal enabling edits without separate approval. It does not authorise broader work merely because the implementer notices it.

The implementation subagent must:

- Inspect only as far as needed to understand, implement, and verify the approved bug fix. Do not start an open-ended audit of the module, application, architecture, test suite, security posture, performance, or nearby code.
- Make only changes necessary for the approved behaviour and its focused, proportionate verification.
- Avoid opportunistic refactors, formatting unrelated files, dependency upgrades, broad test rewrites, architectural redesign, cleanup, and adjacent bug fixes.
- Leave unrelated pre-existing failures and issues unchanged. Report them separately to the controller when material; do not absorb them into this fix.
- Stop before making a materially out-of-scope change, even when it appears useful or required.

When broader work appears necessary, return this proposal to the controller and wait:

```text
Scope expansion required for: <BUG-ID>
Blocked objective: <what cannot be completed inside the approved scope>
Evidence: <why the broader work appears necessary>
Proposed expansion: <specific additional behaviour, files, migrations, APIs, or cleanup>
Impact if declined: <what remains incomplete or alternative narrow outcome>
Work completed so far: <none or concise summary>
```

The implementation subagent must not seek approval by expanding the work first. Keep `Implementation: In progress` while the controller obtains a decision. This is not a separate scope-expansion phase: the controller must resume the existing `<BUG-ID> Review` task, or create it with that exact title when unavailable, and return to the normal bug investigation and user-decision process. Explain the proposal in simple English and obtain explicit human confirmation. Only after the controller updates the authoritative behaviour and `## Scope` may implementation continue.

Any resumed or recreated `<BUG-ID> Review` task used for scope expansion must use the same strongest-available model and maximum-reasoning rule as the original decision task.

If the user declines, finish the original scope when independently viable; otherwise keep the bug tracked and report the precise blocker. Put genuinely separate work into a new bug only through the normal user decision and bug-recording workflow.

## Implement Through A Separate Subagent

When a bug is marked `Implementation: Ready` and every prerequisite is clear:

- Apply the focused readiness process above, confirm that no direct or transitive prerequisite remains unresolved, and do not repeat the original investigation.
- Replace `Implementation: Ready` with `Implementation: In progress` in both files.
- Spawn a new implementation subagent for that one bug using `gpt-5.6-sol` at `high`. Never reuse the decision side task as the implementer. Ultra is reserved for bug investigation and human decisions unless the user explicitly requires it for implementation.
- Give it the bug ID, the user-approved expected behaviour, the exact approved `## Scope`, relevant exclusions, investigation, project guidance, and required tests and documentation. Explicitly instruct it to stop and return a scope-expansion proposal rather than extending the work.
- Require it to inspect current behaviour, implement the fix, run focused tests plus only the proportionate broader regression tests justified by the fix's risk or project rules, and update only required stable documentation or changelog material.
- Do not let the implementation subagent change its implementation marker, merge, remove the buglist entry, delete the investigation, or approve its own work.
- Keep the controller open. It may continue the ordered user-decision pipeline, but must wait for implementation to finish before starting that fix's independent review.

## Require Independent Review

After implementation finishes, spawn a different `gpt-5.6-sol` `xhigh` subagent to review the completed work. Do not reuse the implementation subagent as reviewer. If the exact model configuration is unavailable or the user or project explicitly requires another one, record the configuration used before relying on the review.

Replace `Implementation: In progress` with `Implementation: In review` in the buglist and investigation before starting the reviewer.

Require the reviewer to:

- Compare the complete change against the user-approved behaviour and scope.
- Inspect the complete diff and affected paths for scope escape, regressions, incomplete paths, unrelated changes, and missing edge cases. Do not turn the review into an open-ended audit of surrounding code.
- Identify every change that is not necessary for the approved fix, focused verification, or required documentation. Treat opportunistic refactors, adjacent fixes, broad cleanup, unrelated formatting, dependency upgrades, and unapproved redesign as scope violations.
- Run the relevant tests independently and report the commands and results.
- Confirm that required stable behaviour documentation, technical documentation, and changelog material are accurate and complete.
- Return an explicit pass only when the whole change stays within the approved scope. Treat uncertain scope, unverified requirements, or any unapproved expansion as a failed gate.

The reviewer must not fix scope violations or expand the implementation itself. Require the implementer to remove unrelated work, or return a specific scope-expansion proposal to the controller for explicit user confirmation through `<BUG-ID> Review`. Report unrelated issues as possible follow-up bugs; do not add them to the current fix.

Do not merge when review fails. Replace `In review` with `In progress`, send findings back for correction, then restore `In review` and require another independent review pass of the corrected work.

## Merge And Clean Up

After independent review passes:

- Confirm the target branch from project guidance or the user's instruction; use `main` only when it is the project's normal integration branch.
- Confirm the reviewed changes are the changes being merged and that unrelated work will not be included.
- Merge using the project's normal branch, pull request, and commit conventions.
- After the merge succeeds, find every direct dependant by searching all active and PR Agent-controlled entries for the fixed bug ID before deleting anything.
- Remove the fixed entry from `docs/buglist.md`, remove its ID from every dependant's buglist and investigation `Depends on:` metadata, update their `## Dependencies` explanations, and delete the fixed bug's linked `docs/investigations/{BUG-ID}.md` file as one cleanup.
- Commit or otherwise land the cleanup on the target branch according to project convention, then verify the fixed bug ID no longer appears as an active or controlled bug.
- Re-evaluate every discovered dependant against its remaining direct and transitive prerequisites. If it is marked `Implementation: Ready` and all prerequisites are clear, pass it through the focused readiness process. When its approved scope remains current, replace its marker with `Implementation: In progress` and start its implementation subagent immediately without another user prompt.
- If several dependants become startable together, dispatch them in the established review order. Run them in parallel only when their files, invariants, and workflows do not overlap; otherwise start the first and keep the others ready.
- Do not report the prerequisite bug's closing process as complete until this dependant scan, metadata cleanup, readiness evaluation, and required dispatch have finished. Keep the main controller open while any newly started implementation remains active.

If the merge or cleanup cannot be completed safely, keep the bug tracked and report the exact blocker. If the fix is only partial, keep the same bug ID and update the entry and investigation to describe the remaining issue.
