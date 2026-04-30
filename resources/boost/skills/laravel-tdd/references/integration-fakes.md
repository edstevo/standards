# Laravel Integration Fakes

This reference belongs to the `laravel-tdd` skill. Load it when tests cross an external integration boundary or when a Laravel-native fake should replace ad-hoc mocks.

The goal is to create first-class fakes for your own integrations, similar to Laravel's `Mail::fake()` or `Bus::fake()`.

## When To Use

Use this pattern when:
- the dependency is outside your process, such as a courier, ERP, payment provider, third-party API, webhook sender, or XML endpoint
- feature or workflow tests need to prove what was sent without making real network calls
- tests need deterministic pretend responses or exceptions
- the app already has, or should have, a contract/facade entrypoint for the boundary

Avoid this pattern when:
- testing pure domain logic that has no external boundary
- the collaborator is a small internal class you own end-to-end
- a one-off unit test can use a simpler hand-written collaborator without leaking into application design

## Design Rules

- App code depends on a small contract, not a vendor client.
- Production wiring binds the contract to the real implementation in a service provider.
- Fakes are stateful in-memory recorders, not mocking-framework expectations.
- Fakes expose fluent assertion helpers such as `assertSent()`, `assertSentCount()`, or `assertRouteUsed()`.
- Fakes support seeded responses and exceptions so tests stay deterministic.
- If a facade entrypoint exists, `fake()` must swap both the container binding and facade root.
- `restore()` is optional but useful for suites that mix fake-by-default feature tests with true integration tests.
- Use fake-by-default for feature tests so accidental real external calls are not possible.
- Keep app code unaware of whether the real implementation or fake is active.

If the integration has several operations or payload types, consider a typed call log object instead of loose arrays. The fake can expose `calls()` for custom assertions while still offering common fluent assertions.

## Step 1: Define The Contract

The contract is the boundary. Keep it behavioural and small.

```php
<?php

namespace App\Modules\Fulfillment\Contracts;

use App\Modules\Fulfillment\Definitions\CourierProvider;

interface CourierRouteManagerContract
{
    public function via(CourierProvider $courier): CourierRouteContract;
}
```

Guidelines:
- prefer value objects and enums for provider, route, and status choices
- do not leak vendor SDK types into the domain
- expose the behaviour the app needs, not every vendor operation

## Step 2: Bind The Real Implementation

Production code should resolve the contract.

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

## Step 3: Add A Facade When It Improves The Test API

A facade is optional, but it makes the fake feel native:
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
 * @method static \App\Modules\Fulfillment\Contracts\CourierRouteContract via(\App\Modules\Fulfillment\Definitions\CourierProvider $route)
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
        $real = app(CourierRouteManager::class);

        app()->forgetInstance(CourierRouteManagerContract::class);
        app()->instance(CourierRouteManagerContract::class, $real);
        static::swap($real);

        return $real;
    }
}
```

`fake()` must swap the container binding and the facade root. Swapping only one of them leaves some call sites using the real integration.

## Step 4: Implement The Fake

The fake should record calls, return deterministic responses, and provide assertion helpers.

```php
<?php

namespace App\Modules\Fulfillment;

use App\Models\Fulfillment;
use App\Modules\Fulfillment\Contracts\CourierRouteContract;
use App\Modules\Fulfillment\Contracts\CourierRouteManagerContract;
use App\Modules\Fulfillment\Definitions\CourierProvider;
use PHPUnit\Framework\Assert as PHPUnit;
use Throwable;

class CourierRouteManagerFake implements CourierRouteContract, CourierRouteManagerContract
{
    /** @var list<CourierProvider> */
    protected array $routes = [];

    /** @var list<Fulfillment> */
    protected array $fulfillments = [];

    /** @var list<array<string, mixed>|Throwable> */
    protected array $responses = [];

    public function via(CourierProvider $route): static
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function send(Fulfillment $fulfillment): array
    {
        $this->fulfillments[] = $fulfillment;

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response ?? [];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function pushResponse(array $response): static
    {
        $this->responses[] = $response;

        return $this;
    }

    public function pushException(Throwable $exception): static
    {
        $this->responses[] = $exception;

        return $this;
    }

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
            PHPUnit::assertNotEmpty($this->fulfillments, 'A fulfillment dispatch was not recorded.');

            return $this;
        }

        foreach ($this->fulfillments as $sentFulfillment) {
            if ($callback($sentFulfillment)) {
                PHPUnit::assertTrue(true);

                return $this;
            }
        }

        PHPUnit::fail('A matching fulfillment dispatch was not recorded.');

        return $this;
    }

    public function assertSentCount(int $times): static
    {
        PHPUnit::assertCount(
            $times,
            $this->fulfillments,
            "Expected {$times} fulfillment dispatch(es), got ".count($this->fulfillments).'.'
        );

        return $this;
    }
}
```

Keep recorded data typed and inspectable. Return a sensible default when no response was seeded, or throw consistently if the integration should always be seeded.

## Step 5: Use It In App Code

Application code should call the boundary without test knowledge.

```php
use App\Modules\Fulfillment\Definitions\CourierProvider;
use App\Modules\Fulfillment\Facades\CourierRouter;

CourierRouter::via(CourierProvider::Dpd)->send($fulfillment);
```

## Step 6: Use It In Tests

```php
use App\Models\Fulfillment;
use App\Modules\Fulfillment\Definitions\CourierProvider;
use App\Modules\Fulfillment\Facades\CourierRouter;
use Illuminate\Support\Str;

it('routes via DPD and dispatches a fulfillment', function () {
    $trackingNumber = Str::random();

    $fake = CourierRouter::fake()->pushResponse([
        'tracking_number' => $trackingNumber,
    ]);

    $fulfillment = Fulfillment::factory()->create();

    $courierResponse = CourierRouter::via(CourierProvider::Dpd)->send($fulfillment);

    $fake->assertSentCount(1)
        ->assertRouteUsed(CourierProvider::Dpd)
        ->assertSent(fn (Fulfillment $sent) => $sent->is($fulfillment));

    expect($courierResponse['tracking_number'])->toEqual($trackingNumber);
});
```

For fake-by-default suites, set the fake in Pest setup or a testing service provider:

```php
use App\Modules\Fulfillment\Facades\CourierRouter;

beforeEach(function () {
    CourierRouter::fake();
});
```

Only call `restore()` in explicit true-integration tests that intentionally hit the real boundary.

## Verification Checklist

- [ ] Contract exists and app code depends on it.
- [ ] Production binding is wired in a service provider.
- [ ] Fake records calls and inputs in typed, inspectable structures.
- [ ] Fake supports seeded responses and expected exceptions.
- [ ] Facade `fake()` swaps both the container binding and facade root.
- [ ] Facade supports `restore()` when true integration tests need it.
- [ ] Tests assert behaviour using the fake's fluent helpers.
- [ ] Feature tests cannot accidentally make real external calls.
