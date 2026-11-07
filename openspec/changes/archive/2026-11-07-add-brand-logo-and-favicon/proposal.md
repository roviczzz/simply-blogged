# Proposal

## Why

The blog currently lacks visual brand identity, using plain text for the site header and leaving the browser favicon unset. Introducing the official square brand badge (`simply-blogged.webp`) paired with clear brand text, along with the website icon (`favicon.ico`), establishes a cohesive and polished identity across the public blog and the Filament administration panel.

## What Changes

- Render the graphic logo badge (`simply-blogged.webp`) alongside the textual brand title (`config('app.name')`) in the public blog header (`resources/views/components/layout/app.blade.php`), unified into a single link to the homepage.
- Configure the Filament admin panel (`AdminPanelProvider.php`) to display `simply-blogged.webp` as the panel brand logo linking to the admin dashboard.
- Link `public/favicon.ico` in the public site layout `<head>` tag.
- Register `public/favicon.ico` as the admin panel favicon in Filament.
- Add feature tests validating that the logo icon, brand text, and favicon are served and rendered across public and admin pages.

## Capabilities

### New Capabilities

*(None)*

### Modified Capabilities

- `public-blog`: Adds requirements for site brand logo icon and title text display in the header, and browser favicon inclusion.
- `post-publishing`: Adds requirements for administrative brand logo dashboard anchor and admin favicon configuration.

## Impact

- **Views**: `resources/views/components/layout/app.blade.php` header and `<head>` metadata.
- **Providers**: `app/Providers/Filament/AdminPanelProvider.php` brand logo and favicon panel configuration.
- **Static Assets**: References `public/simply-blogged.webp` and `public/favicon.ico`.
- **Tests**: `tests/Feature/HomePageTest.php` and `tests/Feature/AdminPanelTest.php`.
