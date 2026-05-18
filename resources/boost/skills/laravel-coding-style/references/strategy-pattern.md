# Strategy Pattern

Load this reference when Laravel code needs multiple interchangeable ways to perform the same business task, or when conditionals are spreading because rules vary by customer, tenant, region, configuration, integration, feature flag, or entity type.

## Core Rule

Use the strategy pattern for interchangeable algorithms, not lifecycle state.

A strategy should encapsulate one cohesive business rule or algorithm behind a shared contract. Consuming code should depend on the contract and receive the selected strategy from a resolver, manager, factory, or service container binding.

Good strategy candidates:
- VAT calculation
- pricing rules
- shipping-rate calculation
- supplier selection
- routing modes
- payment-provider capture flows
- import row matching modes
- stock allocation algorithms

Avoid strategies when:
- only one implementation exists
- the logic is trivial
- no runtime interchangeability is needed
- the class would only wrap a one-line getter
- the behaviour is actually a model lifecycle transition
- the class only answers a boolean eligibility question
- a policy, enum method, value object, action, or model method is clearer

## Standard Shape

A strategy implementation usually has:
- a contract or interface
- multiple concrete implementations
- a resolver, manager, factory, or configuration map
- consuming code that calls the contract without caring which implementation was chosen

```php
interface PricingStrategy
{
    public function price(Product $product, Customer $customer): Money;
}
```

```php
class RetailPricingStrategy implements PricingStrategy
{
    public function price(Product $product, Customer $customer): Money
    {
        return $product->retail_price;
    }
}
```

```php
class TradePricingStrategy implements PricingStrategy
{
    public function price(Product $product, Customer $customer): Money
    {
        return $product->trade_price;
    }
}
```

Usage should stay boring:

```php
$strategy = $resolver->resolve($customer);

$price = $strategy->price($product, $customer);
```

## Laravel Resolver Pattern

Prefer resolver classes when the selected implementation depends on runtime context.

Resolvers should:
- contain selection logic only
- return the shared strategy contract
- resolve strategies through the container when strategies have dependencies
- fail fast for unsupported configuration
- keep business calculation inside the concrete strategy classes

```php
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PricingStrategyResolver
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function resolve(Customer $customer): PricingStrategy
    {
        $strategyClass = match ($customer->pricing_type) {
            PricingType::Retail => RetailPricingStrategy::class,
            PricingType::Trade => TradePricingStrategy::class,
            PricingType::Vip => VipPricingStrategy::class,
            default => throw new InvalidArgumentException(
                "Unsupported pricing type [{$customer->pricing_type->value}]."
            ),
        };

        return $this->container->make($strategyClass);
    }
}
```

Avoid constructing strategies with `new` inside resolvers unless the classes have no dependencies and that style already matches the project. Container resolution keeps constructors injectable and tests easier to replace.

## Configuration Maps

Use configuration maps when the strategy is selected by a stable setting such as tenant config, warehouse routing mode, carrier code, or feature flag.

```php
// config/pricing.php
return [
    'strategies' => [
        'retail' => RetailPricingStrategy::class,
        'trade' => TradePricingStrategy::class,
        'vip' => VipPricingStrategy::class,
    ],
];
```

```php
class PricingStrategyResolver
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function resolve(string $mode): PricingStrategy
    {
        $strategyClass = config("pricing.strategies.{$mode}");

        if (! is_a($strategyClass, PricingStrategy::class, true)) {
            throw new InvalidArgumentException("Unsupported pricing strategy [{$mode}].");
        }

        return $this->container->make($strategyClass);
    }
}
```

Prefer enum values for mode keys when the domain has a finite set of supported modes.

## Container Binding

Use a direct container binding when there is one active implementation for the application environment.

```php
$this->app->bind(
    PricingStrategy::class,
    TradePricingStrategy::class,
);
```

Use a resolver instead of a direct binding when the implementation changes per request, tenant, model, customer, order, warehouse, carrier, or feature flag.

## Directory And Naming

Follow existing project structure first. Good default locations are:
- `app/Strategies/Pricing`
- `app/Strategies/Shipping`
- domain-local `Pricing/Strategies`
- domain-local `Fulfillment/Strategies`

Use clear names:
- `PricingStrategy`
- `RetailPricingStrategy`
- `TradePricingStrategy`
- `ShippingRateStrategy`
- `WeightBasedShippingRateStrategy`
- `SupplierSelectionStrategy`
- `CheapestSupplierSelectionStrategy`
- `SupplierSelectionStrategyResolver`

