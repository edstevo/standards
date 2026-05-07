# Scenario Test File Structure

This is a focused reference for one topic: organising scenario-focused Pest test files with `describe()` blocks and scoped `beforeEach()` setup.

Load it when the main `SKILL.md` points here, when a scenario file needs internal structure, or when a broad test file should be split into smaller files.

## Contents

- Core rule
- File naming
- Benefits of nested describe blocks
- How to use `beforeEach()`
- When to use nested describes
- When a flat structure is acceptable
- Example structure

## Core rule

Prefer one test file per meaningful behaviour, feature, scenario, or user journey.

A test file should be understandable from its filename alone. The reader should be able to identify the behaviour being tested without opening a large mixed suite.

Use `describe()` blocks to organize tightly related assertions or variants inside one coherent scenario. Do not use nested groups as a way to keep unrelated journeys in the same file.

If a file starts covering independent behaviours, split it into separate scenario files.

## File naming

Prefer names like:
- `CustomerCanCheckoutWithCardTest.php`
- `CustomerCannotCheckoutWithOutOfStockItemTest.php`
- `AdminCanApproveReturnRequestTest.php`
- `SupplierDropshipOrderCanBeRoutedTest.php`
- `FailedWarehouseRouteCanBeReroutedTest.php`

Avoid broad names like:
- `CheckoutTest.php`
- `ReturnsTest.php`
- `FulfillmentTest.php`
- `OrderFlowTest.php`
- `E2ETest.php`

## Benefits of nested describe blocks

- Better organization: related tests are grouped logically by function or behaviour
- Scoped setup and teardown: `beforeEach()` and `afterEach()` can be applied at the correct level
- Improved test reports: output is more readable because it reflects the behavioural hierarchy
- Easier filtering: many runners can target a specific describe group
- Clearer documentation: the test tree itself documents the expected behaviour of the code

These benefits only hold when the file still represents one scenario. If the hierarchy starts reading like a module map, split the file.

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
- testing tightly related aspects of one scenario
- different groups need different setup or teardown
- a scenario has a few meaningful variants that should be separated in the test output
- a failed route scenario, for example, needs assertions for the failed route, replacement route, retry behaviour, and final status

## When a flat structure is acceptable

Use a single `describe()` block, or keep the structure flatter, when:
- testing a single simple function with straightforward behaviour
- testing one small scenario with very limited functionality
- the scenario is small enough that extra nesting adds noise rather than clarity
- team standards explicitly prefer a flatter shape and the file still reads cleanly

Flat does not mean unstructured. Even in small files, related tests should still read as one coherent group.

## Example structure

```php
<?php

describe('failed warehouse route rerouting', function () {
    beforeEach(function () {
        $this->result = FulfillmentRouteScenarioBuilder::new()
            ->withFailedWarehouseRoute()
            ->withReplacementRouteAvailable()
            ->build();

        $this->route = $this->result->route->refresh();
    });

    describe('after rerouting', function () {
        beforeEach(function () {
            RerouteFailedWarehouseRoute::dispatchSync($this->route);
            $this->route->refresh();
        });

        it('marks the original route as failed', function () {
            expect($this->route->status)->toBe(RouteStatus::FAILED);
        });

        it('creates the replacement route', function () {
            expect($this->route->replacementRoute)->not->toBeNull();
        });

        it('marks the fulfillment as rerouted', function () {
            expect($this->route->fulfillment->fresh()->status)->toBe(FulfillmentStatus::REROUTED);
        });
    });
});
```

This belongs in a file named like `FailedWarehouseRouteCanBeReroutedTest.php`. Successful routing, partial routing, supplier fallback, and refund escalation should be separate files.

The point of this structure is not extra ceremony. The point is to make one scenario easy to locate, understand, execute, review, debug, and safely modify.
