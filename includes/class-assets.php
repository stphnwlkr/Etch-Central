<?php
/**
 * Asset loading for Etch Central.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

if (!defined('ABSPATH')) {
    exit;
}

final class Assets {
    public function enqueue_admin_assets(string $hook): void {
        $page = isset($_GET['page']) ? sanitize_key((string) wp_unslash($_GET['page'])) : '';

        $valid_hooks = [
            'toplevel_page_' . Settings::PAGE_SLUG,
            Settings::PAGE_SLUG . '_page_' . Settings::PAGE_SLUG . '-resources',
            'settings_page_' . Settings::PAGE_SLUG,
            'settings_page_' . Settings::PAGE_SLUG . '-resources',
        ];

        $is_etch_central_page = in_array($hook, $valid_hooks, true)
            || Settings::PAGE_SLUG === $page
            || Settings::PAGE_SLUG . '-resources' === $page
            || str_contains($hook, Settings::PAGE_SLUG);

        if (!$is_etch_central_page) {
            return;
        }

        wp_enqueue_style(
            'etch-central-admin',
            ETCH_CENTRAL_URL . 'assets/css/admin.css',
            [],
            ETCH_CENTRAL_VERSION
        );

        wp_enqueue_script(
            'etch-central-admin',
            ETCH_CENTRAL_URL . 'assets/js/admin.js',
            [],
            ETCH_CENTRAL_VERSION,
            true
        );

        wp_localize_script(
            'etch-central-admin',
            'etchCentralAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('etch_central_admin_appearance'),
            ]
        );
    }

    public function enqueue_admin_bar_assets(): void {
        if (!is_admin_bar_showing()) {
            return;
        }

        wp_enqueue_style(
            'etch-central-admin-bar',
            ETCH_CENTRAL_URL . 'assets/css/admin-bar.css',
            [],
            ETCH_CENTRAL_VERSION
        );

        wp_enqueue_script(
            'etch-central-admin-bar',
            ETCH_CENTRAL_URL . 'assets/js/admin-bar.js',
            [],
            ETCH_CENTRAL_VERSION,
            true
        );
    }
}
