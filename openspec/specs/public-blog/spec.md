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
The system SHALL render published posts by URL slug, converting Markdown content into HTML, providing client-side code syntax highlighting for code snippets, and presenting author identity with avatar attribution when available.

#### Scenario: Reading a published post
- **WHEN** a visitor accesses `/posts/{slug}` for an existing published post
- **THEN** the system renders the article HTML with formatted Markdown and initializes syntax highlighting on all preformatted code blocks

#### Scenario: Reading a published post with author avatar
- **WHEN** a visitor accesses `/posts/{slug}` for a published post whose author has an uploaded avatar photo
- **THEN** the system renders the author's circular avatar image alongside the author name in the byline

#### Scenario: Reading a published post with author lacking avatar
- **WHEN** a visitor accesses `/posts/{slug}` for a published post whose author has no avatar photo
- **THEN** the system renders the author name as text without rendering an empty or broken image element

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

### Requirement: Site Brand Logo and Text Navigation
The system SHALL display the brand logo badge image alongside the textual site title in the public layout header as a unified link to the homepage.

#### Scenario: Visitor views header branding
- **WHEN** a visitor navigates to any public blog page
- **THEN** the header displays both the brand logo image and the site title linking to the home route

### Requirement: Public Website Favicon
The system SHALL include a standard favicon link tag pointing to the website icon in the HTML head of all public pages.

#### Scenario: Browser requests public page
- **WHEN** a client requests any public blog route
- **THEN** the rendered HTML contains a link tag referencing `favicon.ico` with relationship `icon`

### Requirement: Authentication-Aware Header Navigation and Session Logout
The public layout header SHALL render contextual authentication navigation reflecting the visitor's authentication state, displaying the administrator avatar when authenticated, and provide a secure session termination mechanism that returns to the homepage.

#### Scenario: Guest views header navigation
- **WHEN** an unauthenticated visitor views any public blog page
- **THEN** the header displays a "Sign in" link pointing to the administration login route alongside standard navigation links

#### Scenario: Authenticated administrator views header navigation
- **WHEN** an authenticated administrator views any public blog page
- **THEN** the header displays an "Admin" link pointing to the administration dashboard, presenting the administrator's avatar photo when available, and a "Sign out" form control

#### Scenario: Administrator signs out from public header
- **WHEN** an authenticated administrator submits the public "Sign out" form via POST
- **THEN** the system invalidates the current session, regenerates the CSRF token, and redirects the user to the public homepage as an unauthenticated visitor
