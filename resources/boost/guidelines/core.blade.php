{{--
    Webminty Laravel & PHP Guidelines for AI Code Assistants

    Maintained by: Webminty
--}}

# Project Coding Guidelines

- This codebase follows Webminty's Laravel & PHP guidelines.
- Always activate the `webminty-laravel-standards` skill whenever writing, editing, reviewing, or formatting Laravel or PHP code.
- Always activate the `webminty-tailwind-standards` skill whenever writing, editing, reviewing, or formatting Tailwind CSS — in Blade templates, Livewire views, Inertia components, or the CSS entry file.
- For Livewire projects, also activate the `webminty-livewire-standards` skill when working on Livewire components, form objects, or Blade templates with `wire:` directives.
- For Inertia projects, also activate the `webminty-inertia-standards` skill when working on controllers that return Inertia responses, shared data, or Inertia-related testing.

## Project Feature Log (`features.md`)

- Every project must maintain a `features.md` file at the repository root. It is the canonical record of what the product does, used for future documentation, marketing copy, and build-out planning.
- The file has three parts:
  1. **Status** — a single line at the top: `Status: In Development` or `Status: Live`.
  2. **User-facing features** — what end users can see, click, or do. Group as a bulleted list. Each entry is a short noun phrase plus a one-line description.
  3. **Backend features** — APIs, queues, scheduled jobs, integrations, webhooks, admin tools, and other capabilities not directly visible to end users. Same format as above.
- If `features.md` does not exist when starting feature work, create it before finishing the task.
- Update `features.md` at the end of any feature or PR that adds, changes, or removes a user-facing capability or backend feature. Skip updates for incidental refactors, bug fixes, dependency bumps, or internal cleanup that does not change behavior.
- When the project transitions from development to live, update the `Status` line in the same PR that performs the launch.
- `features.md` is distinct from `README.md`. `README.md` covers how to install, run, and develop the project. `features.md` covers what the product does. Do not duplicate content between them.
- Do not include any of the following in `features.md`: TODOs or planned-but-unbuilt features, tech-debt notes, refactor history, internal implementation details that do not represent a capability, marketing taglines or sales copy, credentials, API keys, or any secrets.

## Decisions Log (`DECISIONS.md`)

- Every project must maintain a `DECISIONS.md` file at the repository root. It is an append-only log of non-obvious technical and architectural decisions, giving future contributors (human and AI) the context that git history alone does not preserve.
- Each entry follows this shape:
  - `## YYYY-MM-DD — <short decision title>`
  - **Decision:** one or two sentences stating what was chosen.
  - **Why:** the reasoning, constraints, or trade-offs that drove the choice.
  - **Alternatives considered:** brief list of options that were rejected and why (omit if there were none).
- Add an entry when making a choice another developer would reasonably ask "why did we do it this way?" about. Examples: stack picks (Livewire vs Inertia), package selection (e.g. Spatie Permissions vs native Gates), data-model trade-offs, deployment-target choices, opting out of a Laravel default.
- Do not log routine implementation details, bug fixes, or anything obvious from reading the code.
- Never edit or delete prior entries. If a decision is reversed, add a new dated entry that supersedes the old one and references it by title.
- If `DECISIONS.md` does not exist when making a loggable decision, create it before finishing the task.

## Changelog (`CHANGELOG.md`)

- Every project must maintain a `CHANGELOG.md` file at the repository root, listing user-visible changes in reverse chronological order.
- Group entries under a date or version heading (e.g. `## 2026-05-12` or `## v1.4.0 — 2026-05-12`). Under each heading, group changes using `Added`, `Changed`, `Fixed`, `Removed`, `Deprecated`, `Security` sub-headings — omit any that do not apply.
- Each line is a short, plain-language description of what changed from a user's perspective.
- Update `CHANGELOG.md` when shipping a release, deploy, or user-visible change. Skip purely internal refactors, dependency bumps with no behavior change, and test-only changes.
- For projects that are not yet `Status: Live` in `features.md`, the file should still exist with an `## Unreleased` section that collects pre-launch changes until the first release.
- If `CHANGELOG.md` does not exist when shipping a user-visible change, create it before finishing the task.
