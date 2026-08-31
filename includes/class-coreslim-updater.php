<?php
/**
 * CoreSlim Updater: ShareWire native WordPress auto-updater + telemetry.
 * Free product, non-blocking. No license key, no activation lock.
 * Queries api/update.php with only the product slug and current version.
 * Anonymous telemetry pings api/telemetry.php, but ONLY when the user has
 * explicitly opted in (Settings > Privacy & Consent, off by default).
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Updater
{
    const CACHE = 'coreslim_update_check';

    public static function init(): void
    {
        // On the wordpress.org build, never register the ShareWire update channel
        // (guideline #8). This is a defensive guard; core-slim.php already skips it.
        if (defined('CORE_SLIM_WPORG') && CORE_SLIM_WPORG) {
            return;
        }
        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'check'));
        add_filter('plugins_api', array(__CLASS__, 'info'), 10, 3);
    }

    public static function telemetry(): void
    {
        $site = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : 'unknown';
        $site = strtolower($site);
        $response = wp_remote_post(CORE_SLIM_BASE . '/api/telemetry.php', array(
            'timeout' => 8,
            'blocking' => false,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'product' => CORE_SLIM_SLUG,
                'version' => CORE_SLIM_VERSION,
                'site'    => $site,
            )),
        ));
        unset($response);
    }

    public static function fetch(): ?array
    {
        $data = get_transient(self::CACHE);
        if ($data !== false) {
            return $data;
        }
        $url = CORE_SLIM_BASE . '/api/update.php?product=' . rawurlencode(CORE_SLIM_SLUG)
            . '&version=' . rawurlencode(CORE_SLIM_VERSION);
        $response = wp_remote_get($url, array('timeout' => 8, 'redirection' => 0));
        if (is_wp_error($response)) {
            return null;
        }
        $body = wp_remote_retrieve_body($response);
        $r = json_decode($body, true);
        if (!is_array($r) || empty($r['new_version'])) {
            return null;
        }
        $new_version = (string) $r['new_version'];
        if (!preg_match('/^\d+(\.\d+){0,3}$/', $new_version)) {
            return null;
        }
        $data = array(
            'new_version' => $new_version,
            'package'     => (string) ($r['package'] ?? ''),
            'slug'        => (string) ($r['slug'] ?? CORE_SLIM_SLUG),
            'plugin'      => (string) ($r['plugin'] ?? CORE_SLIM_BASENAME),
            'url'         => (string) ($r['url'] ?? ''),
            'requires'    => (string) ($r['requires'] ?? '5.8'),
            'tested'      => (string) ($r['tested'] ?? '6.4'),
            'requires_php'=> (string) ($r['requires_php'] ?? '7.4'),
            'changelog'   => (string) ($r['changelog'] ?? ''),
        );
        set_transient(self::CACHE, $data, HOUR_IN_SECONDS);
        return $data;
    }

    public static function check($transient)
    {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }
        $data = self::fetch();
        if ($data === null || empty($data['package'])) {
            return $transient;
        }
        if (version_compare(CORE_SLIM_VERSION, $data['new_version'], '>=')) {
            return $transient;
        }
        $obj = (object) array(
            'slug'        => $data['slug'],
            'plugin'      => $data['plugin'],
            'new_version' => $data['new_version'],
            'url'         => $data['url'],
            'package'     => $data['package'],
            'requires'    => $data['requires'],
            'tested'      => $data['tested'],
            'requires_php'=> $data['requires_php'],
            'compatibility' => new stdClass(),
        );
        $transient->response[$data['plugin']] = $obj;
        return $transient;
    }

    public static function info($res, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $res;
        }
        $slug = is_object($args) ? ($args->slug ?? '') : '';
        if ($slug !== CORE_SLIM_SLUG) {
            return $res;
        }
        return (object) array(
            'name'         => 'CoreSlim (Zero Bloat WordPress Core Optimizer)',
            'slug'         => CORE_SLIM_SLUG,
            'version'      => CORE_SLIM_VERSION,
            'author'       => 'ShareWire.in',
            'homepage'     => CORE_SLIM_BASE,
            'sections'     => array(
                'description' => '<p>CoreSlim by ShareWire.in is a free, open source WordPress optimizer that lets you '
                    . 'disable unnecessary core features: emojis, Dashicons, jQuery Migrate, oEmbeds, Gutenberg block CSS, '
                    . 'version disclosure, RSD and WLW manifests, shortlinks, REST and feed links, self-pingbacks, XML-RPC, '
                    . 'author enumeration, application passwords, Heartbeat, post revisions and autosave overhead. '
                    . 'Zero bloat, a single autoloaded option, and no external dependencies. Free forever.</p>',
                'changelog'   => '<p>See your ShareWire portal for release notes.</p>',
            ),
            'requires'     => '5.8',
            'tested'       => '6.4',
            'requires_php' => '7.4',
            'downloaded'   => 0,
            'last_updated' => gmdate('Y-m-d'),
        );
    }
}
