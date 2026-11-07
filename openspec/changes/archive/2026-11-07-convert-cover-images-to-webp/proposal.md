# Proposal

## Why

Uploading blog post cover photos in the admin panel freezes at 100% because default PHP runtime upload limits (2MB `upload_max_filesize` and 8MB `post_max_size`) reject common photo file sizes, and unbuffered PHP output causes fatal header-sent errors when returning validation failures. In particular, PHP's built-in development server (`php -S` used by `php artisan serve`) overrides `php.ini` output buffering to 0 at the SAPI level, causing premature output flushing and fatal `ErrorException: Cannot modify header information - headers already sent` in Symfony's `Response.php:328` when returning HTTP 422 validation responses. Furthermore, uploaded images are currently stored as unoptimized original formats (JPEG/PNG) rather than modern, lightweight WebP formats. Converting cover images to WebP reduces file size by up to 70%, accelerates page load times for readers, and saves storage.

## What Changes

- Increase server and upload limits in PHP configuration (`upload_max_filesize = 32M`, `post_max_size = 32M`) and restart the development server process.
- Add application-level output buffering (`ob_start()`) in `public/index.php` to guarantee response buffering across all SAPIs, preventing premature header flushing on 422 upload validation failures.
- Add a 10MB client-side and server-side file size constraint and accepted image MIME types on cover photo uploads in Filament.
- Convert newly uploaded cover photos to WebP format (quality 80) automatically upon saving posts in Filament, while preserving transparency for PNG/GIF assets and normalizing EXIF orientation.
- Store converted images under `covers/<uuid>.webp` on the configured public disk.
- Provide an Artisan command (`posts:convert-covers-to-webp`) to backfill and convert existing non-WebP cover photos, update post records, and clean up superseded original files with optional `--dry-run` safety.

## Capabilities

### New Capabilities
<!-- No new capabilities; all changes fit under existing post-publishing capability. -->

### Modified Capabilities
- `post-publishing`: Updates the cover image upload requirement to enforce a 10MB limit and require conversion to WebP format at quality 80, ensures graceful 422 validation error handling without uncaught header exceptions, and adds a requirement for an artisan command to backfill existing non-WebP cover images.

## Impact

- **Application Entrypoint**: `public/index.php` initializes application-level output buffering (`ob_start()`).
- **Filament Admin Panel**: `app/Filament/Resources/Posts/Schemas/PostForm.php` gains `maxSize(10240)`, accepted file types, and `saveUploadedFileUsing` integration.
- **Image Processing Action**: Introduces `app/Actions/ConvertImageToWebp.php` leveraging PHP GD (already installed in PHP 8.5).
- **Artisan Console**: Introduces `app/Console/Commands/ConvertPostCoversToWebp.php`.
- **PHP Configuration & Server Runtime**: Configuration updates for `upload_max_filesize = 32M`, `post_max_size = 32M`, and restarting the active `artisan serve` process.
- **Tests**: Adds unit tests for the converter action, feature tests for post cover uploads, upload validation error responses, and the CLI command.
- **Dependencies**: No external Composer packages required; uses native PHP GD.
