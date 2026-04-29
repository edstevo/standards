---
name: model-events-observers-workflows
description: Use explicit model events (often fired by behaviour traits) and after-commit observers that only dispatch events/jobs or call methods that trigger further explicit events.
---

# Model Events, Observers, and Workflow Orchestration

Across projects, I prefer a consistent pattern:

1. **Models** expose expressive domain methods (often via behaviour traits) like `markAsSubmitted()`, `markAsCancelled()`, `markAsFulfilled()`.
2. Those methods **persist state** (`saveQuietly()`) and then **fire explicit model events** (`fireModelEvent('submitted', false)`).
3. **Observers** react to those events **after commit** and only dispatch events/jobs or call methods that trigger further explicit events:
   - dispatch timeline/audit recording jobs/events
   - dispatch “next step” orchestration jobs/events
   - dispatch integration sync jobs/events

This keeps models slim, keeps side effects out of domain state transitions, and makes workflows readable.

Use this skill whenever you:
- introduce new model lifecycle events or state transitions
- want to orchestrate multi-step domain workflows (e.g. “created → route → dispatch”)
- integrate with external systems based on model changes (Shopify, ERP, couriers, etc.)

## Core conventions

## 1) Prefer explicit domain events over generic `saved/updated`
Instead of relying on `updated`, define domain-specific events like:
- `submitted`, `accepted`, `rejected`
- `cancelled`, `fulfilled`, `closed`, `archived`
- `trackingUpdated`, `picked`, `packed`, `shipped` (whatever fits)

Explicit events reduce conditional spaghetti in observers and make intent obvious.

```php
$this->saveQuietly();
$this->fireModelEvent('fulfilled', false);
```

## 2) Register custom observable events inside the trait
If you fire custom events, register them in the trait initializer with `addObservableEvents([...])`.

```php
public function initializeCanBeFulfilled(): void
{
    $this->addObservableEvents([
        'fulfilled',
    ]);
}
```

## 3) Observers are the “reaction layer”
Observers should only coordinate follow-up work by dispatching events/jobs or calling methods that trigger further explicit events:
- timeline/audit recording jobs/events
- next-step orchestration jobs/events
- integration sync jobs/events

Synchronous external follow-up actions must not run inline inside observer methods.

Models stay focused on state + invariants.

## 4) Observers should run after commit
Implement `ShouldHandleEventsAfterCommit` so observers only run once database changes are committed.

This avoids:
- writing timeline events for rolled-back transactions
- calling integrations for data that never persisted
- dispatching orchestration jobs that operate on missing state

```php
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    // ...
}
```

## 5) Observers dispatch orchestration events/jobs only
Two common categories:

### A) “Next step” workflow jobs
Triggered by domain events to progress the workflow. For example:
- `RaiseWarehouseOrderForFulfillmentOrder`
- `RaisePurchaseOrderForFulfillmentOrder`
- `SubmitPurchaseOrder`
- `ScheduleCollection`
- `NotifyCustomer`
- `SyncToErp`

### B) Integration sync jobs/events
Observers should dispatch jobs/events that perform integration work outside the observer.
Never call integration clients or run synchronous integration actions inline in observers.

## 6) Use guards to prevent invalid side effects
Observers should guard heavily:
- don’t dispatch next steps if the model is cancelled
- don’t dispatch integration jobs/events if required associations are missing
- don’t perform “updated” logic unless relevant fields changed

## 7) Use `saveQuietly()` intentionally in transition methods
When a transition method mutates state before firing an explicit domain event:
- apply guards first
- mutate state
- persist with `saveQuietly()`
- fire exactly one explicit event via `fireModelEvent(...)`

Why: this prevents unrelated generic lifecycle handlers from running accidentally. The explicit domain event is the orchestration trigger.

```php
public function markAsCarrierLoss(): void
{
    if ($this->status === FulfillmentModelStatus::CARRIER_LOSS && $this->isClosed()) {
        return;
    }

    $this->status = FulfillmentModelStatus::CARRIER_LOSS;
    $this->saveQuietly();

    $this->fireModelEvent('carrierLoss', false);
}
```

## 8) Transaction + after-commit semantics
For atomic multi-write workflows:
- wrap writes in `DB::transaction(...)`
- keep observers on `ShouldHandleEventsAfterCommit`
- dispatch side-effect jobs with after-commit semantics (`->afterCommit()` or job `$afterCommit = true`)

Result:
- committed transaction => observer side effects run
- rolled-back transaction => observer side effects do not run

## 9) Test lifecycle events in isolation (model tests)
Lifecycle transitions should be tested in model tests (for example `tests/Models/FulfillmentTest.php`).

Rules:
- one event transition per test
- assert model state change + direct side effects for that event
- fake follow-up events and assert they were dispatched
- do not assert follow-up event outcomes in the same test; cover those in separate tests

