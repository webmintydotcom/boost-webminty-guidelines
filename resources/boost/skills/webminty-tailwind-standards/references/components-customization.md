# Webminty Tailwind CSS: Components, Custom Utilities & Tooling

## Component Extraction

**The second time you reach for the same class set, extract a component.**

Components — **not custom CSS classes** — are the DRY mechanism for utility-first CSS.

### Blade

```blade
{{-- resources/views/components/ui/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium shadow-sm transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50';

$variants = [
    'primary'   => 'bg-brand-500 text-white hover:bg-brand-600 focus-visible:outline-brand-500 dark:bg-brand-400 dark:hover:bg-brand-300',
    'secondary' => 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200 focus-visible:outline-zinc-400 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700',
    'ghost'     => 'text-zinc-900 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-800',
    'danger'    => 'bg-rose-500 text-white hover:bg-rose-600 focus-visible:outline-rose-500',
];

$sizes = [
    'sm' => 'h-8 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
    'lg' => 'h-12 px-5 text-base',
];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([$base, $variants[$variant], $sizes[$size]]) }}
>
    {{ $slot }}
</button>
```

Usage:

```blade
<x-ui.button variant="primary">Save</x-ui.button>
<x-ui.button variant="secondary" size="sm">Cancel</x-ui.button>
```

### Livewire (single-file component)

Same `@props`-driven pattern. See `webminty-livewire-standards` for component file layout.

### Inertia (React)

```tsx
// resources/js/Components/UI/Button.tsx
import { cn } from '@/lib/cn';
import { type ButtonHTMLAttributes } from 'react';

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
    size?: 'sm' | 'md' | 'lg';
};

const base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium shadow-sm transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50';

const variants = {
    primary:   'bg-brand-500 text-white hover:bg-brand-600 focus-visible:outline-brand-500 dark:bg-brand-400 dark:hover:bg-brand-300',
    secondary: 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200 focus-visible:outline-zinc-400 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700',
    ghost:     'text-zinc-900 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-800',
    danger:    'bg-rose-500 text-white hover:bg-rose-600 focus-visible:outline-rose-500',
} as const;

const sizes = {
    sm: 'h-8 px-3 text-sm',
    md: 'h-10 px-4 text-sm',
    lg: 'h-12 px-5 text-base',
} as const;

export function Button({ variant = 'primary', size = 'md', className, ...props }: Props) {
    return (
        <button
            type={props.type ?? 'button'}
            className={cn(base, variants[variant], sizes[size], className)}
            {...props}
        />
    );
}
```

### When to Extract

Extract when **any** of these is true:
- You've written the same class set twice.
- The class set encodes a design-system primitive (button, badge, input, card).
- The class set is long enough to obscure structure (typically 6+ classes).
- Variants/sizes are involved.

Do **not** extract for one-off compositions or when the only repetition is layout primitives (`flex gap-2`).

---

---

## Custom Utilities & Variants

### `@utility`

Use `@utility` to define a custom utility that participates in the variant system (responsive, hover, dark, etc.):

```css
@utility content-auto {
    content-visibility: auto;
}

@utility scrollbar-hidden {
    &::-webkit-scrollbar { display: none; }
}
```

Then `md:content-auto` and `hover:scrollbar-hidden` just work.

**Use sparingly.** Most needs are already covered by built-ins. Before reaching for `@utility`, check whether the property is already a Tailwind utility — `text-balance`, `text-pretty`, `field-sizing-*`, `aspect-*`, `size-*`, and many other modern CSS features ship in v4.

### `@custom-variant`

Define custom variants for project-specific states with `@custom-variant`:

```css
@custom-variant hocus (&:hover, &:focus-visible);
@custom-variant touch (@media (hover: none) and (pointer: coarse));
@custom-variant theme-midnight (&:where([data-theme="midnight"] *));
```

Then `hocus:underline`, `touch:opacity-100`, and `theme-midnight:bg-black` apply in their respective contexts.

> `@custom-variant` *defines* variants. The separate `@variant` directive *applies* an existing variant from inside custom CSS:
>
> ```css
> .card {
>     background: white;
>     @variant dark { background: oklch(0.20 0 0); }
> }
> ```

### `@layer`

`@layer base`, `@layer components`, `@layer utilities` still work in v4 for ordering scoped CSS:

```css
@layer base {
    h1 { @apply text-3xl font-bold tracking-tight; }
    h2 { @apply text-2xl font-semibold; }
    a  { @apply text-brand-600 underline-offset-4 hover:underline; }
}
```

Use `@layer base` for **element-level resets and typography** — not as a shortcut for repeated utility patterns in markup.

---

---

## `@apply` Guidance

