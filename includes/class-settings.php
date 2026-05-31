<?php
/**
 * Settings management for Etch Central.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

if (!defined('ABSPATH')) {
    exit;
}

final class Settings {
    public const OPTION_KEY = 'etch_central_settings';
    public const PAGE_SLUG  = 'etch-central';

    public function defaults(): array {
        return [
            'enabled_menus' => [
                'templates' => true,
                'patterns'  => true,
                'resources' => true,
                'community' => true,
            ],
            'allowed_roles' => ['administrator'],
            'cleanup_on_deactivation' => false,
            'community_links' => [
                ['label' => 'SnippetNest', 'url' => 'https://snippetnest.com/snippets/?_topic=etch'],
                ['label' => 'FW Cafe', 'url' => 'https://fwcafe.com/'],
                ['label' => 'ETCHucate', 'url' => 'https://etchucate.com/'],
                ['label' => 'FW Foundry', 'url' => 'https://fwfoundry.com/'],
                ['label' => 'Oh My Etch', 'url' => 'https://ohmyetch.com/'],
            ],
        ];
    }

    public function get(): array {
        $settings = get_option(self::OPTION_KEY, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return wp_parse_args($settings, $this->defaults());
    }

    public function can_access(): bool {
        if (!is_user_logged_in()) {
            return false;
        }

        $settings = $this->get();
        $roles    = isset($settings['allowed_roles']) && is_array($settings['allowed_roles'])
            ? array_filter(array_map('sanitize_key', $settings['allowed_roles']))
            : ['administrator'];

        $user = wp_get_current_user();

        foreach ((array) $user->roles as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        return current_user_can('manage_options');
    }

    public function register(): void {
        register_setting(
            'etch_central_settings_group',
            self::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default'           => $this->defaults(),
            ]
        );
    }

    public function sanitize($value): array {
        $defaults = $this->defaults();
        $value    = is_array($value) ? wp_unslash($value) : [];
        $settings = $defaults;

        $settings['enabled_menus'] = [
            'templates' => !empty($value['enabled_menus']['templates']),
            'patterns'  => !empty($value['enabled_menus']['patterns']),
            'resources' => !empty($value['enabled_menus']['resources']),
            'community' => !empty($value['enabled_menus']['community']),
        ];

        $settings['allowed_roles'] = [];
        $editable_roles = array_keys(get_editable_roles());

        if (!empty($value['allowed_roles']) && is_array($value['allowed_roles'])) {
            foreach ($value['allowed_roles'] as $role) {
                $role = sanitize_key((string) $role);

                if ($role && in_array($role, $editable_roles, true)) {
                    $settings['allowed_roles'][] = $role;
                }
            }
        }

        if (empty($settings['allowed_roles'])) {
            $settings['allowed_roles'] = ['administrator'];
        }

        $settings['cleanup_on_deactivation'] = !empty($value['cleanup_on_deactivation']);
        $settings['community_links'] = [];

        if (!empty($value['community_links']) && is_array($value['community_links'])) {
            foreach ($value['community_links'] as $link) {
                if (count($settings['community_links']) >= 10) {
                    break;
                }

                if (!is_array($link)) {
                    continue;
                }

                $label = isset($link['label']) ? sanitize_text_field((string) $link['label']) : '';
                $url   = isset($link['url']) ? esc_url_raw((string) $link['url']) : '';

                if ($label && $url) {
                    $settings['community_links'][] = [
                        'label' => $label,
                        'url'   => $url,
                    ];
                }
            }
        }

        return $settings;
    }
}
