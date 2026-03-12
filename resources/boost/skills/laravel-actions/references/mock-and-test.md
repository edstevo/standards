# Mock and Test

This reference explains how Laravel Actions help testing and how to use that without over-mocking.

Load this reference when:
- testing an action directly
- testing another class that depends on an action
- choosing between a real action call, a mock, a partial mock, or a spy
- cleaning up fake state between tests

## Why the package is useful in tests

The docs highlight a major advantage: Laravel Actions ensure actions are resolved from the container even when they are executed like simple objects.

That makes testing cleaner because the package can swap the action for:
- a mock
- a partial mock
- a spy

without custom container glue in every test.

## Testing rule of thumb

Use the real action when:
- the action itself is what you are testing
- you want to assert its persisted outcome or returned value

Mock or spy the action when:
- another class is the subject under test
- the test only cares that the action was called
- running the real action would make the test broader or more brittle

## Test the real action directly

```php
<?php

use App\Actions\Orders\RecalculateOrderTotals;
use App\Models\Order;

it('recalculates the order total', function (): void {
    $order = Order::factory()->create([
        'total_gross' => 0,
    ]);

    RecalculateOrderTotals::run($order);

    expect($order->refresh()->total_gross)->toBeGreaterThan(0);
});
```

If the action is the behaviour under test, do not mock it.

## Mock an action collaborator

The docs show that `mock()` returns a `MockInterface`, and `shouldRun()` is a convenience helper for expectations on `handle(...)`.

Use that when another class should trigger the action.

```php
<?php

use App\Actions\Orders\SendOrderConfirmation;
use App\Models\Order;

it('sends the order confirmation after checkout', function (): void {
    $order = Order::factory()->create();

    SendOrderConfirmation::shouldRun()
        ->with($order)
        ->andReturnNull();

    $this->postJson("/api/orders/{$order->id}/checkout")
        ->assertOk();
});
```

If the action must not be called, prefer:

```php
SendOrderConfirmation::shouldNotRun();
```

## Use a spy when the test runs first

The docs provide `spy()` and the slightly cleaner `allowToRun()` helper.

Use a spy when you want to:
- let the test execute first
- assert the interaction afterward

```php
<?php

use App\Actions\Orders\SendOrderConfirmation;
use App\Models\Order;

it('triggers the confirmation action after checkout', function (): void {
    $order = Order::factory()->create();

    SendOrderConfirmation::allowToRun()->andReturnNull();

    $this->postJson("/api/orders/{$order->id}/checkout")
        ->assertOk();

    SendOrderConfirmation::spy()
        ->shouldHaveReceived('handle')
        ->with($order);
});
```

## Partial mocks should stay rare

The docs support `partialMock()` for cases where only some methods need expectations.

Use it sparingly:
- it can be useful when an internal helper must be isolated
- frequent need for partial mocks usually means the action is too broad

```php
MyAction::partialMock()
    ->shouldReceive('fetchRemoteData')
    ->andReturn(['ok' => true]);
```

If partial mocks keep showing up, split the action or extract a collaborator.

## Fake lifecycle

The docs note that `mock()`, `partialMock()`, and `spy()` all reuse the same fake instance until it is cleared.

Useful helpers:
- `Action::isFake()` tells you whether the action currently has a fake attached
- `Action::clearFake()` detaches the fake and restores the real implementation

Use `clearFake()` when fake state could leak across tests or when one test needs to switch back to the real action.

```php
MyAction::mock();

expect(MyAction::isFake())->toBeTrue();

MyAction::clearFake();

expect(MyAction::isFake())->toBeFalse();
```

## Action tests vs job tests

Keep the subject clear.

If the action is synchronous business logic:
- test the action directly with `::run(...)`

If a queued Laravel Job calls the action:
- test queue behaviour as a job concern
- mock or spy the action if the job test only needs to prove delegation
- test the action's real business behaviour separately

That keeps action tests fast and keeps queue concerns on native Laravel Jobs.
