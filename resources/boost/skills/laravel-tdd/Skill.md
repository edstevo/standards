---
name: laravel-tdd
description: Apply Test-Driven Development to Laravel applications using Pest PHP. Use when implementing or refactoring Laravel features, bug fixes, model workflows, API endpoints, and tests. Write the failing test first, keep each test focused on one behavioural concern, split large workflow/E2E tests by responsibility, and prefer reusable test support in tests/TestCase.php or tests/Support over helper functions inside test classes.
license: MIT
metadata:
  domain: testing
  role: specialist
  scope: implementation
  triggers: Laravel, Pest, PHPUnit, TDD, feature tests, model tests, workflow tests, E2E refactors
---

# Laravel TDD

Write the test first. Watch it fail for the right reason. Write the smallest code change that makes it pass. Then refactor both production code and test structure.

Use this skill whenever you:
- add or change Laravel behaviour
- fix a bug
- refactor tests
- split oversized feature or E2E tests
- need to decide whether something belongs in a unit, model, feature, or integration test

## Core Workflow

1. Analyse the behaviour and choose the narrowest honest test layer.
2. Write the smallest failing test that proves that behaviour.
3. Verify the failure is for the intended reason.
4. Write the minimum production code needed to go green.
5. Refactor both the code and the tests, splitting broad assertions and extracting shared support where justified.

## Reference Guide

References are load-on-demand support files in this skill's `references/` directory.

Use them deliberately:
- stay in this main skill for the core workflow and default rules
- load a reference when the task matches its topic
- load the reference before planning or editing if that topic is central to the task
- do not load every reference automatically; load only the ones that fit

Why references exist:
- they keep `Skill.md` short and readable
- they hold deeper guidance that would otherwise bloat the main skill
- they give you more precise instructions for specific test-design problems

Load detailed guidance based on context:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Large workflow / E2E refactors | `references/workflow-e2e-refactors.md` | A feature or E2E test has become too large, one test asserts several side effects, or a workflow needs splitting by responsibility |
| Shared test support | `references/test-support.md` | Setup or assertions are repeating, you are tempted to add helper methods inside a test file, or reusable builders/assertions/fakes belong in `tests/TestCase.php` or `tests/Support` |
| Test grouping and describe blocks | `references/test-structure.md` | A test file covers several related behaviours or methods on the same class, you need nested `describe()` blocks, or different groups need different `beforeEach()` setup |
| Factory-driven test data | `references/factories.md` | A test is manually constructing models, associating related records by hand, mutating many fields inline, or a factory should be extended with `state()` / `configure()` instead |

## The loop

`RED -> VERIFY RED -> GREEN -> VERIFY GREEN -> REFACTOR -> REPEAT`

### RED

Write one failing test that names the behaviour clearly.

```php
<?php

use App\Models\Post;
use App\Models\User;

it('allows an authenticated user to create a post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', [
            'title' => 'My First Post',
            'content' => 'Post content here',
        ])
        ->assertRedirect('/posts');

    expect(Post::where('title', 'My First Post')->exists())->toBeTrue()
        ->and(Post::first()->user_id)->toBe($user->id);
});
```

### VERIFY RED

Run the narrowest command possible and confirm the failure matches the intended missing behaviour.

```bash
php artisan test --filter=allows_an_authenticated_user_to_create_a_post
```

### GREEN

Write the minimum Laravel code to satisfy the failing test. Do not solve the next problem yet.

### VERIFY GREEN

Run the targeted test again, then the relevant suite.

```bash
php artisan test --filter=allows_an_authenticated_user_to_create_a_post
php artisan test
```

### REFACTOR

After green:
- improve naming
- remove duplication
- extract shared test support
- move large workflow assertions into separate tests
- simplify production code without broadening test scope

## Choose the right test layer

### Unit tests

Use for:
- pure business logic
- actions, services, value objects, calculators
- logic that does not need a full HTTP/database workflow

### Model tests

Use for:
- lifecycle methods like `markAsSubmitted()` or `markAsCarrierLoss()`
- state transitions
- explicit model events

For event/orchestration patterns, activate `model-events-observers-workflows`.

### Feature tests

Use for:
- API endpoints
- controller flows
- user workflows
- real persistence across multiple models

Feature tests may still be narrow. "Feature" does not mean "assert every side effect in one test".

