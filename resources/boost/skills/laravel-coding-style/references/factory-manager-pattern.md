# Factory And Manager Pattern

Load this reference when object creation, provider selection, driver selection, connection selection, adapter construction, strategy/specification selection, or integration client setup is being repeated or scattered across Laravel code.

## Core Rule

Use factories and managers when object creation or implementation selection should be centralized instead of scattered across controllers, actions, jobs, observers, listeners, or adapters.

A factory creates an object.

A manager selects, configures, optionally caches, and returns the correct implementation, often by driver name, provider, connection, tenant, model context, or configuration.

Factories answer:

```text
How do I build this object?
```

Managers answer:

```text
Which implementation or connection should I use here?
```

## Factory Pattern

Use a factory when construction logic is complex, repeated, or depends on runtime data.

Factories should:
- centralize object creation
- hide setup details from business code
- return a clear interface or base type
- keep construction rules consistent
- avoid duplicated object construction across the application
- resolve concrete classes through Laravel's container when dependencies are involved

Good factory candidates:
- `AccountingClientFactory`
- `ShippingCarrierFactory`
- `VatStrategyFactory`
- `PaymentGatewayFactory`
- `SupplierApiClientFactory`
- `OAuthConnectionFactory`

Factories must not:
- contain business workflow logic
- mutate database state as part of ordinary construction
- send emails, dispatch jobs, or perform use-case actions
- return unrelated object types
- become large nested conditional trees

Example:

```php
interface AccountingClient
{
    public function createInvoice(InvoiceData $data): ExternalInvoiceReferenceData;
}
```

```php
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class AccountingClientFactory
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function make(ExternalConnection $connection): AccountingClient
    {
        return match ($connection->provider) {
            'quickbooks' => $this->container
                ->make(QuickbooksClient::class)
                ->forConnection($connection),
            'xero' => $this->container
                ->make(XeroClient::class)
                ->forConnection($connection),
            default => throw new InvalidArgumentException(
                "Unsupported accounting provider [{$connection->provider}].",
            ),
        };
    }
}
```

Usage:

```php
$client = app(AccountingClientFactory::class)->make($connection);

$client->createInvoice($invoiceData);
```

The caller does not need to know how QuickBooks or Xero clients are constructed.

## Manager Pattern

Use a manager when the application has multiple named implementations, drivers, providers, connections, stores, disks, gateways, tenants, or integration accounts.

Managers are common in Laravel. Laravel's cache, queue, mail, filesystem, database, notification, and broadcasting systems use manager-style APIs.

A manager usually:
- exposes a simple `driver(...)`, `connection(...)`, `store(...)`, `disk(...)`, or domain-specific selector
- resolves configuration or model context
- selects the correct factory or implementation
- caches resolved drivers where appropriate
- provides a consistent public API
- hides provider-specific setup from the rest of the app

Good manager candidates:
- `AccountingManager`
- `ShippingManager`
- `PaymentManager`
- `SupplierIntegrationManager`
- `TaxResolverManager`
- `WarehouseRoutingManager`

Example:

```php
class AccountingManager
{
    /** @var array<string, AccountingClient> */
    private array $resolved = [];

    public function __construct(
        private readonly AccountingClientFactory $factory,
    ) {}

    public function connection(string $key): AccountingClient
    {
        return $this->resolved[$key] ??= $this->factory->make(
            ExternalConnection::query()
                ->where('key', $key)
                ->where('type', 'accounting')
                ->firstOrFail(),
        );
    }

    public function forModel(Model $model): AccountingClient
    {
        return $this->factory->make($model->accountingConnection);
    }
}
```

Usage:

```php
$client = app(AccountingManager::class)->connection('company_a');

$client->createInvoice($invoiceData);
```

This should feel similar to Laravel-style APIs:

```php
Cache::store('redis');
Storage::disk('s3');
DB::connection('mysql');
```

Managers are allowed to be named `Manager` when they genuinely manage named drivers, providers, connections, or implementations. Do not use `Manager` as a vague name for workflow orchestration.

## Factory Versus Manager

A factory creates an implementation.

A manager chooses which implementation, driver, provider, connection, or configured instance should be used.

Factories are lower level. Managers are higher level.

```php
$client = app(AccountingManager::class)->connection('company_a');
```

Internally:

```text
ExternalConnection record
-> AccountingClientFactory
-> QuickbooksClient or XeroClient
```

The manager owns selection and connection lookup. The factory owns construction.

Use a factory without a manager when callers already have all context needed to construct the object.

Use a manager when callers should not know how to find configuration, provider records, tenant connections, or cached driver instances.

## Factories, Managers, And DTOs

DTOs should move structured data into and out of clients, strategies, actions, adapters, and integration boundaries.

Prefer `spatie/laravel-data` DTOs over raw arrays.

