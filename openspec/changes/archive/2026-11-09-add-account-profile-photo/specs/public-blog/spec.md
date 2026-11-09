# Spec Delta

## MODIFIED Requirements

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
