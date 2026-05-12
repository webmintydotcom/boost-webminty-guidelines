# Webminty Tailwind CSS Guidelines (Reference)

## Table of Contents
- [Core Tailwind Principle](#core-tailwind-principle)
- [Tailwind v4 Setup](#tailwind-v4-setup)
- [Project Structure](#project-structure)
- [Theme & Tokens](#theme--tokens)
- [Default Scales](#default-scales)
- [Utility Usage](#utility-usage)
- [Class Ordering](#class-ordering)
- [Variants & Responsive Design](#variants--responsive-design)
- [Container Queries](#container-queries)
- [Dark Mode](#dark-mode)
- [Accessibility](#accessibility)
- [Component Extraction](#component-extraction)
- [Custom Utilities & Variants](#custom-utilities--variants)
- [`@apply` Guidance](#apply-guidance)
- [Arbitrary Values](#arbitrary-values)
- [Plugins](#plugins)
- [Stack-Specific Notes](#stack-specific-notes)
- [Tooling](#tooling)
- [Anti-Patterns](#anti-patterns)
- [v3 → v4 Migration Notes](#v3--v4-migration-notes)
- [Quick Reference](#quick-reference)

---

## Core Tailwind Principle

**Utility-first, default-theme-first, component-extracted second.**

1. Reach for a built-in utility before anything else.
2. Stay on the default theme scale (spacing, color, radius, font-size, breakpoint).
3. Promote to a component the second time you reach for the same class set.
4. Add a custom token to `@theme` only when it is a true design-system value that will be reused.
5. Write custom CSS only when no utility, variant, or token can express it.

If you find yourself fighting Tailwind, the design — not Tailwind — is usually the thing to revisit.

---

## Tailwind v4 Setup

Webminty projects use **Tailwind CSS v4** with CSS-first configuration.

### CSS Entry File

There is a **single CSS entry file** (typically `resources/css/app.css`):

```css
@import "tailwindcss";
```

That single line imports `preflight`, `theme`, and all utilities. **Do not** use the v3 triplet:

```css
/* ❌ v3 style — not valid in v4 */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### No `tailwind.config.js`

Tailwind v4 is configured in CSS. **Do not create a `tailwind.config.js` file.** All configuration lives in the CSS entry file via `@theme`, `@plugin`, `@source`, `@utility`, and `@custom-variant`.

If a legacy project still has `tailwind.config.js`, prefer migrating it into the CSS entry file rather than maintaining both.

### Build Pipeline

Webminty projects use **Vite** with the official Tailwind plugin:

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

Do not configure PostCSS or Autoprefixer manually — Tailwind v4 uses Lightning CSS internally and handles vendor prefixing.

### Content Detection (`@source`)

Tailwind v4 auto-detects content paths from your project. Add `@source` only when files live outside the default detection (e.g. a vendored package, a sibling repo, or generated files):

```css
@import "tailwindcss";

@source "../../app/View/Components/**/*.php";
@source "../../vendor/webminty/ui-kit/resources/views/**/*.blade.php";
```

Do not add `@source` redundantly for files Tailwind already finds.

---

## Project Structure

```
resources/
├── css/
│   └── app.css           # Single Tailwind entry. @import, @theme, @plugin live here.
├── js/
│   └── app.js
└── views/
    ├── components/       # Blade components. Extract repeated utilities here.
    │   ├── layouts/
    │   ├── ui/
    │   └── forms/
    ├── pages/
    └── partials/
```

For Inertia projects, components live under `resources/js/Pages/`, `resources/js/Components/`, etc. Same rules — extract repeated utilities into components, not custom CSS.

---

## Theme & Tokens

### `@theme` Block

`@theme` registers CSS custom properties that **also become Tailwind utilities**. Adding `--color-brand-500: …` automatically creates `bg-brand-500`, `text-brand-500`, `border-brand-500`, etc.

```css
@import "tailwindcss";

@theme {
    /* Brand colours — generate bg-brand-*, text-brand-*, border-brand-*, ring-brand-* */
    --color-brand-50:  oklch(0.97 0.02 250);
    --color-brand-100: oklch(0.94 0.04 250);
    --color-brand-500: oklch(0.62 0.18 250);
    --color-brand-700: oklch(0.46 0.16 250);
    --color-brand-900: oklch(0.30 0.10 250);

    /* Typography */
    --font-display: "Inter Display", ui-sans-serif, system-ui, sans-serif;

    /* One-off shadows you reuse a lot */
    --shadow-card: 0 1px 2px 0 rgb(0 0 0 / 0.04), 0 1px 3px 0 rgb(0 0 0 / 0.06);
}
```

### Token Namespaces

| Namespace        | Generates                      | Example                                      |
|------------------|--------------------------------|----------------------------------------------|
| `--color-*`      | `bg-*`, `text-*`, `border-*`…  | `--color-brand-500` → `bg-brand-500`         |
| `--font-*`       | `font-*`                       | `--font-display` → `font-display`            |
| `--text-*`       | `text-*` (font-size)           | `--text-mega: 4rem` → `text-mega`            |
| `--spacing-*`    | `p-*`, `m-*`, `w-*`, `h-*`, `gap-*` | `--spacing-18: 4.5rem` → `p-18`         |
| `--radius-*`     | `rounded-*`                    | `--radius-card: 0.875rem` → `rounded-card`   |
| `--shadow-*`     | `shadow-*`                     | `--shadow-card` → `shadow-card`              |
| `--breakpoint-*` | `sm:`, `md:`, `lg:`… variants  | `--breakpoint-3xl: 120rem` → `3xl:`          |
| `--container-*`  | `@sm:`, `@md:`… container query variants | `--container-prose: 65ch` → `@prose:`  |
| `--ease-*`       | `ease-*`                       | `--ease-snappy: …` → `ease-snappy`           |
| `--animate-*`    | `animate-*`                    | `--animate-shake` → `animate-shake`          |

### When to Add a Token

Add a token to `@theme` **only when** all three are true:
1. It expresses a brand or design-system value (not a one-off).
2. It will be reused in **at least two** places.
3. No existing default token expresses it.

Otherwise, use the default scale or an arbitrary value.

### `@theme inline`

Use `@theme inline { … }` when the value should be inlined into utilities at build time rather than referenced via `var(--…)`. Useful for values that depend on other CSS variables you want resolved at definition time. Default to plain `@theme` unless you specifically need inline behaviour.

### Overriding Defaults

To **remove** a default theme value (e.g. you don't ship the default colour palette), set the namespace to `initial`:

```css
@theme {
    --color-*: initial;       /* drop the entire default palette */
    --color-brand-500: oklch(0.62 0.18 250);
    --color-white: #fff;
    --color-black: #000;
}
```

Only do this when you genuinely need to constrain the palette. Most projects should keep the defaults and add brand colours alongside.

### Reading Tokens in CSS

Read tokens as native CSS variables, not via the v3 `theme()` function:

```css
/* ✅ v4 */
.callout {
    background: var(--color-brand-50);
    border-color: var(--color-brand-500);
}

/* ❌ v3 — avoid */
.callout {
    background: theme(colors.brand.50);
}
```

---

## Default Scales

Tailwind v4 ships an opinionated default scale. **Use it.**

### Spacing

`p-0`, `p-0.5`, `p-1`, `p-1.5`, `p-2`, `p-2.5`, `p-3`, `p-3.5`, `p-4`, `p-5`, `p-6`, `p-7`, `p-8`, `p-9`, `p-10`, `p-11`, `p-12`, `p-14`, `p-16`, `p-20`, `p-24`, `p-28`, `p-32`, `p-36`, `p-40`, `p-44`, `p-48`, `p-52`, `p-56`, `p-60`, `p-64`, `p-72`, `p-80`, `p-96` (and arbitrary integers via the dynamic spacing scale).

Tailwind v4 derives all spacing from `--spacing` (default `0.25rem`). Override only if your design system genuinely uses a different base unit.

### Colour

Default palette: `slate`, `gray`, `zinc`, `neutral`, `stone`, `red`, `orange`, `amber`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose` — each with `50`, `100`, `200`, `300`, `400`, `500`, `600`, `700`, `800`, `900`, `950`.

**Webminty defaults:**
- **Neutral: `zinc`** — `text-zinc-900` on light, `text-zinc-100` on dark. Choose one neutral family per project and stay consistent.
- **Accent: project brand colour first**, defined via `--color-brand-*` in `@theme`. If no brand is defined, fall back to `blue-600`, `emerald-600`, or `sky-600` depending on tone.

**Do not default to the "Tailwind purple" palette.** `indigo-*`, `violet-*`, `purple-*`, `fuchsia-*`, and `pink-*` are over-represented in Tailwind marketing, official templates, and AI-generated code. Using them as a default accent makes any UI look like an unbranded AI demo or a Tailwind UI starter that nobody finished customising. They are valid colours — use them when:

- The project's actual brand is in that hue range.
- You need a semantic colour with that meaning (`fuchsia-500` for a tagged "experimental" badge, `pink-500` for a specific brand campaign).

Otherwise, pick a colour the design actually requires — not the colour the LLM reaches for unprompted.

### Status / Semantic Colour Defaults

When you need a generic status colour and the design system hasn't defined one:

| Meaning             | Default                  | Avoid                                |
|---------------------|--------------------------|--------------------------------------|
| Primary action      | `--color-brand-*` (or `blue-600`) | `indigo-600`, `violet-600`, `purple-600` |
| Success / positive  | `emerald-600`            | `green-500` (lower contrast)         |
| Warning / caution   | `amber-500`              | `yellow-500` (poor contrast on white) |
| Danger / destructive | `rose-600` or `red-600` | `pink-600` (reads as brand, not danger) |
| Info / neutral notice | `sky-600`              | `cyan-500` (poor contrast)           |

### Font-Size

`text-xs`, `text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`, `text-3xl`, `text-4xl`, `text-5xl`, `text-6xl`, `text-7xl`, `text-8xl`, `text-9xl`.

Each includes a sensible default line-height. Override with `text-sm/6` style modifiers when needed (`text-sm/6` = `text-sm` with `line-height: 1.5rem`).

### Radius

`rounded-none`, `rounded-xs`, `rounded-sm`, `rounded-md`, `rounded-lg`, `rounded-xl`, `rounded-2xl`, `rounded-3xl`, `rounded-full`.

### Breakpoint

| Variant | Min width |
|---------|-----------|
| `sm:`   | 40rem (640px)  |
| `md:`   | 48rem (768px)  |
| `lg:`   | 64rem (1024px) |
| `xl:`   | 80rem (1280px) |
| `2xl:`  | 96rem (1536px) |

---

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

## Accessibility

Tailwind ships utilities and variants that make accessible patterns the default path. Use them.

### Focus Rings

Always use `focus-visible:` — not `focus:` — for visible focus styles, so rings appear for keyboard/programmatic focus and not for mouse clicks:

```html
<!-- ✅ Keyboard users see the ring; mouse users don't get distracted -->
<button class="rounded-lg bg-brand-500 px-4 py-2 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">

<!-- ❌ Ring flashes on every click -->
<button class="… focus:outline-2 focus:outline-brand-500">
```

For focus rings on coloured backgrounds (e.g. a button row on a card), use `focus-visible:ring-*` with `ring-offset-*` matching the background so the ring isn't lost in the surrounding colour:

```html
<div class="bg-brand-500 p-4">
    <button class="rounded-md bg-white px-3 py-1.5 text-brand-700 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-brand-500">
        Action
    </button>
</div>
```

### `outline-hidden` vs `outline-none`

Tailwind v4 introduced `outline-hidden`, which hides the outline visually but preserves it for forced-colors / Windows high-contrast users. **Prefer `outline-hidden` to `outline-none` whenever you intend to replace the default outline with your own focus style** — never strip outlines entirely.

```html
<!-- ✅ Visually replaced, still works in high-contrast mode -->
<input class="outline-hidden focus-visible:ring-2 focus-visible:ring-brand-500">

<!-- ❌ Inaccessible — kills the high-contrast fallback -->
<input class="outline-none focus:ring-2 focus:ring-brand-500">
```

### Screen-Reader-Only Content

`sr-only` hides content visually but keeps it available to assistive tech. `not-sr-only` reverses it. The combination of `sr-only` + `focus:not-sr-only` is the canonical pattern for **skip-to-content** links:

```html
<a
    href="#main"
    class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-brand-500 focus:px-3 focus:py-2 focus:text-white"
>
    Skip to main content
</a>

<main id="main">…</main>
```

Use `sr-only` for icon-only buttons where a visible label would be redundant:

```html
<button>
    <svg aria-hidden="true" class="size-5">…</svg>
    <span class="sr-only">Close dialog</span>
</button>
```

### Reduced Motion

Respect `prefers-reduced-motion` with the `motion-safe:` and `motion-reduce:` variants. Wrap every transition / animation utility:

```html
<!-- ✅ Only animates when the user hasn't requested reduced motion -->
<div class="motion-safe:transition motion-safe:duration-200 motion-safe:hover:translate-y-0.5">

<!-- ✅ Equivalent expressed as a "remove on reduce" -->
<div class="transition duration-200 hover:translate-y-0.5 motion-reduce:transform-none motion-reduce:transition-none">

<!-- ❌ Motion-sensitive users get vestibular disturbance -->
<div class="transition duration-500 hover:scale-110">
```

For decorative animations (spinners, confetti, parallax), prefer `motion-safe:animate-*` so the animation simply doesn't play under reduced motion.

### Forced Colors (Windows High-Contrast)

Use `forced-colors:` to tune styles when Windows high-contrast mode is active. The most common need is preserving borders that disappear under forced colors:

```html
<button class="rounded-md bg-brand-500 px-3 py-1.5 text-white forced-colors:border forced-colors:border-[ButtonBorder]">
```

System colour keywords (`ButtonBorder`, `ButtonText`, `Canvas`, `LinkText`, `Mark`, `Highlight`) are usable as arbitrary values inside `forced-colors:` variants.

### Target Sizes

Touch targets should be **at least 44×44px** (`size-11`). Don't ship icon buttons smaller than that without surrounding hit-target padding:

```html
<!-- ✅ 44px hit target even though the icon is 20px -->
<button class="inline-flex size-11 items-center justify-center rounded-md hover:bg-zinc-100">
    <svg aria-hidden="true" class="size-5">…</svg>
    <span class="sr-only">Settings</span>
</button>
```

### Contrast

Use the default palette ranges that have known contrast properties — body text on light backgrounds should be `text-zinc-900` (or 800), not `text-zinc-500`. Labels and helper text can dip to `text-zinc-600` against white but not below in production UI.

### `aria-*` Variants for State

Drive visible state from ARIA attributes so the visual state and the accessible state can't drift apart:

```html
<button
    aria-pressed="true"
    class="rounded-md px-3 py-1.5 aria-pressed:bg-brand-500 aria-pressed:text-white"
>
    Bold
</button>

<a
    href="/tickets"
    aria-current="page"
    class="rounded-md px-3 py-2 aria-current:bg-zinc-100 aria-current:font-medium"
>
    Tickets
</a>
```

This is preferable to toggling a separate `.active` class from JS — the ARIA attribute is the source of truth.

### Accessibility Checklist

| Concern                            | Utility / Variant                                              |
|-----------------------------------|-----------------------------------------------------------------|
| Visible focus, keyboard only       | `focus-visible:outline-*` or `focus-visible:ring-*`             |
| Replace default outline safely     | `outline-hidden` + your own focus style                         |
| Screen-reader-only text            | `sr-only`                                                       |
| Skip-to-content link               | `sr-only focus:not-sr-only …`                                   |
| Respect reduced motion             | `motion-safe:` (preferred) or `motion-reduce:`                  |
| Windows high-contrast              | `forced-colors:`                                                |
| State without JS class toggling    | `aria-*:` and `data-*:` variants                                |
| Min 44×44px touch targets          | `size-11` (or larger) on icon buttons                           |
| Reasonable body contrast           | `text-zinc-900` body, `text-zinc-600` minimum for secondary     |

---

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

## Anti-Patterns

### ❌ Dynamically Constructed Class Names

**This is the single most common Tailwind mistake.**

Tailwind's content scanner finds classes by matching **complete, unbroken strings** in your source. It does **not** evaluate JavaScript, interpolate templates, or stitch fragments together. The following all silently produce missing styles in production (where unused CSS has been purged):

```tsx
// ❌ Tailwind never sees `bg-red-500` — only the fragments `bg-` and `-500`
<div className={`bg-${color}-500`} />

// ❌ Same problem; concatenation also breaks the scanner
const sizeClass = 'text-' + size;

// ❌ Subtle: works in dev (no purge), breaks in prod
<div className={`p-${spacing} rounded-${radius}`} />
```

These often appear to work locally because the dev build retains all utilities; they break only after the production build prunes unused classes.

**Fix: map full class strings.** Always write the complete utility, then index into a lookup:

```tsx
// ✅ Tailwind sees every full class string
const tones = {
    red:     'bg-red-500 text-white',
    emerald: 'bg-emerald-500 text-white',
    amber:   'bg-amber-500 text-zinc-900',
} as const;

<div className={tones[tone]} />
```

For Blade:

```blade
@php
$tones = [
    'red'     => 'bg-red-500 text-white',
    'emerald' => 'bg-emerald-500 text-white',
    'amber'   => 'bg-amber-500 text-zinc-900',
];
@endphp

<div class="{{ $tones[$tone] }}">
```

**Decision rules:**
- The list of possible values is **closed and known at author time** → use a lookup object (preferred — the type system catches typos).
- Truly user-supplied values that can't be enumerated → use an inline `style="…"` attribute with a sanitised value, not a generated Tailwind class.
- You believe you need `safelist` → you almost certainly don't. Fix the call site first.

If you genuinely need a safelist (e.g. CMS-driven theme colours that the scanner cannot see), declare it explicitly via `@source inline(…)`:

```css
/* Force-include classes the scanner can't find */
@source inline("{bg,text,border}-{red,emerald,amber,sky}-{50,100,500,700}");
```

Treat `@source inline(...)` as a last resort, not a general escape valve. If you find yourself adding entries every week, the underlying data model is wrong — define a fixed enum of brand tones instead.

### ❌ Defaulting to the "Tailwind Purple" Palette

`indigo-*`, `violet-*`, `purple-*`, `fuchsia-*`, and `pink-*` are massively over-represented in Tailwind UI marketing, the official docs, and almost every AI-generated UI. Reaching for them unprompted is a "tell" that nobody bothered to make a real design decision.

```html
<!-- ❌ Pattern-match output — reads as "AI demo" -->
<button class="bg-indigo-600 hover:bg-indigo-700 text-white">Sign in</button>

<!-- ❌ Same problem, worse — "Tailwind UI starter we forgot to brand" -->
<a class="bg-gradient-to-r from-purple-500 via-pink-500 to-red-500">…</a>
```

```html
<!-- ✅ Use the project's brand colour -->
<button class="bg-brand-600 hover:bg-brand-700 text-white">Sign in</button>

<!-- ✅ If no brand is defined yet, fall back to blue/emerald/sky, not violet -->
<button class="bg-blue-600 hover:bg-blue-700 text-white">Sign in</button>
```

Use the indigo/violet/purple/fuchsia/pink range **only when**:
- The project's real brand sits in that hue range.
- The colour has semantic meaning the design has defined (e.g. a tag colour, a campaign accent).

Otherwise, ask "what does the design actually call for?" instead of "what does Tailwind's homepage use?"

### ❌ Creating `tailwind.config.js` in a v4 Project

```js
// ❌ Don't create this in v4 projects
module.exports = {
    content: ['./resources/**/*.blade.php'],
    theme: { extend: { colors: { brand: '#…' } } },
};
```

Configure in CSS via `@theme` instead.

### ❌ Using `@tailwind` Directives

```css
/* ❌ v3 — invalid in v4 */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

Use `@import "tailwindcss";` instead.

### ❌ `@apply`-ing a Button Class

```css
/* ❌ */
.btn { @apply inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-white; }
```

Extract `<x-ui.button>` or `<Button />` instead.

### ❌ Arbitrary Values as Substitutes for Scale

```html
<!-- ❌ -->
<div class="p-[16px] text-[14px] mt-[24px]">

<!-- ✅ -->
<div class="p-4 text-sm mt-6">
```

### ❌ Inline Styles for Tailwind-Expressible Values

```html
<!-- ❌ -->
<div style="margin-top: 1rem; display: flex; gap: 0.5rem;">

<!-- ✅ -->
<div class="mt-4 flex gap-2">
```

Inline `style` is acceptable for **truly dynamic values** computed at render time (e.g. `style="width: {{ $percent }}%"`) that the v4 inline-style-friendly syntax can't express.

### ❌ Hand-Sorting Classes

```html
<!-- ❌ time spent debating class order -->
<div class="flex p-4 bg-white text-zinc-900 rounded-lg shadow gap-2 items-center">
```

Run Prettier. The order is `prettier-plugin-tailwindcss`'s job, not yours.

### ❌ Mixing Dark-Mode Strategies

Don't combine `dark:` variants and semantic tokens in the same project unless deliberately layered. Pick one and stick to it.

### ❌ Custom CSS Variables in `@theme` for One-Offs

```css
/* ❌ One-off page colour shoved into @theme */
@theme {
    --color-marketing-hero-bg: oklch(0.95 0.02 250);
}
```

Use an arbitrary value (`bg-[oklch(0.95_0.02_250)]`) or — better — find a default that fits.

---

## v3 → v4 Migration Notes

When migrating a v3 project to v4 (or working on a partially-migrated codebase):

| v3                                                | v4                                              |
|---------------------------------------------------|-------------------------------------------------|
| `tailwind.config.js`                              | `@theme {}` in CSS                              |
| `@tailwind base; @tailwind components; @tailwind utilities;` | `@import "tailwindcss";`               |
| `theme('colors.brand.500')` in CSS                | `var(--color-brand-500)`                        |
| `darkMode: 'class'`                               | `@custom-variant dark (&:where(.dark, .dark *));` |
| `content: [...]` array                            | Auto-detected; `@source` for exceptions         |
| `bg-opacity-*`, `text-opacity-*`                  | Slash syntax: `bg-zinc-900/50`                  |
| `decoration-slice` (etc.) plugin extras           | Mostly built-in in v4                           |
| `@tailwindcss/container-queries` plugin           | Built-in (`@container`, `@sm:`, `@md:`, …)      |
| `@tailwindcss/aspect-ratio` plugin                | Built-in (`aspect-video`, `aspect-square`, …)   |
| `bg-gradient-to-r`                                | `bg-linear-to-r` (gradient utilities renamed)   |
| `ring` (defaults to 3px blue)                     | `ring` defaults to 1px `currentColor` — set explicitly: `ring-2 ring-brand-500` |
| `shadow-sm`, `shadow`                             | `shadow-xs`, `shadow-sm` (scale shifted)        |
| `rounded`, `rounded-sm`                           | `rounded-sm`, `rounded-xs` (scale shifted)      |
| `outline-none`                                    | `outline-hidden` (accessibility-preserving)     |

**Run `npx @tailwindcss/upgrade@latest` once at the start of a migration.** It rewrites most renamed utilities and converts `tailwind.config.js` to `@theme`. Review the diff carefully — semantic ring/shadow/rounded shifts often need manual tuning.

---

## Quick Reference

### Setup

```css
/* resources/css/app.css */
@import "tailwindcss";
@plugin "@tailwindcss/forms";

@theme {
    --color-brand-500: oklch(0.62 0.18 250);
}
```

### Class Pattern

```
[layout] [box-model] [typography] [colour] [effects] [interactivity] [responsive] [state] [dark]
```

Prettier sorts for you — don't memorise this, just run the formatter.

### Decision Cheatsheet

| Situation                                    | Action                                        |
|---------------------------------------------|-----------------------------------------------|
| Need a colour                                | Use default palette (**not** indigo/violet/purple/fuchsia/pink as default) |
| Need brand colour                            | Add `--color-brand-*` to `@theme`             |
| No brand defined yet, need an accent         | `blue-600`, `emerald-600`, or `sky-600` — not `indigo-*` / `violet-*` |
| Same class set twice in markup               | Extract a component                           |
| Same class set used in 5+ templates          | Extract a component (you waited too long)     |
| One-off CSS value                            | Arbitrary value `class-[…]`                   |
| Repeated arbitrary value                     | Promote to `@theme` or component              |
| Element-level reset                          | `@layer base { … }` with `@apply`             |
| Component reuse                              | **Component**, not `@apply`                   |
| Stateful UI (open/closed, loading)           | `data-*:` / `aria-*:` variants                |
| Container-level responsive                   | `@container` + `@sm:` / `@md:`                |
| Viewport-level responsive                    | `sm:` / `md:` / `lg:`                         |
| User-toggled dark mode                       | `@custom-variant dark (&:where(.dark, .dark *));` |
| OS-driven dark mode                          | `dark:` variant out of the box                |
| Focus ring                                   | `focus-visible:outline-*` (not `focus:`)      |
| Dynamic class value (`bg-${x}-500`)          | **Don't.** Use a lookup of full class strings |
| Animation / transition                       | Wrap in `motion-safe:` (or add `motion-reduce:` reset) |
| Screen-reader-only label                     | `sr-only`                                     |
| Skip-to-content link                         | `sr-only focus:not-sr-only …`                 |
| Replace default outline                      | `outline-hidden` + your own focus style       |

### File Locations

| File                                  | Purpose                                     |
|---------------------------------------|---------------------------------------------|
| `resources/css/app.css`               | Single CSS entry; `@import`, `@theme`, `@plugin` |
| `resources/views/components/ui/`      | Blade UI components                         |
| `resources/js/Components/UI/`         | Inertia UI components                       |
| `vite.config.js`                      | Tailwind Vite plugin registration           |
| `.prettierrc.json`                    | `prettier-plugin-tailwindcss` registration  |

### Commands

```bash
# Migrate a v3 project to v4
npx @tailwindcss/upgrade@latest

# Format & sort classes
npx prettier --write 'resources/**/*.{blade.php,tsx,jsx,vue}'
```
