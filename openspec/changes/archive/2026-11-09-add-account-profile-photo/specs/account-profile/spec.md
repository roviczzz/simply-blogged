# Spec Delta

## Purpose

Enables authenticated administrators to manage their personal account profile details and avatar photo within the administration backoffice, providing image optimization and consistent avatar presentation across administrative interfaces.

## ADDED Requirements

### Requirement: Administrative Account Profile Photo Management
The administration backoffice SHALL provide an authenticated profile management page allowing the logged-in administrator to upload, update, and remove their account profile photo alongside name and email credentials.

#### Scenario: Administrator accesses profile management page
- **WHEN** an authenticated administrator navigates to `/admin/profile`
- **THEN** the interface displays the profile form including an avatar upload field, name, and email

#### Scenario: Administrator uploads a valid profile photo
- **WHEN** an administrator submits an image in JPEG, PNG, or WebP format up to 5MB
- **THEN** the system persists the avatar record, updates the user's avatar path, and displays the new photo preview

#### Scenario: Administrator removes existing profile photo
- **WHEN** an administrator chooses to remove their current profile photo and saves changes
- **THEN** the system clears the user's avatar path and deletes the previous avatar file from storage

### Requirement: Avatar Storage and Format Optimization
The system SHALL normalize, optimize, and store uploaded user avatars as WebP image files on the configured media storage disk.

#### Scenario: Converting uploaded image to WebP format
- **WHEN** an avatar image is processed during profile update
- **THEN** the system normalizes EXIF orientation, encodes the image as WebP format, and stores it in the `avatars/` directory with a unique filename

#### Scenario: Cleaning up superseded image files
- **WHEN** an administrator replaces their existing avatar with a new image
- **THEN** the system removes the superseded avatar file from the storage disk

### Requirement: Administration Panel Avatar Presentation
The administration backoffice SHALL display the authenticated administrator's avatar in the topbar user menu and dashboard account widget, falling back to initials when no photo is set.

#### Scenario: Topbar user menu renders custom avatar
- **WHEN** an authenticated administrator with an uploaded avatar accesses any administration panel page
- **THEN** the topbar user menu button displays the user's custom avatar image

#### Scenario: Topbar user menu falls back to initials
- **WHEN** an authenticated administrator without an avatar photo accesses any administration panel page
- **THEN** the topbar user menu button renders the default initials badge

#### Scenario: Dashboard account widget renders user avatar
- **WHEN** an authenticated administrator views the administration dashboard
- **THEN** the dashboard account widget renders the user's avatar image or default initials badge beside their name
