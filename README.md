# EdStevo Standards

A Laravel package that provides opinionated coding standards, conventions, and best practices with seamless Laravel Boost integration. This package delivers AI-powered guidelines to help maintain consistency across all EdStevo Laravel projects.

## Why EdStevo Standards?

When working with AI coding agents like GPT-5.5, Codex, Claude Code, or Cursor, having a consistent set of coding standards is crucial. EdStevo Standards packages these standards into a reusable Laravel package that automatically integrates with [Laravel Boost](https://github.com/laravel/boost), ensuring every project follows the same conventions.

## Features

- 🤖 **Laravel Boost Integration** - Automatically discovered by Laravel Boost
- 📐 **Project Structure Guidelines** - Domain-based organization and naming conventions
- ✨ **Code Quality Standards** - Actions, DTOs, Services, and best practices
- 🧪 **Testing Standards** - Pest PHP patterns and coverage guidelines
- 📦 **Zero Configuration** - Install and forget
- 🔄 **Auto-updating** - Stay in sync with your coding standards
- 🧠 **GPT-5.5-aligned prompts** - Outcome-first agent guidance with explicit validation and stopping rules

## Requirements

- PHP 8.4+
- Laravel 11.x or 12.x
- Laravel Boost

## Installation

Install via Composer in your Laravel project:

```bash
composer require edstevo/protocol --dev
```

That's it! If you have Laravel Boost installed, the guidelines will be automatically discovered.

## Usage with Laravel Boost

### First Time Setup

If Laravel Boost is installed in your project, run:

```bash
php artisan boost:install
```

The EdStevo Standards guidelines will be automatically included when Boost generates your AI configuration files. Your AI agent will now understand and follow your coding standards.

### Keeping Guidelines Updated

After updating dependencies, refresh your AI guidelines:

```bash
php artisan boost:update
```

Or automate it by adding to your `composer.json`:

```json
{
  "scripts": {
    "post-update-cmd": [
      "@php artisan boost:update --ansi"
    ]
  }
}
```

## What's Included

EdStevo Standards provides comprehensive guidelines covering:

### Project Structure Conventions

- **Directory Organization**: Actions, DTOs, Enums, Services, ValueObjects
- **Naming Conventions**: Controllers, Models, Actions, Services, Traits
- **Domain-Based Structure**: Organize by feature/domain, not by layer

Example structure the AI will understand:
```
app/
├── Actions/User/
│   ├── CreateUser.php
│   └── UpdateUserProfile.php
├── DataTransferObjects/User/
│   └── CreateUserData.php
├── Services/
│   └── UserService.php
└── Http/Controllers/User/
    └── UserController.php
```

### Code Style & Best Practices

- **Action Classes**: Use `lorisleiva/laravel-actions` for one-class-one-task business actions, typically called via `::run()`
- **DTOs**: Immutable data transfer objects with validation
- **Service Classes**: Coordinate multiple actions and domain logic
- **Jobs and Queues**: Use native Laravel jobs and queues for queued or asynchronous work, with data-only job constructors and all work in `handle()`
- **Native Enums**: Type-safe backed enums for fixed values
- **Thin Controllers**: Delegate to actions and services
- **Query Optimization**: Eager loading, indexes, select only needed columns
- **Security**: Input validation, mass assignment protection, XSS prevention

### Testing Standards

- **Pest PHP**: Modern testing syntax with descriptive names
- **Test Organization**: Scenario-focused files with descriptive names, organized under the appropriate test tree
- **Coverage Guidelines**: High coverage of critical business logic
- **Database Testing**: In-memory SQLite, proper seeding, RefreshDatabase trait

## Examples

When you ask your AI to create a new feature, it will follow these patterns:

## Without Laravel Boost

You can manually reference the guidelines in your AI configuration:

**Claude Code (`CLAUDE.md`):**
```markdown
@include('vendor/edstevo/protocol/resources/boost/guidelines/core.blade.php')
```

**Cursor (`.cursorrules`):**
```markdown
Include guidelines from vendor/edstevo/protocol/resources/boost/guidelines/core.blade.php
```

## Verifying Installation

Check that the protocol is active:

```bash
php artisan protocol:info
```

You should see:
```
EdStevo Standards is active!
AI Boost guidelines are available for this project.
```

### Overriding

To override a specific guideline, create a file with the same path structure in your project's `.ai/guidelines/` directory. Your custom version takes precedence.

## Configuration

Publish the config file if you need to customize behavior:

```bash
php artisan vendor:publish --tag="standards-config"
```

## Development

### Running Tests

```bash
composer test
```

### Code Style

```bash
composer format
```

## How It Works

1. **Discovery**: Laravel Boost scans installed packages for `resources/boost/guidelines/` directories
2. **Loading**: Found guidelines are automatically included when running `boost:install` or `boost:update`
3. **Context**: AI agents receive these guidelines in their system prompts
4. **Consistency**: All code generated follows the same patterns across projects

## Philosophy

This package embodies the principle that **consistency is more valuable than perfection**. By standardizing patterns across all EdStevo projects:

- **Onboarding is faster** - New team members see familiar patterns
- **AI assistance is better** - Coding agents know exactly how to structure code
- **Maintenance is easier** - Predictable structure across all projects
- **Code reviews are quicker** - Everyone follows the same conventions

## Roadmap

- [ ] Add Agent Skills for specific domains
- [ ] Include Blade component patterns
- [ ] Add API documentation standards
- [ ] Database design conventions
- [ ] Queue job patterns

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.

---

Built with ❤️ by [EdStevo](https://github.com/edstevo) for consistent Laravel development.
