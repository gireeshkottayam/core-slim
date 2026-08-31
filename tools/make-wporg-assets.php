<?php
/**
 * One-shot asset generator for the wordpress.org listing of CoreSlim.
 * Renders icon-256x256.png, icon-128x128.png, banner-772x250.png and
 * screenshot-1.png into svn-assets/ (the folder a wordpress.org SVN repo
 * publishes as top-level assets/). These are brand-styled placeholders that
 * match assets/icon.svg; a designer can replace them before submission.
 * Run:  php products/core-slim/tools/make-wporg-assets.php
 */
$out = dirname(__DIR__) . '/svn-assets';
if (!is_dir($out)) {
    mkdir($out, 0777, true);
}

$blue = array(6, 117, 233);
$blueLight = array(165, 216, 255);
$green = array(0, 208, 132);
$ink = array(3, 35, 15);
$white = array(255, 255, 255);
$bg = array(6, 117, 233);

function renderIcon($size, $blueLight, $green, $ink, $white, $blue)
{
    $im = imagecreatetruecolor($size, $size);
    $cBlue = imagecolorallocate($im, $blue[0], $blue[1], $blue[2]);
    $cBlueL = imagecolorallocate($im, $blueLight[0], $blueLight[1], $blueLight[2]);
    $cGreen = imagecolorallocate($im, $green[0], $green[1], $green[2]);
    $cInk = imagecolorallocate($im, $ink[0], $ink[1], $ink[2]);
    $cWhite = imagecolorallocate($im, $white[0], $white[1], $white[2]);

    imagefilledrectangle($im, 0, 0, $size, $size, $cBlue);
    $s = $size / 128;

    // white header bar
    imagefilledrectangle($im, (int)(32 * $s), (int)(40 * $s), (int)(96 * $s), (int)(56 * $s), $cWhite);
    // light blue bars
    imagefilledrectangle($im, (int)(32 * $s), (int)(68 * $s), (int)(80 * $s), (int)(80 * $s), $cBlueL);
    imagefilledrectangle($im, (int)(32 * $s), (int)(92 * $s), (int)(60 * $s), (int)(104 * $s), $cBlueL);
    // green check circle
    imagefilledellipse($im, (int)(92 * $s), (int)(88 * $s), (int)(36 * $s), (int)(36 * $s), $cGreen);
    // check mark
    imagesetthickness($im, (int)max(2, 4 * $s));
    imageline($im, (int)(84 * $s), (int)(88 * $s), (int)(90 * $s), (int)(94 * $s), $cInk);
    imageline($im, (int)(90 * $s), (int)(94 * $s), (int)(100 * $s), (int)(82 * $s), $cInk);
    return $im;
}

function renderBanner($blue)
{
    $w = 1544; // double-size master, downscaled below as required
    $h = 500;
    $im = imagecreatetruecolor($w, $h);
    imagefilledrectangle($im, 0, 0, $w, $h, imagecolorallocate($im, $blue[0], $blue[1], $blue[2]));
    $cWhite = imagecolorallocate($im, 255, 255, 255);
    $cGreen = imagecolorallocate($im, 0, 208, 132);
    $cBlueL = imagecolorallocate($im, 165, 216, 255);
    $cInk = imagecolorallocate($im, 3, 35, 15);
    $s = $w / 1544;
    // title + subtitle
    imagefilledrectangle($im, (int)(120 * $s), (int)(150 * $s), (int)(760 * $s), (int)(206 * $s), $cWhite);
    imagefilledrectangle($im, (int)(120 * $s), (int)(230 * $s), (int)(620 * $s), (int)(272 * $s), $cBlueL);
    // check circle (right side)
    imagefilledellipse($im, (int)(1200 * $s), (int)(250 * $s), (int)(180 * $s), (int)(180 * $s), $cGreen);
    imagesetthickness($im, (int)max(4, 18 * $s));
    imageline($im, (int)(1155 * $s), (int)(250 * $s), (int)(1185 * $s), (int)(280 * $s), $cInk);
    imageline($im, (int)(1185 * $s), (int)(280 * $s), (int)(1245 * $s), (int)(218 * $s), $cInk);
    imagefilledrectangle($im, (int)(1120 * $s), (int)(300 * $s), (int)(1280 * $s), (int)(312 * $s), $cBlueL);
    return $im;
}

function renderScreenshot($blue)
{
    $w = 1200;
    $h = 900;
    $im = imagecreatetruecolor($w, $h);
    imagefilledrectangle($im, 0, 0, $w, $h, imagecolorallocate($im, 245, 246, 250));
    // mock admin shell top bar
    imagefilledrectangle($im, 0, 0, $w, 64, imagecolorallocate($im, 17, 20, 26));
    // sidebar
    imagefilledrectangle($im, 0, 64, 220, $h, imagecolorallocate($im, 230, 233, 240));
    // content panel
    imagefilledrectangle($im, 260, 120, $w - 40, $h - 50, imagecolorallocate($im, 255, 255, 255));
    // title
    imagefilledrectangle($im, 300, 160, 620, 196, imagecolorallocate($im, $blue[0], $blue[1], $blue[2]));
    $cGreen = imagecolorallocate($im, 0, 208, 132);
    // toggles grid
    $y = 240;
    for ($i = 0; $i < 6; $i++) {
        imagefilledrectangle($im, 300, $y, $w - 100, $y + 44, imagecolorallocate($im, 238, 240, 246));
        imagefilledellipse($im, $w - 150, $y + 22, 80, 40, $cGreen);
        $y += 68;
    }
    return $im;
}

function saveScaled($im, $w, $h, $path)
{
    $dest = imagecreatetruecolor($w, $h);
    imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));
    imagepng($dest, $path, 9);
    imagedestroy($dest);
}

$iconMaster = renderIcon(512, $blueLight, $green, $ink, $white, $blue);
saveScaled($iconMaster, 256, 256, "$out/icon-256x256.png");
saveScaled($iconMaster, 128, 128, "$out/icon-128x128.png");
imagedestroy($iconMaster);

$banner = renderBanner($bg);
saveScaled($banner, 1544, 250, "$out/banner-1544x500.png"); // retained at full size too
saveScaled($banner, 772, 250, "$out/banner-772x250.png");
imagedestroy($banner);

$shot = renderScreenshot($bg);
saveScaled($shot, 1200, 900, "$out/screenshot-1.png");
imagedestroy($shot);

foreach (glob("$out/*.png") as $f) {
    printf("%s : %d bytes\n", basename($f), filesize($f));
}
echo "Done.\n";
