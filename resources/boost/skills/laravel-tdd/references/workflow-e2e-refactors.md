# Workflow / E2E Test Refactors

This is a focused reference for one topic: refactoring oversized workflow and E2E tests.

Load it when the main `SKILL.md` points here or when one test is trying to prove several unrelated outcomes at once.

The examples in this file are illustrative patterns, not project-specific rules. Adapt the split to the workflow in front of you.

## Contents

- Refactor objective
- Smells that a workflow test is too large
- How to choose split boundaries
- Refactor pattern
- Workflow group structure
- Shared post-act setup
- Common concern categories
- Persisted outcomes over mocked internals
- Defensive behaviour tests
- Alternate workflow scenarios
- Practical naming guidance
- Example split

## Refactor objective

A single test should validate one behavioural concern wherever possible.

Keep the scenario setup if it is genuinely shared, but split the assertions by responsibility so each failing test points at one broken behaviour.

## Smells that a workflow test is too large

- The test asserts state transitions plus persistence changes plus side effects in one place
- The assertion section is several screens long
- Helper methods exist only to hide assertion bulk
- A single failure forces the reader to inspect many unrelated assertions
- The test name describes a whole saga instead of one outcome
- Small changes to one side effect break tests that are supposedly about something else

## How to choose split boundaries

Split by responsibility, not by raw assertion count.

Good split boundaries usually match one of these questions:
- Did the entity change state correctly?
- Did the right records get created or updated?
- Did a derived artefact get produced correctly?
- Did the correct event, notification, or audit record get emitted?
- Did the right external sync or job dispatch happen?
- Did the workflow behave safely when repeated or called in the wrong state?

If two assertions answer different questions, they usually belong in different tests.

## Refactor pattern

1. Identify the single act that drives the workflow.
2. Keep or extract shared setup for that workflow.
3. Execute the same act in separate tests.
4. Assert one responsibility per test.
5. Move truly different workflow branches into separate files or top-level `describe()` groups.
6. Add defensive and idempotency cases separately.

## Workflow group structure

When a workflow file grows beyond a couple of tests, a good default structure is:

- positive path tests
- defensive or idempotent tests
- a separate file, or at least a separate top-level `describe()`, for materially different workflow variants

This keeps the file readable and makes failures easier to interpret.

Do not mix:
- the normal success path
- retry, reopen, override, or admin-only variants
- defensive no-op or duplicate-call rules

unless the workflow is genuinely small enough that separation would add noise.

## Shared post-act setup

If every test in a `describe()` block asserts outcomes after the same transition or business act, it is acceptable to perform that act once in that block's `beforeEach()`.

Use this when:
- the act is the same for every test in the group
- the group is asserting different concerns after that same act
- repeating the act inline in every test would add noise rather than clarity

Rules:
- keep the act visible in the group's `beforeEach()`; do not hide it in a file-local helper
- keep the assertions split by concern even when the act is shared
- if some tests need a meaningfully different act, give them a separate nested group

The point is to reduce repetition without hiding the workflow.

## Common concern categories

These are typical concern boundaries for workflow-heavy tests:

- state transition or resolution
- model graph or persistence changes
- financial or domain artefact creation
- external integration sync or job dispatch
- timeline, audit, or notification output
- user-visible response or API contract
- guard, validation, or permission behaviour
- idempotency or repeated-call safety

Not every workflow needs all of these. Use only the concerns that genuinely exist in the code being tested.

## Persisted outcomes over mocked internals

For end-to-end workflow tests, prefer proving persisted outcomes rather than faking internal domain jobs, events, or listeners.

If the goal is to prove the workflow end to end:
- fake external boundaries when needed
- let internal workflow orchestration run
- assert persisted outcomes such as statuses, created records, timeline rows, sync records, or other durable artefacts

If you fake the internal domain work, you are no longer proving the full workflow. That can still be valid, but it becomes a different test layer.

## Defensive behaviour tests

Keep defensive cases separate from the positive workflow assertions.

Common examples:
- calling the workflow action twice does not create duplicate side effects
- the workflow does not proceed when a required association or prerequisite is missing
- the workflow does not run from an invalid state
- the workflow records the correct failure or no-op behaviour when a guard is hit

These tests should make the guard or idempotency rule explicit in the name.

## Alternate workflow scenarios

If the code supports materially different workflow variants, separate them rather than mixing them into one broad file.

Examples:
- standard flow vs retry flow
- initial processing vs reopened or overridden processing
- automatic path vs manual-admin path
- success path vs partial-resolution path

If the setup, lifecycle, or expected outputs differ meaningfully, give that branch its own file or its own top-level `describe()` section.

## Practical naming guidance

Prefer names like:
- `it('closes the record when the workflow completes successfully')`
- `it('creates the expected credit artefact when the workflow completes')`
- `it('records workflow timeline events')`
- `it('does not create duplicate side effects when the action is called twice')`

Avoid names that describe the whole saga in one sentence.

## Example split

For a broad workflow test around a single action such as `completeWorkflow()`, split it into focused tests like these:

### 1. State transition

Cover only the resolved state:
- final status
- close/archive flags
- resolved or failed markers
- related aggregate state changes that belong to the same resolution concern

Do not assert:
- derived artefact creation
- external sync
- audit or timeline output

### 2. Derived artefact creation

Cover only the artefact or record produced by the workflow:
- one artefact is created
- reason or type is correct
- ownership or association is correct
- line items, quantities, or amounts are correct

Do not assert:
- overall state transition
- audit output
- unrelated side effects

### 3. Event or audit output

Cover only emitted records or notifications:
- timeline events
- audit records
- domain events
- notifications

This test should assert only those outputs.

### 4. External side effects

Cover only integration work:
- job dispatches
- sync records
- fake client calls
- outbound payload shape

This test should assert only the external side effect boundary.

### 5. Defensive behaviour

Cover only the no-duplicate / guard rule:
- repeated calls do not duplicate side effects
- invalid state prevents the action
- missing prerequisites skip or block the side effect

This test should assert only the defensive rule being protected.
