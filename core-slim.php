<?php
/**
 * Plugin Name:       CoreSlim (Zero Bloat WordPress Core Optimizer)
 * Plugin URI:        https://sharewire.in/product.php?product=core-slim
 * Description:       Lightweight, zero-bloat WordPress plugin to disable unnecessary core features: emojis, XML-RPC, embeds, Dashicons, version disclosure, Heartbeat API, post revisions, and autosave overhead. Single autoloaded option, zero external dependencies, under 0.2ms execution. Free and open source with automatic updates through the standard WordPress updater.
 * Version:           1.0.2
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            ShareWire.in
 * Author URI:        https://sharewire.in
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-slim
 *
 * CoreSlim is a free, open source product of ShareWire.in.
 * All optimizations run locally on your own server. Updates use the standard
 * WordPress update channel; optional anonymous telemetry (opt-in, off by
 * default) is sent to the ShareWire platform (https://sharewire.in) only when
 * the user enables it under Settings > CoreSlim > Privacy & Consent.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CORE_SLIM_VERSION', '1.0.2');

// wordpress.org-managed build: set to 1 by build.ps1 -WpOrg. In that build the
// ShareWire update channel is disabled so updates flow from WordPress.org only
// (guideline #8). The GitHub/ShareWire build leaves it 0 and keeps ShareWire
// auto-updates for sites that install it from GitHub or sharewire.in.
if (!defined('CORE_SLIM_WPORG')) {
    define('CORE_SLIM_WPORG', 0);
}

// ShareWire server base. Left empty on the wordpress.org build so no external
// host string ships in that distribution (the updater/telemetry that use it are
// not bundled there either).
if (CORE_SLIM_WPORG) {
    define('CORE_SLIM_BASE', '');
} else {
    define('CORE_SLIM_BASE', defined('SW_LICENSE_BASE') ? rtrim((string) SW_LICENSE_BASE, '/') : 'https://sharewire.in');
}
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

add_action('plugins_loaded', array('CoreSlim_Core', 'boot'), 5);
add_action('admin_menu', array('CoreSlim_Admin', 'menu'));
add_action('admin_enqueue_scripts', array('CoreSlim_Admin', 'enqueue'));
add_action('admin_init', array('CoreSlim_Admin', 'register_ajax'));

// The ShareWire updater class (auto-update via /api/update.php + telemetry) is
// loaded ONLY on the GitHub/ShareWire build. On the wordpress.org build the class
// is not shipped at all so the plugin has zero external server calls and updates
// flow purely through the native WordPress.org channel (guideline #8).
if (!CORE_SLIM_WPORG) {
    require_once CORE_SLIM_DIR . 'includes/class-coreslim-updater.php';
    CoreSlim_Updater::init();

    // Telemetry is opt-in (Settings > Privacy & Consent) and off by default.
    if (CoreSlim_Settings::get('enable_telemetry')) {
        add_action('plugins_loaded', array('CoreSlim_Updater', 'telemetry'), 20);
    }
}
