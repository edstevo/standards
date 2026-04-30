# Testing Filament Relation Managers

Load this reference after `testing.md` when a task involves a Filament relation manager, owner-record table, relation table action, attach/create/edit/delete/detach action, relation manager search/filter, or relation manager validation.

Relation manager tests must live beside the relation manager's mirrored test path. Do not put full relation manager coverage inside page tests.

Example application file:

```text
app/Filament/Admin/Resources/Accounts/RelationManagers/AccountOffersRelationManager.php
```

Required test file:

```text
tests/Filament/Admin/Resources/Accounts/RelationManagers/AccountOffersRelationManagerTest.php
```

Namespace Pest files to match the test path:

```php
<?php

namespace Tests\Filament\Admin\Resources\Accounts\RelationManagers;
```

Use this mapping for every relation manager:

```text
app/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{RelationManager}.php
tests/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{RelationManager}Test.php
```

## Checklist

Every relation manager test file should cover the applicable items below:

- [ ] The relation manager loads successfully with an `ownerRecord` and `pageClass`.
- [ ] It lists only records related to the owner record.
- [ ] It does not list records that belong to another owner record.
- [ ] It is tested with each page context where it is expected to work, usually the view page and edit page.
- [ ] Searchable fields can be searched and exclude non-matching related records.
- [ ] Filters can be applied and exclude non-matching related records.
- [ ] Important table columns render.
- [ ] Header actions work, including create and attach actions when present.
- [ ] Record actions work, including view, edit, delete, detach, or custom actions when present.
- [ ] Action validation constraints are tested.
- [ ] Nested form data, owner defaults, relationship fields, and derived defaults are tested when the relation manager uses them.
- [ ] Authorization-sensitive relation managers or actions load `testing-authorization-panel-access.md` and include allowed/denied tests.

## Default Structure

```php
<?php

namespace Tests\Filament\Admin\Resources\Accounts\RelationManagers;

use App\Filament\Admin\Resources\Accounts\Pages\EditAccount;
use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\RelationManagers\AccountContactsRelationManager;
use App\Models\Account;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->account = Account::factory()->createQuietly();
    $this->otherAccount = Account::factory()->createQuietly();

    $this->fin = User::factory()->for($this->account)->createQuietly([
        'first_name' => 'Fin',
        'last_name' => 'User',
        'email' => 'fin@example.test',
    ]);

    $this->sales = User::factory()->for($this->account)->createQuietly([
        'first_name' => 'Sales',
        'last_name' => 'User',
        'email' => 'sales@example.test',
    ]);

    $this->external = User::factory()->for($this->otherAccount)->createQuietly([
        'first_name' => 'Ext',
        'last_name' => 'User',
        'email' => 'ext@example.test',
    ]);
});

describe('Table Interaction', function () {
    test('lists only contacts for the owner account - View Page', function () {
        Livewire::test(AccountContactsRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewAccount::class,
        ])
            ->assertCanSeeTableRecords([$this->fin, $this->sales])
            ->assertCanNotSeeTableRecords([$this->external]);
    });

    test('lists only contacts for the owner account - Edit Page', function () {
        Livewire::test(AccountContactsRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => EditAccount::class,
        ])
            ->assertCanSeeTableRecords([$this->fin, $this->sales])
            ->assertCanNotSeeTableRecords([$this->external]);
    });

    test('search finds by name and email', function () {
        Livewire::test(AccountContactsRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewAccount::class,
        ])
            ->searchTable('Fin')
            ->assertCanSeeTableRecords([$this->fin])
            ->assertCanNotSeeTableRecords([$this->sales])
            ->searchTable('sales@example.test')
            ->assertCanSeeTableRecords([$this->sales])
            ->assertCanNotSeeTableRecords([$this->fin]);
    });

    test('can create a new contact via header action', function () {
        $payload = [
            'first_name' => 'New',
            'last_name' => 'Contact',
            'email' => 'new-contact@example.test',
        ];

        Livewire::test(AccountContactsRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => EditAccount::class,
        ])
            ->callAction(TestAction::make(CreateAction::class)->table(), $payload)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            ...$payload,
            'account_id' => $this->account->getKey(),
        ]);
    });
});

describe('Validation', function () {
    test('requires a valid email when creating a contact', function () {
        Livewire::test(AccountContactsRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => EditAccount::class,
        ])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'first_name' => 'Bad',
                'last_name' => 'Email',
                'email' => 'bad-email',
            ])
            ->assertHasFormErrors(['email']);
    });
});
```

## Owner Record Scoping

Always create at least one owner record and one unrelated owner record.

The table must show related records for the `ownerRecord` and hide records related to another owner:

```php
Livewire::test(AccountOffersRelationManager::class, [
    'ownerRecord' => $this->account,
    'pageClass' => ViewAccount::class,
])
    ->assertCanSeeTableRecords([$this->offerA, $this->offerB])
    ->assertCanNotSeeTableRecords([$this->externalOffer]);
```

When a relation manager appears on both view and edit pages, test both page contexts unless the relation manager is intentionally read-only or only registered on one page.

## Search, Filters, And Columns

Test table interactions through Filament assertions:
- use `searchTable(...)` for each important searchable field
- use `filterTable(...)` for meaningful filter states
- use `assertCanRenderTableColumn(...)` for important columns
- assert both included and excluded records

Do not rely only on visible text when a Filament table assertion exists.

## Header Actions

Use `Filament\Actions\Testing\TestAction` for table header actions:

```php
Livewire::test(AccountOffersRelationManager::class, [
    'ownerRecord' => $this->account,
    'pageClass' => EditAccount::class,
])
    ->callAction(TestAction::make(CreateAction::class)->table(), $payload)
    ->assertHasNoErrors();
```

For attach actions:

```php
Livewire::test(AccountProductBrandsRelationManager::class, [
    'ownerRecord' => $this->account,
    'pageClass' => EditAccount::class,
])
    ->callAction(TestAction::make(AttachAction::class)->table(), [
        'recordId' => $brand->getKey(),
    ])
    ->assertHasNoErrors();
```

Test required action data, defaults from the owner record, nested form data, and relationship persistence.

## Record Actions

Use `TestAction::make(...)->table($record)` for record actions:

```php
Livewire::test(AccountOffersRelationManager::class, [
    'ownerRecord' => $this->account,
    'pageClass' => EditAccount::class,
])
    ->callAction(
        TestAction::make(EditAction::class)->table($this->offerA),
        data: [
            'price' => 222.22,
            'min_quantity' => 20,
        ],
    )
    ->assertHasNoErrors();
```

Cover the actions that are present:
- create
- attach
- edit
- delete
- detach
- view
- custom workflow actions

Assert the database or refreshed model state after each action.

## Validation

Group validation tests under `describe('Validation', ...)` when a relation manager has action forms.

Test constraints that users can realistically hit:
- required fields
- invalid email or URL formats
- uniqueness rules
- numeric ranges
- invalid enum/select values
- date boundaries
- nested form fields such as `address.address_1`
- missing attach `recordId`

Prefer focused validation tests over one broad mixed assertion when the rules describe different behaviours.
