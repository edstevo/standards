# Action Pattern

Load this reference when introducing, refactoring, or reviewing Laravel action classes, especially when a controller, observer, listener, command, job, or service is starting to contain business workflow logic.

## Core Rule

Use an action for one clear business operation or application use case.

Actions should:
- represent one operation
- have a narrow responsibility
- expose clear inputs and outputs
- be easy to test in isolation
- be deterministic where practical
- keep framework coupling low unless the action is explicitly adapting a framework entry point

Good action names:
- `CreateSalesOrder`
- `AllocatePayment`
- `ResolveVatRate`
- `DispatchShipment`
- `ReserveInventory`
- `GenerateInvoice`
- `CloseFulfillmentOrder`

Avoid:
- broad lifecycle coordinators such as `ProcessEntireOrderLifecycle`
- generic service replacements such as `OrderService`, `PaymentService`, or `VatService`
- actions that coordinate unrelated workflows
- hidden side effects that are not obvious from the action name

## Laravel Actions Package

Standardize application actions on `lorisleiva/laravel-actions`.

Actions are synchronous. They are the only pattern in this coding style that should use the Laravel Actions package. Do not use `lorisleiva/laravel-actions` for jobs, listeners, controllers, commands, or queued dispatch boundaries. Those framework entry points may call actions, but they should remain native Laravel classes.

Package reference: `https://www.laravelactions.com/2.x/basic-usage.html`

Use the `AsAction` trait and prefer `handle(...)` as the action method. Call actions in application code with `::run(...)` when the work should happen synchronously in the current process.

```php
use Lorisleiva\Actions\Concerns\AsAction;

class AllocatePayment
{
    use AsAction;

    public function handle(
        Payment $payment,
        Invoice $invoice,
        Money $amount,
    ): PaymentAllocation {
        // Allocation logic.
    }
}
```

```php
$allocation = AllocatePayment::run(
    payment: $payment,
    invoice: $invoice,
    amount: $amount,
);
```

Actions become the public application entry point for the business operation. Controllers, commands, listeners, observers, jobs, and UI actions should usually delegate to actions instead of owning substantial business logic directly.

## When To Use Actions

Use an action when the code answers:

```text
What business operation is occurring?
```

Good candidates:
- creating a sales order
- allocating a payment
- resolving a VAT rate
- dispatching a shipment
- raising a refund
- generating an invoice
- closing a fulfillment order

Prefer actions over asking first:
- which controller needs this?
- which observer should handle this?
- should this be a service?
- should this be queued?

The queue decision is separate. The action names the business behaviour; a native Laravel job owns asynchronous execution when needed.

## Good Action Design

A good action performs one cohesive operation.

```php
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveVatRate
{
    use AsAction;

    public function handle(
        Country $shipToCountry,
        VatCategory $category,
        SupplyContext $context,
    ): VatRate {
        // VAT resolution logic.
    }
}
```

Bad:

```php
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessEntireOrderLifecycle
{
    use AsAction;

    public function handle(): void
    {
        // Payments.
        // Inventory.
        // Shipment.
        // Refunds.
        // Invoicing.
        // Analytics.
        // Notifications.
    }
}
```

If one action starts to contain several unrelated business branches, split it into explicit smaller actions or move lifecycle-specific behaviour to the model/state layer and interchangeable algorithms to strategies.

## Actions And DTOs

DTOs pair strongly with actions. Use DTOs when an action needs a structured input contract instead of several loose scalars or a raw associative array.

Good:

```php
CreateInvoice::run(
    CreateInvoiceData::from([
        'salesOrder' => $salesOrder,
        'dueDate' => $dueDate,
    ]),
);
```

Bad:

```php
CreateInvoice::run($request->all());
```

Prefer `spatie/laravel-data` DTOs for request, command, import, integration, and AI-readable action payloads. Load `dto-pattern.md` when designing an action payload or replacing raw arrays.

## Actions And Specifications

Actions execute workflows. Specifications answer reusable boolean business questions such as whether the workflow is allowed.

Use a specification when eligibility logic would otherwise be duplicated across actions, controllers, observers, jobs, policies, models, or tests.

```php
class DispatchShipment
{
    use AsAction;

    public function __construct(
        private readonly CanDispatchSpecification $canDispatch,
    ) {}

    public function handle(Shipment $shipment): void
    {
        if (! $this->canDispatch->isSatisfiedBy($shipment->salesOrder)) {
            throw new CannotDispatchShipmentException();
        }

        $shipment->markAsDispatched();
    }
}
```

The action performs the operation. The specification answers the eligibility question. Load `specification-pattern.md` when a boolean business rule is reusable, composable, or unclear at the call site.

## Actions And State Pattern

The action receives the instruction to perform the business operation. The model/state layer decides whether the entity can do it in its current lifecycle state.

