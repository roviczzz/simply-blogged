# Spec Delta

## Purpose

Provides bidirectional navigation mechanisms connecting the administrative authentication interface and backoffice dashboard back to the public blog homepage.

## ADDED Requirements

### Requirement: Administration Login Return Navigation
The administration login interface SHALL provide visible, accessible navigation controls directing users back to the public blog homepage.

#### Scenario: Returning to homepage from login page
- **WHEN** a user navigates to the administration login interface at `/admin/login`
- **THEN** the interface displays an explicit return link to the blog homepage below the login form and renders the brand logo linking to the homepage

### Requirement: Administration Dashboard Public Site Navigation
The administration backoffice dashboard SHALL provide a direct navigation action enabling authenticated administrators to open the public blog homepage.

#### Scenario: Navigating to public site from admin panel
- **WHEN** an authenticated administrator accesses any administration panel page
- **THEN** the panel user menu includes a "View Blog" navigation item that links directly to the public homepage route
