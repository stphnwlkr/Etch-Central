# Etch Central

Centralized access to the content that matters.

Etch Central transforms the WordPress admin bar into a unified command center for Etch-powered websites. Quickly access templates, patterns, pages, posts, media, custom post types, resources, tools, and shortcuts without navigating multiple admin screens.

Version 1.5.1 is the current maintenance baseline. Version 1.5.0 established the new baseline release. It consolidates content navigation, editable resources, Quick Links, detected tool integrations, Dynamic Post Type Menu functionality, and the modern Flowguard-inspired admin interface into one plugin for Etch sites.

## 1.5.1 Highlights


- Maintenance release for the 1.5 baseline.
- Fixed admin asset loading after moving Etch Central to a top-level menu.
- Fixed Content Type Menu settings persistence.
- Fixed checkbox state synchronization for content type enable/disable controls.
- New baseline release for Etch Central.
- Flowguard-inspired Settings and Resources admin pages.
- Instant Auto / Light / Dark appearance switching with automatic persistence.
- Dynamic Post Type Menu functionality merged into Etch Central.
- Optional admin cleanup controls.
- Individual detected tool controls for ACF, Meta Box, ACPT, JetEngine, and WPCodeBox.
- Only detected tools can be enabled.
- Dedicated Resources page with editable Etch Resources.
- Repeater-based Quick Links with add, remove, and reorder controls.
- Searchable content browsers for posts, pages, media, and public custom post types.
- Horizontal All / Mine / New content actions in the sticky search area.

## What Etch Central Does

Etch Central reduces the amount of WordPress navigation needed when building with Etch. Instead of jumping between the dashboard, site editor, templates, patterns, post types, plugin tools, and resource links, users can open Etch Central from the admin bar and move directly to the thing they need.

The plugin is intentionally a command-center launcher, not a replacement for the native WordPress or Etch command palettes.

## Key Features

### Content Navigation

- Adds an **Etch Central** item to the WordPress admin bar.
- Shows context-aware links for the current content item when available.
- Shows a current template link when the active WordPress template can be resolved.
- Provides searchable browsers for templates and patterns.
- Provides searchable browsers for enabled public post types.
- Adds All, Mine, and New quick actions for enabled content types.
- Uses a sticky search interface for content panels.

### Tool Detection

Etch Central can detect supported tools and show only the integrations that are actually present on the site. Each detected tool can be enabled or disabled individually.

Supported detections include:

- Advanced Custom Fields (ACF)
- Meta Box
- ACPT
- JetEngine
- WPCodeBox

### Resources and Quick Links

- Dedicated Resources management page.
- Editable Etch Resources list.
- Pre-populated default resources.
- Repeater-based Quick Links.
- Add, remove, and reorder controls.
- Admin-managed links for consistent team workflows.

### Admin Experience

- Modern Flowguard-inspired interface.
- Separate Settings and Resources screens.
- Auto, Light, and Dark appearance modes.
- Instant theme switching without a manual save action.
- Card-based settings layout.
- Accessibility-focused controls.

### Administrative Tools

- Dynamic Post Type Menu functionality integrated directly into Etch Central.
- Optional admin cleanup controls.
- Hide unused menu items.
- Simplify the WordPress admin experience for Etch-focused builds.

## Requirements

- WordPress 6.9.4 or later.
- Tested through WordPress 7.0.
- PHP 8.3 or later.
- Etch Builder recommended.
- A logged-in user with an allowed role.

Etch Central checks for Etch before outputting its admin bar tools. If Etch is not active, the launcher will not appear.

## Installation

1. Download the plugin ZIP.
2. In WordPress, go to **Plugins > Add New Plugin**.
3. Choose **Upload Plugin**.
4. Upload the ZIP file.
5. Activate **Etch Central**.
6. Go to **Settings > Etch Central** to configure menus, content types, roles, shortcuts, and cleanup behavior.

For Git-based deployments, keep the plugin folder name as:

```text
wp-content/plugins/etch-central/
```

## Launcher Interface

Etch Central 1.0.x uses a two-panel command-center style launcher.

```text
Etch Central
├── Left panel: navigation groups
└── Right panel: searchable lists and links
```

The left panel provides high-level navigation. The right panel changes based on the selected item and contains the search field and result list for that section.

Primary groups include:

- Current Content
- Content Types
- Etch Assets
- Resources
- Administration

Separators are not rendered as fake menu items. The launcher uses real headings and grouped panels so the structure is cleaner for keyboard and screen-reader users.

## Current Content

On singular front-end views, Etch Central can show the current post, page, or custom post type item.

Examples:

```text
Current Page: Home
Current Post: News Update
Current Event: Open House
```

The normal click behavior opens the item in Etch.

## Current Template

When the active WordPress template can be resolved, Etch Central adds a direct current-template item.

Examples:

```text
Current Template: Home
Current Template: Page
Current Template: Single Post
Current Template: Index
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

## Editor Click Behavior

For supported content, template, and pattern links:

```text
Click
  Open in Etch

