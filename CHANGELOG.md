# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [3.2.0] - 2026-05-11

### Added
- **Tailwind CSS skill** (`webminty-tailwind-standards`) covering Tailwind v4 CSS-first setup, theme tokens, default scales, utility usage, class ordering with `prettier-plugin-tailwindcss`, state/data/aria variants, container queries, dark mode (`prefers-color-scheme` and class-based), component extraction patterns for Blade/Livewire/Inertia, `@utility` / `@variant` / `@apply` guidance, arbitrary values, plugins, anti-patterns, and v3-to-v4 migration notes
- **Accessibility section** in the Tailwind reference covering `focus-visible:` rings, `outline-hidden` vs `outline-none`, `sr-only` + skip-to-content pattern, `motion-safe:` / `motion-reduce:` for `prefers-reduced-motion`, `forced-colors:` for Windows high-contrast, 44×44px touch targets, contrast guidance, and `aria-*:` variants for stateful UI
- **Dynamic class name anti-pattern** documenting why `bg-${color}-500` silently breaks in production builds and prescribing full-class-string lookup maps (with `@source inline(...)` as a last-resort safelist)
- **"Tailwind purple" anti-pattern** — explicit guidance against defaulting to `indigo-*` / `violet-*` / `purple-*` / `fuchsia-*` / `pink-*` as accent colours (they're an AI/template "tell"); recommends `--color-brand-*` first, with `blue-600` / `emerald-600` / `sky-600` as fallbacks
- **Status/semantic colour defaults table** prescribing `emerald-600` (success), `amber-500` (warning), `rose-600` (danger), `sky-600` (info)
- `webminty-tailwind-standards` entry in `core.blade.php` so it auto-activates alongside `webminty-laravel-standards`

### Changed
- Replaced inline CSS section in `webminty-laravel-standards` with a cross-reference to the new Tailwind skill (single source of truth)
- Scope lines in `webminty-laravel-standards`, `webminty-livewire-standards`, and `webminty-inertia-standards` now cross-reference `webminty-tailwind-standards` for Tailwind CSS conventions
- README updated to list four skills and added a Tailwind CSS section to the Standards Overview

## [3.0.0] - 2026-04-15

### Updated

- Laravel 13 support

## [2.0.1] - 2026-03-09

### Added

- Run Pest tests in parallel by default with `--parallel` flag

## [2.0.0] - 2026-02-24

### Added
- **Multi-stack architecture** — split single skill into three dedicated skills: `webminty-laravel-standards`, `webminty-livewire-standards`, `webminty-inertia-standards`
- Always-loaded guideline (`core.blade.php`) for automatic skill activation based on project context
- **Livewire 4 skill** with full coverage of single-file components, islands, slots, form objects, navigation, and testing
- `#[Reactive]` and `#[Modelable]` attribute documentation with parent/child binding examples
- `wire:confirm`, `wire:dirty`, `wire:offline` directive documentation
- `wire:ref`, `wire:transition`, and `data-loading` CSS documentation
- PHP 8.4 property hooks section for Livewire components
- Emoji prefix disable guidance (`make_command.emoji` config)
- `declare(strict_types=1)` exception note for single-file components
- **Inertia.js skill** covering controllers, shared data, partial reloads, form handling, redirects, routes, and testing (Laravel side only)
- Full Inertia v2 prop types documentation (`lazy`, `defer`, `optional`, `always`, `merge`)
- Deferred props section with `->group()` batching examples
- Server-Side Rendering (SSR) section for Inertia
- API versioning guidelines with URL-based versioning and namespaced controller directory structure
- Enum convention guidance (backed enums, PascalCase cases, model casting)
- Expanded architecture tests: controllers, enums, jobs, form requests
- `composer.json` metadata: `type`, `homepage`, `authors`, `suggest` for `laravel/boost`
- CHANGELOG.md

### Changed
- Upgraded Livewire guidelines from v3 to v4
- Updated PSR-2 references to PSR-12 (includes PSR-1)
- Changed `minimum-stability` from `dev` to `stable`
- Refactored core skill to remove all Livewire-specific content (now in dedicated skill)
- Route examples updated to use single-action invokable controllers
- Method naming guidance updated to prefer `#[Scope]` attribute over legacy `scopeX()` prefix
- Don't list reworded to use imperative phrasing
- README updated to describe all three skills with activation triggers table

### Removed
- Hardcoded `version` field from `composer.json`
- Livewire content from core Laravel skill (moved to `webminty-livewire-standards`)

### Fixed
- Corrected Livewire 4 file extension from `.wire.php` to `.blade.php`
- Replaced non-existent `#[Prop]` attribute with correct `#[Reactive]`
- Removed fake `$this->$refs` PHP syntax, replaced with accurate `wire:ref` documentation
- Added missing `$request` parameter to Inertia partial reloads controller
- Added missing `UserResource` import in `HandleInertiaRequests` middleware example
- Added `print_r` to prohibited debug functions list
- Fixed README heading level and bare URL formatting
