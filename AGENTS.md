# AGENTS.md — Filament Page Manager

This file provides guidance for AI coding agents working in this repository.

## Project Overview

A Laravel package providing a template-based page manager for FilamentPHP v4 admin panels.
Namespace: `CubeAgency\FilamentPageManager`
Key dependencies: Filament v4, `cube-agency/filament-tree-view`, `cube-agency/filament-json`, `cube-agency/filament-template`, `spatie/laravel-package-tools`.

## Directory Structure

- `src/` — Package source code (Models, Services, Filament Resources, Traits, Commands)
- `src/Filament/Resources/PageResource.php` — Main Filament resource with CreatePage, EditPage, ListPages
- `src/Models/Page.php`, `src/Models/PagePreview.php` — Eloquent models
- `src/Services/` — PageRoutes, PageRoutesCache, SlugGenerator
- `config/` — Package config (`filament-page-manager.php`)
- `database/migrations/` — Migration stubs
- `database/factories/` — Model factories
- `resources/css/`, `resources/js/` — Frontend source files
- `resources/dist/` — Compiled frontend assets
- `resources/views/` — Blade templates
- `tests/` — Pest tests (Feature only)
- `tests/Fixtures/` — Test fixtures (e.g., `Templates/TestTemplate.php`)
- `routes/web.php` — Package routes
- `stubs/` — Install command stubs

## Build / Lint / Test Commands

### PHP

| Command | Description |
|---------|-------------|
| `composer test` | Run all tests via Pest |
| `composer test -- --filter="can create a page"` | Run a single test by description |
| `vendor/bin/pest tests/Feature/CreatePageTest.php` | Run a specific test file |
| `vendor/bin/pest --filter="test_name_or_description"` | Run tests matching a pattern |
| `composer test-coverage` | Run tests with coverage report |
| `composer analyse` | Run PHPStan static analysis |
| `composer format` | Format code with Laravel Pint |

### Frontend

| Command | Description |
|---------|-------------|
| `npm run dev` | Watch mode for CSS (Tailwind) and JS (esbuild) |
| `npm run build` | Production build for CSS and JS assets |

### Running a Single Test

```bash
# By file
vendor/bin/pest tests/Feature/EditPageTest.php

# By description (substring match)
vendor/bin/pest --filter="can open create form"

# By pattern
vendor/bin/pest --filter="Clone"
```

## Code Style Guidelines

### General

- PHP 8.2+ required. Use modern PHP features: constructor promotion, union types, enums, named arguments.
- Follow the **Laravel Pint preset** with custom rules defined in `pint.json`.
- Run `composer format` before committing to ensure consistent formatting.

### Formatting (from pint.json)

- **Preset:** `laravel`
- Blank lines before statements (e.g., `if`, `foreach`, `return`)
- One space around concatenation (`.`)
- Method argument spacing: each argument on its own line when multi-line
- One trait per `use` statement
- Single space in type declarations (e.g., `string | int`)

### Imports

- Group imports: PHP built-ins first, then third-party packages, then project namespace.
- Use fully-qualified class names or explicit `use` imports — no backslash-prefixed inline references.
- Alphabetical order within each import group.

### Naming Conventions

- **Classes:** PascalCase (`PageResource`, `SlugGenerator`)
- **Methods:** camelCase (`getFullUrl`, `isChild`)
- **Variables/properties:** snake_case (`$parent_id`, `$with_this`)
- **Config keys:** snake_case with package prefix (`filament-page-manager.table_name`)
- **Route names:** dot-notation with prefix (`pages.1.index`)
- **Test descriptions:** Sentence-style, lowercase (`it('can create a page', ...)`)

### Types

- Declare return types on all methods.
- Use union types where appropriate: `string | BackedEnum | null`.
- Use PHPDoc `@return` annotations for complex types (e.g., `@return array<Asset>`).

### Filament-Specific Patterns

- Resource classes extend `Filament\Resources\Resource`.
- Use `config()` to resolve model class and table names — never hardcode.
- Resource pages (Create, Edit, List) live in `PageResource/Pages/`.
- Use traits (`PageFormTrait`, `HasActivationDates`) for shared logic.

### Models

- Use `HasFactory` trait with a corresponding factory in `database/factories/`.
- Set `$table` dynamically from config in constructor when package-agnostic.
- Cast JSON columns to `'array'` in `$casts`.

### Testing (Pest)

- All tests are Pest v2 using the `it()` function style.
- Base test case is `tests\TestCase.php` extending Orchestra Testbench.
- Use `Livewire::withQueryParams()` for testing Filament Livewire components.
- Use `assertDatabaseHas()` from `Pest\Laravel` for database assertions.
- Use `Page::factory()->make()` to generate test data.
- Test files go in `tests/Feature/` and mirror the feature being tested.
- Keep test fixtures in `tests/Fixtures/`.

### Assets

- CSS uses Tailwind CSS v4 via `@tailwindcss/cli`.
- JavaScript is bundled with esbuild (see `bin/build.js`).
- Run `npm run build` after modifying assets in `resources/css/` or `resources/js/`.
