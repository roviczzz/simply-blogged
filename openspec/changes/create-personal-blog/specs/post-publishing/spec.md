# Spec Delta

## Purpose

Provides administrative capabilities to author, manage, categorize, schedule, and publish blog articles and tags through a secured administrative interface.

## ADDED Requirements

### Requirement: Post Creation and Editing
The system SHALL allow authenticated administrators to create and edit blog posts containing a title, URL slug, excerpt, Markdown body, cover image, and publishing status.

#### Scenario: Authoring a post with Markdown
- **WHEN** an administrator creates a post with a title and Markdown-formatted content
- **THEN** the system generates a unique URL slug and persists the draft post

#### Scenario: Uploading a cover image
- **WHEN** an administrator uploads a cover image file for a post
- **THEN** the system stores the file on the local public disk and associates the image path with the post

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
