---
name: laravel-integration-fakes
description: Build Laravel-native “integration fakes” (like Mail::fake/Bus::fake) for your own app boundaries. Use contracts + container bindings + facades to swap real integrations for in-memory fakes that record calls, provide assertion helpers, and can be seeded with pretend responses.
---

# Laravel Integration Fakes

Create first-class fakes for your own integrations (couriers, ERPs, payment gateways, “Dave’s XML endpoint”) that feel as smooth as Laravel’s built-in fakes.

## Goal

- Prevent accidental real API calls in tests
- Keep app code unaware of tests
- Record what happened (calls, payloads, routes/providers)
- Provide expressive assertions (`assertSent`, `assertUsedRoute`, `assertSentCount`)
- Allow seeding responses (`pushResponse`, `pushException`)
- Support switching between fake/real (`fake()` / `restore()`)

## When to use

Use this pattern when:
- The dependency is an **integration boundary** (anything outside your process)
- You’re writing **feature tests** and don’t want brittle mocks everywhere
- You need **confident assertions** about what was “sent”

Avoid when:
- You’re testing pure domain logic (use normal unit tests)
- The dependency is a trivial collaborator (a small class you own end-to-end)

---

# The Pattern

## Step 1: Define the contract (the boundary)

The contract is the keystone: your app depends on an interface, not a vendor client.

```php
<?php

namespace App\Modules\Fulfillment\Contracts;

use App\Modules\Fulfillment\Definitions\CourierProvider;

interface CourierRouteManagerContract
{
    public function via(CourierProvider $courier);
}
```

Guidelines:
- Keep contracts **small** and **behavioural**
- Prefer **value objects/enums** for providers/route choices
- Don’t leak vendor SDK types into your domain

---

## Step 2: Bind the real implementation (runtime wiring)

Tell Laravel which concrete implementation to use in production.

```php
<?php

namespace App\Modules\Fulfillment;

use App\Modules\Fulfillment\Contracts\CourierRouteManagerContract;
use Illuminate\Support\ServiceProvider;

class FulfillmentModuleServiceProvider extends ServiceProvider
{
    public $bindings = [
        CourierRouteManagerContract::class => CourierRouteManager::class,
    ];
}
```

Rule:
- App code resolves **only the contract** (or a facade that resolves the contract)

---

## Step 3: Add a facade (optional but makes it feel native)

A facade gives you the “Laravel feel”:

- `CourierRouter::via(...)`
- `CourierRouter::fake()`
- `CourierRouter::assertSent()`

```php
<?php

namespace App\Modules\Fulfillment\Facades;

use App\Modules\Fulfillment\Contracts\CourierRouteManagerContract;
use App\Modules\Fulfillment\CourierRouteManager;
use App\Modules\Fulfillment\CourierRouteManagerFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method \App\Modules\Fulfillment\Contracts\CourierRouteContract via(\App\Modules\Fulfillment\Definitions\CourierProvider $route)
 *
 * Fake helpers
 * @method static CourierRouteManagerFake fake()
 * @method static CourierRouteManager restore()
 */
class CourierRouter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CourierRouteManagerContract::class;
    }

    public static function fake(): CourierRouteManagerFake
    {
        $fake = new CourierRouteManagerFake;

        app()->instance(CourierRouteManagerContract::class, $fake);
        static::swap($fake);

        return $fake;
    }

    public static function restore(): CourierRouteManager
    {
        $real = new CourierRouteManager;

        app()->forgetInstance(CourierRouteManagerContract::class);
        app()->instance(CourierRouteManagerContract::class, $real);
        static::swap($real);

        return $real;
    }
}
```

Notes:
- `fake()` must swap **container binding** + **facade root**
- `restore()` is useful for mixed suites (unit/feature vs true integration tests)

---

## Step 4: Implement the Fake (records + assertions + seeded responses)

Your fake should:
- Perform no side effects
- Record calls + inputs
- Return deterministic responses
- Provide fluent assertion helpers

