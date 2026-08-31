<?php
/**
 * CoreSlim wordpress.org build test.
 * Boots the plugin with CORE_SLIM_WPORG=1 and asserts the ShareWire update
 * channel is NOT registered (guideline #8), so updates flow natively from
 * WordPress.org. Telemetry remains opt-in and independent.
 * Run:  php tests/test-wporg-build.php
 */

define('CORE_SLIM_WPORG', 1);
require_once __DIR__ . '/bootstrap.php';

$pass = 0;
$fail = 0;
function wcheck($cond, $label)
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "PASS: $label\n";
    } else {
        $fail++;
        echo "FAIL: $label\n";
    }
}

$updateFilter = false;
$apiFilter = false;
foreach (($GLOBALS['__calls'] ?? array()) as $call) {
    if (($call['type'] ?? '') === 'filter'
        && ($call['hook'] ?? '') === 'pre_set_site_transient_update_plugins') {
        $updateFilter = true;
    }
    if (($call['type'] ?? '') === 'filter'
        && ($call['hook'] ?? '') === 'plugins_api') {
        $apiFilter = true;
    }
}
wcheck(CORE_SLIM_WPORG === 1, 'wp.org variant constant is on');
wcheck($updateFilter === false, 'ShareWire update filter NOT registered on wp.org build');
wcheck($apiFilter === false, 'ShareWire plugins_api filter NOT registered on wp.org build');

wcheck(!class_exists('CoreSlim_Updater'), 'ShareWire updater class is NOT shipped on wp.org build');

$ref = class_exists('CoreSlim_Updater') ? new ReflectionClass('CoreSlim_Updater') : null;
if ($ref) {
    $m = $ref->getMethod('init');
    $m->setAccessible(true);
    $m->invoke(null);
}
$updateAfter = false;
foreach (($GLOBALS['__calls'] ?? array()) as $call) {
    if (($call['type'] ?? '') === 'filter'
        && ($call['hook'] ?? '') === 'pre_set_site_transient_update_plugins') {
        $updateAfter = true;
    }
}
wcheck($updateAfter === false, 'No update filter registered even after a direct init attempt on wp.org build');

$d = CoreSlim_Settings::defaults();
wcheck(isset($d['enable_telemetry']) && $d['enable_telemetry'] === false, 'Telemetry still opt-in / OFF on wp.org build');

echo "\n----\n";
echo "Passed: $pass  Failed: $fail\n";
exit($fail === 0 ? 0 : 1);
