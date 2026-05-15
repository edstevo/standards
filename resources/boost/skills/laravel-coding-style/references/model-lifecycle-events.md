# Model Lifecycle Events

Load this reference when adding model transition methods, behaviour traits, custom observable events, `saveQuietly()`, or `fireModelEvent(...)`.

## Preferred Pattern

Models expose expressive domain methods, often via behaviour traits:
- `markAsSubmitted()`
- `markAsAccepted()`
- `markAsCancelled()`
- `markAsFulfilled()`
- `markAsClosed()`

Transition methods should:
1. guard invalid or idempotent states first
2. mutate model state
3. persist with `saveQuietly()`
4. fire exactly one explicit model event with `fireModelEvent(...)`

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

Use `saveQuietly()` intentionally here. The explicit domain event is the orchestration trigger, so unrelated generic lifecycle handlers should not run accidentally.

## Custom Observable Events

Prefer explicit domain events over generic `saved` or broad `updated` observers.

Examples:
- `submitted`
- `accepted`
- `rejected`
- `cancelled`
- `fulfilled`
- `closed`
- `archived`
- `trackingUpdated`

Register custom events in the behaviour trait initializer with `addObservableEvents([...])`.

```php
public function initializeCanBeFulfilled(): void
{
    $this->addObservableEvents([
        'fulfilled',
    ]);
}
```

## Boundaries

Models and behaviour traits own:
- state transitions
- transition guards
- invariants
- explicit event firing

They should not own:
- timeline/audit side effects
- external integration IO
- long-running workflow orchestration
- queued retryable work

Those reactions belong in after-commit observers and native jobs. If a reusable synchronous action already contains the business logic, the job may call that action from `handle(...)`.
