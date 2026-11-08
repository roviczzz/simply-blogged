# post-publishing Specification

## Purpose

Provides administrative capabilities to author, manage, categorize, schedule, and publish blog articles and tags through a secured administrative interface.

## Requirements

### Requirement: Post Creation and Editing
The system SHALL allow authenticated administrators to create and edit blog posts containing a title, URL slug, excerpt, Markdown body, cover image, and publishing status.

#### Scenario: Authoring a post with Markdown
- **WHEN** an administrator creates a post with a title and Markdown-formatted content
- **THEN** the system generates a unique URL slug and persists the draft post

#### Scenario: Uploading a cover image
- **WHEN** an administrator uploads a cover image file up to 10MB in JPEG, PNG, or WebP format for a post
- **THEN** the system converts the image into WebP format at quality 80, stores the file on the configured media storage disk under the covers directory, and associates the WebP path with the post

#### Scenario: Uploading a cover image exceeding size limit
- **WHEN** an administrator attempts to upload an image exceeding 10MB
- **THEN** the system rejects the file and returns a standard HTTP 422 validation error response without throwing uncaught headers-sent server exceptions

#### Scenario: Resolving cover image URL
- **WHEN** a post has an associated cover image path
- **THEN** the system resolves the public image URL from the configured media storage disk

### Requirement: Cover Image Optimization Command
The system SHALL provide an Artisan command to convert existing non-WebP cover images to WebP format and update post records.

#### Scenario: Converting existing cover images to WebP
- **WHEN** an administrator runs the cover image optimization command
- **THEN** the system converts all non-WebP cover images on the configured media storage disk to WebP format at quality 80, updates the post records with the new paths, and removes the old image files

#### Scenario: Dry-run preview of cover image conversion
- **WHEN** an administrator runs the cover image optimization command with the dry-run flag
- **THEN** the system reports the images that would be converted without modifying files or database records

### Requirement: Post Publishing and Scheduling
The system SHALL support draft, published, and scheduled states based on an explicit publishing flag and publication timestamp.

#### Scenario: Publishing a post immediately
- **WHEN** an administrator marks a post as published with a timestamp at or before current time
- **THEN** the system marks the post as live and visible to the public

#### Scenario: Scheduling a post for future publication
- **WHEN** an administrator sets a post publication timestamp in the future
- **THEN** the system keeps the post hidden from the public until the scheduled timestamp arrives

### Requirement: Post Tagging
The system SHALL allow administrators to create tags and associate multiple tags with any post.

#### Scenario: Assigning tags to a post
- **WHEN** an administrator selects one or more existing or newly created tags while editing a post
- **THEN** the system updates the post-tag relationships in the database

### Requirement: Admin Panel Brand Logo
The system SHALL display the brand logo image as the backoffice panel logo in the Filament administration interface linking to the administration dashboard.

#### Scenario: Administrator accesses admin interface
- **WHEN** an administrator views any page within the administration panel
- **THEN** the panel renders the brand logo image linking to the administration dashboard

### Requirement: Admin Panel Favicon
The system SHALL configure the administration panel to reference the website favicon icon.

#### Scenario: Administrator loads admin page
- **WHEN** a user navigates to the administration login or dashboard interface
- **THEN** the response includes a favicon link referencing the site's favicon icon
