# Tasks

## 1. Public Blog Branding & Favicon

- [x] 1.1 Update `resources/views/components/layout/app.blade.php` to include `<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">` in the `<head>` section and display the logo icon paired with the brand text `<a href="{{ route('home') }}" class="inline-flex items-center gap-3"><img src="{{ asset('simply-blogged.webp') }}" alt="" class="h-8 w-8 rounded-lg object-contain"><span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span></a>`, verifying rendered markup locally
- [x] 1.2 Update tests in `tests/Feature/HomePageTest.php` asserting the presence of the favicon link tag, the brand logo image, and the site title text inside the home link, and verify tests pass with `php artisan test --filter=HomePageTest`

## 2. Filament Admin Panel Branding & Favicon

- [x] 2.1 Configure `app/Providers/Filament/AdminPanelProvider.php` with `->brandLogo(asset('simply-blogged.webp'))`, `->brandLogoHeight('2.5rem')`, and `->favicon(asset('favicon.ico'))`, verifying the panel builder options are registered
- [x] 2.2 Add tests to `tests/Feature/AdminPanelTest.php` asserting that the admin panel renders the brand logo and favicon on the login and dashboard pages, and verify tests pass with `php artisan test --filter=AdminPanelTest`

## 3. Suite Verification & Code Formatting

- [x] 3.1 Run `php artisan test --compact` to verify the full test suite passes without regressions
- [x] 3.2 Run `vendor/bin/pint --dirty --format agent` to verify clean PHP code style formatting
