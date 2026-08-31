<?php
/**
 * CoreSlim test harness bootstrap.
 * Provides the minimal WordPress function stubs needed to exercise the plugin
 * classes in isolation on the local WAMP server (no live WordPress required).
 * Run with:  php tests/test-coreslim.php
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$GLOBALS['__opts'] = array();
$GLOBALS['__calls'] = array();
$GLOBALS['__transients'] = array();

/** Minimal option store. */
function get_option($key, $default = false)
{
    if (!isset($GLOBALS['__opts'][$key])) {
        $GLOBALS['__opts'][$key] = $default;
    }
    return $GLOBALS['__opts'][$key];
}
function update_option($key, $value, $autoload = null)
{
    $GLOBALS['__opts'][$key] = $value;
    return true;
}
function delete_option($key)
{
    unset($GLOBALS['__opts'][$key]);
    return true;
}

function wp_parse_args($args, $defaults = array())
{
    if (is_object($args)) {
        $args = get_object_vars($args);
    }
    if (!is_array($args)) {
        $args = array();
    }
    return array_merge($defaults, $args);
}

function get_transient($key)
{
    return $GLOBALS['__transients'][$key] ?? false;
}
function set_transient($key, $value, $expire = 0)
{
    $GLOBALS['__transients'][$key] = $value;
    return true;
}

function wp_json_encode($data, $options = 0, $depth = 512)
{
    return json_encode($data, $options);
}

function esc_html($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
function esc_attr($text)
{
    return esc_html($text);
}
function checked($checked, $current = true)
{
    echo ($checked == $current) ? 'checked' : '';
}
function plugin_basename($file)
{
    return 'core-slim/core-slim.php';
}
function plugin_dir_path($file)
{
    return dirname(__DIR__) . '/';
}
function plugin_dir_url($file)
{
    return 'https://example.com/wp-content/plugins/core-slim/';
}
function is_admin()
{
    return !empty($GLOBALS['__is_admin']);
}
function is_user_logged_in()
{
    return false;
}
function is_singular()
{
    return false;
}
function comments_open($id = 0)
{
    return false;
}
function get_the_ID()
{
    return 0;
}
function add_action($hook, $cb, $priority = 10, $args = 1)
{
    $GLOBALS['__calls'][] = array('type' => 'action', 'hook' => $hook, 'cb' => $cb);
    return true;
}
function add_filter($hook, $cb, $priority = 10, $args = 1)
{
    $GLOBALS['__calls'][] = array('type' => 'filter', 'hook' => $hook, 'cb' => $cb);
    return true;
}
function remove_action($hook, $cb, $priority = 10, $args = 1)
{
    return true;
}
function remove_filter($hook, $cb, $priority = 10, $args = 1)
{
    return true;
}
function register_activation_hook($file, $cb)
{
    return true;
}
function register_deactivation_hook($file, $cb)
{
    return true;
}
function add_options_page($title, $menu, $cap, $slug, $cb)
{
    return 'settings_page_core-slim';
}
function admin_url($path = '')
{
    return 'https://example.com/wp-admin/' . $path;
}
function current_user_can($cap)
{
    return true;
}
function check_ajax_referer($action, $query, $die = true)
{
    return 1;
}
function sanitize_key($key)
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
}
function sanitize_text_field($str)
{
    return trim(strip_tags($str));
}
function sanitize_textarea_field($str)
{
    return trim((string) $str);
}
function esc_url_raw($url)
{
    return $url;
}
function home_url($path = '')
{
    return 'https://example.com' . $path;
}
function wp_safe_redirect($url, $code = 302)
{
    return true;
}
function wp_unslash($value)
{
    return $value;
}
function wp_create_nonce($action)
{
    return 'test-nonce';
}
function wp_enqueue_style($h, $src, $deps = array(), $ver = false, $media = 'all')
{
    return true;
}
function wp_enqueue_script($h, $src, $deps = array(), $ver = false, $in_footer = false)
{
    return true;
}
function wp_localize_script($h, $name, $data)
{
    return true;
}
function wp_remote_post($url, $args = array())
{
    return new stdClass();
}
function wp_remote_get($url, $args = array())
{
    return new stdClass();
}
function is_wp_error($thing)
{
    return false;
}
function wp_remote_retrieve_body($response)
{
    return '{"new_version":"0.9.0","package":""}';
}
function status_header($code)
{
    return $code;
}
function __return_false()
{
    return false;
}
function __return_empty_array()
{
    return array();
}
function __return_empty_string()
{
    return '';
}
function set_status_header($code)
{
}
function esc_url($url)
{
    return $url;
}
function get_option_comments($k, $d = '')
{
    return $d;
}

/* Now load the full plugin so constants and classes are available. */
require_once dirname(__DIR__) . '/core-slim.php';

/* Reset option to defaults for deterministic tests. */
$GLOBALS['__opts'][CORE_SLIM_OPT_KEY] = CoreSlim_Settings::defaults();
