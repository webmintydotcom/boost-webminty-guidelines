# Webminty Livewire 4: Islands, Slots, Forms & Navigation

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

---

## Slots

Livewire 4 components accept slots like Blade components.

### Default Slot

```blade
{{-- Using the component --}}
<livewire:modal>
    <p>This content goes in the default slot.</p>
</livewire:modal>
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
<livewire:modal>
    <livewire:slot name="header">
        <h2>Confirm Delete</h2>
    </livewire:slot>

    <p>Are you sure you want to delete this item?</p>

    <livewire:slot name="footer">
        <button wire:click="cancel">Cancel</button>
        <button wire:click="confirm">Confirm</button>
    </livewire:slot>
</livewire:modal>
```

```blade
{{-- Inside the modal component --}}
<div class="modal">
    <div class="modal-header">{{ $slots['header'] }}</div>
    <div class="modal-body">{{ $slot }}</div>
    <div class="modal-footer">{{ $slots['footer'] }}</div>
</div>
```

### Key Rules
- Use `<livewire:component-name>` to render Livewire components — not `<wire:component-name>`.
- Self-close component tags when no slots/content: `<livewire:component-name />` (required in v4).
- Pass named slots via `<livewire:slot name="...">`.
- Access the default slot in the child as `{{ $slot }}` and named slots as `{{ $slots['name'] }}`.

---

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

---

## Navigation

### Route::livewire()

Livewire 4 provides a dedicated route macro for full-page components:

```php
use App\Livewire\Dashboard;
use App\Livewire\Tickets\TicketList;

Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
Route::livewire('/tickets', TicketList::class)->name('tickets.index');
```

Prefer `Route::livewire()` over the older `Route::get('/path', Component::class)` pattern for full-page Livewire components — it's clearer about intent and is the documented Livewire 4 idiom. Works on any Laravel version Livewire 4 supports (10+).

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
