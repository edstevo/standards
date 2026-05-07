---
name: filament
description: Build and refactor Laravel Filament panels, resources, pages, forms, tables, actions, authorization, and tests. Use for Filament admin portals, supplier/customer portals, panel routing/auth, resource CRUD, Livewire-backed UI behaviour, and Pest tests for Filament features.
license: MIT
metadata:
  domain: filament
  role: specialist
  scope: implementation
  triggers: Filament, FilamentPHP, panels, resources, pages, forms, tables, actions, relation managers, Livewire, admin panel, supplier portal, customer portal
---

# Filament

Build Filament features in a way that keeps panel configuration, resource behaviour, authorization, and tests easy to reason about.

Use this skill whenever you:
- add or change a Filament panel, resource, page, widget, form, table, action, or relation manager
- work on a Filament-backed admin, supplier, customer, or internal operations portal
- change Filament authentication, redirects, tenancy, navigation, or panel access
- change Filament labels, icons, action buttons, navigation, headings, empty states, or other UI copy
- write or refactor tests for Filament resources, pages, actions, form behaviour, or table behaviour

## Core Workflow

1. Identify the Filament version and the existing project conventions before editing.
2. Locate the relevant panel provider, resource, page, policy, model, factory, and tests.
3. Choose the narrowest honest test layer before changing behaviour.
4. Keep Filament classes focused on presentation, interaction wiring, and authorization handoff.
5. Put domain work in actions, models, jobs, services, or dedicated workflow classes according to the project conventions.
6. Verify behaviour through targeted Pest tests and only broaden the test run when the change crosses shared boundaries.

## Default Conventions

- Treat panel providers as configuration roots: path, auth, middleware, discovery, navigation, branding, tenancy, and plugins belong there.
- Keep resources and pages readable by extracting repeated form/table schema fragments only when the duplication is meaningful.
- Prefer policies and explicit model/domain methods for authorization decisions instead of burying access rules inside UI callbacks.
- Use Filament actions for UI intent, then delegate business work to application code when the operation is more than simple persistence.
- Keep visible action labels short and pair them with useful icons. Load the UX reference before changing UI copy or action presentation.
- Keep table queries explicit about eager loading, tenant scoping, and soft-delete behaviour.
- Keep form defaults, dehydration, mutation, and relationship handling close to the field or page that owns the interaction.
- Match generated class names, namespaces, and paths to the existing project layout before introducing a new convention.

## Reference Guide

References are load-on-demand support files in this skill's `references/` directory.

Use them deliberately:
- stay in this main skill for general Filament work
- load a reference when the task matches its topic
- load the reference before planning or editing if that topic is central to the task
- do not load every reference automatically

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Filament UX | `references/ux.md` | You are adding or changing action labels, icons, buttons, navigation labels, headings, table empty states, form copy, modal copy, or other user-facing UI text |
| General Filament testing | `references/testing.md` | You are adding or refactoring any Filament test; load this first before more specific testing references |
| Testing list pages | `references/testing-list-pages.md` | A Filament `List*` page, table, search, filter, columns, table actions, bulk actions, or header actions are involved |
| Testing view pages | `references/testing-view-pages.md` | A Filament `View*` page, infolist, relation manager, record action, page action, or record-detail page is involved |
| Testing edit pages | `references/testing-edit-pages.md` | A Filament `Edit*` page, edit form, save/update workflow, form validation, header action, or edit-page authorization is involved |
| Testing create pages | `references/testing-create-pages.md` | A Filament `Create*` page, create form, record creation workflow, form validation, header action, or create-page authorization is involved |
| Testing relation managers | `references/testing-relation-managers.md` | A Filament relation manager, owner-record table, relation table action, attach/create/edit/delete/detach action, relation manager search/filter, or relation manager validation is involved |
| Testing authorization and panel access | `references/testing-authorization-panel-access.md` | Auth, guests, roles, policies, tenants, guards, panel access, cross-panel redirects, or `canAccessPanel()` behaviour are involved |
| Multi-panel supplier/customer portals | `references/supplier-portals.md` | A Filament app needs separate admin and supplier/customer panels, one shared login entry point, role-aware redirects, panel middleware, or `User::canAccessPanel()` rules |

## Testing Baseline

For Filament work, prefer Pest tests that prove user-visible behaviour through the Filament page or Livewire component under test.

Default to:
- scenario-focused test files for meaningful Filament behaviours, mirroring the app namespace under `tests/Filament`
- narrow page or relation-manager smoke files only when they prove the page/component itself renders or mounts
- one behavioural concern per test
- `describe()` blocks only for tightly related assertions or variants inside one coherent scenario
- role- and permission-specific tests for authorization-sensitive UI
- table, form, and action assertions that target the Filament surface instead of only asserting database side effects
- factories and named scenario setup that make the domain state obvious
- no file-local helper functions in Pest files for setup shortcuts; follow `laravel-tdd` test-support rules and use inline setup, factory states, scenario builders, `tests/TestCase.php`, or `tests/Support` instead

Load `references/testing.md` before designing, adding, or refactoring Filament page/resource tests. Then load the matching list page, view page, edit page, create page, relation manager, or authorization reference for the specific behaviour under test.

When Filament test setup starts repeating, also apply the `laravel-tdd` shared test support guidance before extracting anything. Do not add Pest file functions, private helper methods, or local fixture helpers merely to shorten a test.

## Validation Checklist

- The relevant panel can resolve the page/resource/action being changed.
- Navigation visibility and direct route access agree with policy/panel authorization.
- Forms persist only intended fields and handle relationship data deliberately.
- Tables show the right records for the current user, tenant, filters, and search/sort state.
- Actions confirm, authorize, validate, mutate state, notify, redirect, and dispatch side effects as expected.
- Tests cover the behaviour at the Filament surface and the domain boundary touched by the feature.
