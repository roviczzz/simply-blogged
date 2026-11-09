# Tasks

## 1. Public Authentication Header & Logout Endpoint

- [x] 1.1 Create `App\Http\Controllers\LogoutController` and register the `POST /logout` route in `routes/web.php` with `auth` middleware, session invalidation, token regeneration, and redirect to `home`; verify with feature test `test_authenticated_user_can_log_out_and_redirects_to_home`.
- [x] 1.2 Update the public layout header in `resources/views/components/layout/app.blade.php` to conditionally render "Sign in" for guests, and "Admin" plus "Sign out" form for authenticated users; verify with feature tests confirming navigation links render correctly for guest and authenticated states in `tests/Feature/HomePageTest.php`.

## 2. Administration Login Return Navigation

- [x] 2.1 Create the login return link Blade view at `resources/views/filament/auth/back-to-home.blade.php` and register the render hook `PanelsRenderHook::AUTH_LOGIN_FORM_AFTER` in `app/Providers/Filament/AdminPanelProvider.php`; verify with feature test `test_admin_login_page_renders_return_to_home_link` in `tests/Feature/AdminPanelTest.php`.
- [x] 2.2 Configure `->homeUrl(fn () => route('home'))` on the Filament admin panel in `app/Providers/Filament/AdminPanelProvider.php`; verify with feature test `test_admin_login_page_renders_brand_logo_linking_to_home` in `tests/Feature/AdminPanelTest.php`.

## 3. Administration Dashboard Public Blog Navigation

- [x] 3.1 Register a "View Blog" navigation item in the topbar user menu via `->userMenuItems(...)` in `app/Providers/Filament/AdminPanelProvider.php`; verify with feature test `test_admin_dashboard_renders_view_blog_user_menu_item` in `tests/Feature/AdminPanelTest.php`.

## 4. Full Suite Verification & Formatting

- [x] 4.1 Run the full test suite (`php artisan test`) and verify all tests pass without regressions.
- [x] 4.2 Run Laravel Pint code formatter (`vendor/bin/pint --dirty --format agent`) and verify all PHP files adhere to project coding standards.
