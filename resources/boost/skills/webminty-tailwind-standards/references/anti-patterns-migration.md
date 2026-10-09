# Webminty Tailwind CSS: Anti-Patterns & v3 → v4 Migration

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
