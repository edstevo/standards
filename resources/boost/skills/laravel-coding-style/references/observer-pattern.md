# Observer Pattern

Load this reference when writing observers, reviewing observer-driven workflow reactions, or removing hidden logic from Eloquent lifecycle hooks.

## Core Rule

Observers are an extremely shallow reaction layer for global Eloquent lifecycle consequences.

An observer method should answer only:
- what model lifecycle event happened?
- what small global reaction should be triggered?

Use observers sparingly. If behaviour belongs to one checkout, import, admin action, command, or application workflow, call the action directly from that workflow instead of hiding it in an observer.

## What Observers May Do

Observer methods may:
- implement `ShouldHandleEventsAfterCommit` by default
- apply technical guards such as `wasChanged(...)`
- call a synchronous action with `::run(...)`
- dispatch a native Laravel job or event
- record a simple factual audit entry
- call a clearly named model/domain method
- use a specification as a shallow guard for a global reaction

```php
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class FulfillmentOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function fulfilled(FulfillmentOrder $fulfillmentOrder): void
    {
        RecordTimelineEvent::dispatch(
            $fulfillmentOrder,
            TimelineEventType::FulfillmentOrderFulfilled,
        );
    }
}
```

Any `::dispatch(...)` class used from an observer should be a native Laravel job or event using framework conventions. Do not dispatch classes that use `AsAction`.

If reusable synchronous workflow logic lives in an action but must run asynchronously, dispatch a native job and call the action from the job's `handle(...)`.

## What Observers Must Not Do

Observers must not:
- contain business workflows, hidden domain rules, or orchestration logic
- define business state checks in private helper methods
- contain second-order functions or private workflow helpers
- enforce core domain invariants
- make complex state decisions
- become mini service classes or hidden orchestration layers
- duplicate rules that belong on the model, in actions, in specifications, in strategies, in policies, or in state classes
- call external integrations directly
- execute synchronous external follow-up actions inline
- dispatch `lorisleiva/laravel-actions` action classes as queued jobs
- absorb domain state-transition logic that belongs on the model
- use broad `updated` handlers when an explicit domain event would be clearer

Avoid helper methods such as:
- `shouldReleasePayment()`
- `handleInvoiceWorkflow()`
- `syncShipmentStatus()`
- `recordLifecycleEntry()`
- `processAllocationChanges()`

If logic needs extraction into another observer method, move it to the owning pattern instead.

## Rule Placement

- Core invariant: model or state class.
- Workflow: action or dedicated workflow class.
- Reusable boolean eligibility rule: specification.
- Context-specific interchangeable algorithm: strategy.
- Authorization: policy.
- Provider/connection selection: manager or factory.
- Structured data contract: DTO.
- Slow, retryable, integration-heavy, or asynchronous follow-up: native Laravel job.

## Good Observer Shapes

Call an action when the reaction is local, fast, and synchronous:

```php
public function closed(FulfillmentOrder $fulfillmentOrder): void
{
    ResolveClosedFulfillmentOrder::run($fulfillmentOrder);
}
```

Use a specification only as a shallow guard; the observer still does not own the rule:

```php
public function updated(SalesOrder $salesOrder): void
{
    if (! $salesOrder->wasChanged('state')) {
        return;
    }

    if (! app(CanReleasePaymentSpecification::class)->isSatisfiedBy($salesOrder)) {
        return;
    }

    ReleasePaymentForSalesOrder::run($salesOrder);
}
```

Keep factual audit entries simple:

```php
public function updated(SalesOrder $salesOrder): void
{
    if (! $salesOrder->wasChanged('state')) {
        return;
    }

    $salesOrder->recordAuditLedger()
        ->summary('Sales order state changed')
        ->metadata([
            'from' => $salesOrder->getOriginal('state'),
            'to' => $salesOrder->state,
        ])
        ->commit();
}
```

Keep `updated` handlers narrow and guarded by changed fields:

```php
public function updated(Fulfillment $fulfillment): void
{
    if (! $fulfillment->wasChanged(['tracking_company', 'tracking_number'])) {
        return;
    }

    RecordTimelineEvent::dispatch(
        $fulfillment,
        TimelineEventType::FulfillmentTrackingUpdated,
    );

    SyncFulfillmentTrackingToShopify::dispatch($fulfillment);
}
```

If update logic grows beyond narrow field-change handling, prefer an explicit model event such as `trackingUpdated`.

## Bad Observer Shapes

Bad business helper:

```php
private function shouldReleasePaymentInAdvanceOrder(SalesOrder $salesOrder): bool
{
    return $salesOrder->payment_terms === PaymentTerms::PIA
        && $salesOrder->state === SalesOrderState::WaitingForPayment;
}
```

Bad hidden workflow:

```php
public function created(Payment $payment): void
{
    // Large payment allocation workflow hidden inside the observer.
}
```

Bad private audit workflow:

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

If audit recording needs workflow decisions, branching, reusable metadata assembly, or integration context, move it to a named action, native job, audit service, or model method.

## Transactions

For atomic workflows:
- wrap related writes in `DB::transaction(...)`
- keep observers on `ShouldHandleEventsAfterCommit`
- make side-effect jobs implement `ShouldQueueAfterCommit` when they always need committed data
- reserve dispatch-level `->afterCommit()` for one-off or conditional dispatch sites where the job itself cannot own that guarantee

Expected result:
- committed transaction -> observer side effects run
- rolled-back transaction -> observer side effects do not run
