# Factories for Test Data

This is a focused reference for one topic: using factories to keep test data concise, intentional, and reusable.

Load it when the main `SKILL.md` points here or when a test is manually assembling Eloquent models and relationships inline.

## Contents

- Core rule
- Why factories are preferred
- Smells that a test should use a factory
- Factory design rules
- Rich text / structured payload example
- Related model construction examples
- If related models affect test outcome
- Test-side rewrite pattern
- When manual model creation is acceptable

## Core rule

Use factories wherever possible so tests do not become bulky model-construction scripts.

If a factory already exists for the model or graph you need, use it instead of hand-writing:
- `new Model`
- repeated attribute assignments
- manual `associate()` chains
- repeated `save()` calls

If the current factory does not express the scenario cleanly, improve the factory instead of repeating the setup in each test.

## Why factories are preferred

- they keep tests focused on behaviour instead of persistence plumbing
- they reduce repetitive setup noise
- they make domain scenarios easier to name and reuse
- they centralise relationship wiring and sensible defaults
- they make large test suites easier to change safely

## Smells that a test should use a factory

- the test manually creates a model and assigns many fields one by one
- the test manually associates several related models
- the same setup appears in more than one test
- the setup is longer than the behaviour being asserted
- a model already has a factory, but the test bypasses it anyway
- the test mutates many attributes after creation to reach a recurring scenario

That last case is usually a sign that the factory needs a better named state.

## Factory design rules

### 1. Keep `definition()` as the normal, believable default

`definition()` should return a coherent default version of the model.

Use it for:
- standard scalar defaults
- common enums or statuses
- default relationships that are truly part of the model's normal shape
- structured payloads that the app expects to exist in a valid record

Do not stuff every special-case scenario into `definition()`.

### 2. Use named states for recurring scenarios

Use `state()` methods when the scenario is mostly about attributes or a known mode of the model.

Good examples:
- `published()`
- `toBeDropshipped()`
- `withTracking()`
- `forFulfillment($fulfillment)`

Tests should read like domain language, not like object assembly.

### 3. Use `configure()` for shared relation derivation

Use `configure()` when the factory should consistently derive associated data from the model being built.

Good use cases:
- infer the owning order from the refundable model
- copy email/phone/destination from a related aggregate
- register shared `afterMaking()` or `afterCreating()` hooks once instead of repeating them in every state

### 4. Use `afterMaking()` for pre-persist wiring

Use `afterMaking()` when the factory needs to set up associations or derived attributes before persistence.

Typical examples:
- `associate()` a polymorphic owner
- copy values from a parent model
- set destination or contact fields derived from a related order

### 5. Use `afterCreating()` for child records and dependent graphs

Use `afterCreating()` when the factory needs a persisted parent before it can build children.

Typical examples:
- line items
- pivot records
- dependent children that need foreign keys
- records derived from the parent's related collection

### 6. Prefer opt-in graph expansion when child records can change test meaning

If creating related records materially affects the behaviour under test, keep the base factory lean and expose explicit states or methods for the richer graph.

This prevents factories from smuggling in data that makes tests harder to reason about.

## Rich text / structured payload example

Factories are not only for simple scalar fields. They should also encode valid structured payloads when the app expects them.

For example, rich text editors often store JSON documents rather than plain strings:

```php
<?php

class BlogArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(10),
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => fake()->sentence(10),
                            ],
                        ],
                    ],
                ],
            ],
            'blog_category_id' => BlogCategory::factory(),
            'shop_id' => Shop::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['published_at' => now()]);
    }
}
```

Why this is good:
- tests get valid rich text data by default
- tests do not need to know the editor's JSON structure
- the `published()` state keeps lifecycle intent out of the test body

## Related model construction examples

### Example 1: derive relationships in `configure()`

Use `configure()` when a model's associations can be derived from the object being built.