Actions should not duplicate lifecycle conditionals already owned by the State Pattern.

Bad:

```php
class MarkInvoiceAsPaid
{
    use AsAction;

    public function handle(Invoice $invoice, CarbonInterface $paidAt): void
    {
        if ($invoice->state === 'draft') {
            // ...
        } elseif ($invoice->state === 'awaiting_payment') {
            // ...
        } elseif ($invoice->state === 'paid') {
            // ...
        }
    }
}
```

Good:

```php
class MarkInvoiceAsPaid
{
    use AsAction;

    public function handle(
        Invoice $invoice,
        CarbonInterface $paidAt,
    ): void {
        $invoice->markAsPaid($paidAt);
    }
}
```

For this coding style, new complex lifecycle workflows should use `spatie/laravel-model-states` through `state-pattern.md`. The expressive model method delegates to Spatie state config and custom transition classes.

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

Responsibility split:
- Action: receives and coordinates the business operation
- Model method: exposes the domain lifecycle API
- State config/transition class: enforces valid transitions and applies state-specific mutations
- Model events/domain events: announce completed lifecycle changes
- Observers/listeners/jobs: react to completed changes

Do not create a custom `transitionTo(...)` abstraction for new complex workflows in this codebase. If working in a legacy app that already uses custom state classes instead of Spatie, keep transition behaviour in one shared trait or base abstraction; do not duplicate transition persistence and event dispatching in each action or model.

## State Hooks And Events

State-specific mutations belong with the transition itself, usually in a Spatie custom transition class when context is needed. Cross-cutting reactions belong behind explicit model events, domain events, observers, listeners, jobs, or follow-up actions.

Load `state-pattern-transitions.md` for transition mechanics and `state-pattern-events-and-policies.md` for event, observer, and policy boundaries.

## State Event Naming

Use event names that match the lifecycle language exposed by the model method, such as `paid`, `cancelled`, `fulfilled`, `despatched`, or `closed`.

Use business-specific events when the domain action matters. Use Spatie's generic `StateChanged` event for generic infrastructure such as audit trails or analytics. Details live in `state-pattern-events-and-policies.md`.

## Actions And Strategy Pattern

The action coordinates the use case. Strategies own interchangeable algorithms or business rules.

Use a strategy when the operation needs one of several algorithms selected by context, such as VAT, pricing, shipping rates, supplier selection, routing modes, or integration-specific behaviour.

```php
class ResolveVatRate
{
    use AsAction;

    public function handle(
        SalesOrder $salesOrder,
        SalesOrderLine $line,
    ): VatRate {
        $strategy = app(VatStrategyResolver::class)
            ->forSalesOrder($salesOrder);

        return $strategy->resolveRate(
            salesOrder: $salesOrder,
            line: $line,
        );
    }
}
```

The action should not absorb every algorithm into a conditional tree. Load `strategy-pattern.md` when multiple interchangeable implementations exist or are being introduced.

## Actions With Factories And Managers

Actions are usually the right place to coordinate managers and factories for a use case. Controllers should not normally select integration providers, build SDK clients, or choose concrete strategies directly.

```php
class SyncInvoiceToAccounting
{
    use AsAction;

    public function handle(Invoice $invoice): ExternalInvoiceReferenceData
    {
        $client = app(AccountingManager::class)
            ->forModel($invoice);

        return $client->createInvoice(
            InvoiceData::from($invoice),
        );
    }
}
```

The action coordinates the operation. The manager selects the connection. The factory builds the client internally. Load `factory-manager-pattern.md` when provider, driver, connection, adapter, or strategy selection is involved.

## Actions, Specifications, States, And Strategies

Use this split:
- Action Pattern: what business operation are we performing?
- Specification Pattern: is this reusable business rule or eligibility check satisfied?
- State Pattern: is this operation allowed in the entity's current lifecycle state?
- Strategy Pattern: which interchangeable algorithm or business rule should we use?

```php
class DispatchShipment
{
    use AsAction;

    public function handle(Shipment $shipment): void
    {
        $rate = ResolveShippingRate::run($shipment);

        $shipment->markAsDispatched(
            shippingRate: $rate,
        );

        ShipmentDispatched::dispatch($shipment);
    }
}
```

In this example:
- `DispatchShipment` is the action
- `Shipment::markAsDispatched(...)` exposes the lifecycle operation
- the shipment state config and transition class enforce lifecycle rules
- a specification may check reusable dispatch eligibility before the transition
- `ResolveShippingRate` may use a strategy
- `ShipmentDispatched` announces the completed business event

## Actions And Events

Actions perform work. Events announce completed changes.

Actions may dispatch explicit domain events after successful completion when the event is part of the business operation.

```php
class DispatchShipment
{
    use AsAction;

    public function handle(Shipment $shipment): void
    {
        $shipment->markAsDispatched();

        ShipmentDispatched::dispatch($shipment);
    }
}
```

