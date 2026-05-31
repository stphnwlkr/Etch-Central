<?php
/**
 * Deactivation behavior.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

if (!defined('ABSPATH')) {
    exit;
}

final class Deactivator {
    public static function deactivate(): void {
        $settings = get_option(Settings::OPTION_KEY, []);

        if (is_array($settings) && !empty($settings['cleanup_on_deactivation'])) {
            delete_option(Settings::OPTION_KEY);
        }
    }
}
