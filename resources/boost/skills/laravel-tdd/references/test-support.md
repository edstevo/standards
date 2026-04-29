# Shared Test Support

This is a focused reference for one topic: where reusable test logic should live.

Load it when the main `SKILL.md` points here or when you are considering helper methods inside a test file.

Use this reference when test setup or assertion logic starts repeating, or when a test file is tempted to grow helper methods.

## Rule of thumb

Functions or methods defined inside test classes/files are an absolute last resort.

If logic is worth reusing, it usually deserves a real home in the test support layer instead of being hidden inside one file.

For workflow-oriented tests, there is an extra rule:
- prefer file-local narrative readability over extracting a helper that hides the domain story

If the test reads more clearly with visible steps such as:
- build the graph
- evolve the scenario
- perform the business act
- assert the resulting domain graph

then keep those steps local even if a helper would technically be reusable.

## Prefer these options first

### 1. Keep it inline

If setup is short, used once, and still readable, keep it inside the test.

The test should still read as a short story: arrange, act, assert.

For workflow tests, "short story" is literal:
- show the important domain state changes in order
- let the reader see how the scenario moves from one state to the next
- do not compress the interesting parts into a helper only because duplication exists

### 2. Use factory states or builders

If the repetition is about data shape, prefer:
- model factory states
- scenario builders
- fixtures that describe the domain state cleanly

This keeps the test focused on behaviour instead of raw setup plumbing.

### 3. Put broad helpers in `tests/TestCase.php`

Use `tests/TestCase.php` when the helper is broadly useful across the suite.

Examples:
- authentication helpers
- common database assertions
- recurring environment setup
- shared convenience assertions used in many domains

Keep these helpers generic and widely applicable.

### 4. Put domain-specific support in `tests/Support`

Use `tests/Support` when the helper has domain knowledge, state, or more structure than a simple convenience method.

Examples:
- `CarrierLossScenarioBuilder`
- `AssertsCarrierLossRefunds`
- custom expectation extensions
- in-memory fakes or spies
- fixture assemblers for multi-model workflows

If the helper needs constructor arguments, internal state, or several related methods, it should be a support class.

## What to avoid

- private/protected helper methods added only to shorten a long test
- helper methods that hide important domain setup
- helper methods that hide scenario evolution or the order of domain steps
- giant assertion helpers that verify several unrelated behaviours at once
- file-local abstractions that cannot be reused by other tests
- layered defensive branches that repeat what the assertion already proves

Usually unnecessary in a test:

```php
expect($fulfillmentLine)->toBeInstanceOf(FulfillmentLine::class);

if (! $fulfillmentLine instanceof FulfillmentLine) {
    throw new RuntimeException('Expected credit note line to reference a fulfillment line.');
}
```

In production code, fail-fast guards can be valuable. In tests, once an expectation already proves the condition, an extra `throw` usually adds no new signal and tends to bloat the file.

If static analysis needs narrowing after a type assertion, prefer a local `@var` annotation over an extra defensive branch:

```php
expect($fulfillmentLine)->toBeInstanceOf(FulfillmentLine::class);

/** @var FulfillmentLine $fulfillmentLine */
```

Prefer a single clear failure path unless the second branch adds genuinely different diagnostic value.

The goal is not merely less code. The goal is a test API that makes intent clearer.

## Extraction heuristic

Extract when any of these are true:
- the logic appears in more than one file
- the setup represents a named domain scenario
- the assertions form a reusable testing concept
- the helper needs state or configuration
- the test file is becoming harder to read because support code is mixed with test cases

## Keep shared support focused

Good shared support makes tests read more clearly, not more mysteriously.

Prefer:
- `WorkflowScenarioBuilder::withCompleteOrder()->build()`
- `assertRefundMatchesExpectedQuantities($workflow)`

Be cautious with:
- generic `makeScenario()`
- `prepareEverything()`
- helpers that obscure which models and states matter

When the support would hide the workflow itself, keep the workflow visible and let shared support handle only the repeatable edges around it:
- graph construction
- broad environment setup
- domain-specific assertions that stay focused on one concept

## Interaction with TDD

Do not extract support too early.

First let a concrete test prove the need. Once duplication or noise is real, extract to the correct shared location:
- `tests/TestCase.php` for broad helpers
- `tests/Support` for richer domain-specific support

This keeps the test suite expressive without turning each test file into a private utility library.
