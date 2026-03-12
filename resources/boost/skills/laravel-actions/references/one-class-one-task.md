# One Class, One Task

This reference explains the package model behind Laravel Actions and how to apply it in a reusable project style.

Load this reference when:
- deciding whether something should be an action
- shaping a new action class
- refactoring a service or invokable class into an action
- standardising call sites on `::run(...)`

## What the package gives you

According to the Laravel Actions docs, an action can be any PHP class with a `handle` method plus the `AsAction` trait, as long as the class can resolve from the container.

That gives you a practical default:
- constructor injection still works
- the action stays framework-friendly
- the package can swap the action for a mock or spy in tests

In these standards, that becomes:
- one task per action
- clear verb-led naming
- actions grouped under `app/Actions` or module-local `Actions/`
- `handle(...)` contains the core behaviour
- application code calls the action with `::run(...)`

## Standard action shape

Use this as the default shape.

```php
<?php

namespace App\Actions\Inventory;

use App\Models\Product;
use App\Services\Stock\StockAllocator;
use Lorisleiva\Actions\Concerns\AsAction;

class AllocateInventory
{
    use AsAction;

    public function __construct(
        protected StockAllocator $stockAllocator,
    ) {}

    public function handle(Product $product, int $quantity): void
    {
        $this->stockAllocator->allocate($product, $quantity);
    }
}
```

Call it like this:

```php
AllocateInventory::run($product, $quantity);
```

## Why `::run(...)` is the preferred caller

The docs note that Laravel Actions provides `make` and `run` helpers, and also recommends ensuring the action is always resolved from the container.

That matters here because `::run(...)` gives you:
- a short, obvious call site
- consistent container resolution
- constructor injection without manual plumbing
- straightforward package-native mocking and spying in tests

Avoid call sites like:

```php
(new AllocateInventory($allocator))->handle($product, $quantity);
```

That works in plain PHP, but it bypasses the package conventions you are standardising on.

## Placement and naming

The docs recommend:
- naming actions as explicit verb-led tasks
- using an `Actions` folder

Follow that directly:
- `CreateInvoice`
- `SendTrackingEmail`
- `ResolveCarrierLoss`
- `RecalculateOrderTotals`

Avoid vague nouns or manager-style names:
- `InvoiceService`
- `TrackingHelper`
- `OrderProcessor`

If the class is doing multiple distinct tasks, split it.

## How the package works

The docs explain that Laravel Actions adds a special interceptor on the container and can wrap the class in decorators for different execution styles such as controllers or jobs.

That is useful background, but the important rule in these standards is narrower:
- treat Laravel Actions primarily as the package for action classes
- keep the default mental model as "container-resolved object action with `handle(...)` and `::run(...)`"

You do not need to widen every action into a controller, listener, command, or job just because the package can do that.

## Boundary with queued work

Laravel Actions also supports dispatching actions as jobs. The package docs cover `dispatch`, `dispatchSync`, `configureJob`, job middleware, and other queue-facing features.

These standards intentionally draw a firmer line:
- use Laravel Actions for action classes
- use native Laravel Jobs and Queues for queued classes
- if queued work needs shared domain logic, call the action from the job

Example:

```php
<?php

namespace App\Jobs;

use App\Actions\Inventory\AllocateInventory;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;

class AllocateInventoryJob implements ShouldQueue
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {}

    public function handle(): void
    {
        AllocateInventory::run($this->product, $this->quantity);
    }
}
```

That keeps the queue-specific concerns on the job and the business task on the action.
