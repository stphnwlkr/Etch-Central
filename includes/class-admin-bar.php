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
            'title' => 'Etch Central <span class="dashicons dashicons-arrow-down-alt2" style="font-family:dashicons;font-size:14px;line-height:34px;" aria-hidden="true"></span>',
            'href'  => false,
            'meta'  => [
                'title' => __('Etch Central tools', 'etch-central'),
            ],
        ]);

        $admin_bar->add_node([
            'id'     => 'etch-central-panel',
            'parent' => 'etch-central',
            'title'  => $this->render_flyout_panel($settings, $current_context, $content_url, $template_data),
            'href'   => false,
            'meta'   => [
                'class' => 'etch-central-panel-node',
            ],
        ]);
    }

    private function remove_etch_nodes(WP_Admin_Bar $admin_bar): void {
        foreach (['etch-edit-template', 'etch-edit-content', 'edit-with-etch', 'etch-central'] as $node_id) {
            $admin_bar->remove_node($node_id);
        }
    }

    private function render_flyout_panel(array $settings, ?array $current_context, string $content_url, ?array $template_data): string {
        $left_sections = [];
        $panes         = [];
        $active_id     = '';

        $current_items = $this->current_content_items($current_context, $content_url, $template_data);
        if ($current_items) {
            $left_sections[] = $this->render_left_link_section(__('Current Content', 'etch-central'), $current_items);
        }

        $enabled_content_types = $this->enabled_content_types($settings);
        if ($enabled_content_types) {
            $buttons = '';

            foreach ($enabled_content_types as $post_type) {
                $browser = $this->post_type_browser_data($post_type);

                if (!$browser) {
                    continue;
                }

                if ('' === $active_id) {
                    $active_id = $browser['id'];
                }

                $buttons .= $this->render_left_panel_button($browser['id'], $browser['label'], $browser['id'] === $active_id);
                $panes[]  = $this->render_browser_pane($browser, $browser['id'] === $active_id);
            }

            if ('' !== $buttons) {
                $left_sections[] = $this->render_left_button_section(__('Content Types', 'etch-central'), $buttons);
            }
        }

        $asset_buttons = '';
        if (!empty($settings['enabled_menus']['templates'])) {
            $browser = $this->templates_browser_data();

            if ('' === $active_id) {
                $active_id = $browser['id'];
            }

            $asset_buttons .= $this->render_left_panel_button($browser['id'], $browser['label'], $browser['id'] === $active_id);
            $panes[]       = $this->render_browser_pane($browser, $browser['id'] === $active_id);
        }

        if (!empty($settings['enabled_menus']['patterns'])) {
            $browser = $this->patterns_browser_data();

            if ('' === $active_id) {
                $active_id = $browser['id'];
            }

            $asset_buttons .= $this->render_left_panel_button($browser['id'], $browser['label'], $browser['id'] === $active_id);
            $panes[]       = $this->render_browser_pane($browser, $browser['id'] === $active_id);
        }

        if ('' !== $asset_buttons) {
            $left_sections[] = $this->render_left_button_section(__('Etch Assets', 'etch-central'), $asset_buttons);
        }

        if (!empty($settings['enabled_menus']['integrations']) && !empty($settings['show_detected_tools'])) {
            $detected_integrations = $this->integration_browser_data((array) ($settings['detected_tools_enabled'] ?? []));
            if ($detected_integrations) {
                $integration_buttons = '';

                foreach ($detected_integrations as $browser) {
                    if ('' === $active_id) {
                        $active_id = $browser['id'];
                    }

                    $integration_buttons .= $this->render_left_panel_button($browser['id'], $browser['label'], $browser['id'] === $active_id);
                    $panes[]             = $this->render_link_pane($browser['id'], $browser['heading'], $browser['items'], $browser['id'] === $active_id);
                }

                if ('' !== $integration_buttons) {
                    $left_sections[] = $this->render_left_button_section(__('Detected Tools', 'etch-central'), $integration_buttons);
                }
            }
        }

        $resource_buttons = '';
        if (!empty($settings['enabled_menus']['resources'])) {
            $pane_id = 'etch-central-pane-etch-resources';
            $items   = [];

            foreach ((array) ($settings['resource_links'] ?? $this->resource_links()) as $link) {
                $items[] = [
                    'label'  => (string) $link['label'],
                    'url'    => (string) $link['url'],
                    'target' => '_blank',
                ];
            }

            if ($items) {
                if ('' === $active_id) {
                    $active_id = $pane_id;
                }

                $resource_buttons .= $this->render_left_panel_button($pane_id, __('Etch Resources', 'etch-central'), $pane_id === $active_id);
                $panes[]          = $this->render_link_pane($pane_id, __('Etch Resources', 'etch-central'), $items, $pane_id === $active_id);
            }
        }

        if (!empty($settings['enabled_menus']['shortcuts']) || !empty($settings['enabled_menus']['community'])) {
            $pane_id = 'etch-central-pane-shortcuts';
            $items   = [];

            foreach ((array) ($settings['shortcut_links'] ?? $settings['community_links'] ?? []) as $link) {
                if (empty($link['label']) || empty($link['url'])) {
                    continue;
                }

                $items[] = [
                    'label'  => (string) $link['label'],
                    'url'    => (string) $link['url'],
                    'target' => '_blank',
                ];
            }

            if ($items) {
                if ('' === $active_id) {
                    $active_id = $pane_id;
                }

                $resource_buttons .= $this->render_left_panel_button($pane_id, __('Quick Links', 'etch-central'), $pane_id === $active_id);
                $panes[]          = $this->render_link_pane($pane_id, __('Quick Links', 'etch-central'), $items, $pane_id === $active_id);
            }
        }

        if ('' !== $resource_buttons) {
            $left_sections[] = $this->render_left_button_section(__('Resources', 'etch-central'), $resource_buttons);
        }

        $left_sections[] = $this->render_left_link_section(
            __('Administration', 'etch-central'),
            [
                [
                    'label' => __('Etch Central Settings', 'etch-central'),
                    'url'   => admin_url('admin.php?page=etch-central'),
                ],
            ]
        );

        if (!$panes) {
            $panes[] = sprintf(
                '<section class="etch-central-panel__pane is-active" role="region"><h3 class="etch-central-panel__pane-heading">%s</h3><p class="etch-central-panel__empty">%s</p></section>',
                esc_html__('Etch Central', 'etch-central'),
                esc_html__('Select an item from the left column.', 'etch-central')
            );
        }

        return sprintf(
            '<div class="etch-central-panel" role="group" aria-label="%s"><div class="etch-central-panel__nav">%s</div><div class="etch-central-panel__content">%s</div></div>',
            esc_attr__('Etch Central tools', 'etch-central'),
            implode('', $left_sections),
            implode('', $panes)
        );
    }


    private function current_content_items(?array $current_context, string $content_url, ?array $template_data): array {
        $items       = [];
        $homepage_id = (int) get_option('page_on_front');

        if (is_admin() && 'page' === get_option('show_on_front') && $homepage_id > 0) {
            $items[] = [
                'label' => __('Launch Etch', 'etch-central'),
                'url'   => $this->etch_editor_url($homepage_id, 0),
            ];
        }

        if ($content_url && is_singular()) {
            $post_id       = (int) get_queried_object_id();
            $title         = get_the_title($post_id);
            $content_label = $title ?: __('this content', 'etch-central');

            $items[] = [
                'label' => sprintf(
                    /* translators: 1: Post type label, 2: Post title. */
                    __('Current %1$s: %2$s', 'etch-central'),
                    $this->current_post_type_label($post_id),
                    $content_label
                ),
                'url'   => $content_url,
            ];
        }

        if ($template_data && $current_context) {
            $items[] = [
                'label' => sprintf(
                    /* translators: %s: Template label. */
                    __('Current Template: %s', 'etch-central'),
                    (string) $template_data['label']
                ),
                'url'   => $this->etch_editor_url((int) $template_data['id'], (int) $current_context['original_post_id']),
            ];
        }

        return $items;
    }

    private function enabled_content_types(array $settings): array {
        $enabled = [];
        $allowed = $this->settings->public_post_type_names();

        foreach ((array) ($settings['content_types'] ?? []) as $post_type) {
            $post_type = sanitize_key((string) $post_type);

            if ($post_type && in_array($post_type, $allowed, true)) {
                $enabled[] = $post_type;
            }
        }

        return array_values(array_unique($enabled));
    }

    private function render_left_link_section(string $heading, array $items): string {
        if (!$items) {
            return '';
        }

        $links = '';
        foreach ($items as $item) {
            $links .= $this->render_panel_link((string) $item['label'], (string) $item['url'], (string) ($item['target'] ?? ''));
        }

        return sprintf(
            '<section class="etch-central-panel__section etch-central-panel__section--nav"><h3 class="etch-central-panel__heading">%s</h3><ul class="etch-central-panel__list">%s</ul></section>',
            esc_html($heading),
            $links
        );
    }

    private function render_left_button_section(string $heading, string $buttons): string {
        if ('' === $buttons) {
            return '';
        }

        return sprintf(
            '<section class="etch-central-panel__section etch-central-panel__section--nav"><h3 class="etch-central-panel__heading">%s</h3><div class="etch-central-panel__nav-buttons">%s</div></section>',
            esc_html($heading),
            $buttons
        );
    }

    private function render_left_panel_button(string $target_id, string $label, bool $active = false): string {
        return sprintf(
            '<button type="button" class="etch-central-panel__button%s" data-etch-central-pane-trigger="%s" aria-controls="%s" aria-expanded="%s">%s</button>',
            $active ? ' is-active' : '',
            esc_attr($target_id),
            esc_attr($target_id),
            $active ? 'true' : 'false',
            esc_html($label)
        );
    }

    private function render_link_pane(string $id, string $heading, array $items, bool $active = false): string {
        $links = '';
        foreach ($items as $item) {
            $links .= $this->render_panel_link((string) $item['label'], (string) $item['url'], (string) ($item['target'] ?? ''));
        }

        if ('' === $links) {
            $links = '<li class="etch-central-panel__item etch-central-panel__empty">' . esc_html__('No links found', 'etch-central') . '</li>';
        }

        return sprintf(
            '<section id="%s" class="etch-central-panel__pane%s" role="region" aria-label="%s"%s><h3 class="etch-central-panel__pane-heading">%s</h3><ul class="etch-central-panel__list etch-central-panel__list--links">%s</ul></section>',
            esc_attr($id),
            $active ? ' is-active' : '',
            esc_attr($heading),
            $active ? '' : ' hidden',
            esc_html($heading),
            $links
        );
    }

    private function render_action_link(string $label, string $aria_label, string $url): string {
        return sprintf(
            '<a class="etch-central-browser__action" href="%s" aria-label="%s" title="%s">%s</a>',
            esc_url($url),
            esc_attr($aria_label),
            esc_attr($aria_label),
            esc_html($label)
        );
    }

    private function render_panel_link(string $label, string $url, string $target = '', string $wp_editor_url = ''): string {
        $target_attr = '_blank' === $target ? ' target="_blank" rel="noopener noreferrer"' : '';
        $wp_attr     = '';

        if ('' !== $wp_editor_url) {
            $wp_attr = sprintf(
                ' data-etch-central-wp-editor-url="%s" title="%s"',
                esc_url($wp_editor_url),
                esc_attr__('Open in Etch. Command/Ctrl + Option/Alt-click opens the WordPress editor.', 'etch-central')
            );
        }

        return sprintf(
            '<li class="etch-central-panel__item"><a class="etch-central-panel__link" href="%s"%s%s>%s</a></li>',
            esc_url($url),
            $target_attr,
            $wp_attr,
            esc_html($label)
        );
    }

    private function post_type_browser_data(string $post_type): ?array {
        $post_type_object = get_post_type_object($post_type);

        if (!$post_type_object) {
            return null;
        }

        $count           = wp_count_posts($post_type);
        $published_count = 'attachment' === $post_type && isset($count->inherit) ? (int) $count->inherit : (isset($count->publish) ? (int) $count->publish : 0);
        $browser_id      = 'etch-central-pane-' . sanitize_key($post_type);

        $posts = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => 200,
            'post_status'    => 'attachment' === $post_type ? 'inherit' : 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        $actions = $this->post_type_action_links($post_type, $post_type_object);
        $items   = '';

        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }

            $items .= $this->render_panel_link($post->post_title ?: $post->post_name, $this->etch_editor_url((int) $post->ID, 0), '', $this->wp_editor_url((int) $post->ID));
        }

        if ('' === $items) {
            $items = '<li class="etch-central-panel__item etch-central-panel__empty">' . esc_html__('No published items found', 'etch-central') . '</li>';
        }

        return [
            'id'           => $browser_id,
            'label'        => sprintf(
                /* translators: 1: Post type plural label, 2: Published item count. */
                __('%1$s (%2$d)', 'etch-central'),
                (string) $post_type_object->labels->name,
                $published_count
            ),
            'heading'      => (string) $post_type_object->labels->name,
            'search_label' => sprintf(
                /* translators: %s: Post type plural label. */
                __('Search %s', 'etch-central'),
                strtolower((string) $post_type_object->labels->name)
            ),
            'actions'      => $actions,
            'items'        => $items,
        ];
    }


    private function post_type_action_links(string $post_type, object $post_type_object): string {
        $links = '';
        $label = (string) $post_type_object->labels->name;

        if (current_user_can('edit_others_posts')) {
            $links .= $this->render_action_link(__('All', 'etch-central'), sprintf(__('All %s', 'etch-central'), $label), $this->all_items_url($post_type));
            $links .= $this->render_action_link(__('Mine', 'etch-central'), sprintf(__('My %s', 'etch-central'), $label), $this->my_items_url($post_type));
        } else {
            $links .= $this->render_action_link(__('Mine', 'etch-central'), sprintf(__('My %s', 'etch-central'), $label), $this->my_items_url($post_type));
        }

        if ($this->can_create_post_type($post_type_object)) {
            $links .= $this->render_action_link(__('New', 'etch-central'), $this->add_new_label($post_type, $label), $this->add_new_url($post_type));
        }

        return $links;
    }

    private function all_items_url(string $post_type): string {
        if ('post' === $post_type) {
            return admin_url('edit.php');
        }

        if ('attachment' === $post_type) {
            return admin_url('upload.php');
        }

        return admin_url('edit.php?post_type=' . $post_type);
    }

    private function my_items_url(string $post_type): string {
        if ('post' === $post_type) {
            return admin_url('edit.php?author=' . get_current_user_id());
        }

        if ('attachment' === $post_type) {
            return admin_url('upload.php?author=' . get_current_user_id());
        }

        return admin_url('edit.php?post_type=' . $post_type . '&author=' . get_current_user_id());
    }

    private function add_new_url(string $post_type): string {
        if ('post' === $post_type) {
            return admin_url('post-new.php');
        }

        if ('attachment' === $post_type) {
            return admin_url('media-new.php');
        }

        return admin_url('post-new.php?post_type=' . $post_type);
    }

    private function can_create_post_type(object $post_type_object): bool {
        if ('attachment' === $post_type_object->name) {
            return current_user_can('upload_files');
        }

        $capability = $post_type_object->cap->create_posts ?? 'edit_posts';

        return current_user_can($capability);
    }

    private function add_new_label(string $post_type, string $plural_label): string {
        return match ($post_type) {
            'attachment' => __('Add New Media', 'etch-central'),
            default      => sprintf(__('Add New %s', 'etch-central'), $plural_label),
        };
    }

    private function templates_browser_data(): array {
        $templates = get_posts([
            'post_type'      => 'wp_template',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        $items = '';
        foreach ($templates as $template) {
            if (!$template instanceof WP_Post) {
                continue;
            }

            $items .= $this->render_panel_link($template->post_title ?: $template->post_name, $this->etch_editor_url((int) $template->ID, 0), '', $this->wp_editor_url((int) $template->ID));
        }

        if ('' === $items) {
            $items = '<li class="etch-central-panel__item etch-central-panel__empty">' . esc_html__('No templates found', 'etch-central') . '</li>';
        }

        return [
            'id'           => 'etch-central-pane-templates',
            'label'        => __('Templates', 'etch-central'),
            'heading'      => __('Templates', 'etch-central'),
            'search_label' => __('Search templates', 'etch-central'),
            'items'        => $items,
        ];
    }

    private function patterns_browser_data(): array {
        $patterns = get_posts([
            'post_type'      => 'wp_block',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        $items = '';
        foreach ($patterns as $pattern) {
            if (!$pattern instanceof WP_Post) {
                continue;
            }

            $sync_label = $this->pattern_sync_label($pattern);
            $sync_abbr  = $this->pattern_sync_abbr($pattern);
            $label      = $pattern->post_title ?: $pattern->post_name;

            $items .= sprintf(
                '<li class="etch-central-panel__item"><a class="etch-central-panel__link etch-central-panel__link--pattern" href="%s" data-etch-central-wp-editor-url="%s" title="%s"><span>%s</span><span class="etch-central-panel__badge" title="%s" aria-label="%s">%s</span></a></li>',
                esc_url($this->etch_editor_url((int) $pattern->ID, 0)),
                esc_url($this->wp_editor_url((int) $pattern->ID)),
                esc_attr__('Open in Etch. Command/Ctrl + Option/Alt-click opens the WordPress editor.', 'etch-central'),
                esc_html($label),
                esc_attr($sync_label),
                esc_attr($sync_label),
                esc_html($sync_abbr)
            );
        }

        if ('' === $items) {
            $items = '<li class="etch-central-panel__item etch-central-panel__empty">' . esc_html__('No patterns found', 'etch-central') . '</li>';
        }

        return [
            'id'           => 'etch-central-pane-patterns',
            'label'        => __('Patterns', 'etch-central'),
            'heading'      => __('Patterns', 'etch-central'),
            'search_label' => __('Search patterns', 'etch-central'),
            'items'        => $items,
        ];
    }

    private function render_browser_pane(array $browser, bool $active = false): string {
        $actions = !empty($browser['actions'])
            ? '<div class="etch-central-browser__actions" aria-label="' . esc_attr__('Post type actions', 'etch-central') . '">' . (string) $browser['actions'] . '</div>'
            : '';

        return sprintf(
            '<section id="%s" class="etch-central-panel__pane%s" role="region" aria-label="%s"%s><h3 class="etch-central-panel__pane-heading">%s</h3><div class="etch-central-browser__sticky"><label class="etch-central-browser__search" for="%s-search"><span class="screen-reader-text">%s</span><input id="%s-search" class="etch-central-browser__input" type="search" autocomplete="off" placeholder="%s" data-etch-central-browser-search></label>%s</div><ul class="etch-central-panel__list etch-central-panel__list--scroll" data-etch-central-browser-results>%s</ul></section>',
            esc_attr((string) $browser['id']),
            $active ? ' is-active' : '',
            esc_attr((string) $browser['heading']),
            $active ? '' : ' hidden',
            esc_html((string) $browser['heading']),
            esc_attr((string) $browser['id']),
            esc_html((string) $browser['search_label']),
            esc_attr((string) $browser['id']),
            esc_attr((string) $browser['search_label']),
            $actions,
            (string) $browser['items']
        );
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
            return [
                'original_post_id' => 0,
                'candidates'       => ['404', 'index'],
            ];
        }

        if (is_search()) {
            return [
                'original_post_id' => 0,
                'candidates'       => ['search', 'index'],
            ];
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

    private function current_post_type_label(int $post_id): string {
        $post_type = get_post_type($post_id);

        if (!$post_type) {
            return __('Content', 'etch-central');
        }

        $post_type_object = get_post_type_object($post_type);

        if (!$post_type_object || empty($post_type_object->labels->singular_name)) {
            return __('Content', 'etch-central');
        }

        return (string) $post_type_object->labels->singular_name;
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

    private function integration_browser_data(array $enabled_tools = []): array {
        $items = [];

        if (!empty($enabled_tools['acf']) && $this->is_tool_active(['advanced-custom-fields/acf.php', 'advanced-custom-fields-pro/acf.php'], ['ACF', 'ACF\ACF', 'acf_plugin'], ['acf', 'acf_get_field_groups'], ['ACF_VERSION'])) {
            $items[] = [
                'id'      => 'etch-central-pane-acf',
                'label'   => 'ACF',
                'heading' => 'Advanced Custom Fields',
                'items'   => [
                    ['label' => __('Field Groups', 'etch-central'), 'url' => admin_url('edit.php?post_type=acf-field-group')],
                    ['label' => __('Post Types', 'etch-central'), 'url' => admin_url('edit.php?post_type=acf-post-type')],
                    ['label' => __('Taxonomies', 'etch-central'), 'url' => admin_url('edit.php?post_type=acf-taxonomy')],
                    ['label' => __('Options Pages', 'etch-central'), 'url' => admin_url('edit.php?post_type=acf-ui-options-page')],
                ],
            ];
        }

        if (!empty($enabled_tools['metabox']) && $this->is_tool_active(['meta-box/meta-box.php', 'meta-box-aio/meta-box-aio.php', 'meta-box-aio/meta-box-aio.php'], ['RWMB_Loader', 'RWMB_Core'], ['rwmb_meta'], ['RWMB_VER'])) {
            $items[] = [
                'id'      => 'etch-central-pane-metabox',
                'label'   => 'Meta Box',
                'heading' => 'Meta Box',
                'items'   => [
                    ['label' => __('Custom Fields', 'etch-central'), 'url' => admin_url('edit.php?post_type=meta-box')],
                    ['label' => __('Post Types', 'etch-central'), 'url' => admin_url('edit.php?post_type=mb-post-type')],
                    ['label' => __('Taxonomies', 'etch-central'), 'url' => admin_url('edit.php?post_type=mb-taxonomy')],
                    ['label' => __('Settings Pages', 'etch-central'), 'url' => admin_url('edit.php?post_type=mb-settings-page')],
                    ['label' => __('Relationships', 'etch-central'), 'url' => admin_url('edit.php?post_type=mb-relationship')],
                ],
            ];
        }

        if (!empty($enabled_tools['acpt']) && $this->is_tool_active(['advanced-custom-post-type/acpt.php', 'acpt/acpt.php'], ['ACPT_Loader', 'ACPT\Core\Plugin'], [], ['ACPT_PLUGIN_VERSION'])) {
            $items[] = [
                'id'      => 'etch-central-pane-acpt',
                'label'   => 'ACPT',
                'heading' => 'ACPT',
                'items'   => [
                    ['label' => __('Dashboard', 'etch-central'), 'url' => admin_url('admin.php?page=acpt')],
                    ['label' => __('Post Types', 'etch-central'), 'url' => admin_url('admin.php?page=acpt_cpt')],
                    ['label' => __('Taxonomies', 'etch-central'), 'url' => admin_url('admin.php?page=acpt_tax')],
                    ['label' => __('Meta Fields', 'etch-central'), 'url' => admin_url('admin.php?page=acpt_meta')],
                ],
            ];
        }

        if (!empty($enabled_tools['jetengine']) && $this->is_tool_active(['jet-engine/jet-engine.php'], ['Jet_Engine'], [], ['JET_ENGINE_VERSION'])) {
            $items[] = [
                'id'      => 'etch-central-pane-jetengine',
                'label'   => 'JetEngine',
                'heading' => 'JetEngine',
                'items'   => [
                    ['label' => __('Dashboard', 'etch-central'), 'url' => admin_url('admin.php?page=jet-engine')],
                    ['label' => __('Post Types', 'etch-central'), 'url' => admin_url('admin.php?page=jet-engine-cpt')],
                    ['label' => __('Taxonomies', 'etch-central'), 'url' => admin_url('admin.php?page=jet-engine-tax')],
                    ['label' => __('Meta Boxes', 'etch-central'), 'url' => admin_url('admin.php?page=jet-engine-meta-boxes')],
                    ['label' => __('Relations', 'etch-central'), 'url' => admin_url('admin.php?page=jet-engine-relations')],
                ],
            ];
        }

        if (!empty($enabled_tools['wpcodebox']) && $this->is_tool_active(['wpcodebox/wpcodebox.php', 'wpcodebox2/wpcodebox.php', 'wpcodebox2/wpcodebox2.php', 'wpcodebox/wpcodebox2.php'], ['WPCodeBox', 'Wpcb\Plugin', 'WPCodeBox2\Plugin'], [], ['WPCB_VERSION', 'WPCB2_VERSION'])) {
            $items[] = [
                'id'      => 'etch-central-pane-wpcodebox',
                'label'   => 'WPCodeBox',
                'heading' => 'WPCodeBox',
                'items'   => [
                    ['label' => __('Code Snippets', 'etch-central'), 'url' => admin_url('admin.php?page=wpcodebox2')],
                ],
            ];
        }

        return $items;
    }

    private function is_tool_active(array $plugin_files, array $classes = [], array $functions = [], array $constants = []): bool {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                return true;
            }
        }

        foreach ($functions as $function) {
            if (function_exists($function)) {
                return true;
            }
        }

        foreach ($constants as $constant) {
            if (defined($constant)) {
                return true;
            }
        }

        $active_plugins = (array) get_option('active_plugins', []);

        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, array_keys((array) get_site_option('active_sitewide_plugins', [])));
        }

        foreach ($active_plugins as $active_plugin) {
            foreach ($plugin_files as $plugin_file) {
                if ($active_plugin === $plugin_file || str_contains((string) $active_plugin, trim($plugin_file, '/'))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resource_links(): array {
        return [
            [
                'label' => 'Etch Documentation',
                'url'   => 'https://docs.etchwp.com/',
            ],
            [
                'label' => 'Etch Patterns',
                'url'   => 'https://patterns.etchwp.com/',
            ],
            [
                'label' => 'Etch Circle Community',
                'url'   => 'https://community.etchwp.com/',
            ],
            [
                'label' => 'EtchWP Homepage',
                'url'   => 'https://etchwp.com/?aff=77d60d8c',
            ],
        ];
    }

    private function wp_editor_url(int $post_id): string {
        $url = get_edit_post_link($post_id, 'raw');

        return $url ? (string) $url : admin_url('post.php?post=' . $post_id . '&action=edit');
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
