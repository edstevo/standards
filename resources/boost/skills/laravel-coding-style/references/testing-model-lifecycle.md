# Testing Model Lifecycle Workflows

Load this reference when testing model transition methods, explicit model events, observers, or after-commit workflow boundaries.

## Model Transition Tests

Lifecycle transitions should be tested in scenario-focused model test files, for example `tests/Models/FulfillmentCanBeClosedTest.php`.

Rules:
- one transition per test
- one meaningful transition scenario per file as much as reasonably feasible
- assert model state changes
- fake follow-up events and assert they were dispatched
- keep follow-up event outcomes in separate tests
- do not collapse several workflow stages into one broad test

```php
use App\Models\Fulfillment;
use Illuminate\Support\Facades\Event;

it('marks fulfillment as closed and dispatches archived event', function () {
    Event::fake([
        'eloquent.archived: '.Fulfillment::class,
    ]);

    $fulfillment = Fulfillment::factory()->fulfilled()->create();

    $fulfillment->markAsClosed();

    expect($fulfillment->refresh()->closed_at)->not->toBeNull();

    Event::assertDispatched('eloquent.archived: '.Fulfillment::class);
});
```

## Observer And Workflow Tests

Observer tests should prove orchestration, not the full downstream workflow.

Prefer:
- fake jobs/events dispatched by the observer
- assert the observer delegates, and test business eligibility rules on the model or domain object that owns them
- assert field-change guards for narrow `updated` handlers
- test downstream job/action behaviour separately

Avoid:
- one test that triggers a transition, observer, job, integration fake, timeline, and final workflow state all at once
- asserting follow-up event outcomes in the model transition test that only needs to prove the event was fired

## Factories And Events

Use normal `create()` when observers/events are relevant.

Use `createQuietly()` only when events are irrelevant to the behaviour under test.

When a scenario builder creates a domain graph that is central to the workflow, keep the graph-shaping flags visible in the test or describe-level `beforeEach()`.
