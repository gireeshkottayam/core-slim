<?php
/**
 * CoreSlim Assets: front-end script and style de-queuing.
 * Emojis, Dashicons, jQuery Migrate, oEmbeds, Block CSS, Comment Reply.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Assets
{
    public static function init(array $opts): void
    {
        if (!empty($opts['disable_emojis'])) {
            self::disableEmojis();
        }
        if (!empty($opts['disable_dashicons'])) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'disableDashicons'), 999);
        }
        if (!empty($opts['disable_jquery_migrate'])) {
            add_action('wp_default_scripts', array(__CLASS__, 'disableJqueryMigrate'));
        }
        if (!empty($opts['disable_embeds'])) {
            self::disableEmbeds();
        }
        if (!empty($opts['disable_block_css'])) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'disableBlockCss'), 999);
        }
        if (!empty($opts['disable_comment_reply'])) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'disableCommentReply'), 999);
        }
    }

    private static function disableEmojis(): void
    {
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins', array(__CLASS__, 'disableEmojiTinymce'));
        add_filter('wp_resource_hints', array(__CLASS__, 'removeEmojiPrefetch'), 10, 2);
    }

    public static function disableEmojiTinymce(array $plugins): array
    {
        return array_diff($plugins, array('wpemoji'));
    }

    public static function removeEmojiPrefetch(array $urls, string $relation): array
    {
        if ($relation === 'dns-prefetch') {
            foreach ($urls as $i => $url) {
                if (is_string($url) && stripos($url, 's.w.org') !== false) {
                    unset($urls[$i]);
                }
            }
        }
        return $urls;
    }

    public static function disableDashicons(): void
    {
        if (!is_user_logged_in()) {
            wp_dequeue_style('dashicons');
        }
    }

    public static function disableJqueryMigrate($scripts): void
    {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $jquery = $scripts->registered['jquery'];
            if ($jquery->deps) {
                $jquery->deps = array_diff($jquery->deps, array('jquery-migrate'));
            }
        }
    }

    private static function disableEmbeds(): void
    {
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        add_filter('embed_oembed_discover', '__return_false');
        add_filter('rest_endpoints', array(__CLASS__, 'removeEmbedEndpoints'));
        add_filter('oembed_response_data', '__return_empty_array');
        add_action('wp_footer', array(__CLASS__, 'dequeueEmbedScript'), 999);
    }

    public static function removeEmbedEndpoints(array $endpoints): array
    {
        foreach (array('oembed/1.0/embed', 'oembed/1.0/proxy') as $endpoint) {
            if (isset($endpoints[$endpoint])) {
                unset($endpoints[$endpoint]);
            }
        }
        return $endpoints;
    }

    public static function dequeueEmbedScript(): void
    {
        wp_dequeue_script('wp-embed');
    }

    public static function disableBlockCss(): void
    {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('global-styles');
    }

    public static function disableCommentReply(): void
    {
        if (!is_singular() || !comments_open(get_the_ID()) || !get_option('thread_comments')) {
            wp_dequeue_script('comment-reply');
        }
    }
}
