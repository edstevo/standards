# Specification Pattern

Load this reference when business rule checks, eligibility logic, or domain qualification decisions are being duplicated across controllers, observers, actions, jobs, policies, services, models, or tests.

## Core Rule

Use the Specification Pattern for reusable business rule checks and eligibility logic.

A specification answers one business question with a boolean result.

Specifications make business decisions readable, reusable, testable, and composable. They should be side-effect free and should not mutate application state.

Specifications answer questions like:
- is this allowed?
- does this qualify?
- can this happen?
- does this entity meet the rule?

Good specification candidates:
- `CanDispatchSpecification`
- `EligibleForRefundSpecification`
- `CanAllocateStockSpecification`
- `CanReleasePaymentSpecification`
- `CanTransitionToPaidSpecification`
- `SupplierCanDropshipSpecification`
- `IsVatExemptSpecification`

## Standard Shape

Use an intention-revealing class name and a boolean method. `isSatisfiedBy(...)` is a good default because it is widely recognized in Specification Pattern examples.

```php
class CanDispatchSpecification
{
    public function isSatisfiedBy(SalesOrder $salesOrder): bool
    {
        return $salesOrder->is_paid
            && $salesOrder->has_allocated_stock
            && ! $salesOrder->is_cancelled;
    }
}
```

Usage:

```php
if (! app(CanDispatchSpecification::class)->isSatisfiedBy($salesOrder)) {
    throw new CannotDispatchSalesOrderException();
}
```

Prefer method names that read like business language when that is clearer:

```php
class SupplierCanDropshipSpecification
{
    public function isSatisfiedBy(Supplier $supplier, Product $product): bool
    {
        return $supplier->dropships
            && $supplier->stocks($product)
            && $supplier->is_active;
    }
}
```

## Responsibilities

Specifications should:
- answer one clear business question
- return a boolean result
- remain side-effect free
- contain no persistence writes
- contain no orchestration logic
- be composable with other specifications
- be reusable across workflows
- encapsulate domain rules cleanly
- receive all needed context through method arguments or constructor dependencies

Specifications must not:
- mutate models
- write to the database
- dispatch jobs or events
- send notifications
- make HTTP requests
- perform external IO
- coordinate workflows
- replace actions, jobs, policies, strategies, or states
- become service classes with many unrelated checks

## Composition

Specifications become useful when rules need to be reused or combined.

```php
class CanAllocateStockSpecification
{
    public function __construct(
        private readonly HasAvailableInventorySpecification $inventory,
        private readonly ProductIsActiveSpecification $productIsActive,
        private readonly WarehouseIsEnabledSpecification $warehouseIsEnabled,
    ) {}

    public function isSatisfiedBy(
        Product $product,
        Warehouse $warehouse,
    ): bool {
        return $this->inventory->isSatisfiedBy($product, $warehouse)
            && $this->productIsActive->isSatisfiedBy($product)
            && $this->warehouseIsEnabled->isSatisfiedBy($warehouse);
    }
}
```

This avoids large nested conditionals:

```php
if (
    $product->active
    && $product->inventory > 0
    && ! $product->discontinued
    && $warehouse->enabled
) {
    // Allocate stock.
}
```

The code should express business intent directly:

```php
if (! app(CanAllocateStockSpecification::class)->isSatisfiedBy($product, $warehouse)) {
    throw new CannotAllocateStockException();
}
```

## Specifications And Actions

Actions execute workflows. Specifications decide eligibility.

Inject specifications into actions through the action constructor, or resolve them explicitly inside the action. Do not pass a specification to `::run(...)` unless the caller is genuinely selecting a rule as input.

```php
use Lorisleiva\Actions\Concerns\AsAction;

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

The action performs the workflow. The specification decides whether the workflow is allowed.

## Specifications And State Pattern

States represent lifecycle position. Specifications answer reusable domain eligibility questions.

Use a specification when the rule is broader than one transition or needs to be reused outside the state class.

```php
class Invoice extends Model implements HasStatesContract
{
    use HasStates;

    public function markAsShipped(): void
    {
        if (! app(CanShipInvoiceSpecification::class)->isSatisfiedBy($this)) {
            throw new CannotShipInvoiceException();
        }

        $this->state->transitionTo(Shipped::class);
    }
}
```

The model/state layer owns lifecycle behaviour and valid transitions. The specification owns reusable business eligibility logic.

Do not replace Spatie state transition configuration with specifications. Spatie state config should still define valid lifecycle transitions for complex workflows.

## Specifications And Strategies

Strategies determine how something happens. Specifications determine whether it may happen.

```php
class RefundInvoice
{
    use AsAction;

