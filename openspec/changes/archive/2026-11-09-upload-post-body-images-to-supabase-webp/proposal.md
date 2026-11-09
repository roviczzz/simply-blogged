# Proposal

## Why

When administrators insert images into post content using the Markdown editor in production (such as on Render), images are saved to the local ephemeral container storage instead of the configured media storage disk (Supabase Storage). These uploads retain their original format rather than being converted to WebP, and generate local URLs (`https://simplyblogged.onrender.com/storage/...`) that 404 when Render restarts or redeploys containers.

Configuring the Markdown editor to process post body images through the application's WebP conversion pipeline and store them on the configured media storage disk ensures that images are properly optimized and persistently available via Supabase Storage public URLs.

## What Changes

- Configure `MarkdownEditor` in `PostForm` to use the configured media disk (`config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public'`) and directory (`attachments`).
- Integrate `ConvertImageToWebp` into `saveUploadedFileAttachmentUsing` for post body Markdown editor uploads, converting JPEG, PNG, and WebP attachments up to 10MB into WebP format at quality 80 with normalized EXIF orientation.
- Enforce file validation on post body attachments: 10MB maximum file size and accepted MIME types (`image/jpeg`, `image/png`, `image/webp`).
- Configure `getFileAttachmentUrlUsing` to resolve public URLs from the configured media disk, generating Supabase object storage URLs in production instead of local application host URLs.
- For existing posts containing broken local `/storage/` URLs, administrators can re-upload the images in the post editor to replace the missing files with persistent WebP URLs on Supabase Storage.

## Capabilities

### New Capabilities

<!-- None: durable behaviors remain under existing blog management capabilities -->

### Modified Capabilities

- `post-publishing`: Add requirements and scenarios for uploading image attachments within the Markdown post body, specifying WebP conversion, maximum file size, accepted MIME types, and public URL resolution from the configured media disk.

## Impact

- **Admin Panel**: Updates `app/Filament/Resources/Posts/Schemas/PostForm.php` to configure `MarkdownEditor` attachments with `ConvertImageToWebp` and media disk URL resolution.
- **Storage**: In production, body attachments are written to Supabase Storage S3 under `attachments/` rather than local ephemeral storage.
- **Frontend / Rendering**: Post body markdown references Supabase CDN public URLs for images, served reliably without impacting web container disk.
- **Tests**: Adds feature test coverage in `tests/Feature/PostResourceTest.php` for post body image attachment upload, WebP conversion, and media disk resolution.