```php
use Spatie\LaravelData\Data;

final class InvoiceData extends Data
{
    public string $number;

    public string $currency;

    public int $customerId;

    /** @var array<int, InvoiceLineData> */
    public array $lines;
}
```

Factories and managers should return services that accept DTOs:

```php
$invoiceData = InvoiceData::from($payload);

$client = app(AccountingManager::class)->connection('company_a');

$client->createInvoice($invoiceData);
```

The manager chooses the connection. The factory creates the client. The DTO carries the data. The client performs the work.

Load `dto-pattern.md` when designing payloads for factory-created clients or manager-selected services.

## Factories, Managers, And Strategies

A strategy encapsulates interchangeable business rules or algorithms.

Factories and managers often select strategies, but the strategy performs the algorithm.

```php
interface VatCalculationStrategy
{
    public function calculate(VatCalculationData $data): VatResultData;
}
```

```php
use Illuminate\Contracts\Container\Container;

class VatCalculationStrategyFactory
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function make(SupplyContext $context): VatCalculationStrategy
    {
        return match ($context) {
            SupplyContext::Domestic => $this->container->make(DomesticVatStrategy::class),
            SupplyContext::IntraEuB2b => $this->container->make(IntraEuB2bVatStrategy::class),
            SupplyContext::Export => $this->container->make(ExportVatStrategy::class),
        };
    }
}
```

Usage:

```php
$strategy = app(VatCalculationStrategyFactory::class)
    ->make($salesOrder->supply_context);

$result = $strategy->calculate($vatCalculationData);
```

The factory decides which strategy to use. The strategy performs the calculation. The DTO carries the input and output.

Avoid large conditional chains across the application when a strategy factory, resolver, or manager can centralize selection.

## Factories, Managers, And Specifications

A specification answers a reusable boolean business rule or eligibility question.

Most specifications can be resolved directly from the container. Use a factory or manager only when the application has multiple specification implementations selected by tenant, provider, region, connection, configuration, or model context.

```php
interface RefundEligibilitySpecification
{
    public function isSatisfiedBy(Invoice $invoice): bool;
}
```

```php
class RefundEligibilitySpecificationFactory
{
    public function make(RefundPolicyType $type): RefundEligibilitySpecification
    {
        return match ($type) {
            RefundPolicyType::Standard => app(StandardRefundEligibilitySpecification::class),
            RefundPolicyType::Marketplace => app(MarketplaceRefundEligibilitySpecification::class),
        };
    }
}
```

The factory or manager selects the rule implementation. The specification answers the boolean question. It should still be side-effect free.

Load `specification-pattern.md` when the main concern is reusable eligibility logic rather than object construction or implementation selection.

## Factories, Managers, And State Pattern

State represents a model's current lifecycle position.

For new complex Laravel lifecycle workflows in this coding style, use `spatie/laravel-model-states` through `state-pattern.md`. Do not introduce a custom state factory or custom `transitionTo(...)` trait when Spatie already owns state casting, transition validation, transition classes, and state events.

Good for new complex workflows:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
);
```

The expressive model method delegates to Spatie state config and transition classes.

Factories or managers may still be appropriate when:
- working in a legacy app that already uses custom state classes
- resolving non-model state machines
- mapping external state codes into internal state classes or DTOs
- selecting a lifecycle-related adapter, not performing the transition itself

Do not scatter state mutation logic across observers, controllers, jobs, actions, factories, or managers. Lifecycle rules belong in the model/state layer.

## Factories, Managers, And Actions

Actions represent one clear business operation or workflow.

Factories and managers should usually be used inside actions, not directly in controllers.

```php
use Lorisleiva\Actions\Concerns\AsAction;

class SyncInvoiceToAccounting
{
    use AsAction;

    public function handle(Invoice $invoice): ExternalInvoiceReferenceData
    {
        $invoiceData = InvoiceData::from($invoice);

        $client = app(AccountingManager::class)
            ->forModel($invoice);

        return $client->createInvoice($invoiceData);
    }
}
```

The action coordinates the use case. The manager selects the service. The factory builds the implementation. The DTO carries the data.

## Factories, Managers, And Adapters

Adapters translate between your application and external systems. The rest of the app should speak to your interface, not directly to vendor SDKs.

```php
interface AccountingClient
{
    public function createInvoice(InvoiceData $data): ExternalInvoiceReferenceData;
}
```

```php
class QuickbooksClient implements AccountingClient
{
    public function createInvoice(InvoiceData $data): ExternalInvoiceReferenceData
    {
        // Translate InvoiceData into a QuickBooks SDK payload.
    }
}
```

```php
class XeroClient implements AccountingClient
{
    public function createInvoice(InvoiceData $data): ExternalInvoiceReferenceData
    {
        // Translate InvoiceData into a Xero API payload.
    }
}
```

The factory creates the adapter. The manager selects the connection. The action uses the interface.

This prevents vendor-specific logic from spreading through actions, controllers, jobs, observers, or tests.

## Factories, Managers, And Builders

Use builders when constructing complex DTOs, payloads, or domain objects step by step.

Factories are good for choosing or creating services.

Managers are good for selecting named drivers, providers, or connections.

Builders are good for assembling complex data.

```php
$payload = QuickbooksInvoicePayloadBuilder::make()
    ->fromData($invoiceData)
    ->build();
