<?php
/**
 * CoreSlim Performance: server and DB optimization.
 * Heartbeat control, post revisions limiter, autosave interval tuner.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Performance
{
    public static function init(array $opts): void
    {
        if (!empty($opts['heartbeat_frontend'])) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'heartbeatFrontend'));
        }
        if (!empty($opts['heartbeat_admin_freq']) && (int) $opts['heartbeat_admin_freq'] > 0) {
            add_filter('heartbeat_settings', array(__CLASS__, 'heartbeatAdminFreq'));
        }
        if (!empty($opts['heartbeat_editor_freq']) && (int) $opts['heartbeat_editor_freq'] > 0) {
            add_filter('heartbeat_settings', array(__CLASS__, 'heartbeatEditorFreq'));
        }
        if (!empty($opts['max_revisions']) && (int) $opts['max_revisions'] >= 0) {
            add_filter('wp_revisions_to_keep', array(__CLASS__, 'revisionsToKeep'), 10, 2);
        }
        if (!empty($opts['autosave_interval'])) {
            add_filter('autosave_interval', array(__CLASS__, 'autosaveInterval'));
        }
    }

    public static function heartbeatFrontend(): void
    {
        wp_deregister_script('heartbeat');
    }

    public static function heartbeatAdminFreq(array $settings): array
    {
        if (!is_admin()) {
            return $settings;
        }
        $freq = (int) CoreSlim_Settings::get('heartbeat_admin_freq');
        $freq = max(15, min(300, $freq));
        $settings['interval'] = $freq;
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'heartbeat') {
            $settings['autostart'] = true;
        }
        return $settings;
    }

    public static function heartbeatEditorFreq(array $settings): array
    {
        if (empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'post.php') {
            return $settings;
        }
        $freq = (int) CoreSlim_Settings::get('heartbeat_editor_freq');
        $freq = max(15, min(300, $freq));
        $settings['interval'] = $freq;
        return $settings;
    }

    public static function revisionsToKeep($num, $post): int
    {
        $limit = (int) CoreSlim_Settings::get('max_revisions');
        if ($limit < 0) {
            return $num;
        }
        return $limit;
    }

    public static function autosaveInterval(int $seconds): int
    {
        $interval = (int) CoreSlim_Settings::get('autosave_interval');
        $interval = max(60, min(300, $interval));
        return $interval;
    }
}
