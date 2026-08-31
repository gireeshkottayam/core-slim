# build.ps1 - produce an installable WordPress plugin .zip for CoreSlim.
# Usage:  powershell -File build.ps1 [version] [-WpOrg]
#   -WpOrg  emit the wordpress.org-compliant variant (native wp.org updates only,
#           ShareWire update channel disabled, telemetry still opt-in). Output is
#           build/core-slim-<version>-wporg.zip for the SVN trunk / tags.
#   default emits build/core-slim-<version>.zip for the GitHub/ShareWire channel.
param([string]$Version = "", [switch]$WpOrg)

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$version = if ($Version -eq "") { "1.0.0" } else { $Version }
$build = Join-Path $root "build"
New-Item -ItemType Directory -Force -Path $build | Out-Null

$exe = "D:\wamp64\bin\php\php8.3.28\php.exe"
if (Test-Path $exe) {
    Get-ChildItem (Join-Path $root "core-slim.php"), (Join-Path $root "includes"), (Join-Path $root "tests") -Filter *.php | ForEach-Object {
        & $exe -l $_.FullName | Out-Host
    }
}

# Rule 21 (project master rule): refuse to build if any em/en dash slipped in.
$dashes = Get-ChildItem $root -Recurse -File | Where-Object {
    $_.Extension -in '.php', '.txt', '.md', '.css', '.js' -and $_.FullName -notmatch '\\build\\'
} | Select-String -Pattern '[\u2014\u2013]'
if ($dashes) {
    Write-Host "Rule 21 FAIL: em/en dash found. Aborting build." -ForegroundColor Red
    exit 1
}

$suffix = if ($WpOrg) { "-wporg" } else { "" }
$zip = Join-Path $build "core-slim-$version$suffix.zip"
if (Test-Path $zip) { Remove-Item $zip }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$tmp = Join-Path $build ("pkg_" + [guid]::NewGuid().ToString("N"))
New-Item -ItemType Directory -Force -Path (Join-Path $tmp "core-slim") | Out-Null

foreach ($item in @("core-slim.php", "includes", "assets", "readme.txt", "LICENSE", "README.md")) {
    $src = Join-Path $root $item
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $tmp "core-slim") -Recurse -Force
    }
}

if ($WpOrg) {
    # The wordpress.org build ships NO ShareWire integration at all: drop the
    # updater class (auto-update endpoint + telemetry) so the plugin has zero
    # external server references and uses native wp.org updates only (guideline
    # #8, #7). Keep the setting toggle code so existing config stays valid.
    $updater = Join-Path $tmp "core-slim\includes\class-coreslim-updater.php"
    if (Test-Path $updater) { Remove-Item $updater }

    # Rewrite the variant constant in the staged copy so the ShareWire update
    # channel is compiled out of the wordpress.org build (guideline #8).
    $bootstrap = Join-Path $tmp "core-slim\core-slim.php"
    (Get-Content $bootstrap) -replace "define\('CORE_SLIM_WPORG', 0\);", "define('CORE_SLIM_WPORG', 1);" -replace ": 'https://sharewire\.in'\);", ": '');" | Set-Content $bootstrap
}

[System.IO.Compression.ZipFile]::CreateFromDirectory($tmp, $zip)
Remove-Item $tmp -Recurse -Force
Write-Host "Built: $zip"
