# Webminty Tailwind CSS: Quick Reference

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
