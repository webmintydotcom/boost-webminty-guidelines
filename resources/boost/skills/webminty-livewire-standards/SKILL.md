---
name: webminty-livewire-standards
description: Apply Webminty's Livewire coding standards for any task that creates, edits, reviews, refactors, or formats Livewire components, Livewire form objects, or Blade templates using wire: directives; use for full-page components, nested components, form handling, event listeners, and Livewire-specific testing patterns.
license: MIT
compatibility: Livewire 3+, Laravel 11+, PHP 8.2+
metadata:
  author: Webminty
---

# Webminty Livewire Guidelines

## Overview
Apply Webminty's Livewire guidelines for projects using Livewire as the frontend stack. These standards cover component structure, form objects, navigation, attributes, testing, and file organization.

## When to Activate
- Activate this skill when working on Livewire components, Livewire form objects, or Blade templates that use `wire:` directives.
- Activate this skill when creating or editing files in `app/Livewire/` or `resources/views/livewire/`.
- Activate this skill when writing tests that use `Livewire::test()`.

## Scope
- In scope: Livewire components, Livewire form objects, Livewire attributes (`#[Title]`, `#[Layout]`, `#[Validate]`, `#[Computed]`, `#[Url]`, `#[Locked]`, `#[On]`), `wire:` Blade directives, Livewire navigation, Livewire testing.
- Out of scope: Core PHP/Laravel standards (see `webminty-laravel-standards`), non-Livewire frontend stacks.

## Workflow
1. Identify the Livewire artifact (full-page component, nested component, form object, Blade view with `wire:` directives).
2. Read `references/webminty-livewire-guidelines.md` for detailed patterns.
3. Apply `webminty-laravel-standards` first (PHP conventions, `final`, strict types), then Livewire-specific rules.

## Core Rules (Summary)
- Components must be `final`.
- Use `#[Title]` and `#[Layout]` attributes on full-page components.
- Use `#[Url]` for query string binding.
- Use `#[Locked]` to prevent client modification of sensitive properties.
- Use `#[On('event-name')]` for event listeners.
- Use `#[Computed]` for derived data.
- Use `#[Validate]` for property validation.
- Use Livewire Form objects for form state and validation.
- Delegate business logic to Actions, not components.
- Use `wire:navigate` for SPA-style navigation.

## Do and Don't
Do:
- Use Form objects to encapsulate form state and validation.
- Use `$this->redirect(route('...'), navigate: true)` for SPA-style redirects.
- Use `wire:navigate` on links for SPA-style navigation.
- Keep components thin — delegate to Actions.

Don't:
- Put business logic in Livewire components.
- Use inline validation rules when a Form object is appropriate.
- Skip `#[Title]` or `#[Layout]` on full-page components.
- Use `$this->emit()` (Livewire 2 syntax) — use `$this->dispatch()` instead.

## Examples
```php
// Full-page component
#[Title('Dashboard')]
#[Layout('components.layouts.app')]
final class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
```

```php
// Form object
final class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';
}
```

```php
// Livewire test
test('can render ticket list', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TicketList::class)
        ->assertOk();
});
```

## References
- `references/webminty-livewire-guidelines.md`
