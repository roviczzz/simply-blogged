# Spec Delta

## ADDED Requirements

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
