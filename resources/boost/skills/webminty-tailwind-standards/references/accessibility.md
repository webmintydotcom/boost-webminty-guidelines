# Webminty Tailwind CSS: Accessibility

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
