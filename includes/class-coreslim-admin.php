<?php
/**
 * CoreSlim Admin: settings page UI, AJAX toggle handlers, presets, export/import.
 * Admin styling and scripts load only on the CoreSlim settings screen.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Admin
{
    public static function menu(): void
    {
        add_options_page(
            'CoreSlim Settings',
            'CoreSlim',
            'manage_options',
            'core-slim',
            array(__CLASS__, 'render')
        );
    }

    public static function enqueue($hook): void
    {
        if ($hook !== 'settings_page_core-slim') {
            return;
        }
        wp_enqueue_style('core-slim-admin', CORE_SLIM_URL . 'assets/css/coreslim-admin.css', array(), CORE_SLIM_VERSION);
        wp_enqueue_script('core-slim-admin', CORE_SLIM_URL . 'assets/js/coreslim-admin.js', array(), CORE_SLIM_VERSION, true);
        wp_localize_script('core-slim-admin', 'CORE_SLIM', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('core_slim_nonce'),
            'settings' => CoreSlim_Settings::getAll(),
        ));
    }

    public static function register_ajax(): void
    {
        add_action('wp_ajax_core_slim_save_all', array(__CLASS__, 'ajaxSaveAll'));
        add_action('wp_ajax_core_slim_preset', array(__CLASS__, 'ajaxPreset'));
        add_action('wp_ajax_core_slim_export', array(__CLASS__, 'ajaxExport'));
        add_action('wp_ajax_core_slim_import', array(__CLASS__, 'ajaxImport'));
    }

    private static function verify(): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }
        return check_ajax_referer('core_slim_nonce', 'nonce', false);
    }

    public static function ajaxSaveAll(): void
    {
        if (!self::verify()) {
            wp_send_json_error('PERMISSION_DENIED', 403);
        }
        $input = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : array();
        $clean = CoreSlim_Settings::sanitize(wp_unslash($input));
        CoreSlim_Settings::save($clean);
        wp_send_json_success(array('settings' => $clean));
    }

    public static function ajaxPreset(): void
    {
        if (!self::verify()) {
            wp_send_json_error('PERMISSION_DENIED', 403);
        }
        $preset = isset($_POST['preset']) ? sanitize_key(wp_unslash($_POST['preset'])) : '';
        if (!in_array($preset, array('safe', 'max', 'reset'), true)) {
            wp_send_json_error('INVALID_PRESET', 400);
        }
        $apply = ($preset === 'reset') ? 'reset' : $preset;
        $settings = CoreSlim_Settings::applyPreset($apply);
        CoreSlim_Settings::save($settings);
        wp_send_json_success(array('settings' => $settings, 'preset' => $preset));
    }

    public static function ajaxExport(): void
    {
        if (!self::verify()) {
            wp_send_json_error('PERMISSION_DENIED', 403);
        }
        wp_send_json_success(array('json' => CoreSlim_Settings::exportSettings()));
    }

    public static function ajaxImport(): void
    {
        if (!self::verify()) {
            wp_send_json_error('PERMISSION_DENIED', 403);
        }
        $json = isset($_POST['json']) ? wp_unslash($_POST['json']) : '';
        $json = sanitize_textarea_field($json);
        if (!CoreSlim_Settings::importSettings($json)) {
            wp_send_json_error('INVALID_JSON', 400);
        }
        wp_send_json_success(array('settings' => CoreSlim_Settings::getAll()));
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = CoreSlim_Settings::getAll();
        ?>
        <div class="wrap coreslim-wrap">
            <h1>CoreSlim <span class="coreslim-badge">FREE</span></h1>
            <p class="coreslim-subtitle">Zero Bloat WordPress Core Optimizer. Disable unnecessary core features with zero overhead.</p>

            <?php if (defined('CORE_SLIM_WPORG') && CORE_SLIM_WPORG) : ?>
            <p class="coreslim-wporg-note">This copy was installed from the WordPress.org directory, so updates are delivered through the normal WordPress.org update channel.</p>
            <?php endif; ?>

            <div class="coreslim-presets">
                <button type="button" class="button coreslim-preset-btn" data-preset="safe">Safe Defaults</button>
                <button type="button" class="button coreslim-preset-btn" data-preset="max">Maximum Performance</button>
                <button type="button" class="button coreslim-preset-btn" data-preset="reset">Reset All</button>
            </div>

            <form id="coreslim-form" method="post">
                <?php foreach (self::sections() as $section => $meta) : ?>
                <div class="coreslim-section">
                    <h2 class="coreslim-section-title"><?php echo esc_html($meta['title']); ?></h2>
                    <p class="coreslim-section-desc"><?php echo esc_html($meta['desc']); ?></p>
                    <?php foreach ($meta['fields'] as $key => $label) : ?>
                    <div class="coreslim-row">
                        <label class="coreslim-toggle">
                            <input type="checkbox" name="settings[<?php echo esc_attr($key); ?>]" value="1"
                                <?php checked(!empty($settings[$key])); ?>
                                data-key="<?php echo esc_attr($key); ?>">
                            <span class="coreslim-switch"></span>
                            <span class="coreslim-label"><?php echo esc_html($label); ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!empty($meta['selects'])) : ?>
                        <?php foreach ($meta['selects'] as $key => $label) : ?>
                        <div class="coreslim-row">
                            <span class="coreslim-label"><?php echo esc_html($label); ?></span>
                            <select data-select="<?php echo esc_attr($key); ?>">
                                <?php
                                $options = $meta['options'][$key] ?? array();
                                foreach ($options as $val => $txt) :
                                    $selected = ((int) $settings[$key] === (int) $val) ? 'selected' : '';
                                ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php echo $selected; ?>><?php echo esc_html($txt); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <p><button type="submit" class="button button-primary">Save Settings</button></p>

                <div class="coreslim-transfer">
                    <h2 class="coreslim-section-title">Export / Import</h2>
                    <p class="coreslim-section-desc">Back up or move your configuration between sites with clean JSON.</p>
                    <p>
                        <button type="button" class="button coreslim-export-btn">Export JSON</button>
                    </p>
                    <textarea id="coreslim-transfer-area" rows="6" class="widefat" placeholder="Paste JSON here to import"></textarea>
                    <p><button type="button" class="button coreslim-import-btn">Import JSON</button></p>
                    <p class="coreslim-feedback"></p>
                </div>
            </form>
        </div>
        <?php
    }

    private static function sections(): array
    {
        return array(
            'assets' => array(
                'title' => 'Front-End Assets',
                'desc'  => 'Remove unnecessary scripts and styles from the front end.',
                'fields' => array(
                    'disable_emojis'         => 'Disable Emojis',
                    'disable_dashicons'      => 'Disable Dashicons for guests',
                    'disable_jquery_migrate' => 'Disable jQuery Migrate',
                    'disable_embeds'         => 'Disable Embeds (oEmbed)',
                    'disable_block_css'      => 'Disable Gutenberg block CSS',
                    'disable_comment_reply'  => 'Disable Comment Reply JS',
                ),
            ),
            'header' => array(
                'title' => 'Header & Meta Cleanup',
                'desc'  => 'Remove version disclosure and clutter from the head.',
                'fields' => array(
                    'remove_generator'       => 'Remove WordPress version / generator meta',
                    'remove_rsd'             => 'Remove RSD link',
                    'remove_wlwmanifest'     => 'Remove Windows Live Writer manifest',
                    'remove_shortlinks'      => 'Remove shortlinks',
                    'remove_rest_links'      => 'Remove REST API link tags in head',
                    'remove_feed_links'      => 'Remove RSS feed links in head',
                    'disable_self_pingbacks' => 'Disable self-pingbacks',
                ),
            ),
            'security' => array(
                'title' => 'Security & Hardening',
                'desc'  => 'Close common WordPress attack surfaces.',
                'fields' => array(
                    'disable_xmlrpc'        => 'Disable XML-RPC API',
                    'block_author_enum'     => 'Block author enumeration scans',
                    'disable_app_passwords' => 'Disable Application Passwords',
                ),
            ),
            'performance' => array(
                'title' => 'Performance & Server Resources',
                'desc'  => 'Reduce server CPU and database load.',
                'fields' => array(
                    'heartbeat_frontend' => 'Disable Heartbeat on front end',
                ),
                'selects' => array(
                    'heartbeat_admin_freq'  => 'Heartbeat frequency (admin dashboard, seconds)',
                    'heartbeat_editor_freq' => 'Heartbeat frequency (post editor, seconds)',
                    'max_revisions'         => 'Maximum post revisions to keep',
                    'autosave_interval'     => 'Autosave interval (seconds)',
                ),
                'options' => array(
                    'heartbeat_admin_freq'  => array(15 => '15 (default)', 30 => '30', 60 => '60', 120 => '120'),
                    'heartbeat_editor_freq' => array(15 => '15 (default)', 30 => '30', 60 => '60', 120 => '120'),
                    'max_revisions'         => array(-1 => 'Default', 0 => 'None', 3 => '3', 5 => '5', 10 => '10'),
                    'autosave_interval'     => array(60 => '60 (default)', 120 => '120', 180 => '180', 300 => '300'),
                ),
            ),
            'privacy' => array(
                'title' => 'Privacy & Consent',
                'desc'  => 'CoreSlim stores all settings locally and never contacts an external server unless you enable it here.',
                'fields' => array(
                    'enable_telemetry' => 'Send anonymous usage telemetry to ShareWire.in (off by default)',
                ),
            ),
        );
    }
}
