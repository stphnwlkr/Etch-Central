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
        add_options_page(
            __('Etch Central', 'etch-central'),
            __('Etch Central', 'etch-central'),
            'manage_options',
            Settings::PAGE_SLUG,
            [$this, 'render']
        );
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'etch-central'));
        }

        $settings = $this->settings->get();
        $roles    = wp_roles();
        ?>
        <div class="wrap etch-central-admin">
            <header class="etch-central-admin__hero">
                <div class="etch-central-admin__hero-content">
                   
                    <h1 class="etch-central-admin__title">
                        <span class="etch-central-admin__icon" aria-hidden="true"><?php $this->render_icon(); ?></span>
                        <span><?php esc_html_e('Etch Central', 'etch-central'); ?></span>
                    </h1>
					 <p class="etch-central-admin__eyebrow"><?php esc_html_e('WordPress admin bar tools', 'etch-central'); ?></p>
                    <p><?php esc_html_e('Manage quick access to Etch templates, patterns, resources, and community links.', 'etch-central'); ?></p>
                    <div class="etch-central-admin__cleanup">
                        <label class="etch-central-toggle">
                            <input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[cleanup_on_deactivation]" value="1" <?php checked(!empty($settings['cleanup_on_deactivation'])); ?>>
                            <span><?php esc_html_e('Remove all Etch Central settings when deactivated', 'etch-central'); ?></span>
                        </label>
                        <p class="description"><?php esc_html_e('Recommended only for Git-based deployments where plugin uninstall may not be used.', 'etch-central'); ?></p>
                    </div>
                </div>
            </header>

            <form method="post" action="options.php" class="etch-central-admin__grid">
                <?php settings_fields('etch_central_settings_group'); ?>

                <section class="etch-central-card" aria-labelledby="etch-central-menus-title">
                    <h2 id="etch-central-menus-title"><?php esc_html_e('Admin bar menus', 'etch-central'); ?></h2>
                    <?php foreach ((array) $settings['enabled_menus'] as $key => $enabled) : ?>
                        <label class="etch-central-toggle">
                            <input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[enabled_menus][<?php echo esc_attr((string) $key); ?>]" value="1" <?php checked($enabled); ?>>
                            <span><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $key))); ?></span>
                        </label>
                    <?php endforeach; ?>
                </section>

                <section class="etch-central-card" aria-labelledby="etch-central-roles-title">
                    <h2 id="etch-central-roles-title"><?php esc_html_e('Allowed roles', 'etch-central'); ?></h2>
                    <p><?php esc_html_e('Administrators are selected by default. Choose additional roles that may see Etch Central in the admin bar.', 'etch-central'); ?></p>
                    <?php if ($roles instanceof WP_Roles) : ?>
                        <?php foreach ($roles->roles as $role_key => $role_data) : ?>
                            <label class="etch-central-toggle">
                                <input type="checkbox" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[allowed_roles][]" value="<?php echo esc_attr((string) $role_key); ?>" <?php checked(in_array($role_key, (array) $settings['allowed_roles'], true)); ?>>
                                <span><?php echo esc_html(translate_user_role($role_data['name'])); ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <section class="etch-central-card etch-central-card--wide" aria-labelledby="etch-central-community-title">
                    <h2 id="etch-central-community-title"><?php esc_html_e('Etch Community favorites', 'etch-central'); ?></h2>
                    <p><?php esc_html_e('Add, remove, edit, or reorder up to 10 community links shown under Etch Central.', 'etch-central'); ?></p>

                    <div class="etch-central-links" role="group" aria-label="<?php esc_attr_e('Community links. Drag rows or use Move up and Move down to change order.', 'etch-central'); ?>" data-etch-central-sortable>
                        <?php
                        $links = array_slice((array) $settings['community_links'], 0, 10);
                        while (count($links) < 10) {
                            $links[] = ['label' => '', 'url' => ''];
                        }
                        ?>
                        <?php foreach ($links as $index => $link) : ?>
                            <div class="etch-central-link-row" draggable="true" data-etch-central-row tabindex="-1">
                                <div class="etch-central-link-row__tools" aria-label="<?php esc_attr_e('Reorder this link', 'etch-central'); ?>">
                                    <button type="button" class="etch-central-drag-handle" aria-label="<?php esc_attr_e('Drag to reorder', 'etch-central'); ?>" title="<?php esc_attr_e('Drag to reorder', 'etch-central'); ?>">☰</button>
                                    <button type="button" class="etch-central-move-button" data-etch-central-move="up"><?php esc_html_e('Up', 'etch-central'); ?></button>
                                    <button type="button" class="etch-central-move-button" data-etch-central-move="down"><?php esc_html_e('Down', 'etch-central'); ?></button>
                                </div>
                                <label>
                                    <span><?php esc_html_e('Label', 'etch-central'); ?></span>
                                    <input type="text" data-etch-central-field="label" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[community_links][<?php echo esc_attr((string) $index); ?>][label]" value="<?php echo esc_attr((string) ($link['label'] ?? '')); ?>">
                                </label>
                                <label>
                                    <span><?php esc_html_e('URL', 'etch-central'); ?></span>
                                    <input type="url" data-etch-central-field="url" name="<?php echo esc_attr(Settings::OPTION_KEY); ?>[community_links][<?php echo esc_attr((string) $index); ?>][url]" value="<?php echo esc_url((string) ($link['url'] ?? '')); ?>">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <div class="etch-central-admin__actions">
                    <?php submit_button(__('Save Etch Central Settings', 'etch-central')); ?>
                </div>
            </form>
        </div>
        <?php
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
