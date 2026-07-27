# AMW Simple Login

A WordPress plugin to fully customize the login screen (`wp-login.php`): logo, colors, a two-column background image, legal links and a few security tweaks. Built for anyone who deploys across many sites: it is configured entirely from the dashboard and updates itself from GitHub.

## ✨ Features

- **Logo** picked from the media library, with automatic detection of the site's own logo when none is set.
- **Full color palette** (page, form, fields, borders, text, button), injected as CSS variables.
- **Two-column background image**: form on the left, center or right, with adjustable blur and an overlay whose color and intensity you control.
- **Legal links** in the login footer (legal notice, privacy, cookies), with your own text and URLs.
- **Interface options**: corner radius and hiding the "back to site" and "lost your password?" links.
- **Security**: generic login error message (never reveals whether a username exists) and removal of WordPress fingerprints on the login screen.
- **Divi-friendly**: uses the logo set in Divi's Theme Options when no other logo is configured.
- **Self-updating** from this repository's releases.
- **Dark theme by default**, system font, no external assets.

## 📋 Requirements

- WordPress 5.9 or later
- PHP 7.4 or later
- Divi (optional, only for its logo detection)

## 🔧 Installation

1. Download the `.zip` from the latest [release](../../releases/latest).
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, upload the file and activate it.
3. Once active, future updates arrive automatically from this repository.

## ⚙️ Configuration

Everything is set from **Settings → AMW Login**:

- **Logo**: upload your own or let it use the site's logo.
- **Background image**: image, layout (left, center, right), blur, and overlay color and intensity.
- **Color palette** and **interface** (radius, hide links).
- **Legal links** in the footer.

The logo is resolved in this order: plugin logo, Site Identity logo, Divi logo and, as a last resort, an inline fallback SVG that takes the theme color.

## 🚀 Publishing an update (maintenance)

The plugin checks this repo's releases via [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker). To ship a new version:

1. Bump the `Version:` number in the main plugin file header.
2. Commit and push.
3. Create a **release** with the matching tag (for example, `v1.0.1`).

Sites running the plugin will detect the new version and offer the update from their dashboard.

## 📄 License

[GPL-2.0-or-later](LICENSE). Bundles the Plugin Update Checker library by Yahnis Elsts, under its own compatible license.

## 👤 Author

Álvaro Márquez Díaz · [alvaromarquezweb.com](https://alvaromarquezweb.com)
