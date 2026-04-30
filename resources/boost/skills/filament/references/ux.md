# Filament UX

Load this reference when adding or changing Filament action labels, icons, buttons, navigation labels, headings, table empty states, form copy, modal copy, or any other user-facing UI text.

## Action Labels

Action labels must be short, direct, and easy to scan. Prefer one to three words.

Use command-style labels:
- `Create`
- `Edit`
- `View`
- `Delete`
- `Export`
- `Import`
- `Sync`
- `Retry`
- `Approve`
- `Cancel`
- `Update tracking`
- `Mark delivered`
- `Send invoice`

Avoid long sentence labels:
- `Click Here To Update Tracking Details`
- `Update Tracking Details For This Fulfillment`
- `Mark This Fulfillment As Delivered`
- `Create A New Customer Record`
- `Open The Purchase Order Details Page`

If the action lives on a record, the page/table context already supplies the noun. Do not repeat the whole object name in the button unless the action would otherwise be ambiguous.

## Icons

Every visible action should have a useful icon when Filament has one available.

Choose icons that communicate the action intent:
- view/detail actions: `heroicon-o-eye`
- edit/update actions: `heroicon-o-pencil-square`
- create/add actions: `heroicon-o-plus`
- delete/remove actions: `heroicon-o-trash`
- export/download actions: `heroicon-o-arrow-down-tray`
- import/upload actions: `heroicon-o-arrow-up-tray`
- sync/refresh actions: `heroicon-o-arrow-path`
- send/email actions: `heroicon-o-paper-airplane` or `heroicon-o-envelope`
- approve/complete actions: `heroicon-o-check-circle`
- cancel/reject actions: `heroicon-o-x-circle`
- delivery/shipping actions: `heroicon-o-truck`
- money/billing actions: `heroicon-o-banknotes` or `heroicon-o-credit-card`

Follow the icon style already used in the project. If the project uses outline Heroicons for Filament actions, keep using `heroicon-o-*`. If it consistently uses another Heroicon style, match it.

## Where Detail Belongs

Keep the button label short. Put extra detail in the modal heading, modal description, form field labels, helper text, or confirmation copy.

Good:

```php
Action::make('markAsDelivered')
    ->label('Mark delivered')
    ->icon('heroicon-o-truck')
    ->requiresConfirmation()
    ->modalHeading('Mark fulfillment as delivered?')
    ->modalDescription('This records the fulfillment as delivered and triggers the delivered workflow.');
```

Avoid:

```php
Action::make('markAsDelivered')
    ->label('Mark This Fulfillment As Delivered And Trigger The Delivered Workflow');
```

## Action Naming Pattern

Use a concise visible label and a stable internal action name.

```php
Action::make('updateTracking')
    ->label('Update tracking')
    ->icon('heroicon-o-truck');
```

The internal name can stay explicit for tests and code. The visible label should be written for a human scanning the UI.

## Tone And Casing

- Use sentence case for custom labels: `Update tracking`, not `Update Tracking`.
- Prefer verbs over vague nouns.
- Avoid marketing copy, filler words, and instructions like `Click here`.
- Avoid repeating the page title, resource name, or record name in every action.
- Use domain terms users already understand.

## Destructive And Risky Actions

Destructive or risky actions need clear labels, color, icon, and confirmation.

```php
Action::make('cancel')
    ->label('Cancel')
    ->icon('heroicon-o-x-circle')
    ->color('danger')
    ->requiresConfirmation();
```

Use the modal to explain consequences. Do not make the button label carry the whole warning.

## Dense Tables

For table row actions, keep labels especially short because users scan them repeatedly.

Prefer:
- `View`
- `Edit`
- `Retry`
- `Approve`
- `Cancel`

If a row action needs more context, use the modal, tooltip, or action grouping rather than a long label.

## Final Check

Before finishing Filament UI work, check:
- action labels are concise
- visible actions have useful icons
- destructive actions use clear color and confirmation
- modal headings/descriptions carry the detail that does not belong in the button label
- labels are consistent with nearby resources and actions
