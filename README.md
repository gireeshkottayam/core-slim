# CoreSlim (Zero Bloat WordPress Core Optimizer)

Lightweight, open source WordPress plugin to disable unnecessary core features with zero overhead. Free forever.

[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress: 5.8+](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org)
[![PHP: 7.4+](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net)

## About

CoreSlim gives you a clean switchboard to selectively disable unnecessary WordPress core features on every page load. It is built around a strict performance-first philosophy:

- A single autoloaded settings array, so no extra database queries on page load.
- Zero overhead, under 0.2ms execution time.
- Zero external dependencies, pure vanilla PHP and JavaScript.
- No paywall, no lock-in, and no license key.

## Features

### Front-End Assets
- Disable Emojis
- Disable Dashicons for guests
- Disable jQuery Migrate
- Disable Embeds (oEmbed)
- Disable Gutenberg block CSS
- Disable Comment Reply JS

### Header & Meta Cleanup
- Remove WordPress version / generator meta
- Remove RSD link
- Remove Windows Live Writer manifest
- Remove shortlinks
- Remove REST API link tags
- Remove RSS feed links
- Disable self-pingbacks

### Security & Hardening
- Disable XML-RPC API
- Block author enumeration scans
- Disable Application Passwords

### Performance & Server Resources
- Heartbeat control (front end, admin, editor)
- Post revisions limiter (0, 3, 5, 10)
- Autosave interval tuner (60, 120, 180, 300s)

### Presets & Tools
- Safe Defaults, Maximum Performance, and Reset All presets
- JSON settings export and import

## Installation

1. Upload the `core-slim` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Go to **Settings > CoreSlim** and toggle what you want to disable.
4. Start with **Safe Defaults**, then optionally apply **Maximum Performance**.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later (tested through PHP 8.4)

## Automatic Updates

CoreSlim uses the standard WordPress update mechanism. Installations from the WordPress.org directory receive updates through the normal WordPress.org channel. Installations from other sources (such as GitHub or the ShareWire site) receive updates through ShareWire.in using the native updater. No license key required.

## Telemetry

CoreSlim stores all settings locally in your WordPress database. It will not contact any external server unless you opt in under Settings > CoreSlim > Privacy & Consent. Anonymous telemetry (WordPress version, PHP version, plugin version, site domain) is sent to ShareWire.in ONLY when you enable "Send anonymous usage telemetry to ShareWire.in" (off by default). No personal data is collected, and the telemetry call is non-blocking and best-effort.

## Frequently Asked Questions

### Will this break my site?

The Safe Defaults preset contains only non-breaking optimizations. Start there and enable Maximum Performance selectively after verifying your theme and plugins still work.

### Does this work with page builders or WooCommerce?

All toggles are opt-in and reversible. If a builder depends on a disabled asset, leave that toggle off. Test Maximum Performance on staging first.

### Is a license required?

No. CoreSlim is free open source software, available under the GPL-2.0-or-later license.

## Changelog

### 1.0.2
- WordPress.org build now uses native WordPress.org updates only (no external update server); GitHub/ShareWire installs keep ShareWire auto-updates. Tested up to WordPress 7.0.

### 1.0.1
- Telemetry is now opt-in (off by default) under Settings > Privacy & Consent, meeting WordPress.org guideline #7 (no user tracking without explicit consent).

### 1.0.0
- Initial public release.
- Asset, header, security, and performance modules.
- Safe, Max, and Reset presets.
- JSON settings export and import.
- ShareWire automatic updates and anonymous telemetry (opt-in since 1.0.1).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

CoreSlim is a free product of [ShareWire.in](https://sharewire.in). Optional telemetry (off by default) is powered by the ShareWire platform.
