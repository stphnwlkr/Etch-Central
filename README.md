# Etch Central

Etch Central adds a consolidated WordPress admin bar hub for Etch-powered sites. It brings together quick access to the current content item, the active template, all site templates, reusable patterns, official Etch resources, and user-managed community links.

The plugin is intended for teams that work heavily in Etch and want a faster, cleaner way to move between WordPress, the Etch editor, templates, patterns, documentation, and community resources.

## Features

- Adds an **Etch Central** menu to the WordPress admin bar.
- Replaces the default **Edit with Etch** admin bar item with a more organized menu.
- Adds **Launch Etch** on admin screens when a static homepage is configured in **Settings > Reading**.
- Adds a direct **Edit current content** link on singular front-end views.
- Adds a direct **Edit current template** link when the active WordPress template can be identified.
- Adds an **All Templates** submenu listing published WordPress templates.
- Adds an **All Patterns** submenu listing published WordPress patterns.
- Adds search fields for long template and pattern lists.
- Keeps template and pattern search fields fixed above the scrollable list area.
- Uses click-controlled child panels for template, pattern, resource, and community submenus.
- Shows pattern sync status with compact indicators:
  - **S** = Synced
  - **P** = Partially synced
  - **N** = Not synced
- Includes official Etch resource links.
- Includes editable Etch Community favorite links.
- Allows up to 10 custom community links.
- Supports drag-and-drop ordering for community links.
- Includes keyboard-accessible **Up** and **Down** controls for reordering links.
- Allows admins to enable or disable individual submenus.
- Allows admins to select which WordPress roles can see Etch Central.
- Includes an optional setting to delete plugin settings on deactivation.
- Uses separate PHP classes and separate CSS/JS assets for maintainability.

## Requirements

- WordPress 6.9.4 or later.
- PHP 8.3 or later.
- Etch must be installed and active.
- A user must be logged in and assigned to an allowed role.

The plugin checks for the Etch plugin class before adding the admin bar menu. If Etch is not active, Etch Central will not output its admin bar tools.

## Tested Up To

- WordPress 7.0

## Installation

1. Download the plugin ZIP.
2. In WordPress, go to **Plugins > Add New Plugin**.
3. Choose **Upload Plugin**.
4. Upload the ZIP file.
5. Activate **Etch Central**.
6. Go to **Settings > Etch Central** to configure available menus, roles, community links, and cleanup behavior.

## Git-Based Installation

For Git-based deployments, place the plugin folder in:

```text
wp-content/plugins/etch-central/
```

Then activate it through the WordPress admin or your deployment workflow.

If plugin removal is managed through Git rather than the WordPress Plugins screen, use the optional **Remove all Etch Central settings when deactivated** setting if you want the plugin option removed during deactivation.

## Admin Bar Menu

When enabled and available to the current user, the plugin adds an **Etch Central** menu to the WordPress admin bar.

### Launch Etch

On admin screens, **Launch Etch** appears at the top of the Etch Central menu when WordPress is configured to use a static homepage.

This link opens:

```text
/?etch=magic&post_id={homepage_id}
```

This follows the default Etch behavior for launching the homepage into Etch.

The item only appears when:

- The current screen is in the WordPress admin.
- **Settings > Reading** is configured to show a static page on the front page.
- A homepage is assigned.

### Edit Current Content

On singular front-end views, Etch Central adds a link for the current content item, such as:

```text
Edit Home
Edit About
Edit Sample Post
```

The plugin uses the existing Etch admin bar URL when available, preserving Etch’s own editor URL behavior.

### Edit Current Template

When the current template can be resolved, Etch Central adds a direct editor link for that template.

Examples:

```text
Edit Home Template
Edit Page Template
Edit Single Post Template
Edit Index Template
```

Template detection is based on the current WordPress request context and checks likely template slugs in priority order.

For pages, candidates include:

```text
page-{slug}
page-{id}
page
index
```

For posts and custom post types, candidates include:

```text
single-{post_type}-{slug}
single-{post_type}
single
singular
index
```

For archives and taxonomy views, archive and taxonomy template candidates are used.

## All Templates

The **All Templates** submenu lists published `wp_template` posts and opens each one directly in the Etch editor.

