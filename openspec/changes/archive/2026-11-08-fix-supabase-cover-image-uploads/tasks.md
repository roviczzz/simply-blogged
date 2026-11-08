# Tasks

## 1. Dependencies and Runtime Environment

- [x] 1.1 Require `league/flysystem-aws-s3-v3` in `composer.json` and verify `composer show league/flysystem-aws-s3-v3` resolves.
- [x] 1.2 Update `Dockerfile` to install `libwebp` in runtime packages, `libwebp-dev` in `.build-deps`, and add `--with-webp` to `docker-php-ext-configure gd`. Verify the file syntax.

## 2. Storage & Application Configuration

- [x] 2.1 Update `supabase` disk definition in `config/filesystems.php` using default bucket `'media'`, default region `'us-east-1'`, and derived S3 endpoint and public URL from `SUPABASE_URL`.
- [x] 2.2 Define `media_disk` in `config/blog.php` defaulting to `env('MEDIA_DISK', env('COVER_IMAGE_DISK', 'public'))` with `cover_disk` alias.
- [x] 2.3 Update `.env.example` with simplified environment variables (`MEDIA_DISK`, `SUPABASE_URL`, `SUPABASE_ACCESS_KEY_ID`, `SUPABASE_SECRET_ACCESS_KEY`).

## 3. Post Cover Image Storage Refactoring

- [x] 3.1 Update `App\Actions\ConvertImageToWebp` to use `config('blog.media_disk', config('blog.cover_disk', 'public'))` as the default storage disk.
- [x] 3.2 Update `App\Filament\Resources\Posts\Schemas\PostForm` to specify `config('blog.media_disk', config('blog.cover_disk', 'public'))` on `FileUpload::make('cover_image')` and pass the configured disk to the converter action.
- [x] 3.3 Update `App\Models\Post::coverImageUrl()` attribute to resolve the image URL via `Storage::disk(config('blog.media_disk', config('blog.cover_disk', 'public')))->url(...)`.
- [x] 3.4 Update `App\Filament\Resources\Posts\Tables\PostsTable` to configure `ImageColumn::make('cover_image')->disk(config('blog.media_disk', config('blog.cover_disk', 'public')))`.
- [x] 3.5 Update `App\Console\Commands\ConvertPostCoversToWebp` to retrieve and write files using `config('blog.media_disk', config('blog.cover_disk', 'public'))`.

## 4. Testing & Verification

- [x] 4.1 Update automated test coverage in `tests/Feature/PostResourceTest.php` to verify `blog.media_disk` (and `blog.cover_disk` fallback).
- [x] 4.2 Run the full test suite with `php artisan test` and verify all tests pass.
- [x] 4.3 Format modified PHP code with `vendor/bin/pint --format agent` and verify clean formatting.
