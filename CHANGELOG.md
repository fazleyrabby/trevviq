# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- **Interactive Maps**: Integrated MapLibre GL JS (via Alpine.js component) on Location pages and Traveller profiles.
- **Saves**: Added `saved_locations` and `saved_events` tables. Users can now save locations and events directly from their pages. Added a unified `/saved` dashboard.
- **Notifications**: Implemented database notifications for new followers and video likes. Added a `/notifications` view and unread badge in the navigation.
- **Legal Pages**: Created frontend views for Terms, Privacy, Acceptable Use, and DMCA, linked via the footer.
- **Analytics**: Configured Umami analytics script injection via `.env`.

### Security
- Removed leaked plaintext secrets from `/tmp`.
- Rotated `ADMIN_PASSWORD` on the production server.
