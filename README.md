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

This package registers five skills with Laravel Boost:

| Skill | Activates When |
|---|---|
| **webminty-laravel-standards** | Writing, editing, or reviewing any Laravel/PHP code |
| **webminty-tailwind-standards** | Writing or editing Tailwind CSS in Blade, Livewire, Inertia, or the CSS entry file |
| **webminty-livewire-standards** | Working on Livewire components, form objects, or `wire:` directives |
| **webminty-inertia-standards** | Working on controllers returning Inertia responses, shared data, or Inertia testing |
| **webminty-project-docs** | Finishing a feature, making a non-obvious technical decision, or shipping a user-visible change |

Skills activate automatically based on context, ensuring consistent adherence to Webminty's conventions regardless of frontend stack. Each skill's `SKILL.md` is a short index; the detailed rules live in topic files under `references/` that are read only when the task needs them, keeping context usage low.

## Standards Overview

The rules themselves live in each skill's `SKILL.md` (core rules) and `references/` topic files (detail), so they are maintained in one place.

| Skill | Covers |
|---|---|
| `webminty-laravel-standards` | PHP/PSR-12, strict types, `final` classes, naming, models, migrations, Actions, controllers, routes, jobs, commands, API, Pest testing |
| `webminty-tailwind-standards` | Tailwind v4 CSS-first setup, `@theme` tokens, utilities, class ordering, variants, dark mode, accessibility, component extraction |
| `webminty-livewire-standards` | Livewire 4 components, attributes, islands, slots, form objects, navigation, testing |
| `webminty-inertia-standards` | Inertia controllers, shared data, partial reloads, forms, SSR, testing |
| `webminty-project-docs` | `features.md`, `DECISIONS.md`, `CHANGELOG.md` |

Code quality tooling: Laravel Pint, PHPStan + Larastan (level 5), Rector, and Pest PHP.

## Per-Project File Conventions

In addition to coding standards, this package mandates three documentation files at the root of every project that installs it. AI assistants will create and maintain these files automatically as work progresses.

| File | Purpose | Update trigger |
|---|---|---|
| **`features.md`** | Canonical record of what the product does — `Status: In Development \| Live`, then user-facing features and backend features. Used later for documentation, marketing copy, and build-out planning. | End of any feature/PR that adds, changes, or removes a user-facing capability or backend feature. |
| **`DECISIONS.md`** | Append-only log of non-obvious technical and architectural decisions (stack picks, package choices, data-model trade-offs, opting out of Laravel defaults). Each entry: `## YYYY-MM-DD — Title`, **Decision**, **Why**, **Alternatives considered**. | When making a choice another developer would reasonably ask "why did we do it this way?" about. Prior entries are never edited or deleted. |
| **`CHANGELOG.md`** | Reverse-chronological log of user-visible changes, grouped by `Added` / `Changed` / `Fixed` / `Removed` / `Deprecated` / `Security` under a date or version heading. Follows [Keep a Changelog](https://keepachangelog.com). | When shipping a release, deploy, or user-visible change. Pre-launch projects collect entries under `## Unreleased`. |

`features.md` is distinct from `README.md`: README covers how to install, run, and develop the project; `features.md` covers what the product does. The full convention details (including what *not* to put in each file) live in the `webminty-project-docs` skill.

## Full Reference

The complete guidelines are available: [https://github.com/webmintydotcom/standards](https://github.com/webmintydotcom/standards)

For a Laravel Quickstart: [https://github.com/webmintydotcom/laravel-quickstart](https://github.com/webmintydotcom/laravel-quickstart)

## Changelog

This package follows [Semantic Versioning](https://semver.org). See [CHANGELOG.md](CHANGELOG.md) for release history.

## Thank you

[Webminty](https://webminty.com) team
