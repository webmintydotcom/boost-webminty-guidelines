# Webminty Tailwind CSS: Utilities, Variants & Dark Mode

## Utility Usage

### Mobile-First

Write the smallest-screen styles **without** a variant, then layer up:

```html
<!-- ✅ Mobile-first -->
<div class="flex flex-col gap-4 sm:flex-row sm:gap-6 lg:gap-8">

<!-- ❌ Desktop-first; over-applies on small screens -->
<div class="lg:gap-8 sm:gap-6 sm:flex-row flex flex-col gap-4">
```

### Logical Properties (Where Helpful)

Prefer logical-property utilities for layout that should mirror in RTL:

- `ms-*` / `me-*` (margin-inline-start/end) over `ml-*` / `mr-*`
- `ps-*` / `pe-*` over `pl-*` / `pr-*`
- `start-*` / `end-*` over `left-*` / `right-*`

Use physical (`left-*`) only when you genuinely mean "left" regardless of text direction.

### Negative Values

Use the `-` prefix: `-mt-2`, `-translate-x-1/2`. Do **not** write `mt-[-8px]`.

### Fractional Values

Use the fractional scale: `w-1/2`, `w-2/3`, `h-3/4`. Use arbitrary `w-[37%]` only for true one-offs.

### Opacity Modifiers

Use the slash syntax instead of separate opacity utilities:

```html
<!-- ✅ -->
<div class="bg-zinc-900/50 text-white/80 ring-white/10">

<!-- ❌ deprecated separate utilities -->
<div class="bg-zinc-900 bg-opacity-50 text-white text-opacity-80">
```

### Important Modifier

`!` prefix forces `!important`. Reserve for fighting third-party CSS only:

```html
<div class="!mt-0"> <!-- only when something else is forcing margin-top -->
```

If you find yourself reaching for `!` regularly, the underlying CSS is fighting you — fix that instead.

---

---

## Class Ordering

**Use `prettier-plugin-tailwindcss` to sort classes automatically.** Do not hand-sort.

### Installation

```bash
npm install -D prettier prettier-plugin-tailwindcss
```

```json
// .prettierrc.json
{
    "plugins": ["prettier-plugin-tailwindcss"]
}
```

For Blade, add `prettier-plugin-blade` alongside:

```bash
npm install -D @shufo/prettier-plugin-blade
```

```json
{
    "plugins": ["@shufo/prettier-plugin-blade", "prettier-plugin-tailwindcss"],
    "overrides": [
        { "files": "*.blade.php", "options": { "parser": "blade" } }
    ]
}
```

### Multi-line Class Lists

When a single `class="…"` exceeds the printWidth, let Prettier break it onto multiple lines. Do not manually break a single class string into a multi-line JS array unless using a helper like `cn(…)` in Inertia/React.

```html
<button
    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-50 sm:text-base dark:bg-brand-400 dark:hover:bg-brand-300"
>
```

### Conditional Classes in JS/TS

Use `clsx` or a tiny `cn()` helper (`clsx` + `tailwind-merge`) for conditional classes in React/Vue/Svelte:

```ts
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: Parameters<typeof clsx>): string {
    return twMerge(clsx(...inputs));
}
```

```tsx
<div className={cn(
    'rounded-lg px-4 py-2 text-sm',
    isActive && 'bg-brand-500 text-white',
    isDisabled && 'pointer-events-none opacity-50',
    className, // allow consumer overrides
)} />
```

`tailwind-merge` resolves conflicting utilities (e.g. consumer passes `p-6` and component has `p-4` → `p-6` wins).

---

---

## Variants & Responsive Design

### State Variants

| Variant            | Use for                                    |
|--------------------|--------------------------------------------|
| `hover:`           | Pointer hover                              |
| `focus:`           | Element focus (any input method)           |
| `focus-visible:`   | Keyboard/programmatic focus only — **preferred for focus rings** |
| `focus-within:`    | Focus on any descendant                    |
| `active:`          | Active/pressed state                       |
| `disabled:`        | `disabled` attribute or `:disabled`        |
| `checked:`         | Checked checkbox/radio                     |
| `placeholder-shown:` | Empty inputs                             |
| `read-only:`       | `readonly` attribute                       |
| `required:`        | `required` attribute                       |
| `invalid:` / `valid:` | Form validation states                  |

**Always prefer `focus-visible:` over `focus:` for focus rings** so they don't appear on mouse clicks.

### Negation

Use `not-*` to invert any variant:

