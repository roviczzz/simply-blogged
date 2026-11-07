# Design

## Context

The application seeds initial data via `DatabaseSeeder`, which calls `AdminUserSeeder` followed by `BlogContentSeeder`. `AdminUserSeeder` reads credentials from `config/blog.php`, which falls back to `'password'` when `ADMIN_PASSWORD` is missing in the environment. See `proposal.md` for background and motivation.

## Goals / Non-Goals

**Goals:**
- Provide a fail-fast runtime guard in `AdminUserSeeder` preventing execution with default or empty passwords when `app()->isProduction()` is true.
- Conditionally skip sample blog posts and tag fixtures in `DatabaseSeeder` when running in production.
- Preserve zero-config local developer onboarding (`php artisan migrate:fresh --seed` works out-of-the-box).
- Ensure existing users are not overwritten if `AdminUserSeeder` is re-run.

**Non-Goals:**
- Implementing multi-role permissions or modifying `User::canAccessPanel()`.
- Modifying config fallbacks in `config/blog.php` (retaining local developer ergonomics).
- Introducing an interactive CLI wizard for user creation.

## Decisions

### 1. Guard within `AdminUserSeeder` rather than removing config defaults
* **Choice**: Check `app()->isProduction()` in `AdminUserSeeder::run()`. If `$password === 'password'` or empty, throw a `\RuntimeException` with an actionable error message.
* **Alternative Considered**: Remove default `'password'` from `config/blog.php`.
* **Rationale**: Removing the default forces every developer to create a `.env` with `ADMIN_PASSWORD` before running tests or local migrations. Checking inside the seeder maintains zero-config local DX while strictly gating production.

### 2. Environment conditional in `DatabaseSeeder` for fixture seeders
* **Choice**: Wrap `$this->call(BlogContentSeeder::class)` in `if (! app()->isProduction())` inside `DatabaseSeeder::run()`.
* **Alternative Considered**: Introduce separate seeder classes (`ProductionDatabaseSeeder` and `LocalDatabaseSeeder`).
* **Rationale**: Standard deployment pipelines and documentation rely on `php artisan db:seed`. Keeping a unified `DatabaseSeeder` with an environment condition ensures the right seeders execute without requiring custom CLI flags.

### 3. Idempotent Admin Creation
* **Choice**: Maintain `User::firstOrCreate(['email' => $email], [...])`.
* **Rationale**: Prevents accidental password resets on subsequent deploy or migration runs if the admin user changed their password via the UI or CLI.

## Risks / Trade-offs

- **[Risk] Production deployment fails during seeding if `ADMIN_PASSWORD` is unset**  
  → *Mitigation*: This is the intended behavior. The thrown `RuntimeException` provides an explicit message informing the operator to configure `ADMIN_PASSWORD` in their environment.

- **[Risk] Staging environments using `APP_ENV=staging` might not trigger production checks**  
  → *Mitigation*: The check specifically targets `app()->isProduction()`. Operators wanting strict enforcement in staging should set `ADMIN_PASSWORD` or set `APP_ENV=production`.
