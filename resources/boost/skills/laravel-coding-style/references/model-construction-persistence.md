# Model Construction And Persistence

Load this reference when creating non-trivial model graphs or persistence workflows.

## Defaults

Prefer explicit model construction when the flow is meaningful business logic:
- `new Model`
- explicit property assignment
- `associate()` for relationships, including polymorphic relations
- clearly separated parent and child creation
- one transaction around related writes that must succeed together

Avoid large mass-assignment arrays when they hide the domain graph or important relationship wiring.

```php
$reverseFulfillmentOrder = new ReverseFulfillmentOrder;
$reverseFulfillmentOrder->status = OrderStatus::RAISED;
$reverseFulfillmentOrder->routing_reason = $routingReason;
$reverseFulfillmentOrder->shopReturn()->associate($shopReturn);
$reverseFulfillmentOrder->returnable()->associate($destination);
$reverseFulfillmentOrder->save();
```

## Transactions

Wrap related writes in `DB::transaction(...)` when they must succeed or fail together.

Keep transaction bodies readable:
- build the parent
- associate required relationships
- save the parent
- build children in clearly named steps
- dispatch no external IO inside the transaction

## Events And Observers

Prefer Eloquent creation paths that keep observers/events active unless there is a deliberate performance reason to bypass them.

Use `createQuietly()` or `saveQuietly()` only when you intentionally do not want generic Eloquent lifecycle events. For explicit model transition methods, `saveQuietly()` is paired with a custom domain event; see `model-lifecycle-events.md`.
