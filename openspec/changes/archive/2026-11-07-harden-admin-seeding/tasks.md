# Tasks

## 1. Production Admin Seeder Guard

- [x] 1.1 Update `database/seeders/AdminUserSeeder.php` to validate `config('blog.admin.password')` in production (`app()->isProduction()`), throwing a `\RuntimeException` if the password is default or empty, and verify via seeder execution.
- [x] 1.2 Create `tests/Feature/AdminUserSeederTest.php` covering local/testing default execution, production default password failure exception, production custom password success, and idempotency, verifying with `vendor/bin/phpunit --filter=AdminUserSeederTest`.

## 2. Production Database Seeder Fixture Isolation

- [x] 2.1 Update `database/seeders/DatabaseSeeder.php` to conditionally run `BlogContentSeeder` only in non-production environments (`! app()->isProduction()`), and verify by running the seeder.
- [x] 2.2 Create `tests/Feature/DatabaseSeederTest.php` asserting that running `DatabaseSeeder` in production mode provisions the admin user while leaving post and tag tables free of mock data, verifying with `vendor/bin/phpunit --filter=DatabaseSeederTest`.

## 3. Quality Assurance and Formatting

- [x] 3.1 Run Laravel Pint (`vendor/bin/pint --dirty --format agent`) on modified files and verify no formatting violations remain.
- [x] 3.2 Run the full test suite (`vendor/bin/phpunit`) and verify all existing and new tests pass.

## Workflow follow-up

- Archive the change after implementation and verification are complete.