```html
<button class="not-hover:opacity-80"> <!-- 80% opacity except when hovered -->
<input class="not-placeholder-shown:border-emerald-500"> <!-- when filled -->
```

### Group & Peer

`group` lets a parent drive children's variants; `peer` lets a sibling drive a following element:

```html
<a href="#" class="group flex items-center gap-2">
    <span>Read more</span>
    <svg class="size-4 transition group-hover:translate-x-0.5">…</svg>
</a>

<input type="checkbox" class="peer">
<label class="peer-checked:font-bold">Subscribe</label>
```

Named groups for nesting: `group/menu`, `group-hover/menu:bg-zinc-100`.

### Data & ARIA Variants

Prefer attribute-driven state over JS class toggling:

```html
<button
    aria-expanded="false"
    class="aria-expanded:rotate-180 transition-transform"
>
    <svg>…</svg>
</button>

<div
    data-state="open"
    class="data-[state=open]:block data-[state=closed]:hidden"
></div>
```

Tailwind v4 ships shorthand for many common attributes:

- `data-loading:opacity-50` (Livewire 4 auto-applies `data-loading`)
- `data-dirty:border-yellow-500` (Livewire 4 auto-applies `data-dirty`)
- `aria-disabled:pointer-events-none`
- `aria-current:bg-zinc-100`

### `has-*` Variant

Style a parent based on its descendants (`:has()` CSS):

```html
<label class="block rounded-lg border p-4 has-checked:border-brand-500 has-checked:bg-brand-50">
    <input type="radio" name="plan" class="sr-only">
    Pro plan
</label>
```

### Responsive

Standard breakpoints: `sm:`, `md:`, `lg:`, `xl:`, `2xl:`. Combine freely with state variants:

```html
<div class="text-sm sm:text-base hover:underline sm:hover:no-underline">
```

Use `max-sm:`, `max-md:`, etc. for **at most** that breakpoint (rare but useful).

---

---

## Container Queries

Tailwind v4 has **built-in container queries** — no plugin required.

```html
<aside class="@container">
    <article class="flex flex-col @md:flex-row @md:gap-6">
        <img class="@md:w-48" src="…">
        <div class="@md:flex-1">…</div>
    </article>
</aside>
```

| Container variant | Min width |
|-------------------|-----------|
| `@3xs:`           | 16rem     |
| `@2xs:`           | 18rem     |
| `@xs:`            | 20rem     |
| `@sm:`            | 24rem     |
| `@md:`            | 28rem     |
| `@lg:`            | 32rem     |
| `@xl:`            | 36rem     |
| `@2xl:`           | 42rem     |
| `@3xl:`           | 48rem     |
| `@4xl:`           | 56rem     |
| `@5xl:`           | 64rem     |
| `@6xl:`           | 72rem     |
| `@7xl:`           | 80rem     |

**Prefer container queries over viewport breakpoints for component-level responsiveness** — it makes components portable into sidebars, modals, and split layouts without breaking.

Named containers for nesting: `@container/card`, `@md/card:flex-row`.

---

---

## Dark Mode

### Default: `prefers-color-scheme`

Tailwind v4's `dark:` variant **defaults to `prefers-color-scheme: dark`**. Use it directly:

```html
<div class="bg-white text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
```

### Class-Based Strategy

If the project supports a user-toggled theme, override the `dark` variant in CSS with `@custom-variant`:

```css
@custom-variant dark (&:where(.dark, .dark *));
```

Then toggle `<html class="dark">` from JS. This is the v4 equivalent of v3's `darkMode: 'class'`.

> **Note:** `@custom-variant` *defines* a variant. The similarly-named `@variant` directive *applies* an existing variant from inside custom CSS (e.g. nesting `@variant dark { … }` inside a `.card` rule). They are not interchangeable.

### Semantic Token Pattern

For projects with a design system, define **semantic** tokens that swap by colour scheme, and use those in markup instead of literal scale colours:

```css
@theme {
    --color-background: var(--color-white);
    --color-foreground: var(--color-zinc-900);
    --color-card:       var(--color-zinc-50);
    --color-border:     var(--color-zinc-200);
}

.dark {
    --color-background: var(--color-zinc-950);
    --color-foreground: var(--color-zinc-100);
    --color-card:       var(--color-zinc-900);
    --color-border:     var(--color-zinc-800);
}
```

Then in markup:

```html
<div class="bg-background text-foreground border-border">
    <!-- no dark: prefixes needed; tokens swap themselves -->
</div>
```

Choose **one** approach per project and apply it consistently. Don't mix `dark:` variants and semantic tokens in the same component.

---