Keep event dispatch obvious from the action's name and responsibility. Avoid hidden side effects where an action named `ResolveVatRate` unexpectedly sends notifications, syncs integrations, or mutates unrelated models.

## Actions And Jobs

Actions are synchronous. Jobs are asynchronous.

Use the Laravel Actions package for synchronous action classes called with `::run(...)`. Use native Laravel jobs for queued, delayed, retryable, asynchronous, integration-heavy, or long-running work.

When a queued job needs action logic, the job owns queue concerns and calls the action from `handle(...)`:

```php
use App\Actions\Fulfillment\DispatchShipment as DispatchShipmentAction;

class DispatchShipment implements ShouldQueue
{
    public function __construct(
        public readonly Shipment $shipment,
    ) {}

    public function handle(): void
    {
        DispatchShipmentAction::run($this->shipment);
    }
}
```

The job owns queue contracts, retries, middleware, backoff, uniqueness, and after-commit semantics. The action owns the synchronous business operation.

Load `workflow-jobs-actions.md` for job naming, `::dispatch(...)`, Horizon, model serialization, and after-commit rules.

## Actions And Observers

Observers react to global model lifecycle events and delegate. They should not contain the action's workflow.

Good:

```php
class PaymentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Payment $payment): void
    {
        AllocatePayment::run($payment);
    }
}
```

If the observer reaction is slow, retryable, integration-heavy, or must be queued, dispatch a native job and let the job call the action. Load `observer-pattern.md` for full observer rules.

## Transactions

Actions are often a good place for transaction boundaries because they represent a business operation that must succeed or fail as a unit.

```php
class AllocatePayment
{
    use AsAction;

    public function handle(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            // Allocation logic.
        });
    }
}
```

Keep transactional consistency close to the operation. Do not hide unrelated side effects inside the same transaction just because they are convenient to call from the action.

## Testing Actions

Test actions directly and in isolation when they contain business behaviour.

```php
it('allocates payment to invoice', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->create();

    AllocatePayment::run(
        payment: $payment,
        invoice: $invoice,
        amount: Money::GBP(1000),
    );

    expect($invoice->fresh()->balance)->toBe(0);
});
```

Useful action tests prove:
- the expected model graph or returned value is produced
- transactions protect multi-write behaviour where relevant
- state transitions are requested through the model API
- strategies are selected through resolvers when selection matters
- domain events or jobs are dispatched when they are part of the action contract
- invalid inputs fail clearly

When testing a caller, Laravel Actions package fakes may be useful:

```php
AllocatePayment::shouldRun()
    ->with($payment)
    ->andReturn($allocation);
```

Use action fakes to prove delegation at boundaries. Test the action's own business behaviour in its own focused test file.

## Directory And Naming

Follow existing project structure first. Good defaults are:

```text
app/Actions/Sales/CreateSalesOrder.php
app/Actions/Vat/ResolveVatRate.php
app/Actions/Fulfillment/DispatchShipment.php
```

or domain-local module folders:

```text
app/Modules/Sales/Actions/CreateSalesOrder.php
app/Modules/Vat/Actions/ResolveVatRate.php
app/Modules/Fulfillment/Actions/DispatchShipment.php
```

Prefer explicit verb-led names over generic service names:
- `CreateSalesOrder`, not `OrderService`
- `AllocatePayment`, not `PaymentService`
- `ResolveVatRate`, not `VatService`

Use a service only when it represents a cohesive long-lived collaborator, integration boundary, or domain concept that is not itself a single operation.

## Agent Rules

- Introduce an action when a clear business operation is being added or extracted.
- Use `AsAction` and `handle(...)`; call synchronous actions with `::run(...)`.
- Use the Laravel Actions package only for action classes.
- Reserve `::dispatch(...)` for native Laravel jobs/events, never `AsAction` classes.
- Use DTOs for structured action payloads; do not pass raw request arrays into actions.
- Use managers and factories inside actions when a use case needs provider, driver, connection, adapter, or strategy selection.
- Use specifications inside actions for reusable side-effect-free eligibility checks; do not bury repeated boolean business rules in action conditionals.
- Keep actions focused on one use case with explicit inputs and outputs.
- Prefer explicit verb-led action names over vague service names.
- Keep controllers, commands, listeners, observers, and UI handlers thin by delegating business work to actions.
- Use native Laravel jobs for asynchronous execution; jobs may call actions from `handle(...)`.
- Delegate lifecycle rules to expressive model methods and state transitions, not action-level `if`/`elseif` state trees.
- Use strategies for interchangeable algorithms; do not put every business rule branch inside the action.
- Put transaction boundaries in actions when the action owns a multi-write business operation.
- Add focused action tests for meaningful business behaviour.
