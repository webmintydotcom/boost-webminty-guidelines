# Webminty Tailwind CSS: Setup, Structure & Theme

## Core Tailwind Principle

**Utility-first, default-theme-first, component-extracted second.**

1. Reach for a built-in utility before anything else.
2. Stay on the default theme scale (spacing, color, radius, font-size, breakpoint).
3. Promote to a component the second time you reach for the same class set.
4. Add a custom token to `@theme` only when it is a true design-system value that will be reused.
5. Write custom CSS only when no utility, variant, or token can express it.

If you find yourself fighting Tailwind, the design — not Tailwind — is usually the thing to revisit.

---

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
