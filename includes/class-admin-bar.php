<?php
/**
 * Admin bar integration for Etch Central.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

use WP_Admin_Bar;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

final class Admin_Bar {
    private Settings $settings;

    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    public function register(WP_Admin_Bar $admin_bar): void {
        if (!$this->settings->can_access()) {
            return;
        }

        if (!class_exists('\\Etch\\Plugin')) {
            return;
        }

        $settings        = $this->settings->get();
        $current_context = $this->get_current_context();
        $content_url     = $this->get_existing_etch_url($admin_bar);
        $template_data   = $current_context ? $this->get_best_template($current_context['candidates']) : null;

        $this->remove_etch_nodes($admin_bar);

        $admin_bar->add_node([
            'id'    => 'etch-central',
            'title' => 'Etch Central <span class="dashicons dashicons-arrow-down-alt2" style="font-family:dashicons;font-size:14px;line-height:34px;"></span>',
            'href'  => false,
            'meta'  => ['title' => __('Etch Central tools', 'etch-central')],
        ]);

        if (is_admin()) {
            $homepage_id = (int) get_option('page_on_front');

            if ('page' === get_option('show_on_front') && $homepage_id > 0) {
                $admin_bar->add_node([
                    'id'     => 'etch-central-launch-etch',
                    'parent' => 'etch-central',
                    'title'  => esc_html__('Launch Etch', 'etch-central'),
                    'href'   => esc_url($this->etch_editor_url($homepage_id, 0)),
                    'meta'   => ['title' => __('Open the assigned homepage in the Etch editor', 'etch-central')],
                ]);
            }
        }

        if ($content_url && is_singular()) {
            $title = get_the_title((int) get_queried_object_id());

            $admin_bar->add_node([
                'id'     => 'etch-central-edit-content',
                'parent' => 'etch-central',
                'title'  => esc_html(
                    sprintf(
                        /* translators: %s: Post title. */
                        __('Edit %s', 'etch-central'),
                        $title ?: __('this content', 'etch-central')
                    )
                ),
                'href'   => esc_url($content_url),
                'meta'   => ['title' => __('Open this content in the Etch editor', 'etch-central')],
            ]);
        }

        if ($template_data && $current_context) {
            $admin_bar->add_node([
                'id'     => 'etch-central-edit-template',
                'parent' => 'etch-central',
                'title'  => esc_html(
                    sprintf(
                        /* translators: %s: Template label. */
                        __('Edit %s Template', 'etch-central'),
                        $template_data['label']
                    )
                ),
                'href'   => esc_url($this->etch_editor_url((int) $template_data['id'], (int) $current_context['original_post_id'])),
                'meta'   => ['title' => __('Open this template in the Etch editor', 'etch-central')],
            ]);
        }

        if (!empty($settings['enabled_menus']['templates'])) {
            $this->add_templates_menu($admin_bar);
        }

        if (!empty($settings['enabled_menus']['patterns'])) {
            $this->add_patterns_menu($admin_bar);
        }

        if (!empty($settings['enabled_menus']['resources'])) {
            $this->add_static_links_menu(
                $admin_bar,
                'etch-central-resources',
                __('Etch Resources', 'etch-central'),
                $this->resource_links()
            );
        }

        if (!empty($settings['enabled_menus']['community'])) {
            $this->add_static_links_menu(
                $admin_bar,
                'etch-central-community',
                __('Etch Community', 'etch-central'),
                (array) $settings['community_links']
            );
        }

        $admin_bar->add_node([
            'id'     => 'etch-central-settings-separator',
            'parent' => 'etch-central',
            'title'  => '',
            'href'   => false,
            'meta'   => [
                'class' => 'etch-central-separator',
            ],
        ]);

        $admin_bar->add_node([
            'id'     => 'etch-central-settings',
            'parent' => 'etch-central',
            'title'  => sprintf(
                '%s %s',
                '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>',
                esc_html__('Etch Central Settings', 'etch-central')
            ),
            'href'   => esc_url(admin_url('options-general.php?page=etch-central')),
            'meta'   => [
                'title' => esc_attr__('Open Etch Central settings', 'etch-central'),
            ],
        ]);
    }

    private function remove_etch_nodes(WP_Admin_Bar $admin_bar): void {
        foreach (['etch-edit-template', 'etch-edit-content', 'edit-with-etch', 'etch-central'] as $node_id) {
            $admin_bar->remove_node($node_id);
        }
    }

    private function get_existing_etch_url(WP_Admin_Bar $admin_bar): string {
        $parent = $admin_bar->get_node('edit-with-etch');

        return $parent && !empty($parent->href) ? (string) $parent->href : '';
    }

    private function get_current_context(): ?array {
        if (is_singular()) {
            $post_id   = get_queried_object_id();
            $post_type = get_post_type($post_id);

            if (!$post_id || !$post_type) {
                return null;
            }

            $slug = get_post_field('post_name', $post_id);

            return [
                'original_post_id' => (int) $post_id,
                'candidates'       => 'page' === $post_type
                    ? ["page-{$slug}", "page-{$post_id}", 'page', 'index']
                    : ["single-{$post_type}-{$slug}", "single-{$post_type}", 'single', 'singular', 'index'],
            ];
        }

        if (is_post_type_archive()) {
            $post_type_obj = get_queried_object();
            $post_type     = is_object($post_type_obj) && isset($post_type_obj->name) ? $post_type_obj->name : '';

            return $post_type
                ? ['original_post_id' => 0, 'candidates' => ["archive-{$post_type}", 'archive', 'index']]
                : null;
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();

            if (!is_object($term) || empty($term->taxonomy) || empty($term->slug)) {
                return null;
            }

            return [
                'original_post_id' => (int) $term->term_id,
                'candidates'       => [
                    "taxonomy-{$term->taxonomy}-{$term->slug}",
                    "taxonomy-{$term->taxonomy}",
                    'taxonomy',
                    'archive',
                    'index',
                ],
            ];
        }

        if (is_404()) {
            return ['original_post_id' => 0, 'candidates' => ['404', 'index']];
        }

        if (is_search()) {
            return ['original_post_id' => 0, 'candidates' => ['search', 'index']];
        }

        return null;
    }

    private function get_best_template(array $candidates): ?array {
        $matches = get_posts([
            'post_type'      => 'wp_template',
            'post_name__in'  => $candidates,
            'posts_per_page' => count($candidates),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ]);

        if (!$matches) {
            return null;
        }

        $slugs_to_ids = array_column($matches, 'ID', 'post_name');

        foreach ($candidates as $candidate) {
            if (isset($slugs_to_ids[$candidate])) {
                return [
                    'id'    => (int) $slugs_to_ids[$candidate],
                    'label' => $this->template_label($candidate),
                ];
            }
        }

        return null;
    }

    private function template_label(string $slug): string {
        $label = preg_replace('/^(single|page|archive|taxonomy)-/', '', $slug);
        $label = ucwords(str_replace('-', ' ', (string) $label));

        if (str_starts_with($slug, 'archive-')) {
            $label .= ' Archive';
        }

        return $label;
    }

    private function add_templates_menu(WP_Admin_Bar $admin_bar): void {
        $admin_bar->add_node([
            'id'     => 'etch-central-all-templates',
            'parent' => 'etch-central',
            'title'  => __('All Templates', 'etch-central'),
            'href'   => false,
            'meta'   => ['class' => 'etch-central-click-submenu'],
        ]);

        $templates = get_posts([
            'post_type'      => 'wp_template',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        foreach ($templates as $template) {
            if (!$template instanceof WP_Post) {
                continue;
            }

            $admin_bar->add_node([
                'id'     => 'etch-central-template-' . (int) $template->ID,
                'parent' => 'etch-central-all-templates',
                'title'  => esc_html($template->post_title ?: $template->post_name),
                'href'   => esc_url($this->etch_editor_url((int) $template->ID, 0)),
                'meta'   => [
                    'title' => __('Open this template in the Etch editor', 'etch-central'),
                    'class' => 'etch-central-template-node',
                ],
            ]);
        }
    }

    private function add_patterns_menu(WP_Admin_Bar $admin_bar): void {
        $admin_bar->add_node([
            'id'     => 'etch-central-all-patterns',
            'parent' => 'etch-central',
            'title'  => __('All Patterns', 'etch-central'),
            'href'   => false,
            'meta'   => ['class' => 'etch-central-click-submenu'],
        ]);

        $patterns = get_posts([
            'post_type'      => 'wp_block',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        foreach ($patterns as $pattern) {
            if (!$pattern instanceof WP_Post) {
                continue;
            }

            $pattern_title = $pattern->post_title ?: $pattern->post_name;
            $sync_label    = $this->pattern_sync_label($pattern);
            $sync_abbr     = $this->pattern_sync_abbr($pattern);

            $admin_bar->add_node([
                'id'     => 'etch-central-pattern-' . (int) $pattern->ID,
                'parent' => 'etch-central-all-patterns',
                'title'  => sprintf(
                    '<span class="etch-central-pattern-menu-item"><span class="etch-central-pattern-menu-item__title">%s</span><span class="etch-central-pattern-menu-item__status" title="%s" aria-label="%s">%s</span></span>',
                    esc_html($pattern_title),
                    esc_attr($sync_label),
                    esc_attr($sync_label),
                    esc_html($sync_abbr)
                ),
                'href'   => esc_url($this->etch_editor_url((int) $pattern->ID, 0)),
                'meta'   => [
                    'title' => __('Open this pattern in the Etch editor', 'etch-central'),
                    'class' => 'etch-central-pattern-node',
                ],
            ]);
        }
    }

    private function pattern_sync_label(WP_Post $pattern): string {
        $status = (string) get_post_meta($pattern->ID, 'wp_pattern_sync_status', true);

        if ('unsynced' === $status) {
            return __('Not synced', 'etch-central');
        }

        if (str_contains($pattern->post_content, 'metadata:{"bindings"') || str_contains($pattern->post_content, '"bindings"')) {
            return __('Partially synced', 'etch-central');
        }

        return __('Synced', 'etch-central');
    }

    private function pattern_sync_abbr(WP_Post $pattern): string {
        $label = $this->pattern_sync_label($pattern);

        if ($label === __('Partially synced', 'etch-central')) {
            return __('P', 'etch-central');
        }

        if ($label === __('Not synced', 'etch-central')) {
            return __('N', 'etch-central');
        }

        return __('S', 'etch-central');
    }

    private function add_static_links_menu(WP_Admin_Bar $admin_bar, string $id, string $title, array $links): void {
        $admin_bar->add_node([
            'id'     => $id,
            'parent' => 'etch-central',
            'title'  => esc_html($title),
            'href'   => false,
            'meta'   => ['class' => 'etch-central-click-submenu'],
        ]);

        foreach ($links as $index => $link) {
            if (empty($link['label']) || empty($link['url'])) {
                continue;
            }

            $admin_bar->add_node([
                'id'     => $id . '-' . sanitize_title((string) $link['label']) . '-' . (int) $index,
                'parent' => $id,
                'title'  => esc_html((string) $link['label']),
                'href'   => esc_url((string) $link['url']),
                'meta'   => [
                    'target' => '_blank',
                    'rel'    => 'noopener noreferrer',
                    'title'  => sprintf(
                        /* translators: %s: Resource or community link label. */
                        __('Open %s in a new tab', 'etch-central'),
                        (string) $link['label']
                    ),
                ],
            ]);
        }
    }

    private function resource_links(): array {
        return [
            ['label' => 'Etch Documentation', 'url' => 'https://docs.etchwp.com/'],
            ['label' => 'Etch Patterns', 'url' => 'https://patterns.etchwp.com/'],
            ['label' => 'Etch Circle Community', 'url' => 'https://community.etchwp.com/'],
            ['label' => 'EtchWP Homepage', 'url' => 'https://etchwp.com/?aff=77d60d8c'],
        ];
    }

    private function etch_editor_url(int $post_id, int $original_post_id = 0): string {
        $args = [
            'etch'    => 'magic',
            'post_id' => $post_id,
        ];

        if ($original_post_id) {
            $args['original_post_id'] = $original_post_id;
        }

        return add_query_arg($args, home_url('/'));
    }
}