The submenu includes:

- A search field fixed above the list.
- A scrollable list capped at a viewport-friendly height.
- Wrapped text for longer template names.
- Click-controlled panel behavior instead of hover-only behavior.

By default, the menu queries up to 50 published templates ordered alphabetically by title.

## All Patterns

The **All Patterns** submenu lists published `wp_block` posts and opens each one directly in the Etch editor.

The submenu includes:

- A search field fixed above the list.
- A scrollable list capped at a viewport-friendly height.
- Wrapped pattern titles.
- Compact sync status indicators.
- Click-controlled panel behavior instead of hover-only behavior.

By default, the menu queries up to 100 published patterns ordered alphabetically by title.

## Pattern Sync Indicators

Etch Central displays pattern sync status using a compact boxed indicator.

| Indicator | Meaning |
| --- | --- |
| S | Synced |
| P | Partially synced |
| N | Not synced |

The visual indicator is intentionally short so long pattern names can wrap while the sync status remains aligned and readable.

The status text is also exposed through `title` and `aria-label` attributes.

## Etch Resources

The **Etch Resources** submenu includes official Etch links:

- Etch Documentation: <https://docs.etchwp.com/>
- Etch Patterns: <https://patterns.etchwp.com/>
- Etch Circle Community: <https://community.etchwp.com/>
- EtchWP Homepage: <https://etchwp.com/?aff=77d60d8c>

Resource links open in a new tab using `target="_blank"` and `rel="noopener noreferrer"`.

## Etch Community

The **Etch Community** submenu is managed from **Settings > Etch Central**.

Default community links include:

- SnippetNest: <https://snippetnest.com/snippets/?_topic=etch>
- FW Cafe: <https://fwcafe.com/>
- ETCHucate: <https://etchucate.com/>
- FW Foundry: <https://fwfoundry.com/>
- Oh My Etch: <https://ohmyetch.com/>

Admins can add, remove, edit, and reorder up to 10 community links.

## Settings

Settings are available at:

```text
Settings > Etch Central
```

### Admin Bar Menus

The following submenus can be enabled or disabled individually:

- Templates
- Patterns
- Resources
- Community

The primary Etch Central menu can still include context-aware links such as **Launch Etch**, **Edit current content**, and **Edit current template**.

### Allowed Roles

Administrators are selected by default.

Admins can choose additional roles that may see Etch Central in the admin bar. The setting is role-based and only accepts roles returned by WordPress through `get_editable_roles()`.

Users with `manage_options` capability can access the menu even if their role is not explicitly listed.

### Etch Community Favorites

The settings screen provides 10 rows for community favorites.

Each row includes:

- A label field.
- A URL field.
- A drag handle.
- Move Up and Move Down buttons.

Rows with both a label and URL are saved. Empty rows are ignored.

### Cleanup on Deactivation

The plugin stores settings in one WordPress option:

```text
etch_central_settings
```

By default, settings are retained when the plugin is deactivated.

Admins can enable:

```text
Remove all Etch Central settings when deactivated
```

When enabled, the plugin deletes its settings option during plugin deactivation.

This option is useful for Git-based deployments where a plugin may be added, updated, or removed through source control and the standard WordPress uninstall flow may not be used.

## Accessibility

Etch Central is designed with accessibility in mind.

Accessibility considerations include:

- Admin settings are grouped with headings and labeled controls.
- Form fields use visible labels.
- Drag-and-drop ordering is supplemented with keyboard-accessible Move Up and Move Down buttons.
- Search inputs include screen-reader text labels.
- Pattern sync indicators include accessible labels.
- Focus styles are provided for custom controls.
- Click-controlled submenu triggers use `aria-haspopup` and `aria-expanded`.
- Escape closes open click-controlled panels.
- Long menu text can wrap instead of being clipped.

## Security

The plugin follows WordPress security practices, including:

- Direct file access protection with `ABSPATH` checks.
- Capability checks before rendering settings.
- WordPress Settings API registration for option handling.
- Sanitization of all saved settings.
- Role values restricted to editable WordPress roles.
- URLs sanitized using `esc_url_raw()` before saving.
- Text values sanitized using `sanitize_text_field()` before saving.
- Output escaped with WordPress escaping functions such as `esc_html()`, `esc_attr()`, and `esc_url()`.
- External links use `rel="noopener noreferrer"`.
- No front-end database writes.
- No custom database tables.

