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

This package registers four skills with Laravel Boost:

| Skill | Activates When |
|---|---|
| **webminty-laravel-standards** | Writing, editing, or reviewing any Laravel/PHP code |
| **webminty-tailwind-standards** | Writing or editing Tailwind CSS in Blade, Livewire, Inertia, or the CSS entry file |
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

### Tailwind CSS

- **Tailwind v4** with CSS-first config — `@import "tailwindcss"` and `@theme` in a single CSS entry file; no `tailwind.config.js`
- Default to built-in utilities and the default scale (spacing, colour, radius, font-size, breakpoint)
- Brand tokens only via `@theme` (`--color-brand-*`, `--font-display`)
- Extract repeated utility patterns to **components** (Blade / Livewire / Inertia), not custom CSS via `@apply`
- Class ordering enforced by `prettier-plugin-tailwindcss`
- Container queries (`@container`, `@sm:`, `@md:`) for component-level responsiveness
- `data-*:` and `aria-*:` variants for stateful UI over JS class toggling
- `focus-visible:` (not `focus:`) for focus rings

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

## Per-Project File Conventions

In addition to coding standards, this package mandates three documentation files at the root of every project that installs it. AI assistants will create and maintain these files automatically as work progresses.

| File | Purpose | Update trigger |
|---|---|---|
| **`features.md`** | Canonical record of what the product does — `Status: In Development \| Live`, then user-facing features and backend features. Used later for documentation, marketing copy, and build-out planning. | End of any feature/PR that adds, changes, or removes a user-facing capability or backend feature. |
| **`DECISIONS.md`** | Append-only log of non-obvious technical and architectural decisions (stack picks, package choices, data-model trade-offs, opting out of Laravel defaults). Each entry: `## YYYY-MM-DD — Title`, **Decision**, **Why**, **Alternatives considered**. | When making a choice another developer would reasonably ask "why did we do it this way?" about. Prior entries are never edited or deleted. |
| **`CHANGELOG.md`** | Reverse-chronological log of user-visible changes, grouped by `Added` / `Changed` / `Fixed` / `Removed` / `Deprecated` / `Security` under a date or version heading. Follows [Keep a Changelog](https://keepachangelog.com). | When shipping a release, deploy, or user-visible change. Pre-launch projects collect entries under `## Unreleased`. |

`features.md` is distinct from `README.md`: README covers how to install, run, and develop the project; `features.md` covers what the product does. The full convention details (including what *not* to put in each file) live in the always-loaded `core.blade.php` guideline.

## Full Reference

The complete guidelines are available: [https://github.com/webmintydotcom/standards](https://github.com/webmintydotcom/standards)

For a Laravel Quickstart: [https://github.com/webmintydotcom/laravel-quickstart](https://github.com/webmintydotcom/laravel-quickstart)

## Changelog

This package follows [Semantic Versioning](https://semver.org). See [CHANGELOG.md](CHANGELOG.md) for release history.

## Thank you

[Webminty](https://webminty.com) team