```

An adapter may use a builder internally:

```php
class QuickbooksClient implements AccountingClient
{
    public function createInvoice(InvoiceData $data): ExternalInvoiceReferenceData
    {
        $payload = QuickbooksInvoicePayloadBuilder::make()
            ->fromData($data)
            ->build();

        // Send payload to QuickBooks.
    }
}
```

The builder should not decide which accounting provider is used. That belongs to the manager or factory.

## Factories, Managers, And Policies

Policies answer whether a user or actor is allowed to do something.

Factories and managers should not replace policies.

```php
$this->authorize('syncToAccounting', $invoice);

SyncInvoiceToAccounting::run($invoice);
```

Responsibility split:
- Policy: can this user perform this action?
- Action: perform the business use case.
- Manager: select the correct service or connection.
- Factory: create the correct implementation.

## Preferred Flow

A good Laravel flow looks like this:

```text
Controller or command
-> Action
-> DTO
-> Specification check if eligibility rules apply
-> Manager
-> Factory
-> Strategy or Adapter
-> Work is performed
-> State transition if needed
-> Model event or domain event where appropriate
```

## Practical Example

```php
use Lorisleiva\Actions\Concerns\AsAction;

class CapturePayment
{
    use AsAction;

    public function handle(Invoice $invoice, MoneyData $amount): PaymentResultData
    {
        if (! app(CanCapturePaymentSpecification::class)->isSatisfiedBy($invoice)) {
            throw new CannotCapturePaymentException();
        }

        $gateway = app(PaymentManager::class)
            ->forInvoice($invoice);

        $result = $gateway->capture(
            PaymentCaptureData::from([
                'invoice' => $invoice,
                'amount' => $amount,
            ]),
        );

        if ($result->successful) {
            $invoice->markAsPaid($result->paidAt);
        }

        return $result;
    }
}
```

In this example:
- `CapturePayment` is the action.
- `CanCapturePaymentSpecification` checks reusable capture eligibility.
- `Invoice::markAsPaid(...)` exposes lifecycle behaviour.
- `PaymentManager` selects the payment connection.
- `PaymentGatewayFactory` builds the payment client internally.
- `PaymentCaptureData` is the input DTO.
- `PaymentResultData` is the output DTO.

## Directory And Naming

Follow existing project structure first. Good defaults are:

```text
app/Factories/Accounting/AccountingClientFactory.php
app/Managers/AccountingManager.php
app/Contracts/Accounting/AccountingClient.php
app/Integrations/Accounting/QuickbooksClient.php
app/Integrations/Accounting/XeroClient.php
```

or domain-local modules:

```text
app/Modules/Accounting/Factories/AccountingClientFactory.php
app/Modules/Accounting/Managers/AccountingManager.php
app/Modules/Accounting/Contracts/AccountingClient.php
app/Modules/Accounting/Clients/QuickbooksClient.php
app/Modules/Accounting/Clients/XeroClient.php
```

Good factory names:
- `PaymentGatewayFactory`
- `AccountingClientFactory`
- `ShippingCarrierFactory`
- `VatCalculationStrategyFactory`
- `SupplierApiClientFactory`

Good manager names:
- `PaymentManager`
- `AccountingManager`
- `ShippingManager`
- `SupplierIntegrationManager`
- `TaxResolverManager`

Avoid:
- `GeneralFactory`
- `ObjectFactory`
- `IntegrationManager` when it manages unrelated integrations
- `WorkflowManager`
- `ProcessManager`

## Agent Rules

- Before adding a new provider, strategy, state, or integration, look for an existing manager, factory, interface, DTO, action, state, or adapter to extend.
- Introduce a factory when construction logic is repeated, complex, provider-specific, or context-dependent.
- Introduce a manager when provider, driver, connection, tenant, or implementation selection is repeated or should be hidden from callers.
- Do not scatter provider, driver, connection, or implementation selection across controllers, jobs, observers, actions, or tests.
- Keep managers focused on selection and configuration, not business workflows.
- Keep factories focused on construction, not side effects.
- Return interfaces or base types from factories and managers.
- Resolve implementations through Laravel's container when dependencies are involved.
- Use DTOs for factory-created client inputs and outputs.
- Use specifications when repeated eligibility checks need a named, testable rule object.
- Use actions as the place where managers and factories are coordinated for a use case.
- Use builders inside adapters when payload construction becomes noisy.
- Do not introduce every pattern at once; introduce the smallest pattern that removes duplicated construction, scattered selection, or unclear boundaries.