Avoid vague names:
- `BusinessStrategy`
- `DataStrategy`
- `ProcessorStrategy`
- `ManagerStrategy`
- `DefaultStrategy` unless the domain explicitly has a default algorithm

## Strategy Boundaries

Strategies should:
- encapsulate one business rule or algorithm
- share a small, stable contract
- be independently testable
- receive all context needed to perform the algorithm through method arguments or constructor dependencies
- avoid knowing how they were selected
- avoid mutating unrelated systems
- return explicit values or perform a clearly named side effect

Strategies must not:
- contain the whole selection `if`/`elseif` tree inside one mega strategy
- know about every other strategy
- reach into request globals to choose their own behaviour
- become workflow orchestrators with unrelated responsibilities
- replace proper domain modelling or lifecycle states

Bad:

```php
class MegaPricingStrategy
{
    public function price(Product $product, Customer $customer): Money
    {
        if ($customer->isVip()) {
            // VIP pricing.
        } elseif ($customer->isTrade()) {
            // Trade pricing.
        } else {
            // Retail pricing.
        }
    }
}
```

Good:

```php
$strategy = $pricingStrategyResolver->resolve($customer);

$price = $strategy->price($product, $customer);
```

## Strategy Versus State

Use the strategy pattern when behaviour is selected externally and implementations are interchangeable algorithms.

Use the state pattern when behaviour depends on a model lifecycle and transitions must be controlled or validated.

Strategy examples:
- `RetailPricingStrategy`
- `TradePricingStrategy`
- `FlatRateShippingStrategy`
- `WeightBasedShippingStrategy`
- `CheapestSupplierSelectionStrategy`

State examples:
- `DraftOrderState`
- `PaidInvoiceState`
- `ShippedFulfillmentState`
- `CancelledPurchaseOrderState`

If the work involves lifecycle transitions, valid state changes, before/after transition events, or state-specific permissions, load `state-pattern.md` instead.

## Strategy Versus Action

Use an action when the class represents one business operation or use case.

Use a strategy when the class represents one interchangeable algorithm selected by context.

An action may select and call a strategy through a resolver, but the action should not contain every concrete algorithm inline. Load `action-pattern.md` when designing the use-case boundary.

## Strategy Versus Specification

Use a strategy when the class performs one interchangeable algorithm.

Use a specification when the class answers one reusable boolean business rule or eligibility question.

Example:

```php
if (! app(EligibleForRefundSpecification::class)->isSatisfiedBy($invoice)) {
    throw new RefundNotAllowedException();
}

$strategy = app(RefundStrategyResolver::class)->resolve($invoice);

$strategy->refund($invoice);
```

The specification decides whether refunding is allowed. The strategy decides how the refund is performed. Load `specification-pattern.md` when the work is a reusable "can this happen?" rule.

## Strategy Factories And Managers

Factories and managers often select strategies, but they should not perform the strategy's algorithm.

Use a factory when strategy construction depends on runtime context or needs centralized setup.

Use a manager when strategies are selected by named drivers, providers, tenants, connections, or configuration and may be cached.

Load `factory-manager-pattern.md` when strategy selection starts spreading across actions, controllers, jobs, observers, or other strategies.

## Testing

Test each concrete strategy independently when it contains real business rules.

Useful strategy tests prove:
- the algorithm returns the expected value
- edge cases are handled
- invalid inputs fail clearly
- dependencies are called through contracts or fakes
- no unrelated side effects occur

Test resolvers separately when selection logic matters.

Useful resolver tests prove:
- each supported mode resolves the expected strategy class
- unsupported modes fail fast
- tenant, customer, region, config, or feature-flag context selects the correct implementation

Do not over-test strategies that only return simple fixed values unless they protect important business rules.

## Agent Rules

- Introduce strategies when multiple interchangeable implementations of the same behaviour already exist or are required by the requested change.
- Prefer a contract plus focused concrete classes over conditionals spread across controllers, actions, jobs, observers, and models.
- Keep selection logic in a resolver, manager, factory, config map, or container binding, not in the strategy implementation.
- Resolve concrete strategies through Laravel's container when dependencies are involved.
- Keep strategy contracts small and domain-specific.
- Do not introduce a strategy for one trivial implementation just because the pattern exists.
- Use a specification, not a strategy, for reusable boolean eligibility checks.
- Use the state pattern, not strategy, for lifecycle progression and valid transition enforcement.
- Add focused tests for concrete strategy rules and resolver selection logic when those rules affect behaviour.
