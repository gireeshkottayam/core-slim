<?php
/**
 * CoreSlim Security: surface hardening.
 * XML-RPC disable, author enumeration block, application passwords disable.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Security
{
    public static function init(array $opts): void
    {
        if (!empty($opts['disable_xmlrpc'])) {
            self::disableXmlrpc();
        }
        if (!empty($opts['block_author_enum'])) {
            add_action('template_redirect', array(__CLASS__, 'blockAuthorEnum'), 1);
        }
        if (!empty($opts['disable_app_passwords'])) {
            add_filter('wp_is_application_passwords_available', '__return_false');
        }
    }

    private static function disableXmlrpc(): void
    {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', array(__CLASS__, 'removePingbackHeader'));
        add_filter('xmlrpc_methods', array(__CLASS__, 'blockXmlrpcMethods'));
        if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'xmlrpc.php') {
            if (!headers_sent()) {
                status_header(403);
            }
            exit;
        }
    }

    public static function removePingbackHeader(array $headers): array
    {
        unset($headers['X-Pingback']);
        return $headers;
    }

    public static function blockXmlrpcMethods(): array
    {
        return array();
    }

    public static function blockAuthorEnum(): void
    {
        if (is_user_logged_in()) {
            return;
        }
        $author = isset($_GET['author']) ? (int) $_GET['author'] : 0;
        if ($author > 0) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
        $authorName = isset($_GET['author_name']) ? sanitize_text_field(wp_unslash($_GET['author_name'])) : '';
        if ($authorName !== '') {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }
}
