# Proposal

## Why

When administrators create or edit blog posts on production environments (such as Render) with cloud-backed storage (such as Supabase Storage), saving a post with a cover image fails with an internal server error. This occurs because the production Docker image lacks WebP compilation support in PHP's GD extension, Laravel's S3 Flysystem driver is not installed, and the application hardcodes the local `public` disk across post forms, image conversion actions, and image URL resolution.

Enabling configurable cover image storage with Supabase S3 support and fixing Docker's WebP dependencies allows administrators to reliably upload, optimize, and serve post cover images in both containerized production environments and local development.

## What Changes

- Install `league/flysystem-aws-s3-v3` to enable Laravel's S3 Flysystem driver for S3-compatible cloud storage providers like Supabase Storage.
- Update `Dockerfile` to install `libwebp` and `libwebp-dev`, and pass `--with-webp` to `docker-php-ext-configure gd` so WebP encoding works in production container runtimes.
- Add a configurable `supabase` filesystem disk in `config/filesystems.php` using path-style S3 endpoint compatibility, defaulting to the `media` bucket, and deriving endpoint and public object URLs from `SUPABASE_URL`.
- Add a dedicated blog configuration setting for the media storage disk (`config('blog.media_disk')`), defaulting to `'public'` for local development and testing while allowing `'supabase'` or custom disks via `MEDIA_DISK` (with backward-compatible support for `COVER_IMAGE_DISK` and `config('blog.cover_disk')`).
- Update `PostForm`, `ConvertImageToWebp`, `Post` model (`coverImageUrl`), `PostsTable`, and `ConvertPostCoversToWebp` command to use the configured media disk instead of hardcoding `'public'`.
- Update `.env.example` with streamlined Supabase S3 configuration templates.

## Capabilities

### New Capabilities
<!-- None: durable behaviors remain under existing blog management capabilities -->

### Modified Capabilities
- `post-publishing`: Update cover image upload storage and URL resolution requirements to use the configured cover image storage disk (supporting Supabase S3 and local public disks) rather than strictly requiring the local filesystem.

## Impact

- **Composer Dependencies**: Adds `league/flysystem-aws-s3-v3`.
- **Docker Image**: Updates PHP GD build configuration and packages in `Dockerfile`.
- **Filesystem Configuration**: Adds `supabase` disk to `config/filesystems.php` and adds `config/blog.php` configuration for `media_disk`.
- **Application Code**:
  - `app/Filament/Resources/Posts/Schemas/PostForm.php`
  - `app/Filament/Resources/Posts/Tables/PostsTable.php`
  - `app/Actions/ConvertImageToWebp.php`
  - `app/Models/Post.php`
  - `app/Console/Commands/ConvertPostCoversToWebp.php`
- **Environment**: Adds `MEDIA_DISK`, `SUPABASE_URL`, `SUPABASE_ACCESS_KEY_ID`, and `SUPABASE_SECRET_ACCESS_KEY` to `.env.example`.
- **Backward Compatibility**: Fully preserved. When `MEDIA_DISK` (or `COVER_IMAGE_DISK`) is unset or set to `public`, all local development workflows and automated tests behave identically to before.
