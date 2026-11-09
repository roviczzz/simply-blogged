# Proposal

## Why

The blog currently lacks visual personalization and author identity for account holders and readers. Administrators have no profile customization interface within the backoffice, and public blog posts display only plain-text author bylines without author avatars. Adding an account profile photo capability enables administrators to personalize their presence in both the Filament administration panel and the public reading experience.

## What Changes

- Add a profile management screen in the Filament administrative panel (`/admin/profile`) allowing authenticated administrators to view and update their profile details and profile photo.
- Add avatar upload support with preview, aspect ratio constraints, file validation (JPEG, PNG, WebP up to 5MB), and automatic conversion to WebP format via the existing `ConvertImageToWebp` action.
- Automatically prune old or replaced avatar image files from storage to prevent orphaned files.
- Implement Filament's `HasAvatar` contract on the `User` model to display custom avatars in the administrative topbar user menu and dashboard account widget, falling back to default initials when unassigned.
- Update public blog post pages to display the author's avatar photo beside the author byline when available, with a graceful text-only fallback.
- Update public blog header navigation to display the administrator avatar next to the admin link when authenticated.

## Capabilities

### New Capabilities
- `account-profile`: Covers administrative account profile customization, including uploading, converting, storing, displaying, and removing user profile photos.

### Modified Capabilities
- `public-blog`: Enhances post detail views and header navigation to render author and authenticated user avatars alongside textual attribution and navigation links.

## Impact

- Database: Migration adding nullable `avatar_path` column to the `users` table.
- Models: `App\Models\User` implements `Filament\Models\Contracts\HasAvatar`, adds `avatar_path` to fillable attributes, and provides an `avatarUrl` accessor.
- Filament Admin Panel: `AdminPanelProvider` configures `->profile(EditProfile::class)` using a custom `EditProfile` page that includes an avatar `FileUpload` component.
- Storage: Stores avatar files under `avatars/` on the configured `blog.media_disk` storage disk.
- Public Templates: `resources/views/posts/show.blade.php` and `resources/views/components/layout/app.blade.php` render avatar images when available.
