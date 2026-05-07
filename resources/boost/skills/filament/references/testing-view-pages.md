# Testing Filament View Pages

Load this reference after `testing.md` when a task involves a Filament `View*` page, infolist, record details, relation managers, page actions, record actions, or detail-page workflows.

View page scenario tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/ViewCustomer.php
tests/Filament/App/Resources/Customers/Pages/AdminCanViewCustomerDetailsTest.php
```

## Checklist

Split view page coverage across scenario-focused files for the applicable behaviours below. Each file should normally cover one bullet or one coherent user scenario:

- [ ] The page renders successfully with the record route key.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] Key record details from infolists, headings, badges, or sections are visible.
- [ ] Empty, missing, or optional record fields render intentionally when relevant.
- [ ] If the resource has relation managers, their behaviour is tested in mirrored relation manager scenario files, not in this page test file.
- [ ] Page, header, infolist, and record actions are visible/hidden as expected.
- [ ] Actions can be executed and their domain effects are asserted.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

Use separate files for separate behaviours. A render smoke test can be tiny:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

beforeEach(function () {
    $this->customer = Customer::factory()->createQuietly();
});

it('can render the view customer page', function () {
    Livewire::test(ViewCustomer::class, ['record' => $this->customer->getRouteKey()])
        ->assertSuccessful();
});
```

Put record-detail behaviour in a file named like `AdminCanViewCustomerDetailsTest.php`:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
    $this->customer = Customer::factory()->createQuietly();
});

it('displays customer details', function () {
    Livewire::test(ViewCustomer::class, ['record' => $this->customer->getRouteKey()])
        ->assertSee($this->customer->fullName)
        ->assertSee($this->customer->email);
});
```

## Record Details

Test the fields that make the view page useful to the user:
- names, references, statuses, totals, dates, and important badges
- values rendered by infolists, headings, sections, or custom components
- meaningful empty or optional states when the page has conditional display logic

Use direct `assertSee(...)` assertions for record detail text unless the project has a more specific Filament assertion for the rendered surface.

## Relation Manager Coverage

Do not test relation manager coverage in a view page test file under a dedicated `describe()` block.

Do not write or follow this old rule: "When a view or edit page exposes relation managers, keep relation manager coverage in the page's test file under a dedicated `describe()` block." That rule is wrong for this codebase.

Relation manager table records, search, filters, columns, create/edit/delete/attach/detach actions, validation, owner scoping, and view/edit page context coverage belong in scenario files under the relation manager's mirrored `tests/Filament/.../RelationManagers` path. Load `testing-relation-managers.md` for that structure.

## View Page Actions

For page, header, infolist, and record actions:
- assert the action is visible or hidden for the relevant user and record state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters

Use the action testing examples in `testing.md` for form-backed and workflow/state actions.
