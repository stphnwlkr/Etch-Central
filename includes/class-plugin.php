<?php
/**
 * Main plugin bootstrap.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

if (!defined('ABSPATH')) {
    exit;
}

final class Plugin {
    private static ?self $instance = null;
    private Settings $settings;
    private Assets $assets;
    private Admin_Page $admin_page;
    private Admin_Bar $admin_bar;

    private function __construct() {
        $this->settings   = new Settings();
        $this->assets     = new Assets();
        $this->admin_page = new Admin_Page($this->settings);
        $this->admin_bar  = new Admin_Bar($this->settings);
    }

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void {
        add_action('admin_init', [$this->settings, 'register']);
        add_action('admin_menu', [$this->admin_page, 'register']);
        add_action('admin_enqueue_scripts', [$this->assets, 'enqueue_admin_assets']);
        add_action('admin_enqueue_scripts', [$this->assets, 'enqueue_admin_bar_assets']);
        add_action('wp_enqueue_scripts', [$this->assets, 'enqueue_admin_bar_assets']);
        add_action('admin_bar_menu', [$this->admin_bar, 'register'], 9999);
    }
}
