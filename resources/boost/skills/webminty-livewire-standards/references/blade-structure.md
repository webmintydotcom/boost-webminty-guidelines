# Webminty Livewire 4: Blade Integration, Naming & Structure

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

### wire:sort (Drag-and-Drop Sorting)

Enable drag-and-drop reordering on lists:

```blade
<ul wire:sort="updateOrder">
    @foreach($items as $item)
        <li wire:sort.item="{{ $item->id }}">
            <span wire:sort.handle>⠿</span>
            {{ $item->title }}
        </li>
    @endforeach
</ul>
```

```php
public function updateOrder(array $items): void
{
    foreach ($items as $item) {
        Item::where('id', $item['value'])->update(['position' => $item['order']]);
    }
}
```

### wire:intersect (Viewport Detection)

Trigger actions when an element enters the viewport — useful for infinite scroll and lazy loading:

```blade
<div wire:intersect="loadMore">
    {{-- Triggers loadMore() when this element scrolls into view --}}
</div>

{{-- Only trigger once --}}
<div wire:intersect.once="trackImpression">
    {{-- Fires trackImpression() the first time this element is visible --}}
</div>
```

### Data from Components
- All data should come from component properties or the `render()` / `rendering()` method
- No Eloquent queries in Blade views
- Use `$this->propertyName` for computed properties

---

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
