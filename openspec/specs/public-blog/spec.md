# public-blog Specification

## Purpose

Delivers the public reading experience for blog articles, tag collections, client-side code syntax highlighting, and RSS syndication using server-rendered templates.

## Requirements

### Requirement: Public Post Feed and Pagination
The system SHALL display a paginated list of published posts on the homepage, ordered by publication date descending, excluding draft or future scheduled posts.

#### Scenario: Visitor views homepage
- **WHEN** a visitor navigates to the blog homepage
- **THEN** the system displays recent published posts showing title, publication date, excerpt, tags, and reading time estimate

#### Scenario: Draft posts are hidden
- **WHEN** a visitor views the post list
- **THEN** the system excludes any posts marked as draft or with a future publication date

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

### Requirement: Tag Archive
The system SHALL allow visitors to view all published posts associated with a specific tag.

#### Scenario: Filtering posts by tag
- **WHEN** a visitor navigates to `/tags/{slug}`
- **THEN** the system displays only published posts associated with that tag

### Requirement: RSS Feed Syndication
The system SHALL generate a valid RSS 2.0 XML feed at `/feed` containing recent published posts.

#### Scenario: Fetching RSS feed
- **WHEN** an RSS reader requests `/feed`
- **THEN** the system returns an XML document containing the latest published posts with titles, permalinks, publication dates, and excerpts
