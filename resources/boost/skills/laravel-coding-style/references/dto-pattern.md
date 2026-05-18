# DTO Pattern

Load this reference when structured data is being passed between requests, controllers, actions, jobs, integrations, imports, exports, AI workflows, or service boundaries, especially when raw associative arrays are starting to move through the application.

## Core Rule

Use DTOs to move structured data through the application instead of raw associative arrays.

DTOs are especially important in applications that use AI coding agents, static analysis, and modern IDE tooling. AI agents, IDEs, and automated refactoring tools perform much better when applications use explicit typed contracts instead of loose array structures.

Prefer `spatie/laravel-data` for DTO implementation in Laravel applications. Avoid custom DTO infrastructure unless there is a strong project-specific reason.

Package reference: `https://spatie.be/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object`

## Why DTOs Matter

DTOs improve:
- readability
- maintainability
- autocomplete
- static analysis
- validation clarity
- safer refactoring
- explicit contracts between layers
- AI understanding of workflows and data structures

DTOs reduce ambiguity for both humans and machines.

## AI Coding Agent Benefits

AI coding agents perform best when:
- data structures are explicit
- types are known
- naming is consistent
- workflows are predictable
- boundaries are clear

DTOs help AI agents:
- understand intent faster
- avoid hallucinated array keys
- refactor more safely
- generate better tests
- review PRs more accurately
- maintain consistency across large codebases

Good:

```php
CreateShipment::run(
    ShipmentRequestData::from([
        'orderId' => $order->id,
        'warehouseId' => $warehouse->id,
        'carrier' => 'fedex',
    ]),
);
```

Bad:

```php
CreateShipment::run([
    'id' => $order->id,
    'location' => $warehouse->id,
    'type' => 'fedex',
]);
```

DTOs reduce guesswork.

## Preferred Laravel DTO Implementation

Use `spatie/laravel-data`:

```bash
composer require spatie/laravel-data
```

Data classes should usually extend `Spatie\LaravelData\Data`:

```php
use Spatie\LaravelData\Data;

final class ShipmentRequestData extends Data
{
    public int $orderId;

    public int $warehouseId;

    public string $carrier;
}
```

Spatie supports constructor property promotion and regular public properties. Prefer public typed properties for application DTOs when that makes the data shape faster to scan.

## Preferred DTO Style

Prefer public typed properties because they are easy to read quickly.

```php
use Spatie\LaravelData\Data;

final class SalesOrderData extends Data
{
    public int $customerId;

    public string $currency;

    /** @var array<int, SalesOrderLineData> */
    public array $lines;
}
```

Use constructor property promotion when the project already follows that style or when immutability is more important than scan speed:

```php
use Spatie\LaravelData\Data;

final class InvoiceCreationData extends Data
{
    public function __construct(
        public readonly SalesOrder $salesOrder,
        public readonly CarbonInterface $dueDate,
    ) {}
}
```

The goal is fast readability and explicit structure. Developers and AI agents should be able to scan a DTO and immediately understand the data shape.

## DTO Responsibilities

DTOs should:
- represent structured data
- have explicit typed properties
- define stable contracts between layers
- improve validation readability
- reduce fragile array usage
- be easy to serialize and transform
- remain predictable and lightweight

DTOs should not:
- contain business workflows
- perform side effects
- coordinate services
- become service classes
- hide persistence or integration IO

DTOs represent data, not behaviour.

## Good DTO Candidates

Good examples:
- `SalesOrderData`
- `VatCalculationData`
- `ShipmentRequestData`
- `RefundRequestData`
- `ShopifyWebhookData`
- `QuickBooksInvoiceData`
- `SupplierSubmissionData`
- `InvoiceCreationData`

Avoid vague names:
- `DataObject`
- `Payload`
- `GenericData`
- `BaseData`
- `RequestData` without a domain noun

## Preferred Workflow

Typical flow:

```text
Request
-> Validation
-> DTO
-> Action
-> Domain Logic
-> Persistence
```

Example:

```php
public function store(StoreSalesOrderRequest $request): RedirectResponse
{
    CreateSalesOrder::run(
        SalesOrderData::from($request->validated()),
    );

    return to_route('sales-orders.index');
}
```

Prefer validated input as the boundary source. Do not pass `$request->all()` into actions.

## DTOs And Actions

DTOs pair strongly with the Action Pattern. An action with several scalar inputs or a raw array payload often wants a DTO.

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

Use a DTO when:
- an action needs a stable request-like input contract
- several fields travel together
- the shape is reused by tests, jobs, commands, or imports
- the operation crosses a layer boundary
- raw arrays make call sites unclear

Keep actions responsible for behaviour. Keep DTOs responsible for the data contract.

## DTOs And Jobs

