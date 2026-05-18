# State Pattern Multiple State Dimensions

Load this reference when one model has more than one independent state dimension, such as lifecycle state, approval state, despatch state, and delivery state.

Spatie model states supports multiple state fields on one model. Use separate state columns and casts for independent workflows instead of forcing everything into one large composite state.

## Example Dimensions

A `SalesOrder` can have several state dimensions at the same time:
- lifecycle state: `Draft`, `WaitingForApproval`, `InProgress`, `WaitingForPayment`, `Completed`
- approval state: `Submitted`, `Approved`, `Rejected`
- despatch state: `NotDespatched`, `PartiallyDespatched`, `Despatched`
- delivery state: `NotDelivered`, `PartiallyDelivered`, `Delivered`

These answer different questions and should usually be separate state fields.

## Model Casts

Name each state dimension explicitly:

```php
class SalesOrder extends Model implements HasStatesContract
{
    use HasStates;

    protected $casts = [
        'lifecycle_state' => SalesOrderLifecycleState::class,
        'approval_state' => SalesOrderApprovalState::class,
        'despatch_state' => SalesOrderDespatchState::class,
        'delivery_state' => SalesOrderDeliveryState::class,
    ];
}
```

Prefer abstract state classes that include the dimension name:
- `SalesOrderLifecycleState`
- `SalesOrderApprovalState`
- `SalesOrderDespatchState`
- `SalesOrderDeliveryState`

## Dimension Config

Each dimension owns its own defaults and allowed transitions:

```php
abstract class SalesOrderApprovalState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Submitted::class)
            ->allowTransition(Submitted::class, Approved::class)
            ->allowTransition(Submitted::class, Rejected::class);
    }
}
```

```php
abstract class SalesOrderDespatchState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(NotDespatched::class)
            ->allowTransition(NotDespatched::class, PartiallyDespatched::class)
            ->allowTransition(PartiallyDespatched::class, Despatched::class);
    }
}
```

Do not put approval transitions on despatch states, delivery transitions on lifecycle states, or unrelated workflow rules into one state config.

## Expressive Model Methods

Application code should call model methods. It should not instantiate state classes or choose state fields.

Good:

```php
$salesOrder->approve();
$salesOrder->markAsPartiallyDespatched();
$salesOrder->markAsDelivered();
```

Bad:

```php
$salesOrder->approval_state->transitionTo(Approved::class);
$salesOrder->despatch_state->transitionTo(PartiallyDespatched::class);
$salesOrder->delivery_state->transitionTo(Delivered::class);
```

The model delegates to the correct Spatie state field internally:

```php
class SalesOrder extends Model implements HasStatesContract
{
    public function approve(): void
    {
        if ($this->fireModelEvent('approving') === false) {
            return;
        }

        $this->approval_state->transitionTo(Approved::class);

        $this->fireModelEvent('approved', false);
    }

    public function markAsPartiallyDespatched(): void
    {
        if ($this->fireModelEvent('partiallyDespatching') === false) {
            return;
        }

        $this->despatch_state->transitionTo(PartiallyDespatched::class);

        $this->fireModelEvent('partiallyDespatched', false);
    }
}
```

## Cross-Dimension Effects

If one dimension should affect another, keep that orchestration explicit in a model method, after model event observer, Spatie `StateChanged` listener, or domain action.

Example:

```php
class SalesOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function approved(SalesOrder $salesOrder): void
    {
        if (! $salesOrder->canStartProgress()) {
            return;
        }

        $salesOrder->startProgress();
    }
}
```

Keep the rule named and visible. Do not hide cross-dimension changes in broad `updated` handlers.
