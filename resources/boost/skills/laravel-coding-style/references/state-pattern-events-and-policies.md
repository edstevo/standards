# State Pattern Events And Policies

Load this reference when deciding between before/after model events, Spatie `StateChanged`, domain events, observers, Laravel Policies, and reusable specification checks around model state transitions.

## Before And After Model Events

Expressive model methods should expose custom Laravel model events around important state transitions:
- before event: fires before the Spatie transition is attempted
- after event: fires after the Spatie transition has persisted

Example:

```php
class Invoice extends Model implements HasStatesContract
{
    use HasStates;

    protected $observables = [
        'paying',
        'paid',
    ];

    public function markAsPaid(CarbonInterface $paidAt): void
    {
        if ($this->fireModelEvent('paying') === false) {
            return;
        }

        $this->state->transitionTo(Paid::class, $paidAt);

        $this->fireModelEvent('paid', false);
    }
}
```

Use before events for synchronous pre-transition checks. Use after events for model lifecycle reactions.

## Observer Boundary

Before-event observers run synchronously and may halt the transition. Use them only for small pre-transition checks that cannot live cleanly in the model method, transition class, state config, policy, or specification.

After-event observers react after persistence. They should stay shallow and delegate to actions, native jobs/events, or simple audit entries. Load `observer-pattern.md` for full observer rules.

## Spatie StateChanged

Spatie dispatches `Spatie\ModelStates\Events\StateChanged` after a transition succeeds. Use it for generic state-change infrastructure that applies across a state field or across several models.

Good uses:
- generic audit records
- generic timeline records
- analytics around state field changes
- infrastructure reactions that need the initial state, final state, model, transition class, and field name

Use custom model events when the domain action matters:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
);
```

The `paid` model event is clearer than a generic state-changed listener when business language matters.

## Domain Events

Business-significant Laravel domain events should be dispatched explicitly from after model event observers, Spatie `StateChanged` listeners, or dedicated workflow code.

Use domain events for reactions that are:
- business significant
- cross-system
- queued
- audited
- likely to grow

Example:

```php
class InvoiceObserver implements ShouldHandleEventsAfterCommit
{
    public function paid(Invoice $invoice): void
    {
        InvoicePaid::dispatch($invoice);
    }
}
```

## Avoid Hidden Workflow Rules

Avoid hiding transition rules inside generic observers.

Bad:

```php
class SalesOrderObserver
{
    public function updated(SalesOrder $salesOrder): void
    {
        if ($salesOrder->despatch_state->equals(Despatched::class)) {
            // Important workflow behaviour hidden here.
        }
    }
}
```

Good:

```php
$salesOrder->markAsDespatched();
```

The model method makes the lifecycle action explicit, and the after model event or Spatie `StateChanged` listener handles reactions.

## Policies Versus State Pattern

Laravel Policies answer:

```text
Is this user allowed to do this?
```

The State Pattern answers:

```text
Is this entity allowed to do this in its current lifecycle state?
```

Good separation:

```php
Gate::authorize('cancel', $salesOrder);

$salesOrder->cancel(
    reason: 'customer_request',
    cancelledBy: $user->id,
);
```

The policy checks whether the user may attempt cancellation. Spatie transition config and model methods check whether the sales order can be cancelled now.

Bad policy example:

```php
public function cancel(User $user, SalesOrder $salesOrder): bool
{
    return $salesOrder->lifecycle_state->equals(Draft::class);
}
```

That puts lifecycle rules inside authorization.

Better:

```php
public function cancel(User $user, SalesOrder $salesOrder): bool
{
    return $user->canManageSalesOrders();
}
```

Policies control who may attempt the action. State transitions control whether the entity may transition.

## Specifications Versus Policies And State

Use a specification when a domain eligibility rule is reusable outside one lifecycle transition.

```php
if (! app(CanReleasePaymentSpecification::class)->isSatisfiedBy($salesOrder)) {
    throw new CannotReleasePaymentException();
}
```

Responsibility split:
- Policy: can this user attempt the operation?
- State pattern: is this transition valid from the current lifecycle state?
- Specification: does this entity satisfy a reusable business eligibility rule?

Do not put authorization into specifications. Do not replace Spatie transition configuration with specifications. Specifications should remain side-effect-free rule objects.
