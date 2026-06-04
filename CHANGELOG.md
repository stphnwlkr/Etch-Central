# Changelog

All notable Etch Central changes are documented here, including the iteration history that led to the stable 1.0.0 launcher.


## 1.0.1

### Fixed

- Fixed a pane ID collision where a custom post type named `resources` could populate the Etch Resources panel.
- Kept Etch Resources limited to the four static Etch links.
- Adjusted static link-list sizing so Etch Resources and My Etch Shortcuts no longer stretch awkwardly to fill the available vertical space.

## 1.0.0

### Changed

- Promoted the command-center style launcher to the stable Etch Central interface.
- Updated plugin version references from 0.6.4.1 to 1.0.0.
- Rewrote the README to document the stable feature set.
- Added this dedicated changelog to preserve the iteration history.

## 0.6.4.1

### Fixed

- Added inline padding around browser search fields so keyboard focus outlines remain fully visible.

## 0.6.4

### Changed

- Removed scrolling from the outer launcher panel.
- Limited scrolling to internal item/result lists where it is actually needed.
- Improved the launcher so it feels more like an application surface and less like an oversized dropdown.

## 0.6.3

### Fixed

- Reattached the launcher visually to the admin bar item on larger screens after viewport-aware positioning changes.

## 0.6.2

### Changed

- Added viewport-aware positioning and sizing so the launcher is less likely to be clipped on narrower screens.
- Added responsive behavior for smaller screens where the full desktop launcher cannot comfortably fit.

## 0.6.1

### Fixed

- Hardened launcher styles against theme and framework layout rules.
- Added protection against Automatic CSS section gap defaults causing excessive spacing inside the launcher.
- Normalized internal launcher spacing so front-end styles do not break the UI.

## 0.6.0

### Added

- Introduced the command-center style launcher as the primary Etch Central interface.
- Added a compact two-panel layout with navigation on the left and searchable panels on the right.
- Added consistent panel behavior for content types, templates, patterns, Etch resources, and My Etch Shortcuts.
- Added alternate click behavior for opening supported items in the WordPress editor.

### Changed

- Removed the experimental label from the launcher direction.
- Made the command-center launcher the way forward for Etch Central.
- Removed Launch Etch from front-end views.
- Changed resource and shortcut links to use the same panel-based interaction model as content browsers.

## 0.5.1 Experimental Panels

### Added

- Reworked the experimental flyout so the left column acts as navigation and each searchable browser/list opens in the right column.
- Moved Etch Resources and My Etch Shortcuts into right-column panels instead of listing all links in the left column.

## 0.5.1 Experimental Two-Column

### Added

- Tested a two-column panel layout with current content, resources, and settings on the left and searchable browsers on the right.

## 0.5.1 Experimental

### Added

- Replaced separator-style admin bar menu items with one custom Etch Central flyout panel.
- Added semantic section headings for Current Content, Content Types, Etch Assets, Resources, and Administration.
- Tested searchable `<details>` groups for content types, templates, and patterns.

### Changed

- Began moving away from native admin-bar submenu separators toward a custom accessible panel.

## 0.5.0

### Added

- Optional searchable admin bar browsers for Pages, Posts, and public custom post types.
- Content type browser settings, all disabled by default.
- Published item counts in content-type submenu labels.
- Current Post/Page/CPT link.
- Current Template link.

### Changed

- Renamed Etch Community to My Etch Shortcuts.
- Updated current context labels to use Current Post/Page/CPT and Current Template language.
- Moved cleanup setting into the saved settings form.

## 0.4.2

### Added

- Admin bar shortcut to Etch Central Settings.
- Visual divider separating tools from plugin configuration.

### Improved

- Improved discoverability of plugin settings from the admin bar.

## 0.4.1

### Fixed

- Fixed folder structure.
- Added translation comments.
- Verified with Plugin Check with no errors.

## 0.4.0

### Changed

- Refactored the plugin into separate maintainable classes.
- Moved admin styles into a dedicated asset file.
- Moved admin bar styles into a dedicated asset file.
- Moved admin scripts into a dedicated asset file.
- Moved admin bar scripts into a dedicated asset file.
- Separated settings, admin page rendering, admin bar rendering, assets, and deactivation logic.

## 0.3.1

### Added

- Added hub icon to the admin settings page.

### Changed

- Increased settings page title font weight.
- Moved cleanup setting into the main hero panel.

## 0.3.0

### Added

- Added Launch Etch for admin screens when a static homepage is configured.

## 0.2.x

### Added

- Added optional cleanup on deactivation.
- Added fixed search fields for templates and patterns.
- Added scrollable template and pattern lists.
- Added pattern sync indicators.

### Improved

- Improved template and pattern submenu behavior.

## 0.1.x

### Added

- Initial Etch Central admin bar functionality.
- Added template, pattern, resource, and community menu foundations.
