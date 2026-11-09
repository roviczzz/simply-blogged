# Design

## Context

The application is built on Laravel 13 with Tailwind CSS v4 and Filament 5 for administrative management. The public blog uses server-rendered Blade templates (`resources/views/components/layout/app.blade.php`), while the backoffice resides at `/admin`. Currently, the public header has no awareness of authentication, and Filament's login page and panel do not provide direct navigation links back to the blog homepage.

See `proposal.md` for motivation and `specs/` for behavioral requirements.

## Goals / Non-Goals

**Goals:**
- Provide clear, accessible navigation between the public blog and the administration interface for the blog author.
- Render contextual links in the public header: "Sign in" for guests; "Admin" and "Sign out" for authenticated administrators.
- Provide direct return navigation to the public blog from the administration login page and the admin panel dashboard.
- Implement secure session termination on the public site that invalidates the session and returns the user to the homepage (`/`).
- Preserve existing login security controls (rate limiting, Timebox protection, CSRF validation, session regeneration).

**Non-Goals:**
- Public reader account registration or subscriber authentication.
- Password reset / email verification email flows.
- Multi-role permission schemes (all authenticated users remain blog administrators).

## Decisions

### 1. Leverage Filament's Existing Login with Render Hooks
- **Decision:** Use Filament's `/admin/login` page rather than creating a separate public `/login` controller. Inject a `"← Return to simply-blogged"` link using `PanelsRenderHook::AUTH_LOGIN_FORM_AFTER` and set `->homeUrl(fn () => route('home'))` on `AdminPanelProvider`.
- **Rationale:** Filament's login implementation already incorporates robust security primitives: `WithRateLimiting` (5 attempts/min lockout), Laravel `Timebox` for timing attack mitigation, and automatic `session()->regenerate()`. Render hooks allow clean extension without overriding or duplicating core authentication routines.
- **Alternatives Considered:** Building custom Blade authentication controllers and views. This would require re-implementing and maintaining rate limiters, credential validation, and session management for a single administrative user.

### 2. Dedicated Public `POST /logout` Route
- **Decision:** Introduce a dedicated `POST /logout` route mapped to `App\Http\Controllers\LogoutController` with `auth` middleware that logs the user out, calls `$request->session()->invalidate()`, `$request->session()->regenerateToken()`, and redirects to `route('home')`.
- **Rationale:** Filament's default `/admin/logout` redirects to the login screen (`/admin/login`). Creating a public logout endpoint cleanly decouples public-facing session termination (which should return to the blog homepage) from backoffice logout without overriding global container bindings.
- **Alternatives Considered:** Binding a custom `LogoutResponse` in the container. While possible, inspecting referrer URLs or request origins to decide between `/` and `/admin/login` introduces fragile branching. A dedicated route is explicit, standard, and easy to test.

### 3. Inline Header Navigation Design
- **Decision:** Add conditional `@guest` and `@auth` sections in `resources/views/components/layout/app.blade.php` matching the existing nav link styling (`text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white`).
- **Rationale:** Maintains the minimalist aesthetic of the blog layout while providing clear affordances for signing in, opening the admin panel, and signing out.
- **Alternatives Considered:** Dropdown menus or modal popups. These add unnecessary JavaScript dependencies and complexity for a single-author blog.

### 4. Admin Dashboard Return Link via User Menu
- **Decision:** Register a `"View Blog"` menu item in `AdminPanelProvider` via `->userMenuItems(...)` linking to `route('home')`.
- **Rationale:** Filament displays the user menu in the top-right header across all admin resources, giving the administrator an effortless way to preview the live blog.

## Risks / Trade-offs

- **[Risk] User remains logged in across browser sessions if they do not explicitly sign out**  
  → *Mitigation:* Standard session cookie configuration with session lifetime controls; users can sign out from either the public header or the admin dropdown.
- **[Risk] CSRF token expiration if the public page is left idle before clicking "Sign out"**  
  → *Mitigation:* Standard Laravel CSRF verification with exception handling and clean redirect fallback.
- **[Risk] Future non-admin users accessing `/admin`**  
  → *Mitigation:* Currently the application is scoped exclusively to the administrator (`AdminUserSeeder`). If reader accounts are introduced in the future, `User::canAccessPanel` must be updated with an explicit role/privilege check.
