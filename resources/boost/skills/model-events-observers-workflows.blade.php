---
name: model-events-observers-workflows
description: Use explicit model events (often fired by behaviour traits) and after-commit observers to drive timelines, next-step jobs, and integration actions in a reusable, project-agnostic way.
---

# Model Events, Observers, and Workflow Orchestration

Across projects, I prefer a consistent pattern:

1. **Models** expose expressive domain methods (often via behaviour traits) like `markAsSubmitted()`, `markAsCancelled()`, `markAsFulfilled()`.
2. Those methods **persist state** (`saveQuietly()`) and then **fire explicit model events** (`fireModelEvent('submitted', false)`).
3. **Observers** react to those events **after commit** and perform side effects:
   - record timeline/audit events
   - dispatch “next step” orchestration jobs
   - call integration actions (or dispatch integration jobs)

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

@verbatim
<code-snippet name="Behaviour method fires explicit event" lang="php">
$this->saveQuietly();
$this->fireModelEvent('fulfilled', false);
</code-snippet>
@endverbatim

## 2) Register custom observable events inside the trait
If you fire custom events, register them in the trait initializer with `addObservableEvents([...])`.

@verbatim
<code-snippet name="Trait registers custom events" lang="php">
public function initializeCanBeFulfilled(): void
{
    $this->addObservableEvents([
        'fulfilled',
    ]);
}
</code-snippet>
@endverbatim

## 3) Observers are the “reaction layer”
Observers should be where side effects happen:
- timeline/audit logging
- dispatching next-step jobs
- integration calls (or integration jobs)

Models stay focused on state + invariants.

## 4) Observers should run after commit
Implement `ShouldHandleEventsAfterCommit` so observers only run once database changes are committed.

This avoids:
- writing timeline events for rolled-back transactions
- calling integrations for data that never persisted
- dispatching orchestration jobs that operate on missing state

@verbatim
<code-snippet name="Observer after-commit contract" lang="php">
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    // ...
}
</code-snippet>
@endverbatim

## 5) Observers dispatch orchestration jobs and integration jobs/actions
Two common categories:

### A) “Next step” workflow jobs
Triggered by domain events to progress the workflow. For example:
- `RaiseWarehouseOrderForFulfillmentOrder`
- `RaisePurchaseOrderForFulfillmentOrder`
- `SubmitPurchaseOrder`
- `ScheduleCollection`
- `NotifyCustomer`
- `SyncToErp`

### B) Integration calls (prefer Actions/Jobs)
If an observer is calling an integration directly, keep it thin and delegate to an integration service / action.
In high-latency integrations, prefer dispatching a job.

## 6) Use guards to prevent invalid side effects
Observers should guard heavily:
- don’t dispatch next steps if the model is cancelled
- don’t call integrations if required associations are missing
- don’t perform “updated” logic unless relevant fields changed

## Examples (project-agnostic patterns)

## Example 1: Orchestrate “created → route → dispatch next step”
A common pattern is: when something is created, write a timeline event, then dispatch follow-on jobs based on the model state.

@verbatim
<code-snippet name="Observer dispatches next-step jobs on created" lang="php">
<?php

namespace App\Observers;

use App\Models\FulfillmentOrder;
use App\Modules\Fulfillment\Jobs\RaiseWarehouseOrderForFulfillmentOrder;
use App\Modules\Purchasing\Jobs\RaisePurchaseOrderForFulfillmentOrder;
use App\Services\Timeline\Timeline;
use App\Services\Timeline\TimelineEventType;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(FulfillmentOrder $fulfillmentOrder): void
    {
        Timeline::for($fulfillmentOrder)->event(TimelineEventType::FulfillmentOrderCreated);

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
        Timeline::for($fulfillmentOrder)->event(TimelineEventType::FulfillmentOrderFulfilled);
    }

    public function closed(FulfillmentOrder $fulfillmentOrder): void
    {
        Timeline::for($fulfillmentOrder)->event(TimelineEventType::FulfillmentOrderClosed);
    }

    public function archived(FulfillmentOrder $fulfillmentOrder): void
    {
        Timeline::for($fulfillmentOrder)->event(TimelineEventType::FulfillmentOrderArchived);
    }
}
</code-snippet>
@endverbatim

