# Spec Delta

## ADDED Requirements

### Requirement: Post Body Image Attachments
The system SHALL allow authenticated administrators to upload image attachments within the Markdown post body editor, converting uploads to WebP format and persisting them to the configured media storage disk.

#### Scenario: Uploading an image attachment inside post body
- **WHEN** an administrator attaches an image file up to 10MB in JPEG, PNG, or WebP format within the Markdown post body editor
- **THEN** the system converts the image into WebP format at quality 80, stores the file on the configured media storage disk under the attachments directory, and resolves the public image URL from the configured media storage disk into the Markdown content

#### Scenario: Uploading a post body image exceeding size limit
- **WHEN** an administrator attempts to upload an image exceeding 10MB within the Markdown post body editor
- **THEN** the system rejects the file and displays an error message

#### Scenario: Resolving post body image URL via configured media disk
- **WHEN** a post body image attachment is uploaded with the media disk configured to Supabase
- **THEN** the inserted image URL references the Supabase public object storage URL rather than the local application host URL
