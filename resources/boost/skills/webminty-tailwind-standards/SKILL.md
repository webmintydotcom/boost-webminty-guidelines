---
name: webminty-tailwind-standards
description: Apply Webminty's Tailwind CSS standards for any task that creates, edits, reviews, refactors, or formats styling in Blade, Livewire, Inertia (React/Vue/Svelte), or plain HTML; covers Tailwind v4 CSS-first setup, theme tokens, utility usage and class order, variants and responsive patterns, component extraction, dark mode, and v3-to-v4 migration concerns.
license: MIT
compatibility: Tailwind CSS v4+, Laravel 11+, PHP 8.2+
metadata:
  author: Webminty
---

# Webminty Tailwind Guidelines

## Overview
Apply Webminty's Tailwind CSS guidelines to keep styling consistent, fast, and framework-native. These standards cover **Tailwind v4** (CSS-first configuration, no `tailwind.config.js`), utility-first usage, theme tokens, component extraction, variants, and dark mode.

## When to Activate
- Activate this skill for any styling work on Blade templates, Livewire views, Inertia components (React/Vue/Svelte), or plain HTML — even if the user does not explicitly mention Webminty.
- Activate this skill when editing `.css` entry files (e.g. `resources/css/app.css`) that contain `@import "tailwindcss"`, `@theme`, `@plugin`, `@utility`, `@custom-variant`, `@variant`, `@source`, or `@apply`.
- Activate this skill when adding, removing, or reorganising `class="..."` attributes, or using arbitrary values (`w-[37px]`, `bg-[#abcdef]`).
- Activate this skill when introducing or refactoring brand tokens, design tokens, or dark mode.

## Scope
- In scope: Tailwind v4 setup, CSS-first config (`@import`, `@theme`, `@plugin`, `@source`, `@utility`, `@custom-variant`, `@variant`), utility usage, class ordering, arbitrary values, responsive/state/data variants, container queries, dark mode, theme tokens, component extraction, `prettier-plugin-tailwindcss`, v3-to-v4 migration anti-patterns.
- Out of scope: PHP/Laravel conventions (see `webminty-laravel-standards`), Livewire directives and `data-loading` patterns (see `webminty-livewire-standards`), Inertia controller conventions (see `webminty-inertia-standards`), JavaScript framework component architecture (React/Vue/Svelte composition).

## Workflow
1. Identify the artifact (Blade view, Livewire component, Inertia page/component, plain HTML, CSS entry file, theme definition).
2. Read `references/webminty-tailwind-guidelines.md` and focus on the relevant section.
3. Apply the core Tailwind principle first (utility-first, default theme, no premature custom CSS), then variants, then component extraction.
4. If a rule conflicts with existing project conventions, follow Webminty conventions and keep changes consistent across the file.

## Core Rules (Summary)
- Use Tailwind v4 with CSS-first config: `@import "tailwindcss";` plus `@theme { … }` in a single CSS entry file. **No `tailwind.config.js`.**
- **Default to built-in utilities and theme variables.** Do not invent custom classes, CSS variables, or `@theme` extensions unless an existing utility/variable genuinely cannot express the design.
- Prefer the default theme scale (spacing, color, radius, font-size, breakpoint) over custom values.
- Use arbitrary values (`w-[37px]`, `bg-[#abcdef]`) only for true one-offs; never as a substitute for the scale.
- Add to `@theme` **only** when introducing a reusable design token (e.g. `--color-brand-*`, `--font-display`).
- Extract repeated utility patterns to **components** (Blade, Livewire, React/Vue/Svelte), not to custom CSS classes via `@apply`.
- Use `@apply` only inside `@layer components` for genuinely component-level styles (typography resets, third-party-library overrides) — not as a general DRY mechanism.
- Use `@custom-variant` to **define** a new variant; use `@variant` to **apply** an existing variant inside custom CSS. They are not interchangeable.
- Write classes mobile-first; layer up with `sm:`, `md:`, `lg:`, `xl:`, `2xl:` (and `@sm:`, `@md:` for container queries).
- Sort utility classes with `prettier-plugin-tailwindcss`. Do not hand-sort.
- Use the `dark:` variant for dark mode. Do not maintain separate light/dark stylesheets.
- Use semantic colour tokens (`text-foreground`, `bg-card`, `border-border`) when the project uses a design system; otherwise prefer named scale colours (`text-zinc-900`, `bg-white`).
- Use `data-*` and `aria-*` variants (`data-loading:opacity-50`, `aria-expanded:rotate-180`) over JS-toggled classes where possible.
- Use `@source "…"` to include extra content paths only when Tailwind's auto-detection misses them.

