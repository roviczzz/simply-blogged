# Spec Delta

## MODIFIED Requirements

### Requirement: Post Creation and Editing
The system SHALL allow authenticated administrators to create and edit blog posts containing a title, URL slug, excerpt, Markdown body, cover image, and publishing status.

#### Scenario: Authoring a post with Markdown
- **WHEN** an administrator creates a post with a title and Markdown-formatted content
- **THEN** the system generates a unique URL slug and persists the draft post

#### Scenario: Uploading a cover image
- **WHEN** an administrator uploads a cover image file up to 10MB in JPEG, PNG, or WebP format for a post
- **THEN** the system converts the image into WebP format at quality 80, stores the file on the local public disk under the covers directory, and associates the WebP path with the post

#### Scenario: Uploading a cover image exceeding size limit
- **WHEN** an administrator attempts to upload an image exceeding 10MB
- **THEN** the system rejects the file and returns a standard HTTP 422 validation error response without throwing uncaught headers-sent server exceptions

## ADDED Requirements

### Requirement: Cover Image Optimization Command
The system SHALL provide an Artisan command to convert existing non-WebP cover images to WebP format and update post records.

#### Scenario: Converting existing cover images to WebP
- **WHEN** an administrator runs the cover image optimization command
- **THEN** the system converts all non-WebP cover images to WebP format at quality 80, updates the post records with the new paths, and removes the old image files

#### Scenario: Dry-run preview of cover image conversion
- **WHEN** an administrator runs the cover image optimization command with the dry-run flag
- **THEN** the system reports the images that would be converted without modifying files or database records
