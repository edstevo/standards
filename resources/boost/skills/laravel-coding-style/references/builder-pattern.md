# Builder Pattern Guidance For PHP Projects

Load this reference as soon as PHP or Laravel object construction becomes complex, option-heavy, multi-step, or difficult to read at the call site. Repetition is not required.

## Core Rule

Default to the builder pattern for complex construction.

As soon as construction needs several readable steps, several optional values, domain defaults, validation, or a large payload, use a builder even if the code only appears in one or two places. The threshold is complexity at the call site, not how many times the construction is repeated.

A builder separates object or workflow assembly from the target object. Instead of large constructors, arrays of options, or many optional parameters, a builder exposes named steps that gradually assemble the object or configuration.

Laravel and PHP builders are usually fluent APIs where methods return `$this`:

```php
$order = SalesOrderBuilder::make()
    ->forCustomer($customer)
    ->withLine($product, quantity: 2)
    ->shipTo($address)
    ->build();
```

## Default To A Builder When

Introduce a builder as the default when:
- construction has many optional values
- construction needs several readable steps
- construction has defaults, domain rules, or validation
- arrays are acting as informal configuration objects
- constructor calls are becoming unclear or fragile
- action payloads or command inputs have several named pieces
- test setup requires a lot of boilerplate that factories do not express cleanly
- commands, reports, imports, exports, filters, or domain workflows need step-by-step configuration
- the same setup logic is repeated, even only once or twice, and it already looks complex

Builders are especially useful when the call site currently looks like this:

```php
new SalesOrder(
    customer: $customer,
    billingAddress: $billingAddress,
    shippingAddress: $shippingAddress,
    lines: $lines,
    paymentTerms: $paymentTerms,
    currency: $currency,
    taxMode: $taxMode,
    metadata: $metadata,
);
```

Or this:

```php
CreateSalesOrder::run([
    'customer_id' => $customer->id,
    'payment_terms' => 'PIA',
    'currency' => 'GBP',
    'shipping_address' => $address,
    'lines' => $lines,
    'metadata' => $metadata,
]);
```

Use an intention-revealing builder when it makes the setup easier to scan:

```php
$order = SalesOrderBuilder::make()
    ->forCustomer($customer)
    ->paymentInAdvance()
    ->inCurrency('GBP')
    ->shipTo($address)
    ->withLines($lines)
    ->withMetadata($metadata)
    ->build();
```

## Do Not Force Builders

Do not add a builder simply because the pattern exists. The escape hatch is genuinely simple construction, not low repetition.

Avoid builders when:
- construction is already simple
- a constructor or named constructor is clearer
- the object has only one or two obvious required values
- the builder only passes values through without improving readability
- the builder hides important domain behaviour
- the builder would become a large procedural service
- a DTO, factory, action, service, value object, or Laravel model factory is a better fit

Use:
- a constructor for simple objects with obvious required values
- a named constructor for a few clear creation variants
- a DTO when the main concern is carrying a structured typed data contract through an application boundary
- a factory when deciding which concrete implementation to create
- a manager when selecting a named driver, provider, connection, tenant, or configured implementation
- a builder by default when one object or workflow is assembled over several readable steps, even at the first call site
- a Laravel model factory mainly for test data and seed data
- an action or service when the main concern is executing a workflow rather than assembling an object

Load `factory-manager-pattern.md` when the construction question is really implementation, provider, driver, or connection selection.

## Implementation Guidelines

Builders should have a clear target and domain-specific name:
- `SalesOrderBuilder`
- `InvoiceBuilder`
- `ShipmentRequestBuilder`
- `ProductImportBuilder`
- `TaxCalculationBuilder`
- `ReportQueryBuilder`

Avoid vague names such as:
- `DataBuilder`
- `ObjectBuilder`
- `ManagerBuilder`
- `HelperBuilder`

Builder methods should reveal intent:

```php
->forCustomer($customer)
->withLine($product, $quantity)
->paymentInAdvance()
->shipTo($address)
->usingWarehouse($warehouse)
```

Prefer domain language over generic setters in domain builders. Generic setters are acceptable for mostly technical configuration builders.

## Terminal Methods And Side Effects

A builder should end with a clear terminal method.

Use `build()` or `make()` when returning an unsaved object. Use `create()` when persistence or external side effects are expected. Use `save()` only when the builder clearly owns persistence.

Acceptable terminal methods include:
- `build()`
- `make()`
- `create()`
- `toDto()`
- `toArray()`
- `save()`

Do not hide database writes or external API calls inside `build()` unless that behaviour is obvious from the class or method name.

## Validation And Invariants

A builder may validate required fields before producing the final object:

```php
public function build(): SalesOrderData
{
    if (! $this->customer) {
        throw new LogicException('Customer is required.');
    }

    if ($this->lines === []) {
        throw new LogicException('At least one order line is required.');
    }

    return SalesOrderData::from([
        'customer' => $this->customer,
        'lines' => $this->lines,
        'paymentTerms' => $this->paymentTerms,
    ]);
}
```

Keep domain invariants close to the domain model when they are part of the model's truth. Use the builder to protect construction from incomplete or inconsistent setup, but do not duplicate important validation rules across many builders.

## Mutability

Default to simple mutable builders that return `$this` when the builder is short-lived and used locally.

If a builder is reused, shared, or passed around, consider making it immutable by returning a cloned instance from each method.

## Testing

Test builders when they contain real construction logic, defaults, validation, branching behaviour, persistence, or external side-effect boundaries.

Do not over-test builders that only pass values through.

Useful builder tests prove:
- required fields are enforced
- defaults are applied correctly
- optional steps affect the final object correctly
- invalid combinations fail clearly
- no unexpected persistence or side effects occur

## Agent Rules

- Default to a builder as soon as complex PHP or Laravel construction becomes clearer as fluent, named steps.
- Do not wait for repeated usage before introducing a builder; one complex call site is enough.
- Do not force the pattern into genuinely simple code.
- Before skipping a builder, check that a constructor, named constructor, DTO, factory, action, service, value object, or Laravel model factory is clearly simpler at the call site.
- Use domain language in builder method names.
- Keep builders focused on construction, not unrelated workflow execution.
- Do not make builders select providers, drivers, or integration clients; use a factory or manager for that.
- Make terminal methods explicit about whether they only build data or also persist data.
- Preserve existing project conventions unless there is a clear reason to improve them.
- Include focused tests for any construction rules, defaults, validation, branching behaviour, persistence, or side effects.
