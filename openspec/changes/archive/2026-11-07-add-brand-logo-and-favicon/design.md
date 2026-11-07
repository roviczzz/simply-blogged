# Design

## Context

See `proposal.md` for background and motivation. The application has static assets `public/simply-blogged.webp` (1024x1024 red square badge) and `public/favicon.ico` available, and requires pairing visual badge iconography with brand text on the public frontend alongside Filament backoffice branding.

## Goals / Non-Goals

**Goals:**
- Present a crisp, unified visual identity using `simply-blogged.webp` across both the public blog header and the Filament backoffice.
- Use `simply-blogged.webp` alongside `config('app.name')` in the public layout header, providing visual branding and immediate text clarity.
- Configure the Filament panel to display the logo image linking to the admin dashboard.
- Register `favicon.ico` in the public `<head>` and the Filament panel configuration.

**Non-Goals:**
- Dynamic user-uploaded branding or multi-tenant logos.
- Multiple favicon resolution sizes (e.g. apple-touch-icon, android manifest); single `favicon.ico` is sufficient for this scope.
- Header navigation changes beyond the brand link.

## Decisions

### 1. Logo Icon + Brand Text in Public Header
- **Decision**: Render `<a href="{{ route('home') }}" class="inline-flex items-center gap-3"><img src="{{ asset('simply-blogged.webp') }}" alt="" class="h-8 w-8 rounded-lg object-contain"><span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span></a>` in `resources/views/components/layout/app.blade.php`.
- **Rationale**: Pairing the red square logo badge (`h-8 w-8 rounded-lg`) with the clear textual brand title provides both distinct visual iconography and effortless readability across all screen sizes. Using decorative `alt=""` avoids screen readers announcing the title twice since the adjacent text element provides the accessible name.
- **Alternatives considered**: Standalone icon without text (reduces at-a-glance recognition of the blog title on small screens).

### 2. Native Filament Panel Branding
- **Decision**: Configure `$panel->brandLogo(fn () => asset('simply-blogged.webp'))->brandLogoHeight('2.5rem')` in `AdminPanelProvider.php`.
- **Rationale**: Filament's built-in `brandLogo` API natively handles replacing the text brand/dashboard link with the provided image URL, keeping navigation intact without custom Blade overrides.
- **Alternatives considered**: Custom Filament navigation view component (unnecessary complexity since native panel methods exist).

### 3. Favicon Registration via Layout and Panel APIs
- **Decision**: Add `<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">` to `resources/views/components/layout/app.blade.php` and configure `$panel->favicon(fn () => asset('favicon.ico'))` in `AdminPanelProvider.php`.
- **Rationale**: Ensures both public reader sessions and administrative sessions correctly display the favicon tab icon.
- **Alternatives considered**: Relying on default browser root path polling `/favicon.ico` (unreliable in some browsers and doesn't explicitly link the asset).

## Risks / Trade-offs

- **[Risk] Long site names could cause wrapping with the logo icon on very narrow mobile viewports.**
  - → *Mitigation*: The header uses `flex items-center justify-between gap-4` and `max-w-3xl`, leaving plenty of room on standard mobile screens (320px+) for an icon + two-word name.
