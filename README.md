# AMW Simple Login

Customises the WordPress login screen from a single settings page: backgrounds, colours, logo, layout, and a few sensible extras, with no external assets and no bloat.

![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)
![Requires PHP](https://img.shields.io/badge/PHP-7.0%2B-777bb4)
![Requires WordPress](https://img.shields.io/badge/WordPress-5.5%2B-21759b)

## Features

### Appearance
- Logo from the media library, with a configurable link destination. Falls back to the Customizer logo, and then to a built in logo.
- Form position: left, centre, or right. Left and right use a two column layout with a solid panel behind the form.
- Background: solid colour, gradient, or image.
  - Image backgrounds support blur and an overlay (none, solid, or gradient). Gradient overlays have separate start and end opacity, so they can fade to transparent.
- Login box drop shadow (toggle, amount, colour) and a colour for the selected field.
- Colour palette of nine tokens with three presets: Dark, Light, and Slate.
- Corner radius, optional heading text above the form, and an extra CSS box for per site tweaks.
- Legal links in the login footer (legal notice, privacy, cookies), plus options to hide the default WordPress links.

### Security (lightweight by design)
This is not a security plugin and does not replace one. It adds two small measures on the login screen:
- An anti bot honeypot (a toggle in the settings, and a filter for code).
- A generic error message that does not reveal whether the username or the password was wrong.

### Admin experience
- Tabbed settings panel with native WordPress styling, keyboard navigation, and ARIA roles.
- The WordPress colour picker on every colour field, so you can type or paste a hex value.
- Export and import all settings as JSON.
- Fully translatable. Ships with English source strings and a Spanish (es_ES) translation, including the plugin description shown on the Plugins screen.

### Under the hood
- No external fonts or assets; uses the system font stack.
- Structural CSS lives in stylesheets and is driven by CSS variables; PHP injects only the per site values.
- Self updates from GitHub through the Plugin Update Checker library.
- Keeps the `amw_login_options` option name across versions, so upgrades never lose a site's configuration.

## Requirements

- WordPress 5.5 or newer
- PHP 7.0 or newer

## Installation

1. Download the latest release as a ZIP, or clone this repository.
2. Upload it via Plugins > Add New > Upload Plugin, or copy the folder into `wp-content/plugins/`.
3. Activate the plugin, then open Settings > AMW Login.

Once installed, the plugin keeps itself up to date from this repository.

## Configuration

Everything lives under Settings > AMW Login, organised into tabs:

- **Design**: logo, colour palette, login box, and interface options.
- **Background**: form position, background type, and the side panel.
- **Content**: heading text, legal links, and extra CSS.
- **Security**: the honeypot toggle and what the plugin does on the security side.
- **Tools**: export and import settings.

Changes apply to the login screen. Open it to preview.

## For developers

- `amw_login_honeypot_enabled` (filter): force the honeypot on or off site wide, overriding the setting. Example: `add_filter( 'amw_login_honeypot_enabled', '__return_false' );`
- All settings are stored in a single option, `amw_login_options`.
- Uninstalling removes that option and leaves nothing else behind.

## Translations

Source strings are in English. A Spanish (es_ES) translation is included under `languages/`. To translate into another language, copy `languages/amw-simple-login.pot`, translate it with a tool such as Poedit, and save the resulting `.po` and `.mo` as `amw-simple-login-{locale}` in the same folder.

## Support

This plugin is free and open source. If it saves you time, you can [buy me a coffee](https://buymeacoffee.com/alvaromarquezweb).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## Author

Álvaro Márquez Díaz, [alvaromarquezweb.com](https://alvaromarquezweb.com)