## Data Storage

Etch Central stores all plugin settings in a single WordPress option:

```text
etch_central_settings
```

The plugin does not create custom database tables.

The plugin does not create custom post types, taxonomies, users, roles, or capabilities.

The plugin does not modify Etch data directly. It only generates editor links to existing content, templates, and patterns.

## File Structure

```text
etch-central/
├── etch-central.php
├── includes/
│   ├── class-plugin.php
│   ├── class-settings.php
│   ├── class-admin-page.php
│   ├── class-admin-bar.php
│   ├── class-assets.php
│   └── class-deactivator.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── admin-bar.css
│   ├── js/
│   │   ├── admin.js
│   │   └── admin-bar.js
│   └── images/
│       └── icon.svg
├── LICENSE
└── README.md
```

## Class Responsibilities

### `Plugin`

Bootstraps the plugin and wires dependencies together.

### `Settings`

Owns defaults, option retrieval, role access checks, settings registration, and sanitization.

### `Admin_Page`

Renders the settings screen under **Settings > Etch Central**.

### `Admin_Bar`

Builds the Etch Central admin bar menu and related submenus.

### `Assets`

Enqueues admin and admin bar CSS/JS assets.

### `Deactivator`

Handles optional cleanup when the plugin is deactivated.

## Development Notes

### Text Domain

The plugin text domain is:

```text
etch-central
```

### Version Constant

The plugin version is defined in two places:

- Plugin header in `etch-central.php`.
- `ETCH_CENTRAL_VERSION` constant in `etch-central.php`.

Both should be updated when releasing a new version.

### Asset Versioning

CSS and JS assets are enqueued using `ETCH_CENTRAL_VERSION` so browser caches are refreshed when the plugin version changes.

### Template Query

Templates are queried from:

```text
post_type: wp_template
post_status: publish
```

### Pattern Query

Patterns are queried from:

```text
post_type: wp_block
post_status: publish
```

### Editor URL Format

Etch editor links are generated with:

```text
/?etch=magic&post_id={post_id}
```

When editing a detected template in relation to a viewed object, `original_post_id` is added when available.

## Troubleshooting

### Etch Central does not appear in the admin bar

Check that:

- Etch is installed and active.
- The current user is logged in.
- The current user has one of the allowed roles.
- The WordPress admin bar is enabled for the user.

### Launch Etch does not appear

Check that:

- You are viewing a WordPress admin screen.
- **Settings > Reading** is set to use a static homepage.
- A homepage has been selected.

### Template or pattern lists are empty

Check that:

- Published templates exist in `wp_template`.
- Published patterns exist in `wp_block`.
- The current user has access to Etch Central.

### Community links are not saving

Each saved community link must include both a label and a valid URL. Blank rows are ignored.

### Settings are removed after deactivation

The cleanup setting was likely enabled. When enabled, Etch Central deletes the `etch_central_settings` option during deactivation.

## Changelog
### 0.4.1
- fixed folder structure.
- Added translation comments.
- Verified with Plugin check. No errors.
### 0.4.0

- Refactored the plugin into separate maintainable classes.
- Moved admin and admin bar styles into dedicated asset files.
- Moved admin and admin bar scripts into dedicated asset files.
- Kept settings, admin page rendering, admin bar rendering, assets, and deactivation logic separated.

### 0.3.1

- Added hub icon to the admin settings page.
- Increased settings page title font weight.
- Moved cleanup setting into the main hero panel.

### 0.3.0

- Added Launch Etch for admin screens when a static homepage is configured.

### 0.2.x

- Added optional cleanup on deactivation.
- Improved template and pattern submenu behavior.
- Added fixed search fields for templates and patterns.
- Added scrollable template and pattern lists.
- Improved pattern sync indicators.

### 0.1.x

- Initial Etch Central admin bar functionality.
- Added template, pattern, resource, and community menu foundations.

## License

GPL-2.0-or-later.

## Author

Stephen Walker