### Integration boundary tests

Use fakes for external boundaries and assert what was sent or synced.

For Laravel-native contract/facade fake patterns, activate `laravel-integration-fakes`.

## Core test design rules

### One behavioural concern per test

A test may contain multiple assertions, but they should all support the same concern.

Good:
- one test for state transition
- one test for credit note creation
- one test for refund creation
- one test for timeline events

Bad:
- one test that verifies state transitions, accounting sync, timeline events, and refunds together

If a failure would leave the reader asking "which part of the workflow broke?", the test is too broad.

### Prefer one act phase

Keep the test shape simple:
- Arrange the scenario
- Act once
- Assert one concern

If multiple acts are required, confirm whether you are really testing more than one behaviour.

### Name and group tests by behaviour, not implementation

Use names that describe what the system does:
- `it('creates a supplier credit note for carrier loss')`
- `it('records carrier-loss timeline events')`

Avoid names that only describe method calls or internal mechanics.

When a file covers several related behaviours, methods, or scenarios for the same class/workflow:
- wrap each related group in a `describe()` block
- use nested `describe()` blocks when a group has meaningful sub-behaviours
- use `beforeEach()` at the narrowest useful scope so each group only sets up what it needs

This keeps test output readable and prevents one large file from becoming a flat list of loosely related tests.

If file structure and grouped setup are becoming important to the task, load `references/test-structure.md`.

### Use datasets for input variation, not unrelated behaviour

Datasets are good for:
- validation matrices
- status permutations
- policy/permission combinations

Do not use datasets to pack several responsibilities into one test body.

## Reuse and shared test support

Helper functions inside test classes or Pest files are an absolute last resort.

Prefer, in order:
- explicit setup inline when the setup is short and only used once
- factory states and builders for data shape
- reusable helpers/assertions in `tests/TestCase.php` when they are broadly useful
- dedicated support classes in `tests/Support` when the logic is domain-specific or stateful

Prefer factories wherever possible so tests do not become bulky model-construction scripts.

If a factory already exists for the model or graph you need:
- use the factory instead of `new Model`, manual `associate()`, and repeated `save()` calls
- prefer named `state()` methods for recurring attribute combinations
- use `configure()`, `afterMaking()`, or `afterCreating()` when the factory needs smarter relation wiring

If test data construction is becoming the problem, load `references/factories.md`.

Good candidates for `tests/Support`:
- scenario builders
- domain-specific assertion classes
- custom expectations
- fake collaborators
- reusable fixture assemblers

Good candidates for `tests/TestCase.php`:
- broad setup hooks
- helpers used across many unrelated test files
- commonly reused database/authentication helpers

Do not add a private or protected helper method to a test file just because a block is long. First ask whether that behaviour deserves a reusable test API instead.

If this is becoming relevant to the task, load `references/test-support.md` before extracting helpers.

## Large workflow and E2E tests

When one workflow produces several meaningful outcomes, keep the scenario setup shared but split assertions by responsibility.

Typical split:
- state transition/resolution
- accounting artefact creation
- refund creation
- timeline/audit records
- defensive or idempotency behaviour

If a scenario represents a different workflow, move it to a separate test file even if the end state is similar.

If the test is broad enough that failures no longer point at one broken behaviour, load `references/workflow-e2e-refactors.md` and follow its split-by-responsibility pattern.

## Laravel testing defaults

- Use Pest with descriptive behaviour names
- Prefer Pest chainable expectations via `expect(...)` for assertions in Pest tests
- Do not use `PHPUnit\\Framework\\Assert` / `Assert::` in Pest tests unless Pest or Laravel has no suitable equivalent
- Laravel-native assertions such as response assertions and `assertDatabaseHas()` are still appropriate when they are the correct API
- Use factories for setup, and prefer factory states / `configure()` hooks over manual model assembly when the scenario is recurring
- Use `RefreshDatabase`
- Fake integration boundaries by default
- Test happy paths and defensive/idempotent paths
- Keep assertions close to the concern being tested

## Final check before shipping

- The first failing test was observed before implementation
- Each test proves one concern
- The narrowest honest test layer was chosen
- Reusable setup/assertions were extracted to shared test support
- Large workflow assertions were split when they covered unrelated responsibilities
- Relevant suites pass
