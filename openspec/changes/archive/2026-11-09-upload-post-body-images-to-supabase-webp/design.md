# Design

## Context

See `proposal.md` for background and motivation.

The application runs on Laravel 13 and Filament 5.11, deployed inside an Alpine-based Docker container on Render with cloud storage provided by Supabase Storage (S3-compatible API). 

The application already has:
- `config/filesystems.php`: Contains the `supabase` disk configuration using path-style S3 endpoint compatibility and public object URLs.
- `config/blog.php`: Defines `media_disk` (derived from `MEDIA_DISK`), which evaluates to `supabase` on production and `public` in local/testing environments.
- `App\Actions\ConvertImageToWebp`: Decodes uploaded images, corrects EXIF orientation, preserves alpha transparency, encodes to WebP at quality 80, and saves to a given disk and directory.

Currently, `MarkdownEditor::make('body')` in `app/Filament/Resources/Posts/Schemas/PostForm.php` leaves file attachment settings unconfigured. As a result, Filament uses the default `public` disk, writes raw JPEGs/PNGs into Render's ephemeral local filesystem, and inserts local `https://simplyblogged.onrender.com/storage/...` URLs that 404 once the container redeploys or restarts.

## Goals / Non-Goals

**Goals:**
- Configure `MarkdownEditor` in `PostForm` to store attachments on the configured media disk (`config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public'`).
- Process all uploaded body image attachments through `ConvertImageToWebp`, storing them as WebP files under `attachments/`.
- Ensure inserted markdown links use public URLs from the configured media disk (Supabase Storage in production).
- Enforce file constraints: max 10MB file size, accepted MIME types (`image/jpeg`, `image/png`, `image/webp`).
- Provide unit and feature test coverage for the post body attachment upload flow.

**Non-Goals:**
- Automated database migration or regex rewrite of existing posts' bodies: Because Render wiped previous ephemeral files, author re-uploading via the admin panel (Option A) is adopted.
- Custom media library / asset tracking database table: Filament's native Markdown editor attachment hooks handle storage and URL insertion directly without introducing additional database models.

## Decisions

### Decision 1: Use Filament's `saveUploadedFileAttachmentUsing` and `getFileAttachmentUrlUsing` hooks
- **Choice**: Configure `saveUploadedFileAttachmentUsing` and `getFileAttachmentUrlUsing` directly on `MarkdownEditor::make('body')` in `PostForm.php`.
- **Rationale**: Filament's `HasFileAttachments` trait exposes these hooks specifically for customizing attachment handling. Intercepting the upload before the URL is placed into the editor ensures that only WebP files and persistent Supabase URLs enter the Markdown content.
- **Alternatives considered**:
  - *Post-save Eloquent model observer/hook*: Parsing markdown with regex after post save to download and replace images. This is brittle, introduces latency on post save, and fails if external files cannot be downloaded.
  - *Custom Livewire file upload component*: Unnecessary overhead since Filament's built-in MarkdownEditor already supports custom attachment callbacks.

### Decision 2: Reuse `App\Actions\ConvertImageToWebp` with directory `attachments`
- **Choice**: Call `ConvertImageToWebp::execute($file, disk: $disk, directory: 'attachments')` inside `saveUploadedFileAttachmentUsing`.
- **Rationale**: `ConvertImageToWebp` is battle-tested, handles EXIF orientation normalization, transparent PNGs, and WebP encoding at quality 80, and saves directly to the target disk using a UUID filename (`attachments/<uuid>.webp`).
- **Alternatives considered**:
  - *Storing original image formats (JPEG/PNG)*: Results in larger payloads and inconsistent image handling compared to post covers.

### Decision 3: Explicitly define `getFileAttachmentUrlUsing`
- **Choice**: Return `Storage::disk($disk)->url($file)` inside `getFileAttachmentUrlUsing`.
- **Rationale**: Filament's default URL resolution performs an `exists()` check against the storage disk before calling `url()`. On cloud S3/Supabase storage, this introduces a blocking remote HTTP HEAD request. Explicitly resolving the URL eliminates this latency and guarantees the URL matches the media disk configuration.
- **Alternatives considered**:
  - *Relying only on `fileAttachmentsDisk(...)`*: Functional, but adds unnecessary S3 HEAD requests on every uploaded attachment.

### Decision 4: Segregate body attachments into `attachments/`
- **Choice**: Use `attachments/` as the storage directory for body images, separate from `covers/`.
- **Rationale**: Clear separation between primary post cover art and inline rich content attachments prevents clutter in the root bucket and simplifies storage inspection.

## Risks / Trade-offs

- **[Risk] Author uploads large image exceeding size limit** &rarr; **Mitigation**: `fileAttachmentsMaxSize(10240)` and `fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])` enforce client and server validation before conversion.
- **[Risk] Missing Supabase credentials in local development or tests** &rarr; **Mitigation**: Fallback to `config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public'` ensures local dev and tests use the local `public` or faked storage disk seamlessly.
- **[Risk] Existing posts have 404 links to wiped Render files** &rarr; **Mitigation**: Author re-uploads images in affected posts in the admin panel; the newly uploaded files will immediately go to Supabase as WebP.

## Migration Plan

1. Deploy code updates to Render.
2. No database migrations or schema alterations required.
3. For existing posts with broken images, administrators edit the post in the Filament panel, re-upload the missing images in the editor, and save.
