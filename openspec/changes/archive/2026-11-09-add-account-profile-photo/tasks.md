# Tasks

## 1. Database Schema and User Model Integration

- [x] 1.1 Create migration adding nullable `avatar_path` string column to `users` table and verify migration executes and rolls back cleanly with `php artisan migrate`.
- [x] 1.2 Update `App\Models\User` to include `avatar_path` in fillable attributes, implement `Filament\Models\Contracts\HasAvatar`, add `getFilamentAvatarUrl()` method, and define `avatarUrl` accessor attribute.
- [x] 1.3 Update `Database\Factories\UserFactory` with an optional `withAvatar` state and write unit tests in `tests/Unit/UserAvatarTest.php` verifying avatar URL generation and null fallback.

## 2. Filament Administration Profile Management

- [x] 2.1 Create custom `App\Filament\Pages\Auth\EditProfile` page extending Filament's `EditProfile` page, containing an avatar `FileUpload` component with 5MB limit, image types restriction (`jpeg`, `png`, `webp`), and integration with `ConvertImageToWebp`.
- [x] 2.2 Register `EditProfile::class` in `App\Providers\Filament\AdminPanelProvider` via `->profile(EditProfile::class)` and verify the profile route `/admin/profile` is registered.
- [x] 2.3 Implement previous avatar cleanup logic to prune superseded or removed avatar image files from the media disk on save.
- [x] 2.4 Write feature tests in `tests/Feature/AdminProfilePhotoTest.php` verifying avatar upload, WebP storage in `avatars/`, deletion/replacement file pruning, and topbar avatar rendering in the panel.

## 3. Public Blog Template Enhancements

- [x] 3.1 Update `resources/views/posts/show.blade.php` to render the author's avatar image next to the author byline when `avatar_url` is present, maintaining clean text-only fallback when absent.
- [x] 3.2 Update `resources/views/components/layout/app.blade.php` to display the administrator avatar beside the "Admin" link in header navigation when authenticated.
- [x] 3.3 Add feature tests in `tests/Feature/PostPageTest.php` and `tests/Feature/PublicNavigationTest.php` verifying that post detail pages and public headers display author avatars when present and maintain clean rendering when null or anonymous.

## 4. Quality and Integration Verification

- [x] 4.1 Run the full test suite with `php artisan test` and verify all unit and feature tests pass.
- [x] 4.2 Run `vendor/bin/pint --format agent` to ensure code formatting adheres to project conventions.