Notes:
- The observer is an orchestrator, not a domain model.
- It uses explicit guards to avoid dispatching invalid work.

## Example 2: Integrations triggered on created / updated
Integration sync often happens from observers, with `updated` guarded by `wasChanged()` to avoid noisy calls.

@verbatim
<code-snippet name="Observer triggers integration on created/updated with guards" lang="php">
<?php

namespace App\Observers;

use App\Integrations\Shopify\Shopify;
use App\Models\Fulfillment;
use App\Services\Timeline\Timeline;
use App\Services\Timeline\TimelineEventType;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Fulfillment $fulfillment): void
    {
        Timeline::for($fulfillment)->event(TimelineEventType::FulfillmentCreated, eventVersion: 1);

        Shopify::fulfillments()->create($fulfillment);
    }

    public function updated(Fulfillment $fulfillment): void
    {
        if ($fulfillment->wasChanged(['tracking_company', 'tracking_number'])) {
            Timeline::for($fulfillment)->event(TimelineEventType::FulfillmentTrackingUpdated, eventVersion: 1);

            Shopify::fulfillments()->updateTracking($fulfillment);
        }
    }
}
</code-snippet>
@endverbatim

Guidelines:
- Keep `updated` handlers narrowly scoped and guarded.
- Prefer “domain events” (e.g. `trackingUpdated`) if updates get more complex.

## Example 3: Behaviour trait → explicit event → observer reaction
Traits produce a clean API in jobs/services, while observers centralise side effects.

@verbatim
<code-snippet name="Job calls behaviour methods; observer reacts after commit" lang="php">
// In a job/service:
$order->markAsSubmitted();
$order->markAsAccepted();

// In trait:
$this->saveQuietly();
$this->fireModelEvent('accepted', false);

// In observer:
public function accepted(PurchaseOrder $purchaseOrder): void
{
    Timeline::for($purchaseOrder)->event(TimelineEventType::PurchaseOrderAccepted, data: [
        'accepted_ip' => $purchaseOrder->accepted_ip,
        'accepted_at' => $purchaseOrder->accepted_at,
    ]);

    // Optionally: dispatch next step jobs here too.
}
</code-snippet>
@endverbatim

## Design guidelines

## Keep the layers clean
- **Models/Traits:** state transitions + invariants + explicit events
- **Observers:** orchestration + integration triggers + timeline/audit
- **Jobs/Actions:** heavy lifting, retries, integration IO, long workflows

## Prefer jobs for external IO
If the integration:
- can be slow
- can fail transiently
- needs retry/backoff
then dispatch a job from the observer instead of calling directly.

@verbatim
<code-snippet name="Prefer integration job for reliability" lang="php">
public function created(Fulfillment $fulfillment): void
{
    Timeline::for($fulfillment)->event(TimelineEventType::FulfillmentCreated);

    SyncFulfillmentToShopify::dispatch($fulfillment);
}
</code-snippet>
@endverbatim

## Do / Don’t

### Do
- Fire explicit domain events from behaviour traits.
- Register custom events via `addObservableEvents()` in trait initializers.
- Implement `ShouldHandleEventsAfterCommit` on observers.
- Use observers to dispatch next-step jobs and integration actions/jobs.
- Guard observer logic heavily (`hasNotBeenCancelled()`, `wasChanged()`, required relations).

### Don’t
- Put orchestration logic inside model methods.
- Use generic `updated` for everything when explicit events would be clearer.
- Call integrations without guarding for relevant changes.
- Do heavy work inline in observers—delegate to jobs/actions.
