# Spec Delta

## MODIFIED Requirements

### Requirement: Post Detail View with Markdown and Code Highlighting
The system SHALL render published posts by URL slug, converting Markdown content into HTML and providing client-side code syntax highlighting for code snippets.

#### Scenario: Reading a published post
- **WHEN** a visitor accesses `/posts/{slug}` for an existing published post
- **THEN** the system renders the article HTML with formatted Markdown and initializes syntax highlighting on all preformatted code blocks

#### Scenario: Reading a published post with missing or deleted author
- **WHEN** a visitor accesses `/posts/{slug}` for a published post whose author record does not exist
- **THEN** the system renders the article HTML successfully with HTTP status 200 without throwing server exceptions

#### Scenario: Accessing non-existent or unpublished post
- **WHEN** a visitor requests `/posts/{slug}` for a draft or nonexistent post
- **THEN** the system returns a 404 Not Found response
