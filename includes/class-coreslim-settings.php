<?php
/**
 * CoreSlim Settings: option storage, defaults, sanitization, presets, export/import.
 * All config lives in a single autoloaded wp_options row (coreslim_settings).
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Settings
{
    private static $defaults = array(
        // Assets
        'disable_emojis'         => false,
        'disable_dashicons'      => false,
        'disable_jquery_migrate' => false,
        'disable_embeds'         => false,
        'disable_block_css'      => false,
        'disable_comment_reply'  => false,
        // Header & Meta
        'remove_generator'       => false,
        'remove_rsd'             => false,
        'remove_wlwmanifest'     => false,
        'remove_shortlinks'      => false,
        'remove_rest_links'      => false,
        'remove_feed_links'      => false,
        'disable_self_pingbacks' => false,
        // Security
        'disable_xmlrpc'         => false,
        'block_author_enum'      => false,
        'disable_app_passwords'  => false,
        // Performance
        'heartbeat_frontend'     => false,
        'heartbeat_admin_freq'   => 15,
        'heartbeat_editor_freq'  => 15,
        'max_revisions'          => -1,
        'autosave_interval'      => 60,
    );

    private static $presets = array(
        'safe' => array(
            'disable_emojis'         => true,
            'remove_generator'       => true,
            'remove_rsd'             => true,
            'remove_wlwmanifest'     => true,
            'remove_shortlinks'      => true,
            'disable_self_pingbacks' => true,
            'disable_xmlrpc'         => true,
            'block_author_enum'      => true,
        ),
        'max' => array(
            'disable_emojis'         => true,
            'disable_dashicons'      => true,
            'disable_jquery_migrate' => true,
            'disable_embeds'         => true,
            'disable_comment_reply'  => true,
            'remove_generator'       => true,
            'remove_rsd'             => true,
            'remove_wlwmanifest'     => true,
            'remove_shortlinks'      => true,
            'remove_rest_links'      => true,
            'remove_feed_links'      => true,
            'disable_self_pingbacks' => true,
            'disable_xmlrpc'         => true,
            'block_author_enum'      => true,
            'disable_app_passwords'  => true,
            'heartbeat_frontend'     => true,
            'heartbeat_admin_freq'   => 60,
            'heartbeat_editor_freq'  => 120,
            'max_revisions'          => 3,
            'autosave_interval'      => 180,
        ),
    );

    public static function getAll(): array
    {
        $stored = get_option(CORE_SLIM_OPT_KEY, array());
        return wp_parse_args($stored, self::$defaults);
    }

    public static function get(string $key)
    {
        $all = self::getAll();
        return $all[$key] ?? self::$defaults[$key] ?? null;
    }

    public static function set(string $key, $value): void
    {
        $all = self::getAll();
        $all[$key] = $value;
        update_option(CORE_SLIM_OPT_KEY, $all, false);
    }

    public static function save(array $input): void
    {
        update_option(CORE_SLIM_OPT_KEY, $input, false);
    }

    public static function defaults(): array
    {
        return self::$defaults;
    }

    public static function sanitize(array $input): array
    {
        $clean = array();
        $bools = array(
            'disable_emojis', 'disable_dashicons', 'disable_jquery_migrate',
            'disable_embeds', 'disable_block_css', 'disable_comment_reply',
            'remove_generator', 'remove_rsd', 'remove_wlwmanifest',
            'remove_shortlinks', 'remove_rest_links', 'remove_feed_links',
            'disable_self_pingbacks', 'disable_xmlrpc', 'block_author_enum',
            'disable_app_passwords', 'heartbeat_frontend',
        );
        foreach ($bools as $k) {
            $clean[$k] = !empty($input[$k]);
        }
        $clean['heartbeat_admin_freq'] = isset($input['heartbeat_admin_freq'])
            ? max(15, min(300, (int) $input['heartbeat_admin_freq']))
            : 15;
        $clean['heartbeat_editor_freq'] = isset($input['heartbeat_editor_freq'])
            ? max(15, min(300, (int) $input['heartbeat_editor_freq']))
            : 15;
        $revMap = array(-1 => -1, 0 => 0, 3 => 3, 5 => 5, 10 => 10);
        $rev = isset($input['max_revisions']) ? (int) $input['max_revisions'] : -1;
        $clean['max_revisions'] = isset($revMap[$rev]) ? $revMap[$rev] : -1;
        $autoMap = array(60 => 60, 120 => 120, 180 => 180, 300 => 300);
        $auto = isset($input['autosave_interval']) ? (int) $input['autosave_interval'] : 60;
        $clean['autosave_interval'] = isset($autoMap[$auto]) ? $autoMap[$auto] : 60;
        return $clean;
    }

    public static function applyPreset(string $name): array
    {
        if ($name === 'safe') {
            return wp_parse_args(self::$presets['safe'], self::$defaults);
        }
        if ($name === 'max') {
            return wp_parse_args(self::$presets['max'], self::$defaults);
        }
        return self::$defaults;
    }

    public static function exportSettings(): string
    {
        return wp_json_encode(self::getAll(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function importSettings(string $json): bool
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return false;
        }
        $clean = self::sanitize($data);
        self::save($clean);
        return true;
    }
}