Jobs may receive DTOs when the queued operation needs a structured payload that is not simply one Eloquent model.

```php
SubmitShipment::dispatch(
    ShipmentRequestData::from($request->validated()),
);
```

When the job operates on an Eloquent model, pass the model directly and let Laravel serialize it:

```php
SubmitShipment::dispatch($shipment);
```

Do not create a DTO just to wrap one model for a queue job.

## DTOs And Integrations

Use DTOs at integration boundaries where payload shape matters:
- incoming webhooks
- outbound API requests
- ERP records
- courier shipment requests
- payment provider callbacks
- AI tool inputs or outputs
- import rows after parsing

Good:

```php
ProcessShopifyWebhook::run(
    ShopifyWebhookData::from($request->validated()),
);
```

Avoid passing decoded JSON arrays through the application after the boundary has been parsed and validated.

## DTOs With Factories And Managers

Factories and managers should return clients, adapters, or strategies that accept and return DTOs for structured data.

```php
$invoiceData = InvoiceData::from($payload);

$client = app(AccountingManager::class)->connection('company_a');

$reference = $client->createInvoice($invoiceData);
```

The manager selects the connection. The factory creates the client. The DTO carries the data. Load `factory-manager-pattern.md` when provider, driver, connection, adapter, or strategy selection is involved.

## DTOs And Specifications

Specifications answer reusable boolean business rule questions. Use DTOs when the rule needs structured context that is larger than one model or scalar.

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
app(EligibleForRefundSpecification::class)->isSatisfiedBy(
    RefundEligibilityData::from([
        'invoice' => $invoice,
        'amount' => $amount,
        'requestedAt' => now(),
    ]),
);
```

The DTO carries the rule context. The specification stays side-effect free and returns a boolean. Load `specification-pattern.md` when reusable eligibility checks or duplicated conditional rules appear.

## DTOs And State Transitions

Use a DTO when transition context becomes large or reused across actions, model methods, jobs, or tests.

```php
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class SupplierSubmissionData extends Data
{
    public string $supplierReference;

    public CarbonInterface $submittedAt;
}
```

```php
$fulfillmentOrder->markAsSubmittedToSupplier(
    SupplierSubmissionData::from([
        'supplierReference' => $response->reference,
        'submittedAt' => $response->submittedAt,
    ]),
);
```

Keep lifecycle permission and transition rules in the model/state layer. The DTO only carries transition context.

## DTOs And Refactoring

DTOs improve refactor safety because:
- field names are centralized
- types are explicit
- usages are traceable
- IDEs and static analyzers can understand contracts

Arrays make large refactors significantly more dangerous because keys are hidden in strings and are harder to rename safely.

## DTOs And Static Analysis

DTOs improve tooling support for:
- PHPStan
- Psalm
- Rector
- PhpStorm
- VS Code IntelliSense
- AI coding agents

Typed DTOs enable:
- autocomplete
- dead code detection
- invalid type detection
- safer renames
- automated refactors
- clearer architecture understanding

Use shaped arrays only for tiny, local, non-reused structures where a DTO would add ceremony without improving clarity. Once the shape crosses a boundary or appears in more than one place, prefer a DTO.

## Directory And Naming

Follow the existing project structure first. Good defaults are:

```text
app/Data/Sales/SalesOrderData.php
app/Data/Vat/VatCalculationData.php
app/Data/Fulfillment/ShipmentRequestData.php
```

or domain-local module folders:

```text
app/Modules/Sales/Data/SalesOrderData.php
app/Modules/Vat/Data/VatCalculationData.php
app/Modules/Fulfillment/Data/ShipmentRequestData.php
```

Use `app/Dtos` only when the project already has that convention. Prefer class names ending in `Data` to match `spatie/laravel-data` conventions.

## Avoid

Avoid:
- passing associative arrays through the application
- deeply nested raw array payloads
- hidden array structures
- unclear keys such as `id`, `type`, `value`, or `data` when the domain has better names
- DTOs with unrelated responsibilities
- behaviour-heavy DTOs
- giant god DTOs
- generic base DTOs that add little beyond the package

## Agent Rules

- Introduce DTOs when structured data crosses a layer boundary.
- Prefer `spatie/laravel-data` and classes extending `Spatie\LaravelData\Data`.
- Prefer public typed properties for scan-friendly application DTOs unless the project already uses constructor-promoted readonly DTOs.
- Use `::from($request->validated())` at request boundaries.
- Do not pass `$request->all()` or loose associative arrays into actions.
- Pair DTOs with actions when an operation has several related inputs or a stable input contract.
- Keep DTOs lightweight and free of business workflows or side effects.
- Prefer domain names ending in `Data`, such as `SalesOrderData` or `ShipmentRequestData`.
- Add DTO tests only when validation, mapping, casts, or transformation rules are non-trivial.
