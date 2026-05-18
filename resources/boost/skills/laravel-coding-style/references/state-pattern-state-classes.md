# State Pattern State Classes

Load this reference when adding Spatie state casts, abstract state classes, concrete state classes, defaults, or valid transition config.

State classes centralize workflow behaviour so lifecycle rules are not scattered through controllers, services, jobs, observers, and policies.

## Use State Classes For Workflows

Good candidates:
- `SalesOrder`: `Draft`, `AwaitingPayment`, `ReadyToDespatch`, `Despatched`, `Cancelled`
- `Invoice`: `Draft`, `Issued`, `PartiallyPaid`, `Paid`, `Void`
- `FulfillmentOrder`: `PendingRouting`, `Routed`, `SubmittedToSupplier`, `Accepted`, `InTransit`, `Delivered`, `Failed`, `Cancelled`

Bad candidate:

```text
User:
- active
- inactive
```

That is usually a boolean or enum.

## Install And Cast

Applications that use complex state workflows should install the package:

```bash
composer require spatie/laravel-model-states
```

Models with states should use Spatie's trait and contract:

```php
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\HasStatesContract;

class Invoice extends Model implements HasStatesContract
{
    use HasStates;

    protected $casts = [
        'state' => InvoiceState::class,
    ];
}
```

The backing database column should be a string column.

## Abstract State Class

Each state field gets one abstract state class. Configure defaults and valid transitions there:

```php
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<\App\Models\Invoice>
 */
abstract class InvoiceState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Issued::class)
            ->allowTransition(Issued::class, Paid::class, MarkInvoicePaid::class)
            ->allowTransition(Issued::class, Void::class);
    }
}
```

Prefer explicit transition config. Use `allowAllTransitions()` only when every registered state can truly transition to every other state.

## Concrete States

Concrete state classes should stay focused on state-specific behaviour and presentation metadata:

```php
class Paid extends InvoiceState
{
    public function label(): string
    {
        return 'Paid';
    }
}
```

Avoid putting unrelated orchestration, IO, or cross-system side effects into concrete state classes.

## Avoid Scattered State Checks

Bad:

```php
if ($invoice->state->equals(Paid::class)) {
    // ...
}
```

Bad:

```php
switch ($invoice->state) {
    case 'draft':
        // ...
        break;

    case 'paid':
        // ...
        break;
}
```

Good:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
);
```

The model delegates to the current state field and transition config. Application callers should not instantiate state classes for normal workflow actions.

## Practical Split

- Model method: what domain action does the caller request?
- Abstract state class: which transitions are valid?
- Concrete state class: what behaviour or labels belong to this state?
- Transition class: what contextual data and model mutations are required?
- Model event or `StateChanged` listener: what reacts before or after the transition?
