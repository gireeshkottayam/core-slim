<?php
/**
 * Plugin Name:       CoreSlim (Zero Bloat WordPress Core Optimizer)
 * Plugin URI:        https://sharewire.in/product.php?product=core-slim
 * Description:       Lightweight, zero-bloat WordPress plugin to disable unnecessary core features: emojis, XML-RPC, embeds, Dashicons, version disclosure, Heartbeat API, post revisions, and autosave overhead. Single autoloaded option, zero external dependencies, under 0.2ms execution. Free and open source with automatic updates powered by ShareWire.in.
 * Version:           1.0.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            ShareWire.in
 * Author URI:        https://sharewire.in
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-slim
 *
 * CoreSlim is a free, open source product of ShareWire.in.
 * No licensing core is embedded. Telemetry and auto-update delivery
 * use the ShareWire platform (https://sharewire.in) over HTTPS.
 * All optimizations run locally on your own server.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CORE_SLIM_VERSION', '1.0.1');
define('CORE_SLIM_BASE', defined('SW_LICENSE_BASE') ? rtrim((string) SW_LICENSE_BASE, '/') : 'https://sharewire.in');
define('CORE_SLIM_SLUG', 'core-slim');
define('CORE_SLIM_BASENAME', plugin_basename(__FILE__));
define('CORE_SLIM_DIR', plugin_dir_path(__FILE__));
define('CORE_SLIM_URL', plugin_dir_url(__FILE__));
define('CORE_SLIM_OPT_KEY', 'coreslim_settings');

require_once CORE_SLIM_DIR . 'includes/class-coreslim-settings.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-core.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-assets.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-cleaner.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-security.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-perf.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-admin.php';
require_once CORE_SLIM_DIR . 'includes/class-coreslim-updater.php';

add_action('plugins_loaded', array('CoreSlim_Core', 'boot'), 5);
add_action('admin_menu', array('CoreSlim_Admin', 'menu'));
add_action('admin_enqueue_scripts', array('CoreSlim_Admin', 'enqueue'));
add_action('admin_init', array('CoreSlim_Admin', 'register_ajax'));
CoreSlim_Updater::init();

// Telemetry is opt-in (Settings > Privacy & Consent) and off by default.
if (CoreSlim_Settings::get('enable_telemetry')) {
    add_action('plugins_loaded', array('CoreSlim_Updater', 'telemetry'), 20);
}
