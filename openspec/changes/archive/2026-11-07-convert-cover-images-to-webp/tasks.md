# Tasks

## 1. Environment & Runtime Preparation

- [x] 1.1 Update PHP configuration in `C:\Users\rubikii\.config\php\8.5.12\php.ini` to set `upload_max_filesize = 32M`, `post_max_size = 32M`, and `output_buffering = 4096`, and verify with `php -r "echo ini_get('upload_max_filesize') . ' ' . ini_get('output_buffering');"`.

## 2. WebP Conversion Action

- [x] 2.1 Implement `App\Actions\ConvertImageToWebp` to load images via GD (`imagecreatefromstring`), normalize EXIF orientation (`exif_read_data`), preserve alpha channels for transparent formats, encode to WebP with quality 80 (`imagewebp`), and store to the public disk as `covers/<uuid>.webp`.
- [x] 2.2 Create `tests/Unit/ConvertImageToWebpTest.php` to test JPEG conversion, PNG alpha transparency preservation, WebP input handling, and verify with `php artisan test --compact --filter=ConvertImageToWebpTest`.

## 3. Filament PostForm Upload Integration

- [x] 3.1 Update `app/Filament/Resources/Posts/Schemas/PostForm.php` to add `->maxSize(10240)`, `->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])`, and `->saveUploadedFileUsing(...)` using `ConvertImageToWebp`.
- [x] 3.2 Update `tests/Feature/PostResourceTest.php` to assert that uploaded cover photos are persisted as `.webp` files with valid WebP headers, and verify with `php artisan test --compact --filter=PostResourceTest`.

## 4. Backfill Artisan Command

- [x] 4.1 Implement `app/Console/Commands/ConvertPostCoversToWebp.php` (`posts:convert-covers-to-webp`) supporting `--dry-run` to locate posts with non-WebP covers, convert them to WebP using `ConvertImageToWebp`, update database records, and remove previous source files.
- [x] 4.2 Create `tests/Feature/ConvertPostCoversToWebpCommandTest.php` covering conversion of existing records, database updates, old file deletion, and dry-run safety, and verify with `php artisan test --compact --filter=ConvertPostCoversToWebpCommandTest`.

## 5. Verification & Code Style

- [x] 5.1 Run the full test suite with `php artisan test --compact` and ensure all tests pass.
- [x] 5.2 Run `vendor/bin/pint --format agent` to ensure code formatting complies with project standards.

## 6. Output Buffering & Upload Error Resilience

- [x] 6.1 Add application-level output buffering (`ob_start()`) at the entrypoint of `public/index.php` to prevent premature header flushing and `headers already sent` crashes during Livewire 422 validation responses under PHP's development server.
- [x] 6.2 Add a feature test in `tests/Feature/PostResourceTest.php` asserting that upload validation failures return HTTP 422 JSON cleanly without uncaught header exceptions.
- [x] 6.3 Restart the development server (`php artisan serve`) to ensure updated `php.ini` runtime limits are loaded, and verify cover photo upload in the Filament admin panel.
