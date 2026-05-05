## EdStevo Packages Code Guidelines

This package provides coding standards, conventions, and best practices for EdStevo Laravel projects.

This file is compiled into `AGENTS.md`, so keep it as the always-loaded baseline. Detailed, topic-specific guidance belongs in skills and their references.

### AI Execution Rules

- Treat these guidelines as defaults for new code and refactors.
- Follow existing project conventions first when they conflict with these defaults.
- Prefer explicit, deterministic implementations over clever or implicit behavior.
- When tradeoffs exist, prioritize correctness, readability, and maintainability in that order.
- Inspect the relevant code, documentation, and tests before editing.
- Preserve unrelated user changes and avoid refactors that are not needed for the requested outcome.
- Validate with the narrowest useful command or test suite, and report any validation that could not be run.
- Stop when the requested behavior is delivered, required docs/tests are aligned, and the verification result or blocker is clear.

### Delivery Workflow

- For larger refactors, project-sized work, or issue fixes, open a pull request so the work is stored and reviewable while it is in progress.
- Open the PR against the project's `dev` branch unless the project has an explicit different integration-branch convention.
- Add the plan and detailed checklist to the PR body at the start of the work. Do not create local `.md` plan files.
- Keep that checklist updated in the PR body and check items off as the work develops so the PR remains the source of truth for progress.
- When asked to create or rewrite a GitHub issue from a thin issue or rough brief, activate `github-issue-briefs` so the issue body is codebase-grounded before implementation begins.
- When writing complex features or significant refactors, activate `execution-plans` and use an ExecPlan from design to implementation as described in that skill.
- When workflow behavior changes, activate `process-documentation-with-flowcharts` and update both process docs and related class/method documentation in the same change.

### Skills Activation

Laravel Boost may inject a `## Skills Activation` section with project-specific skills and trigger rules.

- Treat matching skills as required, not optional. Activate relevant skills as soon as the task enters that domain.
- Do not wait until blocked before applying a relevant skill.
- Skills are specialized guidance layered on top of these core defaults.
- If multiple skills apply, follow all relevant skills together.
- Resolve conflicts in this order: explicit user request, existing project conventions, relevant skill guidance, then these core defaults.
- If no relevant skill is listed for the task, follow these core guidelines as the baseline.

### Project Structure

- Organize controllers by domain or feature under `app/Http/Controllers/`.
- Keep application actions in `app/Actions/` or domain-local `Actions/` folders.
- Use `app/Dtos/`, `app/Enums/`, `app/ValueObjects/`, and `app/Exceptions/` for those concepts unless the project already has a different convention.
- Use singular model names.
- Name controllers as `{Resource}Controller`.
- Name action classes as explicit verb-led tasks, for example `CreateUser` or `SendWelcomeEmail`.
- Prefer domain-specific names over generic `Manager`, `Processor`, or `Service` names unless the surrounding domain language makes the responsibility obvious.

### Coding Baseline

- Keep code readable over clever. If a method or class cannot be understood by scanning names and the main control flow, improve the design before adding more behavior.
- Follow SOLID in production code and tests. Do not create monolithic classes, catch-all services, bloated actions, or unreadable manager objects. For detailed guidance, activate `solid-design`.
- Use domain-first, intention-revealing names.
- Prefer explicit orchestration with small private methods for local steps and separate classes for distinct responsibilities.
- Favor defensive code paths: fail fast on invalid configuration and protect multi-write operations with transactions.
- Use strict parameter and return types where possible.
- Use enums for finite domain states and routing outcomes instead of loose strings.
- Prefer explicit method contracts and shaped PHPDoc where native PHP types are not expressive enough.
- Use explicit `@var` annotations only where static analysis needs help.
- Avoid premature micro-optimization in business workflows unless profiling shows a real bottleneck.

### Laravel Coding Style

