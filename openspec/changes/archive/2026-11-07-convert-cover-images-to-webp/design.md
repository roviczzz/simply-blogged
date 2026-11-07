# Design

## Context

The application uses Filament 5 on Laravel 13 running on PHP 8.5 with the `ext-gd` extension enabled. In the administrative post management schema (`PostForm.php`), cover image uploads are configured with `FileUpload::make('cover_image')` without explicit file size constraints or file transformations.

When users upload standard high-resolution camera or smartphone photos (often 3MB–10MB), uploads freeze at 100% because PHP's default runtime limits (`upload_max_filesize = 2M`, `post_max_size = 8M`) reject the payload. With `output_buffering = 0` and `display_errors = 1`, PHP flushes warning messages before Laravel sends headers, causing Symfony's `Response::sendHeaders()` to fail with an uncaught `ErrorException: Cannot modify header information - headers already sent`. In particular, PHP's CLI-server SAPI (`php -S` used by `php artisan serve`) overrides `php.ini` output buffering to 0. When Livewire's upload endpoint returns HTTP 422 on validation failure or dropped uploads, Symfony's `Response.php:328` tries to call `header('HTTP/1.1 422 ...')` while `headers_sent()` is true, causing a fatal 500 crash that freezes FilePond in the browser.

Furthermore, images that do pass are stored in their raw formats (JPEG/PNG). Converting all cover photos to WebP at quality 80 significantly optimizes page load speed, open graph assets, and storage utilization.

## Goals / Non-Goals

**Goals:**
- Eliminate the 100% upload freeze by enforcing explicit client-side and server-side size limits (10MB), adding application-level output buffering (`ob_start()`) in `public/index.php`, and setting appropriate PHP runtime limits.
- Automatically convert all newly uploaded cover photos to WebP format at quality 80 upon saving.
- Preserve alpha transparency for PNG and GIF images and normalize EXIF orientation for mobile camera photos.
- Store all converted covers with unique filenames under `covers/<uuid>.webp` on the public storage disk.
- Provide a safe Artisan command (`posts:convert-covers-to-webp`) to backfill existing non-WebP cover images with a `--dry-run` preview.
- Maintain zero external Composer dependencies by utilizing PHP 8.5's native `ext-gd`.

**Non-Goals:**
- Client-side WebP transcoding (server-side processing ensures deterministic output, security, and consistent quality).
- Generating multiple responsive `srcset` breakpoint sizes (out of scope; covers remain single optimized WebP assets).
- Queued asynchronous image transformation (single 10MB image conversion takes ~50–150ms in GD, well within synchronous HTTP lifecycle; avoiding asynchronous post-save race conditions).

## Decisions

### 1. Conversion Hook: Filament `saveUploadedFileUsing`
- **Choice**: Use `saveUploadedFileUsing` on the `cover_image` FileUpload component in `PostForm`, delegating to an invokable action `App\Actions\ConvertImageToWebp`.
- **Rationale**: This is Filament's idiomatic hook for custom file transformations. It executes only when a new file has actually been uploaded, before the record is persisted. If form validation fails on other fields, no orphaned converted files are created.
- **Alternatives Considered**:
  - *Eloquent Model Observer (`saving` / `created`)*: Rejected because Filament's default file handler already writes the original file to storage before model events fire, requiring redundant file I/O and manual deletion of the intermediate file.
  - *Page-level hooks (`mutateFormDataBeforeCreate`/`Save`)*: Rejected because it scatters field-specific behavior across `CreatePost` and `EditPost` pages instead of keeping it encapsulated in `PostForm`.

### 2. Image Processing Engine: Native PHP `ext-gd`
- **Choice**: Use PHP's built-in `ext-gd` functions (`imagecreatefromstring`, `imagesavealpha`, `imagerotate`, `imagewebp`).
- **Rationale**: `ext-gd` is already bundled and verified active on PHP 8.5. It supports WebP read/write natively, avoids introducing external package dependencies, and performs efficiently.
- **Note on PHP 8.5**: `imagedestroy()` is deprecated in PHP 8.5 since `GdImage` objects are automatically garbage collected. The action will omit manual `imagedestroy()` calls.
- **Alternatives Considered**:
  - *Intervention Image (`intervention/image`)*: Adds an external dependency for a task fully solvable with native PHP standard library functions.
  - *Spatie MediaLibrary*: Requires database schema migrations and refactoring existing Post model attributes.

### 3. File Naming and Storage Strategy
- **Choice**: Converted files are stored as `covers/<uuid>.webp` on the configured `public` disk.
- **Rationale**: Using `Str::uuid()` guarantees uniqueness, eliminates collision risks, prevents directory traversal attacks, and avoids browser caching conflicts when a post's cover photo is replaced.
- **Cleanup**: Livewire cleans up temporary upload files automatically. When a post cover is updated or backfilled via the command, superseded original files are deleted from the public disk.

### 4. Client-side & Server-side Size Guards
- **Choice**: Enforce `->maxSize(10240)` and `->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])` on the FileUpload component.
- **Rationale**: FilePond validates file size in the browser immediately upon selection, displaying clear error messages before any network bytes are uploaded.

### 5. Migration Command: `posts:convert-covers-to-webp`
- **Choice**: Artisan command that finds all `Post` records where `cover_image` does not end in `.webp`.
- **Behavior**:
  - Validates source file existence on `Storage::disk('public')`.
  - Runs `ConvertImageToWebp` to create the new `.webp` file.
  - Updates the post's `cover_image` path.
  - Deletes the previous non-WebP image file.
  - Includes `--dry-run` flag to list affected records without mutating disk or database.

### 6. Application-Level Output Buffering (`public/index.php`)
- **Choice**: Call `ob_start();` at the beginning of `public/index.php`.
- **Rationale**: PHP CLI-server (`php -S`) forces `output_buffering = 0` at the engine level regardless of `php.ini`. When Livewire returns HTTP 422 for upload validation errors or when PHP issues early upload warnings, unbuffered output immediately sends HTTP headers, causing Symfony's `Response.php:328` to crash with `Cannot modify header information - headers already sent`. Calling `ob_start()` at the application entrypoint ensures output is buffered across all environments, allowing Symfony to set 422 and 500 status codes cleanly without exception.

### 7. Development Server Process Restart
- **Choice**: Restart `php artisan serve` following `php.ini` modifications.
- **Rationale**: Running server processes hold their initial ini configuration in memory. Restarting ensures the updated `upload_max_filesize = 32M` and `post_max_size = 32M` limits take effect immediately.

## Risks / Trade-offs

- **[Risk] High-resolution image memory consumption during GD processing**
  → *Mitigation*: The 10MB upload constraint keeps raw uncompressed bitmap dimensions well within PHP's configured 512MB memory limit.
- **[Risk] Unhandled EXIF rotation causing images to appear sideways**
  → *Mitigation*: Inspect EXIF Orientation tag using `exif_read_data()` and apply `imagerotate()` before exporting to WebP.
- **[Risk] Broken image links if backfill command fails midway**
  → *Mitigation*: The conversion command verifies that the new WebP file exists on disk and is non-empty before updating the database column and deleting the source file.
- **[Risk] CLI server unbuffered output breaking 422 validation responses in development**
  → *Mitigation*: Application-level `ob_start()` in `public/index.php` guarantees headers are never flushed prematurely, allowing Livewire to report validation errors to FilePond without crashing.
