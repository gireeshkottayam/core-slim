<?php
/**
 * CoreSlim automated test suite. Run:  php tests/test-coreslim.php
 * Unless otherwise noted, each class is tested in isolation from WordPress hooks
 * (the classes are static and side-effect free until init() is invoked).
 */

require_once __DIR__ . '/bootstrap.php';

$pass = 0;
$fail = 0;

function check($cond, $label)
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

/* ---------- 1. Option integrity ---------- */
$d = CoreSlim_Settings::defaults();
check(is_array($d) && count($d) >= 20, 'Default settings is a complete array');
check(isset($d['disable_emojis'], $d['disable_xmlrpc'], $d['heartbeat_admin_freq']), 'Key defaults present');
check($d['disable_emojis'] === false, 'Defaults start all toggles off');
check($d['max_revisions'] === -1, 'Default max revisions is -1 (WordPress default)');
check($d['autosave_interval'] === 60, 'Default autosave interval is 60');

$all = CoreSlim_Settings::getAll();
check(count($all) === count($d), 'getAll returns a full merged array');

/* ---------- 2. Sanitization ---------- */
$clean = CoreSlim_Settings::sanitize(array(
    'disable_emojis' => '1',
    'disable_xmlrpc' => '1',
    'disable_dashicons' => '',
    'max_revisions' => '5',
    'autosave_interval' => '180',
    'heartbeat_admin_freq' => '60',
    'heartbeat_editor_freq' => '120',
    'unknown_fake' => 'should drop',
));
check($clean['disable_emojis'] === true, 'Sanitize turns string "1" into bool true');
check($clean['disable_xmlrpc'] === true, 'Sanitize keeps xmlrpc true');
check($clean['disable_dashicons'] === false, 'Sanitize turns empty into false');
check($clean['max_revisions'] === 5, 'Sanitize maps valid revisions value');
check($clean['autosave_interval'] === 180, 'Sanitize maps valid autosave value');
check($clean['heartbeat_admin_freq'] === 60, 'Sanitize keeps valid heartbeat freq');
check(!isset($clean['unknown_fake']), 'Sanitize drops unknown keys');
check(!isset($clean['unknown_fake']), 'Sanitize whitelists keys only');
$bad = CoreSlim_Settings::sanitize(array('max_revisions' => '999'));
check($bad['max_revisions'] === -1, 'Sanitize clamps invalid revisions to default');
$bad2 = CoreSlim_Settings::sanitize(array('heartbeat_admin_freq' => '5'));
check($bad2['heartbeat_admin_freq'] === 15, 'Sanitize clamps heartbeat min to 15');

/* ---------- 3. Presets ---------- */
$safe = CoreSlim_Settings::applyPreset('safe');
check($safe['disable_emojis'] === true, 'Safe preset enables emojis removal');
check($safe['remove_generator'] === true, 'Safe preset removes generator');
check($safe['disable_xmlrpc'] === true, 'Safe preset disables xmlrpc');
check($safe['disable_jquery_migrate'] === false, 'Safe preset keeps jquery migrate off');
check($safe['max_revisions'] === -1, 'Safe preset leaves revisions at default');

$max = CoreSlim_Settings::applyPreset('max');
check($max['disable_jquery_migrate'] === true, 'Max preset enables jquery migrate');
check($max['disable_embeds'] === true, 'Max preset disables embeds');
check($max['disable_dashicons'] === true, 'Max preset disables dashicons');
check($max['heartbeat_frontend'] === true, 'Max preset disables front-end heartbeat');
check($max['heartbeat_admin_freq'] === 60, 'Max preset throttles admin heartbeat');
check($max['heartbeat_editor_freq'] === 120, 'Max preset throttles editor heartbeat');
check($max['max_revisions'] === 3, 'Max preset limits revisions to 3');

$reset = CoreSlim_Settings::applyPreset('reset');
check($reset === CoreSlim_Settings::defaults(), 'Reset preset returns exact defaults');

/* ---------- 4. Export / Import ---------- */
CoreSlim_Settings::save(CoreSlim_Settings::applyPreset('max'));
$json = CoreSlim_Settings::exportSettings();
check(is_string($json) && strpos($json, 'disable_emojis') !== false, 'Export returns JSON string');
check(CoreSlim_Settings::importSettings($json) === true, 'Import accepts valid JSON');
check(CoreSlim_Settings::get('disable_xmlrpc') === true, 'Imported settings persisted');
check(CoreSlim_Settings::importSettings('{not json') === false, 'Import rejects invalid JSON');
check(CoreSlim_Settings::importSettings('42') === false, 'Import rejects non-object JSON');

