# Design

## Context

See `proposal.md` for background and motivation. This is a greenfield implementation in an initialized Git repository with PHP 8.5, Composer 2.8, and Laravel Installer available locally.

## Goals / Non-Goals

**Goals:**
- Provide a clean, robust, and idiomatic Laravel blog application using SQLite.
- Isolate the Filament admin interface (`/admin`) from the public frontend (`/`).
- Render the public blog using pure Blade templates and Tailwind CSS without client-side SPA frameworks.
- Support GitHub Flavored Markdown authoring with client-side code syntax highlighting via Prism.js.
- Ensure zero external runtime dependencies for core blog functionality.
- Equip the repository with `laravel/boost` for MCP and AI agent alignment.

**Non-Goals:**
- Multi-author role-based access control (single administrator assumed for a personal blog).
- Client-side comment system (can be introduced in a future change if needed).
- Cloud object storage setup (local storage used via `public` disk).
- Dynamic server-side syntax rendering engines (e.g. Torchlight API keys or Node Shiki dependencies).

## Decisions

### 1. SQLite with WAL mode
- **Decision**: Use SQLite as the default database connection.
- **Rationale**: Zero configuration, no database server process to manage, portable, and fast enough for high-traffic blogs when running with Write-Ahead Logging (WAL).
- **Alternatives considered**: MySQL/PostgreSQL (added infrastructure operational complexity without tangible benefit for a personal blog).

### 2. Filament v5 for Backoffice (`/admin`)
- **Decision**: Install Filament Panel Builder for post, tag, and media administration.
- **Rationale**: Provides out-of-the-box authentication, rich forms, table filtering, relationship pickers, and a built-in Markdown editor without writing boilerplate CRUD controllers.
- **Alternatives considered**: Custom Blade admin dashboard (significant manual boilerplate) or Laravel Nova (commercial license required).

### 3. Pure Blade + Tailwind CSS for Public Site
- **Decision**: Build public views exclusively with server-rendered Blade components and Tailwind CSS.
- **Rationale**: Minimal bundle size, lightning-fast first contentful paint (FCP), optimal SEO, and simple maintainability.
- **Alternatives considered**: Livewire or Inertia (React/Vue) on public pages (unnecessary bundle weight and complexity for static reading experiences).

### 4. Built-in `Str::markdown()` for Content Rendering
- **Decision**: Use Laravel's native `Illuminate\Support\Str::markdown()` helper.
- **Rationale**: Built upon `league/commonmark` which is already bundled inside Laravel. Avoids extra third-party PHP packages while fully supporting CommonMark and GitHub Flavored Markdown tables and code fences.
- **Alternatives considered**: External markdown packages like `spatie/laravel-markdown` (adds extra dependency for features already supported by the framework).

### 5. Client-Side Prism.js for Syntax Highlighting
- **Decision**: Load Prism.js CSS and JavaScript conditionally on post reading pages.
- **Rationale**: Completely decouples code highlighting from the PHP backend, requires no external API keys or server Node binaries, and supports a wide range of programming languages and dark/light modes.
- **Alternatives considered**: Torchlight (requires external API calls and token configuration) or Shiki PHP (requires local Node/Deno environment).

### 6. Local Disk Image Storage with Filament FileUpload
- **Decision**: Store uploaded post covers in `storage/app/public` linked to `public/storage`.
- **Rationale**: Standard Laravel idiom (`php artisan storage:link`) with zero cloud credentials needed.

### 7. Laravel Boost for Development
- **Decision**: Install `laravel/boost` as a `--dev` dependency and run `php artisan boost:install`.
- **Rationale**: Configures MCP server and provides coding agent guidelines without affecting production dependencies or deployment overhead.

## Risks / Trade-offs

- **[Risk] High volume of posts could slow down uncached markdown parsing.**
  - → *Mitigation*: Cache the parsed HTML output of `Str::markdown($post->body)` or generate an HTML column on model save.
- **[Risk] Livewire assets from Filament leaking into public pages.**
  - → *Mitigation*: Filament routes and assets are scoped strictly under the `/admin` path; public Blade layouts do not load Filament/Livewire scripts.
- **[Risk] Broken image links if storage symlink is missing in deployment.**
  - → *Mitigation*: Ensure `php artisan storage:link` is part of setup instructions and database seeding.

## Migration Plan

1. Scaffold fresh Laravel application files into the repository root.
2. Configure `.env` with SQLite and app settings.
3. Run database migrations and generate storage symlink.
4. Install Filament panel and create admin user.
5. Install and configure Laravel Boost (`--dev`).
6. Implement public models, controllers, and Blade templates.
