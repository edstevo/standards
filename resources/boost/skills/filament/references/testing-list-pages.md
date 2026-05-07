# Testing Filament List Pages

Load this reference after `testing.md` when a task involves a Filament `List*` page, table, searchable columns, filters, visible columns, table actions, bulk actions, or header actions.

List page scenario tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/ListCustomers.php
tests/Filament/App/Resources/Customers/Pages/CustomerListCanBeSearchedByEmailTest.php
```

## Checklist

Split list page coverage across scenario-focused files for the applicable behaviours below. Each file should normally cover one bullet or one coherent user scenario:

- [ ] The page renders successfully with `Livewire::test(PageClass::class)->assertSuccessful()`.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] The table can show expected records with `assertCanSeeTableRecords(...)`.
- [ ] Searchable columns can be searched and exclude non-matching records. Test each important searchable field separately.
- [ ] Filters can be applied and exclude non-matching records. Test each meaningful filter state separately.
- [ ] The expected columns render with `assertCanRenderTableColumn(...)`.
- [ ] Header, table, row, and bulk actions are visible/hidden as expected.
- [ ] Actions can be executed and their domain effects are asserted.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

Use separate files for separate behaviours. A render smoke test can be tiny:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

it('can render the customer list page', function () {
    Livewire::test(ListCustomers::class)
        ->assertSuccessful();
});
```

Put search behaviour in a file named like `CustomerListCanBeSearchedByEmailTest.php`:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

it('can search customers by email', function () {
    $matching = Customer::factory()->createQuietly(['email' => 'ada@example.test']);
    $other = Customer::factory()->createQuietly(['email' => 'grace@example.test']);

    Livewire::test(ListCustomers::class)
        ->searchTable('ada@example.test')
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$other]);
});
```

Add separate scenario files for authorization, filters, columns, header actions, row actions, and bulk actions when those behaviours matter.

## Table Records

Use `assertCanSeeTableRecords(...)` and `assertCanNotSeeTableRecords(...)` to prove the Filament table surface, not only the database state.

Create enough records to prove inclusion and exclusion:

```php
$matching = Customer::factory()->createQuietly(['first_name' => 'Ada']);
$other = Customer::factory()->createQuietly(['first_name' => 'Grace']);

Livewire::test(ListCustomers::class)
    ->searchTable('Ada')
    ->assertCanSeeTableRecords([$matching])
    ->assertCanNotSeeTableRecords([$other]);
```

## Search And Filters

Test each important searchable field separately. A single broad search test is not enough when the table exposes several searchable fields that matter to users.

For filters, assert both the included and excluded records for each meaningful filter state.

## Columns

Assert the columns that define the page contract with `assertCanRenderTableColumn(...)`.

Do not only assert visible text for table coverage when a Filament table assertion exists.

## List Page Actions

When list pages expose header, row, table, or bulk actions:
- assert the action is visible or hidden for the relevant user and record state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters
