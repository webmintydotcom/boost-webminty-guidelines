# Webminty Livewire Guidelines (Reference)

## Table of Contents
- [Component Structure](#component-structure)
- [Attributes](#attributes)
- [Form Objects](#form-objects)
- [Navigation](#navigation)
- [Naming Conventions](#naming-conventions)
- [Directory Structure](#directory-structure)
- [Blade Integration](#blade-integration)
- [Testing](#testing)
- [Architecture Tests](#architecture-tests)

---

## Component Structure

### Full-Page Component

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

### Component with Data

```php
#[Title('Tickets')]
#[Layout('components.layouts.app')]
final class TicketList extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function render(): View
    {
        return view('livewire.ticket-list', [
            'tickets' => Ticket::query()
                ->when($this->search, fn ($q, $search) => $q->search($search))
                ->when($this->status, fn ($q, $status) => $q->where('status', $status))
                ->latest()
                ->paginate(15),
        ]);
    }
}
```

### Key Rules
- Components must be `final`
- Use `#[Title]` and `#[Layout]` attributes on full-page components
- Pass data to views via the `render()` method's second argument
- Keep components thin — delegate business logic to Actions
- Use `declare(strict_types=1)` and explicit return types

---

## Attributes

| Attribute | Purpose | Example |
|-----------|---------|---------|
| `#[Title('...')]` | Set page title | `#[Title('Dashboard')]` |
| `#[Layout('...')]` | Set layout component | `#[Layout('components.layouts.app')]` |
| `#[Url]` | Bind property to query string | `#[Url] public string $search = ''` |
| `#[Locked]` | Prevent client modification | `#[Locked] public int $userId` |
| `#[On('event-name')]` | Listen for events | `#[On('ticket-created')] public function refresh()` |
| `#[Computed]` | Cache derived data for request lifecycle | `#[Computed] public function total(): int` |
| `#[Validate('...')]` | Inline validation rule | `#[Validate('required\|string')]` |

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

### Using Forms in Components

```php
#[Title('Create Ticket')]
#[Layout('components.layouts.app')]
final class CreateTicketPage extends Component
{
    public TicketForm $form;

    public function save(CreateTicket $createTicket): void
    {
        $ticket = $this->form->store($createTicket);

        $this->redirect(
            route('tickets.show', $ticket),
            navigate: true,
        );
    }

    public function render(): View
    {
        return view('livewire.create-ticket');
    }
}
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

## Naming Conventions

| What | Convention | Example |
|------|-----------|---------|
| Components | PascalCase, descriptive | `Dashboard`, `TicketList` |
| Form objects | PascalCase, suffixed with `Form` | `LoginForm`, `TicketForm` |
| Component views | kebab-case in `livewire/` | `livewire/ticket-list.blade.php` |
| Events | kebab-case | `ticket-created`, `order-updated` |

---

## Directory Structure

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

{{-- Loading states --}}
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

### Data from Components
- All data should come from component properties or the `render()` method
- No Eloquent queries in Blade views
- Use `$this->propertyName` for computed properties

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
arch()
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toBeFinal();
```
