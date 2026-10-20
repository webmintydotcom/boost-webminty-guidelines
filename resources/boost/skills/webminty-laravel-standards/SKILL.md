---
name: webminty-laravel-standards
description: Apply Webminty's Laravel and PHP coding standards for any task that creates, edits, reviews, refactors, or formats Laravel/PHP code or Blade templates; use for controllers, Eloquent models, routes, config, validation, migrations, tests, actions, jobs, DTOs, and related files to align with Laravel conventions, PSR-12, and Webminty project patterns.
license: MIT
metadata:
  author: Webminty
---

# Webminty Laravel & PHP Guidelines

## Overview
Apply Webminty's Laravel and PHP guidelines to keep code style consistent and Laravel-native. These standards cover PHP conventions, Laravel architecture patterns, testing with Pest, and project structure.

## When to Activate
- Activate this skill for any Laravel or PHP coding work, even if the user does not explicitly mention Webminty.
- Activate this skill when asked to generate, edit, format, refactor, review, or align Laravel/PHP code.
- Activate this skill when working on `.php` or `.blade.php` files, routes, controllers, models, config, validation, migrations, tests, actions, jobs, or Data objects.

## Scope
- In scope: `.php`, `.blade.php`, Laravel conventions (routes, controllers, config, validation, migrations, tests, actions, jobs, DTOs, enums, commands, API).
- Out of scope: JS/TS, infrastructure, non-Laravel frameworks, Tailwind CSS (see `webminty-tailwind-standards`), frontend-stack-specific patterns (see `webminty-livewire-standards` or `webminty-inertia-standards` skills).

## Workflow
1. Identify the artifact (action, controller, model, Blade, test, job, DTO, enum, route, migration, etc.).
2. Before writing code, read the reference file(s) listed under References that match the task. Each holds the rules plus a canonical example (Action, controller, model, job, command, Pest test, Blade); match its shape.
3. Apply the core Laravel principle first, then PHP standards, then section-specific rules.
4. If a rule conflicts with existing project conventions, follow Laravel conventions and keep changes consistent.

## Core Rules
- Follow Laravel conventions first.
- Follow PSR-12 (includes PSR-1).
- Every PHP file must have `declare(strict_types=1)`.
- Use typed properties and explicit return types (including `void`).
- Use constructor property promotion when possible.
- Handle error cases first and keep the happy path last; prefer early returns and avoid `else`.
- Put ternary parts on their own lines unless the ternary is short.
- Always use curly braces for control structures.
- Use string interpolation over concatenation.
- Put blank lines between statements; keep runs of equivalent one-liners together; no blank lines just inside `{}`.
- Avoid comments; write expressive code and extract named methods. When a comment is needed, explain why, not what.
- Avoid docblocks; add one only when types can't express it (generics, array shapes) or the code is genuinely confusing.
- Use strict comparison (`===`/`!==`).
- Use kebab-case URLs, dot-notation route names, and snake_case database columns.
- Use the `#[Fillable([...])]` attribute to list mass-assignable columns; do not use `$guarded` or `#[Unguarded]`.
- Use the `casts()` method instead of the `$casts` property.
- Use the `#[Scope]` attribute for query scopes, not `scopeX()` methods.
- Use anonymous migrations (`return new class`), the `hash_id` pattern for public-facing IDs, and `is_`/`has_` prefixes for boolean columns.
- Use Form Requests for controller validation, with array notation for rules (not pipe syntax).
- Controllers stay thin: delegate business logic to Actions (classes with a single `execute()` method).
- DTOs extend `Spatie\LaravelData\Data`.
- Use backed enums (`enum Status: int`) with PascalCase cases; cast enum columns in the model's `casts()` method.
- Use `config()` and avoid `env()` outside config files.
- Use Pest PHP for all tests with `test()` function syntax, and architecture tests to enforce coding standards.
- Structure tests as arrange, act, assert; name them as sentences describing behaviour.

## Avoid
- `dd()`, `dump()`, `ray()`, `var_dump()`, or `print_r` in committed code.
- Instantiating a model of the same class while it boots (inside `boot()`/`booted()`) — Laravel 13 throws a `LogicException`.

## References
Read only what the task needs, all under `references/`:
- `php-standards.md` — PHP standards, strict types, types, control flow, comments, docblocks
- `naming-structure.md` — Naming conventions (classes, routes, database, files) and directory structure
- `models-database.md` — Models, casts, scopes, Eloquent, migrations, `hash_id`
- `actions-controllers-routes.md` — Actions, controllers, Form Requests, routes, Blade
- `jobs-commands-api.md` — Jobs, console commands, API standards, batches vs pipelines
- `testing-tooling.md` — General Laravel, Pint/PHPStan/Rector, Pest testing, architecture tests
- `quick-reference.md` — One-page cheat sheet and Laravel 13 notes
