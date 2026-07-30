# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project follows [Semantic Versioning](https://semver.org/).

## [1.3.1] - 2026-07-29

### Fixed
- The export "Copy" button now falls back to the legacy copy method if the modern clipboard write is rejected, instead of failing silently.

## [1.3.0] - 2026-07-29

### Added
- Overlay option "None", which shows the background image with no tint. When selected, no overlay layer is composited at all.
- Separate end opacity for gradient overlays, so the overlay can fade to transparent instead of applying one opacity everywhere.
- Configurable destination for the login logo link. Leave it empty to use the site home.
- The plugin description is now translatable and ships translated in Spanish.

### Changed
- Settings tabs are now keyboard accessible and follow the ARIA tabs pattern (arrow keys, Home and End, correct roles).
- The export "Copy" button uses the modern clipboard API, with a fallback for admin screens served over plain HTTP.

## [1.2.1] - 2026-07-29

### Added
- A discreet support link ("Buy me a coffee") in the settings header.

### Changed
- The version is now read from the plugin header, so the header and the internal constant can no longer fall out of sync.

## [1.2.0] - 2026-07-28

### Added
- Background and layout redesign: form position (left, centre, right) now always applies; background type selector (solid, gradient, image); image overlay and blur; two column layout with a solid panel behind the form.
- Login box drop shadow and a colour for the selected field.
- Security section with an anti bot honeypot toggle.
- Tabbed settings panel with a branded header.
- The WordPress colour picker on colour fields, so hex values can be typed or pasted.

### Changed
- The username field on the login form is labelled "User".
- Default legal footer labels are now translatable.
- Structural CSS moved out of PHP into stylesheets driven by CSS variables; the settings page CSS and JavaScript moved to enqueued files.
- Files reorganised into `assets/` and `includes/`; the main file is now a small loader.

## [1.1.0] - 2026-07-14

### Added
- Export and import settings as JSON.
- Palette presets: Dark, Light, and Slate.
- Optional heading text and an extra CSS box.
- Solid or gradient background when no image is set.
- A Settings link on the plugins row.

### Changed
- Rewritten in English, with a Spanish (es_ES) translation.

## [1.0.0] - 2026-07-07

### Added
- Initial public release as AMW Simple Login.
- Two column image background with blur and a colour overlay.
- Settings page: logo from the media library, colour palette, options to hide the default login links, and legal links in the footer.
- Self update from GitHub.
