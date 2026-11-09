# Tasks

## 1. Configure Markdown Editor File Attachments

- [x] 1.1 Update `MarkdownEditor::make('body')` in `app/Filament/Resources/Posts/Schemas/PostForm.php` with `fileAttachmentsDisk`, `fileAttachmentsDirectory('attachments')`, `fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])`, and `fileAttachmentsMaxSize(10240)`.
- [x] 1.2 Attach `saveUploadedFileAttachmentUsing` to invoke `App\Actions\ConvertImageToWebp::execute($file, disk: $disk, directory: 'attachments')` and `getFileAttachmentUrlUsing` to return `Storage::disk($disk)->url($file)`.

## 2. Test Coverage & Verification

- [x] 2.1 Add feature tests in `tests/Feature/PostResourceTest.php` verifying that attaching an image in `MarkdownEditor` converts the file to WebP format, stores it under `attachments/` on the configured media disk, and generates the expected public storage URL.
- [x] 2.2 Add feature tests verifying that files exceeding 10MB or non-image types are rejected by the Markdown editor attachment validation.
- [x] 2.3 Run the post resource tests via `php artisan test --compact --filter=PostResourceTest` and verify all tests pass.
- [x] 2.4 Format code with `vendor/bin/pint --format agent` and run the entire test suite via `php artisan test --compact`.
