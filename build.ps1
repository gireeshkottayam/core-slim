# build.ps1 - produce an installable WordPress plugin .zip for CoreSlim.
# Usage:  powershell -File build.ps1 [version]   (defaults to 1.0.0)
# Output: build/core-slim-<version>.zip containing a top-level
#         core-slim/ folder (for Upload in Plugins > Add New).
param([string]$Version = "")

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

$zip = Join-Path $build "core-slim-$version.zip"
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

[System.IO.Compression.ZipFile]::CreateFromDirectory($tmp, $zip)
Remove-Item $tmp -Recurse -Force
Write-Host "Built: $zip"
