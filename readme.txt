=== CoreSlim (Zero Bloat WordPress Core Optimizer) ===
Contributors: sharewire
Tags: performance, optimization, speed, bloat, cleanup, xmlrpc, emoji, heartbeat
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, zero-bloat WordPress plugin to disable unnecessary core features with zero overhead. Free and open source, with automatic updates powered by ShareWire.in.

== Description ==

CoreSlim is a free, open source WordPress optimizer that gives you a clean switchboard to selectively disable unnecessary WordPress core features on every page load:

* Front-end assets: Emojis, Dashicons for guests, jQuery Migrate, oEmbed embeds, Gutenberg block CSS, and Comment Reply JS.
* Header & meta cleanup: version generator tag, RSD link, Windows Live Writer manifest, shortlinks, REST API link tags, and RSS feed links.
* Security hardening: XML-RPC API, author enumeration scans, and Application Passwords.
* Performance: Heartbeat API (front end and admin throttling), post revisions limiter, and autosave interval tuner.

Why CoreSlim stands apart:

* Zero bloat. A single autoloaded option array, so no extra database queries on page load.
* Zero overhead. Under 0.2ms execution time on the front end.
* Zero external dependencies. Pure vanilla PHP and JavaScript.
* 100% free and open source. No paywall, no lock-in, no license key required.
* Clean and simple UI. A modern toggle switchboard under Settings, with Safe Defaults, Maximum Performance, and Reset presets plus JSON export and import.

**Presets**

* Safe Defaults: enables the non-breaking optimizations, safe for every site.
* Maximum Performance: enables everything plus jQuery Migrate, Embeds, Dashicons, Heartbeat throttling.
* Reset All: restores WordPress default state.

= Automatic updates =

CoreSlim uses the standard WordPress update mechanism. When you install it from the WordPress.org directory, updates are delivered automatically through the normal WordPress.org channel. Installations from other sources (such as GitHub or the ShareWire site) receive updates through ShareWire.in using the native updater. Free forever.

== Frequently Asked Questions ==

= Will this break my site? =

The Safe Defaults preset contains only non-breaking optimizations. Start there, and enable Maximum Performance selectively after verifying your theme and plugins still work. Each toggle matches a standard WordPress filter or action, so nothing custom is required.

= Does this work with my page builder or WooCommerce? =

All toggles are opt-in and reversible. If a builder depends on a disabled asset such as Gutenberg block CSS, simply leave that toggle off. We recommend testing Maximum Performance on a staging site first.

= Is a license key needed? =

No. CoreSlim is free open source software. All optimizations and updates work with no license key and no activation.

= How is my data handled? =

CoreSlim stores all settings locally in your WordPress database only. It will not contact any external server unless you opt in under Settings > CoreSlim > Privacy & Consent. Telemetry (WordPress version, PHP version, plugin version, site domain) is sent to ShareWire.in ONLY when you enable "Send anonymous usage telemetry to ShareWire.in". No personal data, passwords, or content is ever collected. CoreSlim similarly never sends an update request outside of the WordPress update screen unless you have automatic updates enabled.

== Changelog ==

= 1.0.2 =
* WordPress.org build now uses native WordPress.org updates only (no external update server), while GitHub/ShareWire installs keep ShareWire auto-updates. Tested up to WordPress 7.0.

= 1.0.1 =
* Telemetry is now opt-in (off by default) under Settings > Privacy & Consent, brought forward to meet WordPress.org guideline #7 (no user tracking without explicit consent).

= 1.0.0 =
* Initial public release. Asset, header, security, and performance modules.
* Safe, Max, and Reset presets.
* JSON settings export and import.
* ShareWire automatic updates and anonymous telemetry.