Cmd/Ctrl + Click
  Open in Etch in a new tab

Option/Alt + Click
  Open in the WordPress editor

Cmd/Ctrl + Option/Alt + Click
  Open in the WordPress editor in a new tab
```

This keeps normal browser new-tab behavior while adding a low-visual-noise way to access the WordPress editor.

## Content Type Browsers

Admins can enable searchable browsers for public, viewable WordPress post types from **Settings > Etch Central**.

All content-type browsers are off by default.

Each enabled content type appears in the launcher and opens a searchable right-panel list of published items.

Examples:

```text
Pages
Posts
Events
Products
```

Each content-type browser includes:

- A search field.
- Published items ordered alphabetically by title.
- Direct links into Etch.
- Alternate click behavior for opening the WordPress editor.

By default, each content-type browser lists up to 200 published items.

## Templates

The template browser lists published `wp_template` posts and opens each one directly in Etch.

The template browser includes:

- A search field.
- A scrollable results list.
- Wrapped text for longer template names.
- The same launcher panel behavior used by content types and patterns.

By default, the browser queries up to 50 published templates ordered alphabetically by title.

## Patterns

The pattern browser lists published `wp_block` posts and opens each one directly in Etch.

The pattern browser includes:

- A search field.
- A scrollable results list.
- Wrapped pattern titles.
- Compact sync status indicators.
- The same launcher panel behavior used by content types and templates.

By default, the browser queries up to 100 published patterns ordered alphabetically by title.

## Pattern Sync Indicators

Etch Central displays pattern sync status using compact indicators.

| Indicator | Meaning |
| --- | --- |
| S | Synced |
| P | Partially synced |
| N | Not synced |

The status text is also exposed through `title` and `aria-label` attributes.

## Etch Resources

The **Etch Resources** panel includes official Etch links:

- Etch Documentation: <https://docs.etchwp.com/>
- Etch Patterns: <https://patterns.etchwp.com/>
- Etch Circle Community: <https://community.etchwp.com/>
- EtchWP Homepage: <https://etchwp.com/?aff=77d60d8c>

Resource links open in a new tab using `target="_blank"` and `rel="noopener noreferrer"`.

## My Etch Shortcuts

The **My Etch Shortcuts** panel is managed from **Settings > Etch Central**.

Default shortcut links include:

- SnippetNest: <https://snippetnest.com/snippets/?_topic=etch>
- FW Cafe: <https://fwcafe.com/>
- ETCHucate: <https://etchucate.com/>
- FW Foundry: <https://fwfoundry.com/>
- Oh My Etch: <https://ohmyetch.com/>

Admins can add, remove, edit, and reorder up to 10 shortcut links.

## Settings

Settings are available at:

```text
Settings > Etch Central
```

### Admin Bar Menus

The following sections can be enabled or disabled individually:

- Templates
- Patterns
- Resources
- My Etch Shortcuts

The primary Etch Central launcher can still include context-aware items such as the current content and current template.

### Allowed Roles

Administrators are selected by default.

Admins can choose additional roles that may see Etch Central in the admin bar. Role values are restricted to roles returned by WordPress through `get_editable_roles()`.

Users with `manage_options` capability can access the menu even if their role is not explicitly selected.

### Content Type Browsers

The content type browser setting lists public, viewable WordPress post types. All options are off by default. Enabling a post type adds a searchable launcher panel for published items of that type.

### My Etch Shortcuts

The settings screen provides 10 rows for shortcuts.

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

## Accessibility

Etch Central is designed with accessibility in mind.

Accessibility considerations include:

- The launcher uses section headings instead of fake separator menu items.
- Search inputs include screen-reader text labels.
- Search controls include padding so focus outlines remain visible.
- Focus styles are provided for custom controls.
- Outer panel scrolling is avoided; only internal lists scroll when needed.
- The launcher is spatially aware and avoids being clipped on narrower screens.
- Admin settings are grouped with headings and labeled controls.
- Form fields use visible labels.
- Drag-and-drop ordering is supplemented with keyboard-accessible move controls.
- Pattern sync indicators include accessible labels.
- Long menu text can wrap instead of being clipped.
- The launcher resets common layout properties so theme/framework styles do not create unexpected spacing.

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

The plugin does not create custom database tables, custom post types, taxonomies, users, roles, or capabilities.

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
├── CHANGELOG.md
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

Builds the Etch Central admin bar launcher and related panels.

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

Launch Etch intentionally does not appear on the front end.

### Template, pattern, or content-type lists are empty

Check that:

- Published templates exist in `wp_template`.
- Published patterns exist in `wp_block`.
- The relevant content type is enabled in **Settings > Etch Central**.
- Published content exists for the enabled content type.
- The current user has access to Etch Central.

### Shortcuts are not saving

Each saved shortcut must include both a label and a valid URL. Blank rows are ignored.

### Settings are removed after deactivation

The cleanup setting was likely enabled. When enabled, Etch Central deletes the `etch_central_settings` option during deactivation.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later.

## Author

Stephen Walker
