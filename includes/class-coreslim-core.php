<?php
/**
 * CoreSlim Core: main controller, conditional hook registration based on saved settings.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CoreSlim_Core
{
    public static function boot(): void
    {
        $opts = CoreSlim_Settings::getAll();
        CoreSlim_Assets::init($opts);
        CoreSlim_Cleaner::init($opts);
        CoreSlim_Security::init($opts);
        CoreSlim_Performance::init($opts);
    }
}
