# Boost Webminty Guidelines

A [Laravel Boost](https://laravelboost.com) plugin that provides [Webminty's](https://webminty.com) Laravel & PHP coding standards as an AI skill. When installed, AI code assistants automatically follow Webminty's conventions for any Laravel or PHP work.

## Requirements

- PHP 8.2+
- Laravel Boost

## Installation

```bash
composer require webminty/boost-webminty-guidelines --dev
```

The package auto-discovers via Laravel's package discovery — no additional setup required.

## What It Does

This package registers three skills with Laravel Boost:

| Skill | Activates When |
|---|---|
| **webminty-laravel-standards** | Writing, editing, or reviewing any Laravel/PHP code |
| **webminty-livewire-standards** | Working on Livewire components, form objects, or `wire:` directives |
| **webminty-inertia-standards** | Working on controllers returning Inertia responses, shared data, or Inertia testing |

Skills activate automatically based on context, ensuring consistent adherence to Webminty's conventions regardless of frontend stack.

## Standards Overview

### PHP

- `declare(strict_types=1)` in every file
- All classes `final` by default
- Typed properties, parameters, and return types (including `void`)
- Constructor property promotion
- Strict comparison (`===` / `!==`)
- PSR-12 compliance (includes PSR-1)

### Laravel

- **Models** — `$guarded = ['id']`, `casts()` method, `#[Scope]` attribute for query scopes
- **Actions** — Single-purpose `final` classes with an `execute()` method for business logic
- **Controllers** — Thin controllers that delegate to Actions; Form Requests for validation
- **DTOs** — Extend `Spatie\LaravelData\Data`, always `final`
- **Routes** — Kebab-case URLs, dot-notation names, RESTful conventions
- **Migrations** — Anonymous classes, `hash_id` pattern, boolean columns prefixed with `is_`/`has_`
- **Testing** — Pest PHP with `test()` syntax, architecture tests to enforce standards
- **Jobs** — `final`, `ShouldQueue`, dependencies injected in `handle()`

### Naming Conventions

| What | Convention | Example |
|---|---|---|
| URLs | kebab-case | `/about-us` |
| Route names | dot notation | `tickets.show` |
| Models | Singular PascalCase | `User` |
| Actions | Verb-first PascalCase | `CreateTicket` |
| Tables | Plural snake_case | `mash_items` |
| Columns | snake_case | `is_active` |
| Views | kebab-case | `ticket-list.blade.php` |
| Commands | `app:` prefix, kebab-case | `app:send-email` |

### Code Quality Tools

| Tool | Purpose |
|---|---|
| Laravel Pint | Code formatting |
| PHPStan + Larastan | Static analysis (level 5) |
| Rector | Automated refactoring |
| Pest PHP | Testing |

## Full Reference

The complete guidelines are available: [https://github.com/webmintydotcom/standards](https://github.com/webmintydotcom/standards)

For a Laravel Quickstart: https://github.com/webmintydotcom/laravel-quickstart

# Thank you

[Webminty](https://webminty.com) team
