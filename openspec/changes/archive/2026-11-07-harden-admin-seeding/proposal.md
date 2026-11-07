# Proposal

## Why

The application currently defaults admin credentials to a well-known email and password (`admin@example.com` / `password`) in configuration and database seeders. If database seeding is invoked in a production or staging deployment without explicit environment variables, this creates an immediate unauthorized access risk. Furthermore, demo blog content and sample tags are executed alongside the admin seeder, risking polluting production databases with mock content.

## What Changes

- Guard `AdminUserSeeder` against running with the default password (`password`) or an empty password in production environments (`APP_ENV=production`), halting execution immediately with an informative exception.
- Ensure production admin provisioning relies on explicit environment configuration (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`) provided via `.env`.
- Guard `DatabaseSeeder` so mock content (`BlogContentSeeder`) only executes in non-production environments (e.g. `local` or `testing`), keeping production databases clean.
- Add automated feature test coverage to verify that production seeding throws when using default credentials and that demo fixtures are excluded in production.

## Capabilities

### New Capabilities
- `admin-provisioning`: Provides safe, environment-aware administrative user provisioning and database seeding rules across local, testing, and production environments.

### Modified Capabilities
<!-- None -->

## Impact

- **Affected Code**: `database/seeders/AdminUserSeeder.php`, `database/seeders/DatabaseSeeder.php`.
- **Configuration & Environment**: Production deployments must provide a non-default `ADMIN_PASSWORD` if running `php artisan db:seed`.
- **Breaking Changes**: None for local development. In production, running `db:seed` without configuring `ADMIN_PASSWORD` will intentionally fail.
- **Tests**: New feature tests verifying seeder guardrails across environment configurations.
