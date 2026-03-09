# Test Structure with Describe Blocks

This is a focused reference for one topic: organising Pest tests with `describe()` blocks and scoped `beforeEach()` setup.

Load it when the main `Skill.md` points here or when a file contains several related tests for the same class, method, or workflow.

## Contents

- Core rule
- Benefits of nested describe blocks
- How to use `beforeEach()`
- When to use nested describes
- When a flat structure is acceptable
- Example structure

## Core rule

When blocks of tests exercise similar functionality or the same class, group them with `describe()` blocks.

Use nested `describe()` blocks to separate meaningful sub-behaviours, especially when each group needs its own setup or mental context.

Do not leave a long file as a flat sequence of tests if the tests naturally cluster by behaviour.

## Benefits of nested describe blocks

- Better organization: related tests are grouped logically by function or behaviour
- Scoped setup and teardown: `beforeEach()` and `afterEach()` can be applied at the correct level
- Improved test reports: output is more readable because it reflects the behavioural hierarchy
- Easier filtering: many runners can target a specific describe group
- Clearer documentation: the test tree itself documents the expected behaviour of the code

## How to use `beforeEach()`

Use `beforeEach()` to refine setup for a group of tests, not to hide everything globally.

Good practice:
- keep common file-wide setup at the top level only if nearly every test needs it
- move behaviour-specific setup into the closest enclosing `describe()`
- let nested groups add setup for their own scenario instead of bloating the parent setup
- if every test in a group asserts outcomes after the same business act, it is acceptable to perform that act once in that group's `beforeEach()`

Avoid:
- one giant top-level `beforeEach()` that prepares every possible scenario
- repeated inline setup across many tests when the repetition clearly belongs to one behavioural group
- hiding the business act in a helper when keeping it visible in the group's `beforeEach()` would better explain the workflow

## When to use nested describes

Use nested `describe()` blocks when:
- testing multiple functions or methods in one file
- testing different aspects or scenarios of a complex function
- different groups need different setup or teardown
- a class has several related behaviours that should be separated in the test output

## When a flat structure is acceptable

Use a single `describe()` block, or keep the structure flatter, when:
- testing a single simple function with straightforward behaviour
- testing a small component or class with very limited functionality
- the suite is small enough that extra nesting adds noise rather than clarity
- team standards explicitly prefer a flatter shape and the file still reads cleanly

Flat does not mean unstructured. Even in small files, related tests should still read as one coherent group.

## Example structure

```php
<?php

describe('CompleteWorkflow', function () {
    beforeEach(function () {
        $this->result = WorkflowScenarioBuilder::new()->withCompleteOrder()->build();
        $this->workflow = $this->result->workflow->refresh();
    });

    describe('state resolution', function () {
        it('marks the workflow as complete', function () {
            CompleteWorkflow::dispatchSync($this->workflow);

            expect($this->workflow->refresh()->status)->toBe(WorkflowStatus::COMPLETE);
        });
    });

    describe('artefact creation', function () {
        it('creates the expected workflow artefact', function () {
            CompleteWorkflow::dispatchSync($this->workflow);

            expect(WorkflowArtefact::count())->toBe(1);
        });
    });

    describe('when called twice', function () {
        beforeEach(function () {
            CompleteWorkflow::dispatchSync($this->workflow);
        });

        it('does not create duplicate artefacts', function () {
            CompleteWorkflow::dispatchSync($this->workflow);

            expect(WorkflowArtefact::count())->toBe(1);
        });
    });
});
```

The point of this structure is not extra ceremony. The point is to make the test file read like a map of behaviours.