## Do and Don't
Do:
- Keep all Tailwind setup in a single CSS entry file (e.g. `resources/css/app.css`).
- Use the default colour, spacing, radius, font-size, and breakpoint scales.
- Promote a class set to a component (`<x-ui.button>`, `<Button />`) the second time you reach for it.
- Sort classes with `prettier-plugin-tailwindcss` on save / via CI.
- Use `data-*:` and `aria-*:` variants for stateful UI driven by HTML attributes.
- Use container queries (`@container`, `@sm:`, `@md:`) for component-level responsiveness.
- Use the OKLCH-based default palette in v4; only override for brand colours.
- Use `@plugin "@tailwindcss/forms";` (or similar) at the top of the CSS entry file when needed.
- Use `focus-visible:` (not `focus:`) for focus rings so they don't appear on mouse clicks.
- Respect `prefers-reduced-motion` with `motion-safe:` / `motion-reduce:` on any animation or transition utility.
- Use `sr-only` for screen-reader-only text; pair with `focus:not-sr-only` for skip-to-content links.

Don't:
- Construct class names dynamically (`bg-${color}-500`, `` `text-${size}` ``) — Tailwind's content scanner only sees **complete, unbroken** class strings. Map full class strings in a lookup object instead.
- Default to `indigo-*`, `violet-*`, `purple-*`, `fuchsia-*`, or `pink-*` as an accent or brand colour. These dominate Tailwind's marketing material and have become an AI/template "tell". If the project has no defined brand, default to a neutral primary (`blue-600`, `emerald-600`, `sky-600`, or a custom `--color-brand-*` token via `@theme`).
- Add a `tailwind.config.js` file — Tailwind v4 is CSS-first.
- Use `@apply` to deduplicate utilities across templates — extract a component instead.
- Use arbitrary values when an existing scale value works (`w-[16px]` → `w-4`).
- Invent custom colour names (`bg-myblue`) when a default works (`bg-blue-500`).
- Maintain manual class-sort order — let Prettier do it.
- Use inline `style="…"` for values Tailwind can express.
- Keep v3-only directives (`@tailwind base; @tailwind components; @tailwind utilities;`) in v4 projects — use `@import "tailwindcss";`.
- Use `theme()` in CSS — read the CSS variable directly (`var(--color-brand-500)`).
- Use `darkMode: 'class'` config — use the `@custom-variant dark (…)` CSS directive if you need to override the default media-query behaviour.

## Examples

```css
/* resources/css/app.css — single CSS entry, Tailwind v4 */
@import "tailwindcss";

@plugin "@tailwindcss/forms";

@source "../../app/View/Components/**/*.php";

@theme {
    --color-brand-50:  oklch(0.97 0.02 250);
    --color-brand-500: oklch(0.62 0.18 250);
    --color-brand-900: oklch(0.30 0.10 250);

    --font-display: "Inter Display", ui-sans-serif, system-ui, sans-serif;
}

/* Optional: override default dark-mode behaviour to a class strategy. */
@custom-variant dark (&:where(.dark, .dark *));
```

```blade
{{-- Blade: utility-first, mobile-first, sorted by prettier-plugin-tailwindcss --}}
<button
    type="submit"
    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:opacity-50 sm:text-base dark:bg-brand-400 dark:hover:bg-brand-300"
>
    {{ __('tickets.create') }}
</button>
```

```blade
{{-- Extract repeated utility sets to a Blade component, not @apply. --}}
<x-ui.button variant="primary">
    {{ __('tickets.create') }}
</x-ui.button>
```

```blade
{{-- Stateful UI via data-* variants instead of JS class toggling. --}}
<button
    wire:click="save"
    class="data-loading:pointer-events-none data-loading:opacity-50"
>
    {{ __('common.save') }}
</button>
```

```tsx
// Inertia (React): same class conventions; sort via prettier-plugin-tailwindcss.
export function Badge({ tone = 'neutral', children }: BadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                tone === 'success' && 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300',
                tone === 'danger'  && 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-300',
                tone === 'neutral' && 'bg-zinc-100 text-zinc-800 dark:bg-zinc-500/10 dark:text-zinc-300',
            )}
        >
            {children}
        </span>
    );
}
```

## References
- `references/webminty-tailwind-guidelines.md`
