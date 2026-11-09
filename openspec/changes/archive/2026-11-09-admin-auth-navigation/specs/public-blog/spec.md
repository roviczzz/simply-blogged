# Spec Delta

## ADDED Requirements

### Requirement: Authentication-Aware Header Navigation and Session Logout
The public layout header SHALL render contextual authentication navigation reflecting the visitor's authentication state and provide a secure session termination mechanism that returns to the homepage.

#### Scenario: Guest views header navigation
- **WHEN** an unauthenticated visitor views any public blog page
- **THEN** the header displays a "Sign in" link pointing to the administration login route alongside standard navigation links

#### Scenario: Authenticated administrator views header navigation
- **WHEN** an authenticated administrator views any public blog page
- **THEN** the header displays an "Admin" link pointing to the administration dashboard and a "Sign out" form control

#### Scenario: Administrator signs out from public header
- **WHEN** an authenticated administrator submits the public "Sign out" form via POST
- **THEN** the system invalidates the current session, regenerates the CSRF token, and redirects the user to the public homepage as an unauthenticated visitor