`@apply` exists in v4 but is **the wrong tool 90% of the time**.

### When `@apply` Is Appropriate

- Styling raw HTML elements you don't control (markdown output, third-party widgets).
- Typography resets in `@layer base`.
- Bridging to a CSS-only context where utilities can't reach (e.g. a `::marker`, a `[type="search"]` reset).

### When `@apply` Is Wrong

- Deduplicating utilities across templates → **extract a component instead**.
- Creating named "design tokens" like `.btn`, `.card`, `.input` → **components, not classes**.
- Hiding utility complexity from designers → **the complexity is real; abstract it via components, not class names**.

### Example: Acceptable

```css
@layer base {
    .prose h1 {
        @apply text-3xl font-bold tracking-tight;
    }
    .prose pre {
        @apply rounded-lg bg-zinc-900 p-4 text-sm text-zinc-100;
    }
}
```

### Example: Wrong

```css
/* ❌ Don't do this — extract a Blade/Inertia component */
@layer components {
    .btn-primary {
        @apply inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-white;
    }
}
```

---

---

## Arbitrary Values

Tailwind supports `class-[value]` arbitrary syntax for every property:

```html
<div class="top-[117px] grid-cols-[1fr_auto_1fr] bg-[#1da1f2] [&_p]:mt-0">
```

### Rules

1. **Use only for true one-offs.** If you write the same arbitrary value twice, promote it to `@theme` or a component.
2. **Never substitute for the scale.** `w-[16px]` is a bug — use `w-4`.
3. **Prefer arbitrary properties (`[property:value]`) for one-off CSS** that has no utility:

```html
<div class="[mask-image:linear-gradient(to_bottom,black,transparent)]">
```

4. **Use arbitrary variants (`[&_…]:`)** for descendant styling that would otherwise need a custom selector:

```html
<div class="prose [&_h2]:mt-12 [&_pre]:rounded-lg">
```

5. **Quote complex CSS values** with underscores (Tailwind converts `_` to space):

```html
<div class="grid-cols-[repeat(auto-fill,minmax(250px,1fr))]">
```

---

---

## Plugins

Use `@plugin` to load official Tailwind plugins:

```css
@import "tailwindcss";

@plugin "@tailwindcss/forms";
@plugin "@tailwindcss/typography";
```

Common plugins:

| Plugin                         | Use for                                                          |
|--------------------------------|------------------------------------------------------------------|
| `@tailwindcss/forms`           | Sensible base styles for `<input>`, `<select>`, `<textarea>`     |
| `@tailwindcss/typography`      | `prose` class for long-form HTML (markdown output)               |
| `@tailwindcss/container-queries` | **Not needed in v4** — container queries are built in          |
| `@tailwindcss/aspect-ratio`    | **Not needed in v4** — `aspect-*` utilities are built in         |

If you find yourself needing a plugin not on this list, check whether v4 already supports it natively before adding.

---

---

## Stack-Specific Notes

### Blade / Livewire

- See `webminty-laravel-standards` and `webminty-livewire-standards` for component-file conventions.
- Use `$attributes->class([...])` in Blade components to merge consumer classes with component defaults.
- For Livewire 4 stateful UI, prefer **`data-loading:`** and **`data-dirty:`** variants over `wire:loading` class swapping.

### Inertia (React / Vue / Svelte)

- See `webminty-inertia-standards` for Laravel-side conventions.
- Use `clsx` + `tailwind-merge` (`cn()` helper) for conditional class composition.
- Co-locate component-specific Tailwind classes with the component file. No CSS modules, no scoped styles.

### Plain HTML / Email

- Tailwind is **not** suitable for email templates — most clients strip `<style>` tags and ignore custom properties. Use inline styles via a tool like `maizzle` (which can consume Tailwind classes and inline them) or hand-rolled inline styles.

---

---

## Tooling

### Required

- `tailwindcss` v4+
- `@tailwindcss/vite` (build)
- `prettier` + `prettier-plugin-tailwindcss` (class ordering)

### Recommended

- `@shufo/prettier-plugin-blade` for Blade formatting
- `tailwind-merge` for Inertia/React conditional class composition
- VS Code: `bradlc.vscode-tailwindcss` extension (Tailwind IntelliSense)

### IntelliSense Configuration

For class detection inside `cn()` and `clsx()` calls, add to `.vscode/settings.json`:

```json
{
    "tailwindCSS.experimental.classRegex": [
        ["cn\\(([^)]*)\\)", "[\"'`]([^\"'`]*).*?[\"'`]"],
        ["clsx\\(([^)]*)\\)", "[\"'`]([^\"'`]*).*?[\"'`]"]
    ]
}
```

---
