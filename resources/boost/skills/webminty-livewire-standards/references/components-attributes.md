# Webminty Livewire 4: Component Formats & Attributes

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
| `#[Defer]` | Defer loading until after initial page render | `#[Defer] public function loadData()` |
| `#[Async]` | Non-blocking action execution | `#[Async] public function generate()` |

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

### #[Defer] Attribute

Defer loading of a component until after the initial page render:

```php
<?php

use Livewire\Attributes\Defer;
use Livewire\Component;

new class extends Component {
    #[Defer]
    public function loadExpensiveData(): array
    {
        return ExpensiveQuery::run();
    }
};
?>

<div>
    <div wire:loading>Loading...</div>
    <div>{{ $this->loadExpensiveData() }}</div>
</div>
```

Use `#[Defer]` for data that is not critical for the initial page render. For bundling multiple deferred loads into a single request, use `lazy.bundle` or `defer.bundle`.

### #[Async] Attribute

Mark an action as non-blocking so the UI remains interactive while the server processes the request:

```php
<?php

use Livewire\Attributes\Async;
use Livewire\Component;

new class extends Component {
    #[Async]
    public function generateReport(): void
    {
        // Long-running operation — UI is not blocked
        app(GenerateReport::class)->execute();
    }
};
?>

<div>
    <button wire:click="generateReport">Generate</button>
</div>
```

You can also use the `.async` modifier directly in Blade: `wire:click.async="generateReport"`.

---