    public function __construct(
        private readonly EligibleForRefundSpecification $eligibleForRefund,
        private readonly RefundStrategyResolver $refundStrategies,
    ) {}

    public function handle(Invoice $invoice): void
    {
        if (! $this->eligibleForRefund->isSatisfiedBy($invoice)) {
            throw new RefundNotAllowedException();
        }

        $this->refundStrategies
            ->resolve($invoice)
            ->refund($invoice);
    }
}
```

The specification validates eligibility. The strategy executes the selected algorithm.

## Specifications And Policies

Policies answer authorization questions. Specifications answer domain business rule questions.

Policy:

```php
$user->can('refund', $invoice);
```

Specification:

```php
app(EligibleForRefundSpecification::class)->isSatisfiedBy($invoice);
```

Policies are usually actor and permission focused. Specifications are domain-rule focused.

Sometimes both are required:

```php
if (
    $user->can('refund', $invoice)
    && app(EligibleForRefundSpecification::class)->isSatisfiedBy($invoice)
) {
    RefundInvoice::run($invoice);
}
```

## Specifications And Observers

Observers may use a specification as a shallow guard for a global lifecycle reaction, but they should not define the rule themselves.

If the eligibility check is part of one workflow rather than a global lifecycle reaction, put the specification check inside the action instead of the observer. Load `observer-pattern.md` for the observer shape.

## Specifications And DTOs

Use DTOs when the rule needs structured input that is not naturally represented by one model.

```php
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class RefundEligibilityData extends Data
{
    public Invoice $invoice;

    public MoneyData $amount;

    public CarbonInterface $requestedAt;
}
```

```php
class EligibleForRefundSpecification
{
    public function isSatisfiedBy(RefundEligibilityData $data): bool
    {
        return $data->invoice->isPaid()
            && $data->amount->isLessThanOrEqualTo($data->invoice->refundableAmount())
            && $data->requestedAt->lessThanOrEqualTo($data->invoice->refundWindowEndsAt());
    }
}
```

The DTO carries the structured context. The specification answers the business question.

## Specifications With Factories And Managers

Most specifications can be resolved directly from the Laravel container.

Use a factory or manager only when selecting between multiple specification implementations is itself a repeated, context-dependent rule.

Examples:
- tenant-specific eligibility rules
- provider-specific refund rules
- country-specific VAT exemption checks
- supplier-specific dropship eligibility checks

Do not create a manager for one specification implementation.

## Directory And Naming

Follow existing project structure first. Good default locations are:

```text
app/Specifications/Sales/CanDispatchSpecification.php
app/Specifications/Payments/EligibleForRefundSpecification.php
app/Specifications/Vat/IsVatExemptSpecification.php
```

or domain-local modules:

```text
app/Modules/Sales/Specifications/CanDispatchSpecification.php
app/Modules/Payments/Specifications/EligibleForRefundSpecification.php
app/Modules/Vat/Specifications/IsVatExemptSpecification.php
```

Good names:
- `CanDispatchSpecification`
- `EligibleForRefundSpecification`
- `CanAllocateStockSpecification`
- `CanReleasePaymentSpecification`
- `CanTransitionToPaidSpecification`
- `SupplierCanDropshipSpecification`
- `IsVatExemptSpecification`

Avoid vague names:
- `OrderSpecification`
- `BusinessRule`
- `EligibilityService`
- `RulesChecker`
- `ValidatorService`

## Testing

Test specifications directly when they encode meaningful business rules.

Useful specification tests prove:
- satisfied cases return `true`
- unsatisfied cases return `false`
- boundary conditions are clear
- composed specifications call or combine the expected rules
- no persistence, events, jobs, or external IO occur

Example:

```php
it('allows dispatch when an order is paid and stock is allocated', function () {
    $salesOrder = SalesOrder::factory()->create([
        'is_paid' => true,
        'has_allocated_stock' => true,
        'is_cancelled' => false,
    ]);

    expect(app(CanDispatchSpecification::class)->isSatisfiedBy($salesOrder))
        ->toBeTrue();
});
```

## Agent Rules

- Introduce a specification when a boolean business rule is duplicated, reused across workflows, or hard to name at the call site.
- Keep specifications side-effect free.
- Keep each specification focused on one business question.
- Compose specifications instead of spreading the same conditionals across actions, observers, jobs, policies, models, and tests.
- Use actions for workflows, strategies for interchangeable algorithms, states for lifecycle behaviour, policies for authorization, and specifications for reusable domain eligibility checks.
- Prefer constructor injection for specifications used by actions, strategies, managers, or other specifications.
- Do not make HTTP requests, dispatch jobs, fire events, or write to the database from a specification.
- Add focused tests for specifications that protect real business rules.