```php
<?php

namespace App\Modules\Fulfillment;

use App\Models\Fulfillment;
use App\Modules\Fulfillment\Contracts\CourierRouteContract;
use App\Modules\Fulfillment\Contracts\CourierRouteManagerContract;
use App\Modules\Fulfillment\Definitions\CourierProvider;
use PHPUnit\Framework\Assert as PHPUnit;

class CourierRouteManagerFake implements CourierRouteContract, CourierRouteManagerContract
{
    /** @var list<CourierProvider> */
    protected array $routes = [];

    /** @var list<Fulfillment> */
    protected array $fulfillments = [];

    /** @var list<array> */
    protected array $responses = [];

    public function via(CourierProvider $route): static
    {
        $this->routes[] = $route;

        return $this;
    }

    public function send(Fulfillment $fulfillment): array
    {
        $this->fulfillments[] = $fulfillment;

        return array_shift($this->responses) ?? [];
    }

    public function pushResponse(array $response): static
    {
        $this->responses[] = $response;

        return $this;
    }

    // Assertions

    public function assertRouteUsed(CourierProvider $route): static
    {
        PHPUnit::assertContains(
            $route,
            $this->routes,
            "Courier route {$route->value} was not used."
        );

        return $this;
    }

    public function assertSent(?callable $callback = null): static
    {
        if ($callback === null) {
            PHPUnit::assertNotEmpty($this->fulfillments, 'A fulfilment dispatch was not recorded.');
            return $this;
        }

        foreach ($this->fulfillments as $sentFulfillment) {
            if ($callback($sentFulfillment)) {
                PHPUnit::assertTrue(true);
                return $this;
            }
        }

        PHPUnit::fail('A fulfilment dispatch was not recorded.');
        return $this;
    }

    public function assertSentCount(int $times): static
    {
        PHPUnit::assertCount(
            $times,
            $this->fulfillments,
            "Expected {$times} fulfilment dispatch(es), got " . count($this->fulfillments) . "."
        );

        return $this;
    }
}
```

Non-negotiables:
- Keep recorded data **typed** and **inspectable**
- Return a sensible default if no seeded response exists (or throw—your choice, but be consistent)
- Assertions should read like Laravel: fluent and specific

---

## Step 5: Use it in app code (no test knowledge)

Your application code should not care if it’s real or fake.

```php
use App\Modules\Fulfillment\Facades\CourierRouter;
use App\Modules\Fulfillment\Definitions\CourierProvider;

CourierRouter::via(CourierProvider::Dpd)->send($fulfillment);
```

---

## Step 6: Use it in tests (the smooth bit)

```php
use App\Models\Fulfillment;
use App\Modules\Fulfillment\Facades\CourierRouter;
use App\Modules\Fulfillment\Definitions\CourierProvider;
use Illuminate\Support\Str;

it('routes via DPD and dispatches a fulfilment', function () {
    $trackingNumber = Str::random();
    $response = ['tracking_number' => $trackingNumber];

    $fake = CourierRouter::fake()->pushResponse($response);

    $fulfillment = Fulfillment::factory()->create();

    $courierResponse = CourierRouter::via(CourierProvider::Dpd)->send($fulfillment);

    $fake->assertSentCount(1)
         ->assertRouteUsed(CourierProvider::Dpd)
         ->assertSent(fn (Fulfillment $sent) => $sent->is($fulfillment));

    expect($courierResponse['tracking_number'])->toEqual($trackingNumber);
});
```

---

# Default testing posture: fake-by-default

To guarantee tests never hit real integrations:

- Call your facade fake in `beforeEach()`
- Or register a `TestServiceProvider` that binds fakes in the testing environment
- Only `restore()` in explicit integration tests

Example in Pest:

```php
use App\Modules\Fulfillment\Facades\CourierRouter;

beforeEach(function () {
    CourierRouter::fake();
});
```

---

# Design rules

- **Contracts are stable**. Real implementations can change freely.
- **Fakes are stateful recorders**, not mocks.
- **Assertions are part of the fake** (like `MailFake` / `BusFake`).
- Prefer **fakes over mocking frameworks** for integration boundaries.
- If the integration has multiple operations, consider a **Call Log** object:
    - `calls()` returns structured call records for custom assertions.

---

# Verification checklist

- [ ] Contract exists and app depends on it
- [ ] Production binding wired via ServiceProvider
- [ ] Fake records calls + inputs (payloads, route/provider, entities)
- [ ] Fake supports seeded responses (and/or exceptions)
- [ ] Facade supports `fake()` and optionally `restore()`
- [ ] Tests assert behaviour using fake’s fluent helpers
- [ ] No accidental external calls in the test suite