```php
use App\Models\Fulfillment;
use App\Services\Timeline\TimelineEventType;
use Illuminate\Support\Facades\Event;

it('marks fulfillment as closed and dispatches archived event', function () {
    Event::fake([
        'eloquent.archived: '.Fulfillment::class,
    ]);

    $result = (new \Tests\Support\ScenarioBuilder)->dropship()->fulfilled()->build();
    $fulfillment = $result->fulfillments->sole()->refresh();

    $fulfillment->markAsClosed();
    $fulfillment = $fulfillment->refresh();

    expect($fulfillment->closed_at)->not->toBeNull();
    Event::assertDispatched('eloquent.archived: '.Fulfillment::class);

    assertDatabaseHas('timeline_events', [
        'timelineable_id' => $fulfillment->getKey(),
        'timelineable_type' => get_class($fulfillment),
        'event' => TimelineEventType::FulfillmentClosed,
    ]);
});
```

## Examples (project-agnostic patterns)

## Example 1: Orchestrate “created → route → dispatch next step”
A common pattern is: when something is created, write a timeline event, then dispatch follow-on jobs based on the model state.

```php
<?php

namespace App\Observers;

use App\Models\FulfillmentOrder;
use App\Jobs\RecordTimelineEvent;
use App\Modules\Fulfillment\Jobs\RaiseWarehouseOrderForFulfillmentOrder;
use App\Modules\Purchasing\Jobs\RaisePurchaseOrderForFulfillmentOrder;
use App\Services\Timeline\TimelineEventType;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderCreated);

        if ($fulfillmentOrder->hasNotBeenCancelled() && $fulfillmentOrder->fulfillable !== null) {
            if ($fulfillmentOrder->fulfillment_method->isFromStock()) {
                RaiseWarehouseOrderForFulfillmentOrder::dispatch($fulfillmentOrder);
            }

            if ($fulfillmentOrder->fulfillment_method->isDropship()) {
                RaisePurchaseOrderForFulfillmentOrder::dispatch($fulfillmentOrder);
            }
        }
    }

    public function fulfilled(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderFulfilled);
    }

    public function closed(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderClosed);
    }

    public function archived(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderArchived);
    }
}
```

Notes:
- The observer is an orchestrator, not a domain model.
- It uses explicit guards to avoid dispatching invalid work.

## Example 2: Integration sync dispatched from created / updated
Integration sync should be dispatched from observers, with `updated` guarded by `wasChanged()` to avoid noisy calls.

```php
<?php

namespace App\Observers;

use App\Jobs\RecordTimelineEvent;
use App\Models\Fulfillment;
use App\Jobs\SyncFulfillmentToShopify;
use App\Jobs\SyncFulfillmentTrackingToShopify;
use App\Services\Timeline\TimelineEventType;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Fulfillment $fulfillment): void
    {
        RecordTimelineEvent::dispatch($fulfillment, TimelineEventType::FulfillmentCreated, eventVersion: 1);

        SyncFulfillmentToShopify::dispatch($fulfillment);
    }

    public function updated(Fulfillment $fulfillment): void
    {
        if ($fulfillment->wasChanged(['tracking_company', 'tracking_number'])) {
            RecordTimelineEvent::dispatch($fulfillment, TimelineEventType::FulfillmentTrackingUpdated, eventVersion: 1);

            SyncFulfillmentTrackingToShopify::dispatch($fulfillment);
        }
    }
}
```

Guidelines:
- Keep `updated` handlers narrowly scoped and guarded.
- Prefer “domain events” (e.g. `trackingUpdated`) if updates get more complex.

## Example 3: Behaviour trait → explicit event → observer reaction
Traits produce a clean API in jobs/services, while observers centralise side effects.

```php
// In a job/service:
$order->markAsSubmitted();
$order->markAsAccepted();

// In trait:
$this->saveQuietly();
$this->fireModelEvent('accepted', false);

// In observer:
public function accepted(PurchaseOrder $purchaseOrder): void
{
    RecordTimelineEvent::dispatch($purchaseOrder, TimelineEventType::PurchaseOrderAccepted, data: [
        'accepted_ip' => $purchaseOrder->accepted_ip,
        'accepted_at' => $purchaseOrder->accepted_at,
    ]);

    // Dispatch follow-up orchestration jobs/events here too.
}
```

## Design guidelines

## Keep the layers clean
- **Models/Traits:** state transitions + invariants + explicit events
- **Observers:** orchestration by dispatching jobs/events or calling methods that trigger further explicit events (no synchronous external follow-up work)
- **Jobs/Actions:** heavy lifting, retries, integration IO, long workflows

## Prefer jobs for external IO
If the integration:
- can be slow
- can fail transiently
- needs retry/backoff
then dispatch a job from the observer instead of calling directly.

```php
public function created(Fulfillment $fulfillment): void
{
    RecordTimelineEvent::dispatch($fulfillment, TimelineEventType::FulfillmentCreated);

    SyncFulfillmentToShopify::dispatch($fulfillment);
}
```

## Do / Don’t

### Do
- Fire explicit domain events from behaviour traits.
- Register custom events via `addObservableEvents()` in trait initializers.
- Use `saveQuietly()` in transition methods before firing explicit domain events.
- Implement `ShouldHandleEventsAfterCommit` on observers.
- Only fire events, jobs or methods that trigger further events in observers.
- Guard observer logic heavily (`hasNotBeenCancelled()`, `wasChanged()`, required relations).
- Test lifecycle events in isolation and fake follow-up events.

### Don’t
- Put orchestration logic inside model methods.
- Use generic `updated` for everything when explicit events would be clearer.
- Execute synchronous external follow-up actions inline in observers.
- Call integrations directly from observers.
