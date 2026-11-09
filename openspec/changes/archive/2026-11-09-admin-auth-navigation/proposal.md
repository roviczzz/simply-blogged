# Proposal

## Why

The blog administrator and visitors currently have no visual navigation between the public blog and the administration panel. Logging in requires manually entering `/admin/login`, the login page lacks a link to return to the blog, and there is no way to navigate back to the blog from the dashboard or sign out from the public header. Introducing contextual navigation and secure session termination links resolves this friction while upholding login security best practices.

## What Changes

- Update the public layout header to render contextual authentication controls:
  - Guests see a "Sign in" link pointing to the Filament administration login page.
  - Authenticated administrators see an "Admin" link pointing to the admin dashboard and a "Sign out" action.
- Add a dedicated, secure public `POST /logout` route that terminates the session, invalidates session storage, regenerates the CSRF token, and redirects back to the public homepage.
- Configure Filament's administration login page with an explicit return link ("← Return to simply-blogged") and ensure the panel brand logo links to the public homepage.
- Configure Filament's administration panel user menu with a "View Blog" navigation action linking to the public homepage.
- Maintain existing login security patterns: rate limiting (5 attempts/min), timing-attack resistance via Timebox, CSRF protection, and session fixation prevention on login.

## Capabilities

### New Capabilities
- `admin-navigation`: Provides navigation links from the administration login page and admin backoffice back to the public blog homepage.

### Modified Capabilities
- `public-blog`: Adds authentication-aware header navigation for guests and authenticated administrators, along with secure public logout and homepage redirection.

## Impact

- Public layout component (`resources/views/components/layout/app.blade.php`)
- Web routes (`routes/web.php`)
- New public logout controller (`app/Http/Controllers/LogoutController.php`)
- Filament admin panel provider (`app/Providers/Filament/AdminPanelProvider.php`)
- Blade view for login page return link render hook (`resources/views/filament/auth/back-to-home.blade.php`)
- Feature tests (`tests/Feature/HomePageTest.php`, `tests/Feature/AdminPanelTest.php`, and a new feature test for auth navigation)
