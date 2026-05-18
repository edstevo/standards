# State Pattern Transitions

Load this reference when adding expressive model methods, Spatie transition classes, before/after model events, or transition persistence rules.

Do not create an application-level transition trait for this pattern. Use `spatie/laravel-model-states` for transition validation, serialization, casting, query scopes, validation, and transition events.

## Expressive Model Methods

Application callers should use expressive model methods:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
);
```

The model method owns authorization-adjacent workflow clarity, before/after model events, and the package transition call:

```php
class Invoice extends Model implements HasStatesContract
{
    use HasStates;

    protected $observables = [
        'paying',
        'paid',
    ];

    protected $casts = [
        'state' => InvoiceState::class,
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

The `paying` event is the before hook. The `paid` event is the after hook. Use clear domain event names for each model method.

## Simple Transitions

Use Spatie transition config for allowed state changes:

```php
abstract class InvoiceState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Issued::class)
            ->allowTransition(Issued::class, Paid::class);
    }
}
```

The model method may call the package transition directly when no extra data is needed:

```php
public function issue(): void
{
    if ($this->fireModelEvent('issuing') === false) {
        return;
    }

    $this->state->transitionTo(Issued::class);

    $this->fireModelEvent('issued', false);
}
```

## Custom Transition Classes

Use custom Spatie transition classes when the transition needs extra model data, timestamps, references, audit values, or other contextual mutations.

```php
use Spatie\ModelStates\Transition;

class MarkInvoicePaid extends Transition
{
    public function __construct(
        private Invoice $invoice,
        private CarbonInterface $paidAt,
    ) {
    }

    public function handle(): Invoice
    {
        $this->invoice->state = new Paid($this->invoice);
        $this->invoice->paid_at = $this->paidAt;

        $this->invoice->saveQuietly();

        return $this->invoice;
    }
}
```

Register the transition class in the abstract state config:

```php
abstract class InvoiceState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->allowTransition(Issued::class, Paid::class, MarkInvoicePaid::class);
    }
}
```

The model method stays expressive:

```php
public function markAsPaid(CarbonInterface $paidAt): void
{
    if ($this->fireModelEvent('paying') === false) {
        return;
    }

    $this->state->transitionTo(Paid::class, $paidAt);

    $this->fireModelEvent('paid', false);
}
```

## Before Hooks

Use before model events for checks that must happen before the transition is attempted:
- validate model prerequisites
- stop the transition by returning `false`
- throw a domain exception for invalid business conditions
- perform no external side effects

Before hooks are synchronous. Do not make before observers `ShouldHandleEventsAfterCommit`.

## After Hooks

Use after model events for model lifecycle reactions that should run after the transition has persisted:
- timeline entries
- audit records
- native jobs
- Laravel domain events
- simple global model lifecycle reactions

After observers should implement `ShouldHandleEventsAfterCommit` when they dispatch jobs, write audit records, or depend on committed data.

## Persistence

In custom transition classes, prefer `saveQuietly()` when the expressive model method fires explicit before/after model events. This keeps workflow reactions on named lifecycle events instead of generic `updating` or `updated` observers.

Do not directly mutate state fields from controllers, jobs, services, or observers.
