# Scenario Builders and Deterministic Graphs

This is a focused reference for one topic: using scenario builders or rich test-data builders without making tests implicit or fragile.

Load it when the main `Skill.md` points here or when a builder constructs a graph of related models for the test.

## Contents

- Core rule
- Deterministic graph shape
- Result object usage
- Group-level setup
- Keep the business act visible
- What to avoid
- Example pattern

## Core rule

Scenario builders are useful when the test needs a coherent graph of related models.

But they must stay deterministic and visible:
- the builder options that make an asserted graph shape true should be explicit in the same scope as the assertion or group setup
- the test should resolve the models it needs from the builder result
- the builder should reduce setup noise, not hide why the graph looks the way it does

## Deterministic graph shape

If a test asserts a specific graph shape, state count, or relationship arrangement, do not rely on implicit builder defaults.

Good:
- use explicit builder flags, states, or options that make the expected shape obvious
- keep those builder choices near the tests that depend on them

Bad:
- assuming the builder's default shape happens to match the assertion
- hiding shape-determining flags in a distant helper
- asserting collection counts or relationships without showing where that shape came from

If the reader cannot tell why the graph has that shape, the setup is too implicit.

## Result object usage

Many builders return a result object or aggregate that exposes the created models.

Use that result directly:
- call `build()` once
- pull the models you need from the result
- refresh/load only what the current group of assertions actually needs

Do not:
- rebuild the same graph again inside the test
- create file-local helper methods just to unwrap the result
- manually reconstruct models that the result already exposes

## Group-level setup

If a whole `describe()` group shares the same builder output, promote the result and shared models in that group's `beforeEach()`.

Good pattern:
- build the scenario in the group `beforeEach()`
- resolve the shared models there
- let each test assert one concern against those shared models

This keeps the group deterministic while avoiding repeated setup noise.

## Keep the business act visible

Builders should create the starting graph. They should not usually hide the business act being tested.

If the setup includes the business act itself:
- keep that act visible in the relevant `beforeEach()` for the group, or inline in the test
- do not tuck it away inside a helper that makes the workflow harder to read

The test should still read like a workflow:
- starting graph
- business act
- focused assertion

## What to avoid

- file-local builder helpers that simply wrap `build()`
- hidden builder defaults that determine the asserted graph shape
- mixing builder setup with the business act under test
- building one graph per test when the whole group shares the same starting graph
- using a builder so magical that the reader cannot tell what was created

## Example pattern

Good:

```php
describe('CompleteWorkflow', function () {
    beforeEach(function () {
        $this->result = WorkflowScenarioBuilder::new()
            ->withTwoLineItems()
            ->withExternalSyncEnabled()
            ->build();

        $this->workflow = $this->result->workflow->refresh();
        $this->lineItems = $this->result->lineItems;
    });

    describe('after completion', function () {
        beforeEach(function () {
            CompleteWorkflow::dispatchSync($this->workflow);
            $this->workflow->refresh();
        });

        it('marks the workflow as complete', function () {
            expect($this->workflow->status)->toBe(WorkflowStatus::COMPLETE);
        });

        it('creates the expected artefact', function () {
            expect(Artefact::query()->count())->toBe(1);
        });
    });
});
```

Why this is good:
- the graph-shaping flags are explicit
- the result object is used directly
- the business act is visible in the group that depends on it
- each test still proves one concern