```php
<?php

class ShopRefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->newModel()->generateUniqueRef(),
            'status' => ProcessStatus::OPEN,
            'refunded_at' => null,
            'closed_at' => null,
            'archived_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ShopRefund $refund) {
            $refund->shopOrder()->associate($refund->refundable->resolveRefundShopOrder());
        });
    }

    public function forFulfillment(Fulfillment $fulfillment): static
    {
        return $this->state(fn () => [
            'reason' => RefundReason::CARRIER_LOSS,
            'refundable_type' => Fulfillment::class,
            'refundable_id' => $fulfillment->getKey(),
        ])->afterCreating(function (ShopRefund $refund) {
            $refund->refundable->lines->each(function (FulfillmentLine $fulfillmentLine) use ($refund) {
                $refundLine = new ShopRefundLine;
                $refundLine->quantity = $fulfillmentLine->resolveRefundableQuantity();
                $refundLine->refundableLine()->associate($fulfillmentLine);
                $refundLine->shopOrderLine()->associate($fulfillmentLine->resolveShopOrderLine());
                $refund->shopRefundLines()->save($refundLine);
            });
        });
    }
}
```

Why this is good:
- the test says `forFulfillment(...)` instead of rebuilding refund wiring manually
- association rules live once in the factory
- dependent refund lines are created only when the scenario asks for them

### Example 2: build a child graph from a parent aggregate

Use `afterCreating()` when the parent must exist before its lines can be derived.

```php
<?php

class FulfillmentOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->newModel()->generateUniqueRef(),
            'status' => ProcessStatus::OPEN,
            'fulfillment_status' => FulfillmentStatus::UNFULFILLED,
            'delivery_status' => DeliveryStatus::PENDING,
            'delivery_resolved_at' => null,
            'fulfillment_method' => fake()->randomElement(FulfillmentMethod::cases()),
            'shop_order_id' => ShopOrder::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (FulfillmentOrder $fulfillmentOrder) {
            $fulfillmentOrder->destination()->associate($fulfillmentOrder->shopOrder->shippingAddress);
            $fulfillmentOrder->email = $fulfillmentOrder->shopOrder->email;
            $fulfillmentOrder->phone = $fulfillmentOrder->shopOrder->phone;
        })->afterCreating(function (FulfillmentOrder $fulfillmentOrder) {
            $fulfillmentOrder->shopOrder->shopOrderLines->each(function (ShopOrderLine $shopOrderLine, int $index) use ($fulfillmentOrder) {
                $product = $shopOrderLine->shopProduct->product;

                $fulfillmentOrderLine = new FulfillmentOrderLine;
                $fulfillmentOrderLine->line = $index + 1;
                $fulfillmentOrderLine->sku = $shopOrderLine->sku;
                $fulfillmentOrderLine->description = $shopOrderLine->description;
                $fulfillmentOrderLine->quantity = $shopOrderLine->quantity;
                $fulfillmentOrderLine->quantity_fulfilled = 0;
                $fulfillmentOrderLine->quantity_unfulfillable = 0;
                $fulfillmentOrderLine->quantity_delivered = 0;
                $fulfillmentOrderLine->quantity_undelivered = 0;
                $fulfillmentOrderLine->delivery_status = DeliveryStatus::PENDING;
                $fulfillmentOrderLine->delivery_resolved_at = null;
                $fulfillmentOrderLine->shopOrderLine()->associate($shopOrderLine);
                $fulfillmentOrderLine->product()->associate($product);

                $fulfillmentOrder->fulfillmentOrderLines()->save($fulfillmentOrderLine);
            });
        });
    }

    public function toBeDropshipped(): static
    {
        return $this->state(fn () => [
            'fulfillment_method' => FulfillmentMethod::DROPSHIP,
        ]);
    }
}
```

Why this is good:
- the factory produces a usable order graph
- child line construction is centralised
- tests no longer need to manually mirror domain line-building logic

### Example 3: cascade one factory into the next

Factories can compose larger graphs when that composition reflects the domain.

```php
<?php

class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => PurchaseOrder::class,
            'name' => $this->newModel()->generateUniqueRef(),
            'status' => ProcessStatus::OPEN,
            'delivery_status' => DeliveryStatus::PENDING,
            'delivery_resolved_at' => null,
            'fulfillment_order_id' => FulfillmentOrder::factory()
                ->toBeDropshipped()
                ->afterMaking(function (FulfillmentOrder $fulfillmentOrder): void {
                    if (! $fulfillmentOrder->fulfillable instanceof Supplier) {
                        $fulfillmentOrder->fulfillable()->associate(Supplier::factory()->createQuietly());
                    }
                }),
        ];
    }
}
```

