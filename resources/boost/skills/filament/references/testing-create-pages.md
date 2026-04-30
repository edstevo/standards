# Testing Filament Create Pages

Load this reference after `testing.md` when a task involves a Filament `Create*` page, create form, record creation workflow, form validation, header action, or create-page authorization.

Create page tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/CreateCustomer.php
tests/Filament/App/Resources/Customers/Pages/CreateCustomerTest.php
```

## Checklist

Every create page test file should cover the applicable items below:

- [ ] The page renders successfully. Create pages do not receive a record route key.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] Test the form submits successfully with new data.
- [ ] Test the form validation constraints.
- [ ] Test any header actions.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\CreateCustomer;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

describe('Rendering', function () {
    it('can render the create page', function () {
        Livewire::test(CreateCustomer::class)
            ->assertSuccessful();
    });
});

describe('Form Submission', function () {
    it('can create a customer', function () {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@example.test',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Customer::query()
            ->where('email', 'ada@example.test')
            ->exists())->toBeTrue();
    });
});

describe('Validation', function () {
    it('validates required fields', function () {
        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'first_name' => null,
                'email' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'email' => 'required',
            ]);
    });
});
```

Add separate `describe('Authorization', ...)` and `describe('Header Actions', ...)` blocks when the page has authorization requirements or actions.

## Creating Records

Every create page should prove the form can create a new record with representative valid data.

Pick data that exercises the important fields on the page:
- required scalar fields
- select fields or enum-backed fields
- relationship fields
- conditional fields
- fields with custom dehydration or mutation

After submitting, assert the created model or related records exist with the expected values.

## Validation

Test validation constraints that users can realistically hit:
- required fields
- invalid email or URL formats
- min/max lengths
- uniqueness rules
- numeric ranges
- invalid enum/select values
- conditional required fields

Prefer focused validation tests over one large matrix when the failure messages or rules cover different behaviours.

## Header Actions

When create pages expose header actions:
- assert the action is visible or hidden for the relevant user and page state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters

Use the action testing examples in `testing.md` for form-backed and workflow/state actions.
