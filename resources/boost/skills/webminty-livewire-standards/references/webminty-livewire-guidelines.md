# Webminty Livewire 4 Guidelines (Reference)

## Table of Contents
- [Component Formats](#component-formats)
- [Attributes](#attributes)
- [Islands](#islands)
- [Slots](#slots)
- [Form Objects](#form-objects)
- [Navigation](#navigation)
- [Blade Integration](#blade-integration)
- [Naming Conventions](#naming-conventions)
- [Directory Structure](#directory-structure)
- [Testing](#testing)
- [Architecture Tests](#architecture-tests)

---

## Component Formats

Livewire 4 supports three component formats. Prefer single-file for most components.

### Single-File Component (Preferred)

Single-file components combine PHP and Blade in one `.blade.php` file:

```php
{{-- resources/views/components/dashboard.blade.php --}}
<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] #[Layout('components.layouts.app')]
class extends Component {
    //
};
?>

<div>
    <h1>{{ __('dashboard.title') }}</h1>
</div>
```

### Single-File Component with Data

```php
{{-- resources/views/components/ticket-list.blade.php --}}
<?php

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Tickets')] #[Layout('components.layouts.app')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function rendering($view): void
    {
        $view->with('tickets', Ticket::query()
            ->when($this->search, fn ($q, $search) => $q->search($search))
            ->when($this->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15));
    }
};
?>

<div>
    <input type="text" wire:model.live.debounce.300ms="search">
    {{-- ticket list markup --}}
</div>
```

### Class-Based Component (Alternative)

Use when you need separate PHP and Blade files, or for complex components:

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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

### Multi-File Component

Organizes PHP, Blade, JS, and tests in a dedicated directory:

```
resources/views/components/ticket-list/
├── ticket-list.blade.php    # PHP + Blade
├── ticket-list.js          # JavaScript (optional)
└── ticket-list.test.php    # Tests (optional)
```

Create with: `php artisan make:livewire ticket-list --mfc`

### Converting Between Formats

```bash
php artisan livewire:convert ticket-list          # Auto-detect and convert
php artisan livewire:convert ticket-list --sfc    # Convert to single-file
php artisan livewire:convert ticket-list --mfc    # Convert to multi-file
```

### Key Rules
- Prefer single-file components for most cases
- Class-based components must be `final`
- Single-file components use `new class extends Component`
- Use `#[Title]` and `#[Layout]` attributes on all full-page components
- Keep components thin — delegate business logic to Actions
- Use `declare(strict_types=1)` in class-based components
- `declare(strict_types=1)` cannot be used in single-file components due to the combined PHP/Blade format — this is the one exception to the strict types rule

---

## Attributes

| Attribute | Purpose | Example |
|-----------|---------|---------|
| `#[Title('...')]` | Set page title | `#[Title('Dashboard')]` |
| `#[Layout('...')]` | Set layout component | `#[Layout('components.layouts.app')]` |
| `#[Reactive]` | Keep child property in sync with parent | `#[Reactive] public string $filter = ''` |
| `#[Url]` | Bind property to query string | `#[Url] public string $search = ''` |
| `#[Locked]` | Prevent client modification | `#[Locked] public int $userId` |
| `#[On('event-name')]` | Listen for events | `#[On('ticket-created')] public function refresh()` |
| `#[Computed]` | Cache derived data for request lifecycle | `#[Computed] public function total(): int` |
| `#[Validate('...')]` | Inline validation rule | `#[Validate('required\|string')]` |
| `#[Modelable]` | Enable wire:model binding on child component property | `#[Modelable] public string $value = ''` |

### #[Reactive] Attribute

Mark a child component's public property as reactive so it stays in sync when the parent re-renders. Without `#[Reactive]`, a property passed from a parent is only set once during `mount()` and will not update when the parent changes.

```php
{{-- Parent component --}}
<?php

use Livewire\Component;

new class extends Component {
    public string $filter = '';
};
?>

<div>
    <input type="text" wire:model.live="filter">
    <livewire:ticket-list :filter="$filter" />
</div>
```

```php
{{-- Child component (ticket-list.blade.php) --}}
<?php

use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {
    #[Reactive]
    public string $filter = '';

    // $filter updates automatically when the parent's $filter changes
};
?>

<div>
    {{-- Use $this->filter, which stays in sync with the parent --}}
</div>
```

Pass data from parent to child via public properties and the `mount()` method. Use `#[Reactive]` when the child must track ongoing changes from the parent.

### #[Modelable] Attribute

Mark a child component's public property as modelable to enable two-way data binding between parent and child via `wire:model`. Unlike `#[Reactive]` (which is one-way, parent-to-child), `#[Modelable]` allows the child to push changes back up to the parent.

```php
{{-- Parent component --}}
<?php

use Livewire\Component;

new class extends Component {
    public string $color = '#000000';
};
?>

<div>
    <livewire:color-picker wire:model="color" />
    <p>Selected color: {{ $color }}</p>
</div>
```

```php
{{-- Child component (color-picker.blade.php) --}}
<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component {
    #[Modelable]
    public string $value = '';
};
?>

<div>
    <input type="color" wire:model.live="value">
</div>
```

When the user picks a color in the child, the parent's `$color` property updates automatically. Use `#[Modelable]` when building reusable input components that need to integrate with `wire:model` on the parent side.

### Computed Properties

```php
#[Computed]
public function activeTicketCount(): int
{
    return Ticket::where('is_active', true)->count();
}
```

Access in Blade with `$this->activeTicketCount`.

### Event Listeners

```php
#[On('ticket-created')]
public function refreshList(): void
{
    // Component re-renders automatically
}

// Dispatching events
$this->dispatch('ticket-created');
$this->dispatch('ticket-created')->to(TicketList::class);
```

### PHP 8.4 Property Hooks

Use native property hooks as an alternative to `updating` lifecycle hooks:

```php
public int $quantity {
    set => max(1, $value);
}

public string $email {
    set => strtolower(trim($value));
}
```

---

## Islands

Islands are isolated regions within a component that re-render independently. Use them to prevent expensive parts of a view from blocking the rest of the page.

### Basic Usage

```blade
<div>
    <h1>Dashboard</h1>

    {{-- This section re-renders independently --}}
    @island
        <livewire:activity-feed />
    @endisland

    {{-- This section is not affected by activity-feed updates --}}
    <div>
        <p>Static content that won't re-render</p>
    </div>
</div>
```

### When to Use Islands
- Expensive queries or computations that shouldn't block the page
- Independently updating sections (e.g., activity feeds, notification counts)
- Components with frequent updates that shouldn't cause full-page re-renders

### When NOT to Use Islands
- Simple components with minimal render cost
- Components that need to share state with the parent

---

## Slots

Livewire 4 components accept slots like Blade components.

### Default Slot

```blade
{{-- Using the component --}}
<wire:modal>
    <p>This content goes in the default slot.</p>
</wire:modal>
```

```blade
{{-- Inside the modal component --}}
<div class="modal">
    {{ $slot }}
</div>
```

### Named Slots

```blade
{{-- Using the component --}}
<wire:modal>
    <wire:slot name="header">
        <h2>Confirm Delete</h2>
    </wire:slot>

    <p>Are you sure you want to delete this item?</p>

    <wire:slot name="footer">
        <button wire:click="cancel">Cancel</button>
        <button wire:click="confirm">Confirm</button>
    </wire:slot>
</wire:modal>
```

```blade
{{-- Inside the modal component --}}
<div class="modal">
    <div class="modal-header">{{ $header }}</div>
    <div class="modal-body">{{ $slot }}</div>
    <div class="modal-footer">{{ $footer }}</div>
</div>
```

---

## Form Objects

### Basic Form

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        // ...
    }
}
```

### Form with Actions

```php
final class TicketForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|min:10')]
    public string $body = '';

    #[Validate('nullable|integer|exists:categories,id')]
    public ?int $category_id = null;

    public function store(CreateTicket $createTicket): Ticket
    {
        $this->validate();

        return $createTicket->execute(
            TicketData::from($this->all()),
        );
    }
}
```

### Using Forms in Single-File Components

```php
<?php

use App\Livewire\Forms\TicketForm;
use App\Actions\Tickets\CreateTicket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create Ticket')] #[Layout('components.layouts.app')]
class extends Component {
    public TicketForm $form;

    public function save(CreateTicket $createTicket): void
    {
        $ticket = $this->form->store($createTicket);

        $this->redirect(
            route('tickets.show', $ticket),
            navigate: true,
        );
    }
};
?>

<div>
    <form wire:submit="save">
        <input type="text" wire:model="form.title">
        <textarea wire:model="form.body"></textarea>
        <button type="submit">Create</button>
    </form>
</div>
```

### Key Rules
- Form objects must be `final`
- Use `#[Validate]` attributes for validation rules
- Delegate business logic to Actions from form objects
- Call `$this->validate()` before processing
- Reset forms after successful submission with `$this->form->reset()`

---

## Navigation

### SPA-Style Redirects

```php
// From a component method
$this->redirect(route('dashboard'), navigate: true);

// With flash data
session()->flash('success', __('tickets.created'));
$this->redirect(route('tickets.index'), navigate: true);
```

### SPA-Style Links (Blade)

```blade
{{-- SPA-style link --}}
<a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>

{{-- Prefetch on hover --}}
<a href="{{ route('tickets.index') }}" wire:navigate.hover>Tickets</a>
```

### Key Rules
- Always use `navigate: true` for internal redirects
- Use `wire:navigate` on `<a>` tags for SPA-style page transitions
- Use `wire:navigate.hover` for prefetching on hover

---

## Blade Integration

### Wire Directives

```blade
{{-- Model binding --}}
<input type="text" wire:model="form.title">

{{-- Live model binding --}}
<input type="text" wire:model.live="search">

{{-- Debounced binding --}}
<input type="text" wire:model.live.debounce.300ms="search">

{{-- Form submission --}}
<form wire:submit="save">
    ...
</form>

{{-- Click handler --}}
<button wire:click="delete({{ $ticket->id }})">Delete</button>

{{-- Confirmation --}}
<button wire:click="delete({{ $ticket->id }})" wire:confirm="Are you sure?">Delete</button>
```

### wire:ref (New in v4)

Name a child component for targeted dispatching and streaming:

```blade
<div>
    <livewire:modal wire:ref="modal">
        <p>Modal content</p>
    </livewire:modal>

    <button wire:click="openModal">Open</button>
</div>
```

In PHP, target a ref with `$this->dispatch('event')->to(ref: 'modal')` or `$this->stream($content)->to(ref: 'modal')`. In JavaScript, access the child via `this.$refs.modal.$wire`.

### wire:transition (New in v4)

Add enter/leave animations using the native View Transitions API:

```blade
{{-- Basic fade transition --}}
<div wire:transition>
    Content that fades in/out
</div>
```

Note: Livewire 4 uses the browser's View Transitions API. The v3 modifiers (`.opacity`, `.scale`, `.duration`) are no longer supported.

### Loading States (data-loading)

Livewire 4 automatically adds a `data-loading` attribute to elements that trigger network requests. Use CSS classes instead of verbose `wire:loading` patterns:

```blade
{{-- Preferred v4 approach — use data-loading CSS --}}
<button wire:click="save" class="data-loading:opacity-50 data-loading:pointer-events-none">
    Save Changes
</button>

{{-- Still works but more verbose --}}
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

Prefer the `data-loading` CSS approach for simple loading states. Use `wire:loading` only when you need conditional content swapping.

### wire:dirty (Form State)

Livewire automatically adds a `data-dirty` attribute to elements when bound form data has changed from its initial state. Use CSS classes to provide visual feedback:

```blade
{{-- Highlight input border when value has changed --}}
<input type="text" wire:model="form.name" class="data-dirty:border-yellow-500">

{{-- Show a "unsaved changes" notice when dirty --}}
<div wire:dirty>You have unsaved changes.</div>

{{-- Hide an element when dirty using the .remove modifier --}}
<div wire:dirty.remove>All changes saved.</div>
```

Use `wire:dirty` to give users clear feedback that their form state has diverged from what was last saved or loaded.

### wire:offline

Show or hide elements when the user loses their internet connection:

```blade
{{-- Show a banner when the user goes offline --}}
<div wire:offline>
    You are currently offline. Changes will sync when your connection is restored.
</div>

{{-- Add a CSS class when offline --}}
<div wire:offline.class="opacity-50 pointer-events-none">
    <form wire:submit="save">
        ...
    </form>
</div>
```

### wire:confirm

Add a browser confirmation dialog before executing an action:

```blade
{{-- Basic confirmation dialog --}}
<button wire:click="delete({{ $ticket->id }})" wire:confirm="Are you sure you want to delete this ticket?">
    Delete
</button>

{{-- Typed confirmation for destructive actions --}}
<button wire:click="destroy" wire:confirm.prompt="Type DELETE to confirm|DELETE">
    Permanently Destroy
</button>
```

Use `wire:confirm` for any destructive or irreversible action. The `.prompt` modifier requires the user to type a specific value before the action proceeds, adding an extra layer of protection.

### Data from Components
- All data should come from component properties or the `render()` / `rendering()` method
- No Eloquent queries in Blade views
- Use `$this->propertyName` for computed properties

---

## Naming Conventions

| What | Convention | Example |
|------|-----------|---------|
| Single-file components | kebab-case with `.blade.php` suffix | `ticket-list.blade.php` |
| Class-based components | PascalCase | `TicketList.php` |
| Form objects | PascalCase, suffixed with `Form` | `LoginForm`, `TicketForm` |
| Component views (class-based) | kebab-case in `livewire/` | `livewire/ticket-list.blade.php` |
| Events | kebab-case | `ticket-created`, `order-updated` |

---

## Directory Structure

### Single-File Components (Default in v4)

```
resources/views/components/
├── layouts/
│   └── app.blade.php
├── dashboard.blade.php
├── ticket-list.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── tickets/
    ├── create.blade.php
    └── show.blade.php

app/Livewire/
└── Forms/
    ├── LoginForm.php
    └── TicketForm.php
```

### Class-Based Components (Alternative)

```
app/Livewire/
├── Auth/
│   ├── Login.php
│   └── Register.php
├── Forms/
│   ├── LoginForm.php
│   └── TicketForm.php
├── Dashboard.php
└── TicketList.php

resources/views/livewire/
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── dashboard.blade.php
└── ticket-list.blade.php
```

### Emoji Prefix

Livewire 4 defaults to prefixing generated component filenames with a `⚡` emoji. **Do not use this.** Disable it in your Livewire config:

```php
// config/livewire.php
'make_command' => [
    'emoji' => false,
],
```

### Artisan Commands

```bash
php artisan make:livewire dashboard              # Single-file (default)
php artisan make:livewire dashboard --mfc         # Multi-file
php artisan make:livewire dashboard --class        # Class-based (v3 style)
```

---

## Testing

### Component Rendering

```php
test('can render ticket list', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TicketList::class)
        ->assertOk();
});
```

### Form Submission

```php
test('can create a ticket', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', 'Test Ticket')
        ->set('form.body', 'This is a test ticket body.')
        ->call('save')
        ->assertRedirect(route('tickets.index'));

    expect(Ticket::where('title', 'Test Ticket')->exists())->toBeTrue();
});
```

### Validation

```php
test('title is required', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', '')
        ->call('save')
        ->assertHasErrors(['form.title' => 'required']);
});
```

### Events

```php
test('dispatches event after creation', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', 'Test')
        ->set('form.body', 'Test body content.')
        ->call('save')
        ->assertDispatched('ticket-created');
});
```

---

## Architecture Tests

```php
// For class-based components
arch()
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toBeFinal();
```