- For preferred Laravel model, relationship, observer, lifecycle, persistence, jobs/actions, and model-event testing conventions, activate `laravel-coding-style`.
- Keep Eloquent models lean and expose expressive domain methods.
- Use reusable relationship traits for shared Eloquent relationships.
- Prefer explicit model construction and `associate()` for non-trivial domain persistence flows.
- Use explicit model transition methods for lifecycle changes.
- In model transition methods, guard first, mutate state, `saveQuietly()`, then fire one explicit model event.
- Observers should run after commit and coordinate follow-up work by dispatching jobs/events or calling methods that trigger further explicit events.
- External IO belongs in jobs/actions, not observers.
- Use native Laravel jobs for queued, delayed, retryable, or long-running work. Treat queued job constructors as data-only payload assignment and put executable work in `handle(...)`.
- Use `lorisleiva/laravel-actions` for application actions when the project includes it; keep action classes focused and container-resolvable.

### Integrations

- Treat external systems such as ERPs, couriers, payment providers, and third-party APIs as integration boundaries behind contracts.
- Services depending on integrations should depend on contracts, not concrete implementations.
- Bind concrete implementations in service providers.
- In tests, prefer Laravel-style integration fakes over ad-hoc mocks.
- If a facade entrypoint exists for an integration boundary, it should support `fake()` and `restore()` for tests.
- For full integration fake patterns, activate `laravel-tdd` and load `references/integration-fakes.md`.

### Configuration And Security

- Use config files for domain settings.
- Use environment variables only for infrastructure concerns.
- Never call `env()` outside config files.
- Never trust user input; validate using form requests or the project's validation convention.
- Use Laravel's CSRF protection.
- Sanitize output to prevent XSS.
- Use mass-assignment protection on models.
- Store sensitive data encrypted in the database.

### Documentation

- If behavior or workflow changes, update the relevant process documentation in the same change.
- Process docs should include Mermaid top-down flowcharts using `flowchart TB` when a workflow diagram is useful.
- Keep process doc "Key files" and "Key tests" sections aligned with code and coverage.
- Add class-level documentation for non-obvious jobs, actions, observers, and workflow classes.
- Add concise internal method documentation only where the behavior is not obvious from names and control flow.
- When adding or refactoring complex modules, workflows, integrations, LLM interactions, or stable app conventions, activate `laravel-boost-app-knowledge` and update source app skills under `.ai/skills`, not compiled `.agents` output.
- For full process documentation and flowchart conventions, activate `process-documentation-with-flowcharts`.

### Testing Baseline

- Use Pest PHP with clear, descriptive behavior names.
- Otherwise organize tests to mirror application structure unless a framework-specific convention applies.
- Prefer Pest `expect()` assertions and chain them where that improves readability.
- Do not use `PHPUnit\Framework\Assert` / `Assert::` in Pest tests unless Pest or Laravel has no suitable equivalent.
- Laravel-native assertions such as response assertions and `assertDatabaseHas()` are appropriate when they are the correct API.
- Keep each test focused on one behavioral concern.
- Group related tests with `describe()` blocks when a file covers multiple behaviors or methods on the same class/workflow.
- Use `beforeEach()` at the narrowest useful scope.
- Prefer factories and named factory states for setup. If a factory exists but the scenario is awkward, improve the factory instead of hand-building repeated model graphs.
- When builders construct a test graph, keep graph-shaping flags explicit in the same scope as the assertions or describe-group setup.
- Functions or methods inside test classes/files are an absolute last resort; prefer reusable helpers in `tests/TestCase.php` or richer support classes in `tests/Support`.
- Fake integration boundaries and assert what was dispatched or sent via the fake.
- Use `RefreshDatabase` unless the project has a different database isolation convention.
- For Laravel TDD, test structure, factories, scenario builders, workflow/E2E refactors, time testing, shared test support, and integration fakes, activate `laravel-tdd`.
- For model lifecycle event testing, activate `laravel-coding-style`.
- For Filament page/resource/relation-manager tests, activate `filament`.