/* ---------- 5. Assets module hook wiring ---------- */
CoreSlim_Settings::save(CoreSlim_Settings::defaults());
$optsSafe = CoreSlim_Settings::applyPreset('safe');
CoreSlim_Assets::init($optsSafe);
$ref = new ReflectionMethod('CoreSlim_Assets', 'disableEmojiTinymce');
check($ref->isPublic(), 'Assets exposes TinyMCE emoji removal callback');
$result = CoreSlim_Assets::removeEmojiPrefetch(array('//s.w.org', '//cdn.example.com'), 'dns-prefetch');
check(!in_array('//s.w.org', $result, true), 'Emoji DNS prefetch to s.w.org removed');
check(in_array('//cdn.example.com', $result, true), 'Unrelated DNS prefetch preserved');
$plugins = CoreSlim_Assets::disableEmojiTinymce(array('wpemoji', 'link', 'textpattern'));
check(!in_array('wpemoji', $plugins, true), 'TinyMCE wpemoji plugin removed');
check(in_array('link', $plugins, true), 'TinyMCE other plugins preserved');

/* ---------- 6. Cleaner module ---------- */
CoreSlim_Cleaner::init($optsSafe);
$ref = new ReflectionMethod('CoreSlim_Cleaner', 'disableSelfPingbacks');
check($ref->isPublic(), 'Cleaner exposes self-pingback filter');
$home = 'https://example.com';
$links = array('https://other.com/c', $home . '/post-a', $home . '/post-b', 'https://third.net/x');
CoreSlim_Cleaner::disableSelfPingbacks($links);
check(!in_array($home . '/post-a', $links, true), 'Self-pingback local URL removed');
check(in_array('https://other.com/c', $links, true), 'External pingback preserved');

/* ---------- 7. Security module ---------- */
CoreSlim_Security::init($optsSafe);
check(CoreSlim_Security::removePingbackHeader(array('X-Pingback' => 'x', 'Keep' => 'y')) === array('Keep' => 'y'), 'X-Pingback header stripped');
check(CoreSlim_Security::blockXmlrpcMethods() === array(), 'XML-RPC methods emptied');

/* ---------- 8. Performance module ---------- */
$optsMax = CoreSlim_Settings::applyPreset('max');
CoreSlim_Settings::save($optsMax);
CoreSlim_Performance::init($optsMax);
check(CoreSlim_Performance::revisionsToKeep(99, null) === 3, 'Revisions filter returns configured limit');
$baseSettings = array('interval' => 15);
$GLOBALS['__is_admin'] = true;
$adminSettings = CoreSlim_Performance::heartbeatAdminFreq($baseSettings);
check($adminSettings['interval'] === 60, 'Admin heartbeat uses configured 60s under max preset');
$GLOBALS['pagenow'] = 'post.php';
$editorSettings = CoreSlim_Performance::heartbeatEditorFreq($baseSettings);
check($editorSettings['interval'] === 120, 'Editor heartbeat uses configured 120s under max preset');
$GLOBALS['__is_admin'] = false;
check(CoreSlim_Performance::autosaveInterval(60) === 180, 'Autosave interval returns configured 180s');

/* ---------- 9. Rule 36 / 21 dash compliance ---------- */
$root = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$dashFound = false;
foreach ($files as $f) {
    if ($f->isDir()) {
        continue;
    }
    $ext = $f->getExtension();
    if (!in_array($ext, array('php', 'txt', 'md', 'css', 'js', 'svg'), true)) {
        continue;
    }
    $rel = str_replace('\\', '/', $f->getPathname());
    if (strpos($rel, '/build/') !== false) {
        continue;
    }
    $content = (string) file_get_contents($f->getPathname());
    if (preg_match('/[\x{2014}\x{2013}]/u', $content)) {
        echo "DASH in: $rel\n";
        $dashFound = true;
    }
}
check(!$dashFound, 'Rule 36: no em or en dash in any source or doc file');

/* ---------- 10. Updater version handling ---------- */
$ref = new ReflectionClass('CoreSlim_Updater');
check($ref->hasMethod('fetch'), 'Updater defines fetch()');
check($ref->hasMethod('check'), 'Updater defines check()');
check($ref->hasMethod('info'), 'Updater defines info()');
check($ref->hasMethod('telemetry'), 'Updater defines telemetry()');

/* ---------- 11. Admin sections reflect all keys ---------- */
$admin = new ReflectionClass('CoreSlim_Admin');
$m = $admin->getMethod('sections');
$m->setAccessible(true);
$sections = $m->invoke(null);
$fieldKeys = array();
foreach ($sections as $sec) {
    foreach (array_keys($sec['fields'] ?? array()) as $k) {
        $fieldKeys[] = $k;
    }
    foreach (array_keys($sec['selects'] ?? array()) as $k) {
        $fieldKeys[] = $k;
    }
}
$allDefaults = array_keys(CoreSlim_Settings::defaults());
foreach ($allDefaults as $k) {
    check(in_array($k, $fieldKeys, true), "Settings key $k present in admin UI");
}

echo "\n----\n";
echo "Passed: $pass  Failed: $fail\n";
exit($fail === 0 ? 0 : 1);
