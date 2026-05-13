# After-Commit Observers

Load this reference when writing observers or observer-driven workflow reactions.

Observers are the reaction layer. They coordinate follow-up work after model events; they do not perform heavy work inline.

## Rules

Observers should:
- implement `ShouldHandleEventsAfterCommit`
- dispatch timeline/audit jobs or events
- dispatch next-step workflow jobs or events
- dispatch integration sync jobs or events
- call model methods that trigger further explicit model events when chaining transitions
- guard heavily before dispatching follow-up work

Observers must not:
- call external integrations directly
- execute synchronous external follow-up actions inline
- absorb domain state-transition logic that belongs on the model
- use broad `updated` handlers when an explicit domain event would be clearer

```php
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function fulfilled(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderFulfilled);
    }
}
```

## Guard Follow-Up Work

Before dispatching next-step work, guard on meaningful state and required relationships.

```php
public function created(FulfillmentOrder $fulfillmentOrder): void
{
    RecordTimelineEvent::dispatch($fulfillmentOrder, TimelineEventType::FulfillmentOrderCreated);

    if (! $fulfillmentOrder->hasNotBeenCancelled() || $fulfillmentOrder->fulfillable === null) {
        return;
    }

    if ($fulfillmentOrder->fulfillment_method->isFromStock()) {
        RaiseWarehouseOrderForFulfillmentOrder::dispatch($fulfillmentOrder);
    }

    if ($fulfillmentOrder->fulfillment_method->isDropship()) {
        RaisePurchaseOrderForFulfillmentOrder::dispatch($fulfillmentOrder);
    }
}
```

## Updated Handlers

Keep `updated` handlers narrow and guarded by changed fields.

```php
public function updated(Fulfillment $fulfillment): void
{
    if ($fulfillment->wasChanged(['tracking_company', 'tracking_number'])) {
        RecordTimelineEvent::dispatch($fulfillment, TimelineEventType::FulfillmentTrackingUpdated);
        SyncFulfillmentTrackingToShopify::dispatch($fulfillment);
    }
}
```

If update logic grows beyond narrow field-change handling, prefer an explicit event such as `trackingUpdated`.

## Transactions

For atomic workflows:
- wrap related writes in `DB::transaction(...)`
- keep observers on `ShouldHandleEventsAfterCommit`
- make side-effect jobs implement `ShouldQueueAfterCommit` when they always need committed data
- reserve dispatch-level `->afterCommit()` for one-off or conditional dispatch sites where the job itself cannot own that guarantee

Expected result:
- committed transaction -> observer side effects run
- rolled-back transaction -> observer side effects do not run