This pattern is useful when one model naturally exists downstream of another and the relationship should be available in most tests.

### Example 4: build dependent lines from a supply order

```php
<?php

class FulfillmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->newModel()->generateUniqueRef(),
            'external_reference' => fake()->unique()->numerify('FF-#####'),
            'status' => FulfillmentModelStatus::Open,
            'delivery_status' => DeliveryStatus::PENDING,
            'delivery_resolved_at' => null,
            'tracking_company' => null,
            'tracking_number' => null,
            'despatched_at' => null,
            'delivered_at' => null,
        ];
    }

    public function withTracking(): static
    {
        return $this->state(fn () => [
            'tracking_company' => 'FedEx',
            'tracking_number' => strtoupper(Str::random(10)),
        ]);
    }

    public function forSupplyOrder(SupplyOrder $supplyOrder): static
    {
        return $this->afterMaking(function (Fulfillment $fulfillment) use ($supplyOrder) {
            $fulfillment->supplyOrder()->associate($supplyOrder);
        })->afterCreating(function (Fulfillment $fulfillment) use ($supplyOrder) {
            $supplyOrder->lines->each(function (SupplyOrderLine $supplyOrderLine, int $index) use ($fulfillment) {
                $fulfillmentLine = new FulfillmentLine;
                $fulfillmentLine->line = $index + 1;
                $fulfillmentLine->quantity_fulfilled = $supplyOrderLine->quantity;
                $fulfillmentLine->quantity_delivered = 0;
                $fulfillmentLine->quantity_undelivered = 0;
                $fulfillmentLine->delivery_status = DeliveryStatus::PENDING;
                $fulfillmentLine->delivery_resolved_at = null;
                $fulfillmentLine->supplyOrderLine()->associate($supplyOrderLine);
                $fulfillment->fulfillmentLines()->save($fulfillmentLine);
            });
        });
    }
}
```

This pattern is useful when the factory should materialise the graph that downstream behaviour expects to operate on.

## If related models affect test outcome

This is the most common objection to “use factories more”.

If the default factory creates related records that can change the behaviour under test, do not abandon the factory. Refine it.

Prefer one of these approaches:

- keep `definition()` minimal and valid
- move expensive graph creation into opt-in states
- use specific methods like `forFulfillment(...)`, `forSupplyOrder(...)`, or `withLines()`
- use `configure()` only for relationships that are genuinely part of the model's default shape
- create separate states for “minimal record” versus “fully wired workflow record”

Good rule:
- defaults should be believable
- extra graph-building should be explicit when it changes test meaning

## Test-side rewrite pattern

Bad test setup usually looks like this:

```php
$refund = new ShopRefund;
$refund->name = $refund->generateUniqueRef();
$refund->status = ProcessStatus::OPEN;
$refund->reason = RefundReason::INCOMPLETE_FULFILLMENT_ORDER;
$refund->shopOrder()->associate($shopOrder);
$refund->refundable()->associate($fulfillmentOrder);
$refund->save();

$refundLine = new ShopRefundLine;
$refundLine->quantity = 2;
$refundLine->shopOrderLine()->associate($shopOrderLine);
$refundLine->refundableLine()->associate($targetLine);
$refund->lines()->save($refundLine);
```

Better test setup aims to read like this:

```php
$refund = ShopRefund::factory()
    ->forIncompleteFulfillmentOrder($fulfillmentOrder)
    ->withLine(
        quantity: 2,
        shopOrderLine: $shopOrderLine,
        refundableLine: $targetLine,
    )
    ->create();
```

If the second example is not currently possible, that is a factory design problem. Fix the factory rather than copying the first pattern into more tests.

## When manual model creation is acceptable

Manual construction is still acceptable when:
- there is no factory yet and the setup is genuinely one-off
- the object is intentionally invalid or half-built and that shape should not become a reusable factory state
- the model is trivial and a factory would add more indirection than value

Even then, if the pattern appears again, promote it into a factory state or support builder.

## Closing rule

The goal is not “always use the default factory blindly”.

The goal is:
- make the factory express the scenario
- keep the test small and behavioural
- move relationship and graph assembly into reusable factory APIs
