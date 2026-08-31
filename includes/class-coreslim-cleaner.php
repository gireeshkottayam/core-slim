<?php
/**
 * CoreSlim Cleaner: header, meta tag, feed cleanup.
 * Generator tag, RSD, WLW manifest, shortlinks, REST links, feed links, self-pingbacks.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Cleaner
{
    public static function init(array $opts): void
    {
        if (!empty($opts['remove_generator'])) {
            self::removeGenerator();
        }
        if (!empty($opts['remove_rsd'])) {
            remove_action('wp_head', 'rsd_link');
        }
        if (!empty($opts['remove_wlwmanifest'])) {
            remove_action('wp_head', 'wlwmanifest_link');
        }
        if (!empty($opts['remove_shortlinks'])) {
            self::removeShortlinks();
        }
        if (!empty($opts['remove_rest_links'])) {
            self::removeRestLinks();
        }
        if (!empty($opts['remove_feed_links'])) {
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
        }
        if (!empty($opts['disable_self_pingbacks'])) {
            add_filter('pre_ping', array(__CLASS__, 'disableSelfPingbacks'));
        }
    }

    private static function removeGenerator(): void
    {
        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
    }

    private static function removeShortlinks(): void
    {
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        remove_action('template_redirect', 'wp_shortlink_header', 11);
    }

    private static function removeRestLinks(): void
    {
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }

    public static function disableSelfPingbacks(&$links): void
    {
        $home = esc_url_raw(home_url());
        $links = array_filter($links, function ($link) use ($home) {
            return strpos($link, $home) === false;
        });
    }
}
