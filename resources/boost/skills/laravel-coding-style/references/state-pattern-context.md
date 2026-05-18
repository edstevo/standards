# State Pattern Transition Context

Load this reference when a transition needs contextual data such as payment timestamps, delivery timestamps, cancellation reasons, or supplier references.

Transition context must be explicit. Application callers should use expressive model methods, not instantiate state classes or call Spatie `transitionTo(...)` directly.

## Standard Context Shape

Use typed model method arguments at the public boundary:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
    paymentId: $payment->id,
);
```

The model passes that context to a Spatie transition class:

```php
public function markAsPaid(
    CarbonInterface $paidAt,
    int $paymentId,
): void {
    if ($this->fireModelEvent('paying') === false) {
        return;
    }

    $this->state->transitionTo(
        Paid::class,
        $paidAt,
        $paymentId,
    );

    $this->fireModelEvent('paid', false);
}
```

Do not make callers guess this:

```php
$invoice->state->transitionTo(Paid::class, $payment->received_at);
```

## Payment Example

```php
class MarkInvoicePaid extends Transition
{
    public function __construct(
        private Invoice $invoice,
        private CarbonInterface $paidAt,
        private int $paymentId,
    ) {
    }

    public function handle(): Invoice
    {
        $this->invoice->state = new Paid($this->invoice);
        $this->invoice->paid_at = $this->paidAt;
        $this->invoice->paid_by_payment_id = $this->paymentId;

        $this->invoice->saveQuietly();

        return $this->invoice;
    }
}
```

## Carrier Webhook Example

```php
$shipment->markAsDelivered(
    deliveredAt: $carrierTimestamp,
    carrierReference: $payload['tracking_reference'],
);
```

```php
class MarkShipmentDelivered extends Transition
{
    public function __construct(
        private Shipment $shipment,
        private CarbonInterface $deliveredAt,
        private string $carrierReference,
    ) {
    }

    public function handle(): Shipment
    {
        $this->shipment->delivery_state = new Delivered($this->shipment);
        $this->shipment->delivered_at = $this->deliveredAt;
        $this->shipment->carrier_reference = $this->carrierReference;

        $this->shipment->saveQuietly();

        return $this->shipment;
    }
}
```

## Cancellation Example

```php
$salesOrder->cancel(
    reason: 'customer_request',
    cancelledBy: $user->id,
);
```

```php
class CancelSalesOrder extends Transition
{
    public function __construct(
        private SalesOrder $salesOrder,
        private string $reason,
        private int $cancelledBy,
    ) {
    }

    public function handle(): SalesOrder
    {
        $this->salesOrder->lifecycle_state = new Cancelled($this->salesOrder);
        $this->salesOrder->cancelled_at = now();
        $this->salesOrder->cancelled_reason = $this->reason;
        $this->salesOrder->cancelled_by = $this->cancelledBy;

        $this->salesOrder->saveQuietly();

        return $this->salesOrder;
    }
}
```

## Supplier Submission Example

```php
$fulfillmentOrder->markAsSubmittedToSupplier(
    supplierReference: $response->reference,
    submittedAt: $response->submittedAt,
);
```

Use a small DTO instead of many scalar arguments when the transition context becomes large or reused:

```php
class SupplierSubmissionData
{
    public function __construct(
        public readonly string $supplierReference,
        public readonly CarbonInterface $submittedAt,
    ) {
    }
}
```

## Principle

The state should not need to discover transition context. Keep context visible in expressive model method arguments, then pass it into Spatie transition classes so transitions are deterministic, testable, and replayable.
