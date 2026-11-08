# Design

## Context

See `proposal.md` for background and motivation. The application is built on Laravel 13 and Filament 5, packaged in an Alpine-based multi-stage Docker image, and deployed to Render. The blog currently processes cover images through `App\Actions\ConvertImageToWebp` using PHP's GD extension, but hardcodes `Storage::disk('public')` across forms, models, and commands. In production, Supabase Storage provides S3-compatible object storage.

## Goals / Non-Goals

**Goals:**
- Enable WebP compilation in Docker's Alpine PHP 8.4 runtime so `imagewebp()` functions reliably in production containers.
- Add `league/flysystem-aws-s3-v3` to support S3-compatible storage providers (Supabase Storage).
- Add a dedicated `supabase` disk configuration in `config/filesystems.php`.
- Introduce a centralized `blog.media_disk` setting defaulting to `'public'`, decoupling media storage from a hardcoded disk string and supporting future media features (e.g. body images, avatars).
- Keep Livewire temporary uploads local to avoid browser CORS requirements.
- Ensure 100% test compatibility with existing test suites using local fake disks.

**Non-Goals:**
- Moving other files (such as local private documents or sqlite databases) to Supabase Storage.
- Direct-to-S3 client-side presigned uploads via Livewire (local staging + backend WebP conversion is preserved).
- Building custom Supabase REST API clients (standard Flysystem S3 adapter is used).

## Decisions

### Decision 1: Compile WebP into PHP GD in Dockerfile
- **Choice**: Add `libwebp` to runtime packages, `libwebp-dev` to `.build-deps`, and configure GD with `--with-freetype --with-jpeg --with-webp`.
- **Rationale**: Alpine Linux does not include WebP support in GD unless `--with-webp` and development headers are explicitly supplied at compilation time.
- **Alternatives Considered**: Using the Imagick extension (heavier build dependencies) or pure PHP WebP encoders (slow and high memory usage).

### Decision 2: Configure Supabase Storage via Flysystem S3 with Derived URLs
- **Choice**: Configure a `supabase` disk in `config/filesystems.php` using the native `s3` driver, `use_path_style_endpoint => true`, defaulting to bucket `'media'` and region `'us-east-1'`. Automatically derive S3 endpoint (`{SUPABASE_URL}/storage/v1/s3`) and public asset URL (`{SUPABASE_URL}/storage/v1/object/public/{bucket}`) from a single base `SUPABASE_URL`.
- **Rationale**: Supabase Storage exposes an S3-compliant API. Utilizing Laravel's standard `s3` Flysystem driver allows the app to use familiar `Storage::disk(...)` methods. Deriving endpoint and asset URLs avoids forcing users to manually copy `<project-ref>` twice and keeps the bucket name in a single place.
- **Alternatives Considered**: Requiring explicit full URLs for both `SUPABASE_ENDPOINT` and `SUPABASE_URL` (redundant and error-prone), or using Supabase REST API via HTTP client (would bypass Laravel's Storage abstraction, breaking file upload and testing paradigms).

### Decision 3: Decouple Media Disk via `blog.media_disk` Configuration
- **Choice**: Introduce `config('blog.media_disk', env('MEDIA_DISK', env('COVER_IMAGE_DISK', 'public')))` in `config/blog.php`, maintaining `config('blog.cover_disk')` as an alias for backward compatibility.
- **Rationale**: Hardcoding `'public'` blocked cloud storage integration, but changing `FILESYSTEM_DISK` globally could interfere with session/cache files or other features. A scoped `media_disk` configuration allows production on Render to set `MEDIA_DISK=supabase` while local development and automated PHPUnit tests continue defaulting to `'public'`. Using `MEDIA_DISK` (rather than `COVER_IMAGE_DISK`) future-proofs the setting for upcoming media uploads (post body images, author avatars, attachments).
- **Alternatives Considered**: Re-defining the `'public'` disk definition itself in `config/filesystems.php` to conditionally use S3 (confusing semantics and breaks standard expectations of the local public directory).

### Decision 4: Preserve Local Staging for Livewire FileUpload
- **Choice**: Keep Livewire temporary file uploads staged locally on the application server prior to form submission.
- **Rationale**: Livewire handles temporary file uploads via `POST /livewire/upload-file`. By staging locally, the browser uploads to the Laravel app over standard HTTPS. When the administrator clicks "Save", `ConvertImageToWebp` reads the local temp file, generates the WebP binary, and streams it to the target disk (`MEDIA_DISK`). This completely eliminates browser CORS issues with Supabase Storage.
- **Alternatives Considered**: Setting Livewire temp disk to S3/Supabase (requires configuring CORS on the Supabase bucket and exposes unfinished drafts directly to object storage).

## Risks / Trade-offs

- **[Risk] Supabase rejects AWS S3 Object ACL headers (public-read)**  
  → **Mitigation**: Do not configure `'visibility' => 'public'` or send ACL headers on the Supabase S3 disk; Supabase manages public bucket visibility at the bucket level.
- **[Risk] Supabase public URL formatting mismatch**  
  → **Mitigation**: Standard S3 URLs use `<endpoint>/<bucket>/<path>`. Supabase serves public assets via `<project-ref>.supabase.co/storage/v1/object/public/<bucket>/<path>`. Providing an explicit `'url'` entry in the disk configuration ensures `$post->cover_image_url` resolves the exact public URL.
- **[Risk] Unconfigured Supabase credentials on deployment**  
  → **Mitigation**: Provide detailed variable templates in `.env.example` and keep default fallback to `'public'`.
