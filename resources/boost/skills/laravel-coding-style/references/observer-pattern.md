# Observer Pattern

Load this reference when writing observers or observer-driven workflow reactions.

Observers are a thin reaction layer for Eloquent lifecycle events. An observer should answer only:
- what model lifecycle event happened
- what follow-up action should be triggered

Use observers sparingly and only for global consequences that should happen whenever the model changes, regardless of where that change originated.

## Rules

Observers should:
- implement `ShouldHandleEventsAfterCommit` by default
- delegate immediately to a named job, event, action, service, or model method
- dispatch timeline/audit native jobs or events when the record should happen globally
- dispatch next-step workflow native jobs or events when the reaction is a global lifecycle consequence
- dispatch integration sync native jobs or events instead of calling integrations inline
- call model methods that trigger further explicit model events when chaining transitions
- ask the model, a domain service, or an eligibility/specification object for business decisions
- stay flat and readable

Observers must not:
- contain business workflows, hidden domain rules, or orchestration logic
- define business state checks in private helper methods
- grow private helper methods for lifecycle recording, workflow branching, or eligibility decisions
- call external integrations directly
- execute synchronous external follow-up actions inline
- dispatch `lorisleiva/laravel-actions` action classes as queued jobs
- absorb domain state-transition logic that belongs on the model
- run use-case-specific logic that belongs in a checkout, import, admin, command, or application service flow
- use broad `updated` handlers when an explicit domain event would be clearer

Any `::dispatch(...)` class used from an observer should be a native Laravel job/event. If the reusable workflow logic lives in an action, dispatch a native job and have that job call the action from `handle(...)`.

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

## Delegate Immediately

If an observer method grows beyond a few lines, extract the workflow to a named action, job, service, event, or model method.

```php
public function closed(FulfillmentOrder $fulfillmentOrder): void
{
    ResolveClosedFulfillmentOrder::dispatch($fulfillmentOrder);
}
```

Observers may ask the domain whether a reaction applies, but they should not define the rule themselves.

Bad:

```php
private function shouldReleasePaymentInAdvanceOrder(SalesOrder $salesOrder): bool
{
    return $salesOrder->payment_terms === PaymentTerms::PIA
        && $salesOrder->state === SalesOrderState::WaitingForPayment;
}
```

Good:

```php
public function paid(SalesOrder $salesOrder): void
{
    if (! $salesOrder->shouldReleasePaymentInAdvance()) {
        return;
    }

    ReleasePaymentInAdvanceOrder::dispatch($salesOrder);
}
```

## Avoid Private Methods

Private methods inside observers usually mean business rules, lifecycle recording, or workflow orchestration are being hidden in the persistence hook.

Bad:

```php
private function recordSalesOrderLifecycle(
    SalesOrder $salesOrder,
    AuditLedgerAction $action,
    string $summary,
    string $sourceMethod,
    array $metadata = [],
): void {
    $salesOrder->recordAuditLedger($action)
        ->outcome(LifecycleJournalOutcome::Succeeded)
        ->summary($summary)
        ->source(self::class, $sourceMethod)
        ->metadata($metadata)
        ->commit();
}
```

Good:

```php
public function released(SalesOrder $salesOrder): void
{
    $salesOrder->recordLifecycle(
        action: AuditLedgerAction::Released,
        summary: 'Payment in advance order released after payment confirmation.',
        source: self::class,
        sourceMethod: __METHOD__,
    );
}
```

## Guard Follow-Up Work

Technical guards are fine when they keep an observer reaction narrow, such as field-change checks in `updated` handlers or checking that a relationship required by a dispatched job exists.

Business guards belong on the model or in a named domain service/eligibility object. The observer should call that API instead of implementing the rule.

Keep `updated` handlers narrow and guarded by changed fields:

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

## Do Not Use Observers For Specific Use Cases

If behaviour only applies in one workflow or one application service, do not put it in an observer. Call the action directly from that workflow instead.

Avoid observers for:
- checkout-only rules
- import-only behaviour
- admin-only workflows
- one specific command path

Observers are for predictable global lifecycle consequences. A developer saving a model should not unknowingly trigger a large hidden workflow.

## Transactions

For atomic workflows:
- wrap related writes in `DB::transaction(...)`
- keep observers on `ShouldHandleEventsAfterCommit`
- make side-effect jobs implement `ShouldQueueAfterCommit` when they always need committed data
- reserve dispatch-level `->afterCommit()` for one-off or conditional dispatch sites where the job itself cannot own that guarantee

Expected result:
- committed transaction -> observer side effects run
- rolled-back transaction -> observer side effects do not run
