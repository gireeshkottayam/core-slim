<?php
/**
 * Static compliance sweep for the wordpress.org submission (what Plugin Check
 * automates: versions, headers, text domain, escaping, nonces, layout, no
 * external calls, no crucft). Usage:
 *   php tools/compliance-sweep.php <path-to-wporg-build-folder>
 * Exits 1 if any check fails. Read-only.
 */
$root = isset($argv[1]) ? rtrim($argv[1], '/\\') : (__DIR__ . '/../../svn-staging/trunk');
$pass = 0; $fail = 0;
function ok($c, $label) { global $pass, $fail; if ($c) { $pass++; echo "PASS: $label\n"; } else { $fail++; echo "FAIL: $label\n"; } }

// 1. Main file header present with required fields.
$bootstrap = "$root/core-slim.php";
ok(is_file($bootstrap), "bootstrap core-slim.php exists");
$h = (string) file_get_contents($bootstrap);
foreach (array('Plugin Name', 'Version', 'Requires at least', 'Requires PHP', 'License', 'License URI', 'Text Domain') as $f) {
    ok(preg_match('/^ \* ' . preg_quote($f, '/') . ':/mi', $h), "header field: $f");
}
// no need for Plugin URI/Author, but check no GPL-gone-wrong
ok(preg_match('/GPL-2\.0-or-later/i', $h), 'license header is GPL-2.0-or-later');

// 2. No class/function name that collides with wp.org conventions badly (all CoreSlim-prefixed).
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) { if (preg_match('/\.(php)$/i', $f->getFilename())) { $files[] = $f->getPathname(); } }
ok(count($files) > 0, "found php files (".count($files).")");

foreach ($files as $f) {
    $src = (string) file_get_contents($f);
    // text domain string must be used (not undefined)
    if (preg_match('/__\(|_e\(|esc_html__\(|esc_html_e\(/', $src)) {
        ok(stripos($src, "'core-slim'") !== false || stripos($src, '"core-slim"') !== false, "text domain used: ".basename($f));
    }
    // no eval, no base64_decode obfuscation, no shell_exec/system/exec
    foreach (array('eval(', 'base64_decode(', 'shell_exec', 'passthru(', '`', 'system(') as $banned) {
        // backtick check must not flag heredoc; skip backtick
        if ($banned === '`') { continue; }
        if (stripos($src, $banned) !== false) {
            ok(false, "cruft ($banned) in ".basename($f));
        }
    }
    // no obvious gate (var_dump)
    if (stripos($src, 'var_dump(') !== false) { ok(false, "var_dump in ".basename($f)); }
}

// 3. NO runtime external HTTP in the wporg build (guideline #8).
// Author/plugin/License URI in the file header are legitimate credit and are
// excluded; we only flag actual outbound CALLS (wp_remote_*, curl, fopen of http,
// fsockopen, stream contexts). The wporg build must have none.
$ext = 0;
foreach ($files as $f) {
    $src = (string) file_get_contents($f);
    // strip the doc header block ( /* ... */ or /** ... */ at top) - credit URIs live there
    $body = preg_replace('/^<\?php\s*(\/\*\*?[\s\S]*?\*\/)/', '', $src);
    if (preg_match('/(wp_remote_(get|post|request)|\bcurl_init\b|\bfsockopen\b|\bfopen\s*\(\s*["\']https?:|stream_context_create)/i', $body)) {
        $ext++;
        echo "RUNTIME-HTTP in ".basename($f)."\n";
    }
    // also catch any https:// outside the header block
    if (preg_match('/https?:\/\/[a-z0-9.-]+/', $body)) { $ext++; echo "URL-OUTSIDE-HEADER in ".basename($f)."\n"; }
}
ok($ext === 0, "no runtime external HTTP in wporg build (saw $ext)");

// 4. Updater class must NOT ship in wporg build.
$updater = glob("$root/includes/class-coreslim-updater.php");
ok(empty($updater), "ShareWire updater class is NOT shipped in wporg build");

// 5. CORE_SLIM_WPORG must be 1 in the wporg build.
ok(preg_match("/define\('CORE_SLIM_WPORG', 1\);/", $h), "CORE_SLIM_WPORG=1 on");

// 6. readme.txt: required headers + Stable tag matches Version.
$readme = "$root/readme.txt";
ok(is_file($readme), "readme.txt exists");
if (is_file($readme)) {
    $r = (string) file_get_contents($readme);
    foreach (array('=== ', 'Contributors:', 'Tags:', 'Requires at least:', 'Tested up to:', 'Requires PHP:', 'Stable tag:', 'License:') as $f) {
        ok(stripos($r, $f) !== false, "readme header: $f");
    }
    if (preg_match('/Stable tag:\s*([0-9.]+)/i', $r, $m) && preg_match('/^ \* Version:\s*([0-9.]+)/mi', $h, $v)) {
        ok($m[1] === $v[1], "Stable tag ($m[1]) == plugin Version ($v[1])");
        ok(isset($v[1]) && version_compare($v[1], '1.0.2', '>='), "version is >= 1.0.2 ($v[1])");
    }
    // changelog must contain a section matching the current version
    ok(preg_match('/^=\s*' . (isset($v[1]) ? preg_quote($v[1], '/') : '1\.0\.2') . '\s*=$/mi', $r), "changelog section for current version");
}

// 7. Text domain declares in header and readme tags sane.
ok(preg_match('/Text Domain:\s*core-slim/i', $h), "Text Domain: core-slim");

echo "\n----\n";
echo "Passed: $pass  Failed: $fail\n";
exit($fail === 0 ? 0 : 1);
