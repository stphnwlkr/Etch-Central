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
        if ('settings_page_' . Settings::PAGE_SLUG !== $hook) {
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
