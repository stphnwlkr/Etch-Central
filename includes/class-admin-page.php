<?php
/**
 * Settings page UI for Etch Central.
 *
 * @package EtchCentral
 */

declare(strict_types=1);

namespace Etch_Central;

use WP_Roles;

if (!defined('ABSPATH')) {
    exit;
}

final class Admin_Page {
    private Settings $settings;

    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    public function register(): void {
        add_menu_page(
            __('Etch Central', 'etch-central'),
            __('Etch Central', 'etch-central'),
            'manage_options',
            Settings::PAGE_SLUG,
            [$this, 'render'],
            'dashicons-superhero',
            58
        );

        add_submenu_page(
            Settings::PAGE_SLUG,
            __('Etch Central Settings', 'etch-central'),
            __('Settings', 'etch-central'),
            'manage_options',
            Settings::PAGE_SLUG,
            [$this, 'render']
        );

        add_submenu_page(
            Settings::PAGE_SLUG,
            __('Etch Central Resources', 'etch-central'),
            __('Resources', 'etch-central'),
            'manage_options',
            Settings::PAGE_SLUG . '-resources',
            [$this, 'render_resources']
        );
    }

    public function ajax_save_appearance(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'etch-central')], 403);
        }

        check_ajax_referer('etch_central_admin_appearance', 'nonce');
        $appearance = isset($_POST['appearance']) ? sanitize_key((string) wp_unslash($_POST['appearance'])) : 'auto';
        if (!in_array($appearance, ['auto', 'light', 'dark'], true)) {
            wp_send_json_error(['message' => __('Invalid appearance.', 'etch-central')], 400);
        }

        $settings = $this->settings->get();
        $settings['admin_appearance'] = $appearance;
        update_option(Settings::OPTION_KEY, $settings);
        wp_send_json_success(['appearance' => $appearance]);
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'etch-central'));
        }
        $settings = $this->settings->get();
        $roles    = wp_roles();
        $tools    = $this->detected_tools();
        ?>
        <div class="wrap etch-central-admin etch-central-admin--<?php echo esc_attr((string) $settings['admin_appearance']); ?>" data-etch-central-admin>
            <?php $this->render_shell_start('settings', $settings); ?>
            <form method="post" action="options.php" class="etch-central-admin__grid">
                <?php settings_fields('etch_central_settings_group'); ?>
                <input type="hidden" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[settings_screen]" value="settings">

                <section class="etch-central-panel etch-central-panel--wide" aria-labelledby="etch-central-detected-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">⌘</span><div><h2 id="etch-central-detected-title"><?php esc_html_e('Detected Tools', 'etch-central'); ?></h2><p><?php esc_html_e('This section is enabled by default. Only detected tools can be enabled and shown in the admin bar.', 'etch-central'); ?></p></div></div>
                    <label class="etch-central-toggle etch-central-toggle--primary"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[enabled_menus][integrations]" value="1" <?php checked(!empty($settings['enabled_menus']['integrations'])); ?>><span><?php esc_html_e('Show Detected Tools section', 'etch-central'); ?></span></label>
                    <div class="etch-central-tool-grid">
                        <?php foreach ($tools as $tool) : $is_active = !empty($tool['active']); ?>
                            <label class="etch-central-tool-card <?php echo $is_active ? 'is-detected' : 'is-missing'; ?>">
                                <input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[detected_tools_enabled][<?php echo esc_attr($tool['key']); ?>]" value="1" <?php checked($is_active && !empty($settings['detected_tools_enabled'][$tool['key']])); ?> <?php disabled(!$is_active); ?>>
                                <span class="etch-central-tool-card__mark" aria-hidden="true"></span>
                                <span class="etch-central-tool-card__body"><strong><?php echo esc_html($tool['label']); ?></strong><em><?php echo $is_active ? esc_html__('Detected', 'etch-central') : esc_html__('Not detected', 'etch-central'); ?></em></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="etch-central-panel" aria-labelledby="etch-central-display-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">◐</span><div><h2 id="etch-central-display-title"><?php esc_html_e('Appearance', 'etch-central'); ?></h2><p><?php esc_html_e('Switch instantly. This preference saves automatically.', 'etch-central'); ?></p></div></div>
                    <div class="etch-central-segmented" role="group" aria-label="<?php esc_attr_e('Settings appearance', 'etch-central'); ?>" data-etch-central-appearance-control>
                        <?php foreach (['auto' => __('Auto', 'etch-central'), 'light' => __('Light', 'etch-central'), 'dark' => __('Dark', 'etch-central')] as $appearance_key => $appearance_label) : ?>
                            <label><input type="radio" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[admin_appearance]" value="<?php echo esc_attr($appearance_key); ?>" <?php checked($settings['admin_appearance'], $appearance_key); ?>><span><?php echo esc_html($appearance_label); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <p class="etch-central-save-status" data-etch-central-save-status aria-live="polite"></p>
                </section>

                <section class="etch-central-panel" aria-labelledby="etch-central-menus-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">☰</span><div><h2 id="etch-central-menus-title"><?php esc_html_e('Admin Bar Menus', 'etch-central'); ?></h2><p><?php esc_html_e('Choose which Etch Central sections appear in the admin bar.', 'etch-central'); ?></p></div></div>
                    <?php foreach ((array) $settings['enabled_menus'] as $key => $enabled) : ?>
                        <?php if (in_array($key, ['integrations', 'community', 'resources', 'shortcuts'], true)) { continue; } ?>
                        <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[enabled_menus][<?php echo esc_attr((string) $key); ?>]" value="1" <?php checked($enabled); ?>><span><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $key))); ?></span></label>
                    <?php endforeach; ?>
                    <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[enabled_menus][resources]" value="1" <?php checked(!empty($settings['enabled_menus']['resources'])); ?>><span><?php esc_html_e('Etch Resources', 'etch-central'); ?></span></label>
                    <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[enabled_menus][shortcuts]" value="1" <?php checked(!empty($settings['enabled_menus']['shortcuts'])); ?>><span><?php esc_html_e('Quick Links', 'etch-central'); ?></span></label>
                </section>

                <section class="etch-central-panel" aria-labelledby="etch-central-content-types-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">▦</span><div><h2 id="etch-central-content-types-title"><?php esc_html_e('Content Type Menus', 'etch-central'); ?></h2><p><?php esc_html_e('Enable pages, posts, media, and public custom post types.', 'etch-central'); ?></p></div></div>
                    <div class="etch-central-check-list">
                        <?php foreach ($this->post_type_options() as $post_type => $label) : ?>
                            <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[content_types][]" value="<?php echo esc_attr((string) $post_type); ?>" <?php checked(in_array($post_type, (array) $settings['content_types'], true)); ?>><span><?php echo esc_html($label); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="etch-central-panel" aria-labelledby="etch-central-cleanup-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">✦</span><div><h2 id="etch-central-cleanup-title"><?php esc_html_e('Admin Cleanup', 'etch-central'); ?></h2><p><?php esc_html_e('Optional cleanup features merged from Dynamic Post Type Menu.', 'etch-central'); ?></p></div></div>
                    <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[remove_default_posts]" value="1" <?php checked(!empty($settings['remove_default_posts'])); ?>><span><?php esc_html_e('Remove the built-in Posts menu and block direct access to Posts admin screens', 'etch-central'); ?></span></label>
                    <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[hide_wp_new_menu]" value="1" <?php checked(!empty($settings['hide_wp_new_menu'])); ?>><span><?php esc_html_e('Hide the WordPress + New admin bar menu', 'etch-central'); ?></span></label>
                    <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[cleanup_on_deactivation]" value="1" <?php checked(!empty($settings['cleanup_on_deactivation'])); ?>><span><?php esc_html_e('Remove all Etch Central settings when deactivated', 'etch-central'); ?></span></label>
                </section>

                <section class="etch-central-panel" aria-labelledby="etch-central-roles-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">◌</span><div><h2 id="etch-central-roles-title"><?php esc_html_e('Allowed Roles', 'etch-central'); ?></h2><p><?php esc_html_e('Administrators are selected by default. Choose additional roles that may see Etch Central in the admin bar.', 'etch-central'); ?></p></div></div>
                    <div class="etch-central-check-list">
                        <?php if ($roles instanceof WP_Roles) : foreach ($roles->roles as $role_key => $role_data) : ?>
                            <label class="etch-central-toggle"><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[allowed_roles][]" value="<?php echo esc_attr((string) $role_key); ?>" <?php checked(in_array($role_key, (array) $settings['allowed_roles'], true)); ?>><span><?php echo esc_html(translate_user_role($role_data['name'])); ?></span></label>
                        <?php endforeach; endif; ?>
                    </div>
                </section>

                <div class="etch-central-admin__actions"><?php submit_button(__('Save Settings', 'etch-central')); ?></div>
            </form>
            <?php $this->render_shell_end(); ?>
        </div>
        <?php
    }

    public function render_resources(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'etch-central'));
        }
        $settings = $this->settings->get();
        ?>
        <div class="wrap etch-central-admin etch-central-admin--<?php echo esc_attr((string) $settings['admin_appearance']); ?>" data-etch-central-admin>
            <?php $this->render_shell_start('resources', $settings); ?>
            <form method="post" action="options.php" class="etch-central-admin__grid">
                <?php settings_fields('etch_central_settings_group'); ?>
                <input type="hidden" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[settings_screen]" value="resources">
                <section class="etch-central-panel etch-central-panel--wide" aria-labelledby="etch-central-resources-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">↗</span><div><h2 id="etch-central-resources-title"><?php esc_html_e('Etch Resources', 'etch-central'); ?></h2><p><?php esc_html_e('Prepopulated with standard Etch resources. Edit, remove, reorder, or add more links.', 'etch-central'); ?></p></div></div>
                    <?php $this->render_links_manager('resource_links', (array) $settings['resource_links'], __('Etch resource links', 'etch-central')); ?>
                </section>
                <section class="etch-central-panel etch-central-panel--wide" aria-labelledby="etch-central-quick-links-title">
                    <div class="etch-central-panel__header"><span class="etch-central-panel__icon" aria-hidden="true">＋</span><div><h2 id="etch-central-quick-links-title"><?php esc_html_e('Quick Links', 'etch-central'); ?></h2><p><?php esc_html_e('Global admin-managed quick links. These are not user-specific shortcuts.', 'etch-central'); ?></p></div></div>
                    <?php $this->render_links_manager('shortcut_links', (array) ($settings['shortcut_links'] ?? []), __('Quick links', 'etch-central')); ?>
                </section>
                <div class="etch-central-admin__actions"><?php submit_button(__('Save Resources', 'etch-central')); ?></div>
            </form>
            <?php $this->render_shell_end(); ?>
        </div>
        <?php
    }

    private function render_shell_start(string $active, array $settings): void { ?>
        <div class="etch-central-shell">
            <aside class="etch-central-sidebar" aria-label="<?php esc_attr_e('Etch Central sections', 'etch-central'); ?>">
                <div class="etch-central-brand"><span class="etch-central-brand__icon" aria-hidden="true"><?php $this->render_icon(); ?></span><span>Etch Central</span></div>
                <nav class="etch-central-sidebar__nav">
                    <a class="<?php echo 'settings' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=' . Settings::PAGE_SLUG)); ?>"><span aria-hidden="true">⚙</span><?php esc_html_e('Settings', 'etch-central'); ?></a>
                    <a class="<?php echo 'resources' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=' . Settings::PAGE_SLUG . '-resources')); ?>"><span aria-hidden="true">↗</span><?php esc_html_e('Resources', 'etch-central'); ?></a>
                </nav>
                <div class="etch-central-sidebar__version">v<?php echo esc_html(ETCH_CENTRAL_VERSION); ?></div>
            </aside>
            <main class="etch-central-main">
                <header class="etch-central-main__header"><div><p class="etch-central-admin__eyebrow"><?php esc_html_e('WordPress admin bar tools', 'etch-central'); ?></p><h1><?php echo 'resources' === $active ? esc_html__('Resources', 'etch-central') : esc_html__('Settings', 'etch-central'); ?></h1><p><?php esc_html_e('Manage quick access to Etch templates, patterns, content, tools, resources, and links.', 'etch-central'); ?></p></div></header>
    <?php }

    private function render_shell_end(): void { ?>
            </main>
        </div>
    <?php }

    private function render_links_manager(string $field_key, array $links, string $label): void {
        $links[] = ['label' => '', 'url' => '']; ?>
        <div class="etch-central-links" role="group" aria-label="<?php echo esc_attr($label); ?>" data-etch-central-sortable data-etch-central-repeater="<?php echo esc_attr($field_key); ?>">
            <?php foreach ($links as $index => $link) : ?>
                <div class="etch-central-link-row" draggable="true" data-etch-central-row tabindex="-1"><div class="etch-central-link-row__tools" aria-label="<?php esc_attr_e('Reorder this link', 'etch-central'); ?>"><button type="button" class="etch-central-drag-handle" aria-label="<?php esc_attr_e('Drag to reorder', 'etch-central'); ?>" title="<?php esc_attr_e('Drag to reorder', 'etch-central'); ?>">☰</button><button type="button" class="etch-central-move-button" data-etch-central-move="up"><?php esc_html_e('Up', 'etch-central'); ?></button><button type="button" class="etch-central-move-button" data-etch-central-move="down"><?php esc_html_e('Down', 'etch-central'); ?></button><button type="button" class="etch-central-remove-button" data-etch-central-remove><?php esc_html_e('Remove', 'etch-central'); ?></button></div><label><span><?php esc_html_e('Label', 'etch-central'); ?></span><input type="text" data-etch-central-field="label" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[<?php echo esc_attr($field_key); ?>][<?php echo esc_attr((string) $index); ?>][label]" value="<?php echo esc_attr((string) ($link['label'] ?? '')); ?>"></label><label><span><?php esc_html_e('URL', 'etch-central'); ?></span><input type="url" data-etch-central-field="url" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[<?php echo esc_attr($field_key); ?>][<?php echo esc_attr((string) $index); ?>][url]" value="<?php echo esc_url((string) ($link['url'] ?? '')); ?>"></label></div>
            <?php endforeach; ?>
        </div><p><button type="button" class="button button-secondary" data-etch-central-add-row="<?php echo esc_attr($field_key); ?>"><?php esc_html_e('Add link', 'etch-central'); ?></button></p>
    <?php }

    private function post_type_options(): array {
        $post_types = get_post_types(['show_ui' => true], 'objects');
        $excluded_post_types = array_merge(Settings::feature_post_type_slugs(), ['wp_block','wp_navigation','wp_template','wp_template_part','wp_global_styles','wp_font_family','wp_font_face','custom_css','customize_changeset','oembed_cache','user_request','wp_changeset','wp_pattern_category','wp_template_part_area']);
        uasort($post_types, static fn($a, $b): int => strcasecmp((string) $a->labels->name, (string) $b->labels->name));
        $options = [];
        foreach ($post_types as $post_type => $post_type_object) {
            $is_allowed_builtin = in_array($post_type, ['post', 'page', 'attachment'], true);
            if (in_array($post_type, $excluded_post_types, true) || (!$is_allowed_builtin && !empty($post_type_object->_builtin))) { continue; }
            $options[(string) $post_type] = (string) $post_type_object->labels->name;
        }
        return $options;
    }

    private function detected_tools(): array {
        return [
            [
                'key' => 'acf',
                'label'  => 'Advanced Custom Fields',
                'active' => $this->is_tool_active(['advanced-custom-fields/acf.php', 'advanced-custom-fields-pro/acf.php'], ['ACF', 'ACF\\ACF', 'acf_plugin'], ['acf', 'acf_get_field_groups'], ['ACF_VERSION']),
            ],
            [
                'key' => 'metabox',
                'label'  => 'Meta Box',
                'active' => $this->is_tool_active(['meta-box/meta-box.php', 'meta-box-aio/meta-box-aio.php'], ['RWMB_Loader', 'RWMB_Core'], ['rwmb_meta'], ['RWMB_VER']),
            ],
            [
                'key' => 'acpt',
                'label'  => 'ACPT',
                'active' => $this->is_tool_active(['advanced-custom-post-type/acpt.php', 'acpt/acpt.php'], ['ACPT_Loader', 'ACPT\\Core\\Plugin'], [], ['ACPT_PLUGIN_VERSION']),
            ],
            [
                'key' => 'jetengine',
                'label'  => 'JetEngine',
                'active' => $this->is_tool_active(['jet-engine/jet-engine.php'], ['Jet_Engine'], [], ['JET_ENGINE_VERSION']),
            ],
            [
                'key' => 'wpcodebox',
                'label'  => 'WPCodeBox',
                'active' => $this->is_tool_active(['wpcodebox/wpcodebox.php', 'wpcodebox2/wpcodebox.php', 'wpcodebox2/wpcodebox2.php', 'wpcodebox/wpcodebox2.php'], ['WPCodeBox', 'Wpcb\\Plugin', 'WPCodeBox2\\Plugin'], [], ['WPCB_VERSION', 'WPCB2_VERSION']),
            ],
        ];
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

    private function render_icon(): void {
        ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" focusable="false">
            <path opacity="0.4" d="M21.5 12C21.5 13.3807 20.3807 14.5 19 14.5C17.6193 14.5 16.5 13.3807 16.5 12C16.5 10.6193 17.6193 9.5 19 9.5C20.3807 9.5 21.5 10.6193 21.5 12Z" fill="currentColor"></path>
            <path opacity="0.4" d="M13.5 4C13.5 4.82843 12.8284 5.5 12 5.5C11.1716 5.5 10.5 4.82843 10.5 4C10.5 3.17157 11.1716 2.5 12 2.5C12.8284 2.5 13.5 3.17157 13.5 4Z" fill="currentColor"></path>
            <path opacity="0.4" d="M12.5 11.5C12.5 12.3284 11.8284 13 11 13C10.1716 13 9.5 12.3284 9.5 11.5C9.5 10.6716 10.1716 10 11 10C11.8284 10 12.5 10.6716 12.5 11.5Z" fill="currentColor"></path>
            <path opacity="0.4" d="M6.5 7.5C6.5 8.60457 5.60457 9.5 4.5 9.5C3.39543 9.5 2.5 8.60457 2.5 7.5C2.5 6.39543 3.39543 5.5 4.5 5.5C5.60457 5.5 6.5 6.39543 6.5 7.5Z" fill="currentColor"></path>
            <path opacity="0.4" d="M10.5 19.5C10.5 20.6046 9.60457 21.5 8.5 21.5C7.39543 21.5 6.5 20.6046 6.5 19.5C6.5 18.3954 7.39543 17.5 8.5 17.5C9.60457 17.5 10.5 18.3954 10.5 19.5Z" fill="currentColor"></path>
            <path d="M21.5 12C21.5 13.3807 20.3807 14.5 19 14.5C17.6193 14.5 16.5 13.3807 16.5 12C16.5 10.6193 17.6193 9.5 19 9.5C20.3807 9.5 21.5 10.6193 21.5 12Z" stroke="currentColor" stroke-width="1"></path>
            <path d="M13.5 4C13.5 4.82843 12.8284 5.5 12 5.5C11.1716 5.5 10.5 4.82843 10.5 4C10.5 3.17157 11.1716 2.5 12 2.5C12.8284 2.5 13.5 3.17157 13.5 4Z" stroke="currentColor" stroke-width="1"></path>
            <path d="M12.5 11.5C12.5 12.3284 11.8284 13 11 13C10.1716 13 9.5 12.3284 9.5 11.5C9.5 10.6716 10.1716 10 11 10C11.8284 10 12.5 10.6716 12.5 11.5Z" stroke="currentColor" stroke-width="1"></path>
            <path d="M6.5 7.5C6.5 8.60457 5.60457 9.5 4.5 9.5C3.39543 9.5 2.5 8.60457 2.5 7.5C2.5 6.39543 3.39543 5.5 4.5 5.5C5.60457 5.5 6.5 6.39543 6.5 7.5Z" stroke="currentColor" stroke-width="1"></path>
            <path d="M10.5 19.5C10.5 20.6046 9.60457 21.5 8.5 21.5C7.39543 21.5 6.5 20.6046 6.5 19.5C6.5 18.3954 7.39543 17.5 8.5 17.5C9.60457 17.5 10.5 18.3954 10.5 19.5Z" stroke="currentColor" stroke-width="1"></path>
            <path d="M13.5 5L17.5 10M14.5 15.5L10.5 18.5M8 17.5L5 9.5M6.31298 6.65431L10.5 4.5M12.5 11.5L16.505 11.8443" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M12 5.5L11 10" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <?php
    }
}
