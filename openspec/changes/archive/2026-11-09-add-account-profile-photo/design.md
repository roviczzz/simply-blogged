# Design

## Context

The application is built on Laravel with Filament v5.11 for administrative operations and server-rendered Blade templates for the public blog. Currently, `App\Models\User` implements `FilamentUser` but does not implement `Filament\Models\Contracts\HasAvatar`. The database `users` table does not track profile photos, and the Filament panel does not have profile editing enabled (`AdminPanelProvider` lacks `->profile()`). The codebase already features a robust `App\Actions\ConvertImageToWebp` action that handles EXIF orientation correction and WebP conversion for media files.

See `proposal.md` for the motivation and business rationale.

## Goals / Non-Goals

**Goals:**
- Provide a clean administrative profile management page in Filament where an administrator can update their account credentials and upload/remove their avatar.
- Automatically optimize avatar images to WebP format stored under `avatars/` on the configured media storage disk (`config('blog.media_disk')`).
- Automatically clean up previous avatar files when an avatar is replaced or removed.
- Seamlessly integrate with Filament's `HasAvatar` contract to populate user menu avatars and the dashboard `AccountWidget`.
- Enhance the public post detail view and authenticated header navigation with author and administrator avatar images.

**Non-Goals:**
- Multi-user administrative role and permission management (retains single-admin focus).
- Third-party avatar synchronization (e.g., Gravatar, OAuth profile photo syncing).
- Client-side manual cropping UI (Filament's native avatar presentation and server-side WebP conversion provide sufficient square 1:1 framing).

## Decisions

### Decision 1: Relative Path Storage on the `users` Table
- **Choice**: Store a nullable `avatar_path` string column on `users` (e.g., `avatars/{uuid}.webp`).
- **Rationale**: Storing the relative path instead of a full URL ensures asset resolution respects dynamic domains, protocol changes, and storage disk drivers (local `public` vs S3).
- **Alternatives Considered**:
  - *Full URL*: Inflexible if the storage host or application URL changes.
  - *Separate `profiles` table*: Overkill for a lightweight blog with single-author/admin setup.

### Decision 2: Custom Filament `EditProfile` Page
- **Choice**: Create `App\Filament\Pages\Auth\EditProfile` extending Filament's `EditProfile` page and register it in `AdminPanelProvider` using `$panel->profile(EditProfile::class)`.
- **Rationale**: Extends Filament's built-in profile workflow with an avatar `FileUpload` component with `avatar()` styling, maintaining consistency with Filament's UI, security, and notifications.
- **Alternatives Considered**:
  - *Full `UserResource`*: Adds unnecessary listing/crud overhead for a single-admin application.
  - *Custom standalone route outside Filament*: Fragmented user experience that bypasses Filament theme and session state.

### Decision 3: Image Normalization via `ConvertImageToWebp`
- **Choice**: Pass uploaded avatars through `App\Actions\ConvertImageToWebp` on the media disk under the `avatars` directory.
- **Rationale**: Reuses tested application logic, normalizes camera/phone EXIF orientations, and converts images into compressed WebP format for fast web delivery.
- **Alternatives Considered**:
  - *Store raw user uploads directly*: Risk of orientation issues from mobile devices, unoptimized multi-megabyte payloads, and inconsistent formats.

### Decision 4: Automatic Stale Asset Cleanup
- **Choice**: Prune replaced or removed avatar files from the storage disk during the profile save lifecycle.
- **Rationale**: Prevents orphaned WebP files from accumulating in storage over time.

### Decision 5: Non-Destructive Public Presentation with Graceful Fallbacks
- **Choice**: Provide an `avatarUrl` accessor on `User`. In `posts/show.blade.php` and `layout/app.blade.php`, render avatar images only when `avatar_url` is non-null.
- **Rationale**: Guarantees backwards compatibility for existing posts, draft posts, and unconfigured avatars without rendering broken image icons or altering text layout.
- **Alternatives Considered**:
  - *Forcing placeholder SVG on public posts*: Clutters minimal typography if the administrator prefers a clean text byline.

## Risks / Trade-offs

- **[Risk] Missing or deleted disk file when `avatar_path` is present**  
  → *Mitigation*: The `avatarUrl` accessor safely formats the disk URL; Blade templates check for URL validity and render standard text fallback if image loading fails or is absent.
- **[Risk] S3/Cloud Storage visibility mismatch**  
  → *Mitigation*: FileUpload explicitly specifies `public` visibility on the configured disk (`config('blog.media_disk') ?: 'public'`).
- **[Risk] EXIF extraction failure on unusual image encodings**  
  → *Mitigation*: `ConvertImageToWebp` catches invalid image payloads and gracefully validates image input types (`jpeg`, `png`, `webp`).
