<?php
/**
 * Plugin Name: Etch Central
 * Description: Adds an Etch Central admin bar menu for editing current content, templates, patterns, content, resources, and shortcuts.
 * Version: 1.0.1
 * Requires at least: 6.9.4
 * Requires PHP: 8.3
 * Tested up to: 7.0
 * Author: Stephen Walker
 * License: GPL-2.0-or-later
 * Text Domain: etch-central
 *
 * @package EtchCentral
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('ETCH_CENTRAL_VERSION', '1.0.1');
define('ETCH_CENTRAL_FILE', __FILE__);
define('ETCH_CENTRAL_PATH', plugin_dir_path(__FILE__));
define('ETCH_CENTRAL_URL', plugin_dir_url(__FILE__));

require_once ETCH_CENTRAL_PATH . 'includes/class-settings.php';
require_once ETCH_CENTRAL_PATH . 'includes/class-assets.php';
require_once ETCH_CENTRAL_PATH . 'includes/class-admin-page.php';
require_once ETCH_CENTRAL_PATH . 'includes/class-admin-bar.php';
require_once ETCH_CENTRAL_PATH . 'includes/class-deactivator.php';
require_once ETCH_CENTRAL_PATH . 'includes/class-plugin.php';

add_action('plugins_loaded', static function (): void {
    Etch_Central\Plugin::instance()->init();
});

register_deactivation_hook(__FILE__, ['Etch_Central\\Deactivator', 'deactivate']);
