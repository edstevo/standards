# Testing Filament Edit Pages

Load this reference after `testing.md` when a task involves a Filament `Edit*` page, edit form, save/update workflow, form validation, header action, or edit-page authorization.

Edit page tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/EditCustomer.php
tests/Filament/App/Resources/Customers/Pages/EditCustomerTest.php
```

## Checklist

Every edit page test file should cover the applicable items below:

- [ ] The page renders successfully with the record route key.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] Test the form submits successfully without changes.
- [ ] Test the form can update selected data correctly.
- [ ] Test the form validation constraints.
- [ ] Test any header actions.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\EditCustomer;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

beforeEach(function () {
    $this->customer = Customer::factory()->createQuietly();
});

describe('Rendering', function () {
    it('can render the edit page', function () {
        Livewire::test(EditCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->assertSuccessful();
    });
});

describe('Form Submission', function () {
    it('can save the form without changes', function () {
        Livewire::test(EditCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();
    });

    it('can update selected customer data', function () {
        Livewire::test(EditCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->fillForm([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@example.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($this->customer->fresh())
            ->first_name->toBe('Ada')
            ->last_name->toBe('Lovelace')
            ->email->toBe('ada@example.test');
    });
});

describe('Validation', function () {
    it('validates required fields', function () {
        Livewire::test(EditCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->fillForm([
                'first_name' => null,
                'email' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'email' => 'required',
            ]);
    });
});
```

Add separate `describe('Authorization', ...)` and `describe('Header Actions', ...)` blocks when the page has authorization requirements or actions.

## Saving Without Changes

Every edit page should prove the existing record can be opened and saved without changing data.

This catches broken form hydration, dehydration, relationship handling, casts, defaults, and validation rules that reject already-persisted data.

```php
Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
    ->call('save')
    ->assertHasNoFormErrors();
```

## Updating Selected Data

Do not try to update every field in one test. Pick representative fields that prove the edit page's important behaviour:
- simple scalar fields
- select fields or enum-backed fields
- relationship fields
- conditional fields
- fields with custom dehydration or mutation

After saving, assert the refreshed model or related records changed as expected.

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

When edit pages expose header actions:
- assert the action is visible or hidden for the relevant user and record state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters

Use the action testing examples in `testing.md` for form-backed and workflow/state actions.

## Relation Manager Coverage

Do not test relation manager coverage in an edit page test file under a dedicated `describe()` block.

Do not write or follow this old rule: "When a view or edit page exposes relation managers, keep relation manager coverage in the page's test file under a dedicated `describe()` block." That rule is wrong for this codebase.

Relation manager table records, search, filters, columns, create/edit/delete/attach/detach actions, validation, owner scoping, and view/edit page context coverage belong in the relation manager's own mirrored test file under `tests/Filament/.../RelationManagers`. Load `testing-relation-managers.md` for that structure.
