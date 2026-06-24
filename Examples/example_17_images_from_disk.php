<?php
/**
 * Example 17 — Images from Disk
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so \
 *   IMAGE_DIR=/path/to/photos \
 *   php example_17_images_from_disk.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$imageDir  = getenv('IMAGE_DIR') ?: '';
$imagePath = getenv('IMAGE_PATH') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H]  = [595.28, 841.89];
$margin   = 50.0;

function syntheticRgb(int $w, int $h, int $rBase, int $gBase, int $bBase): string {
    $pixels = '';
    for ($py = 0; $py < $h; $py++) {
        for ($px = 0; $px < $w; $px++) {
            $pixels .= chr(min(255, $rBase + $px * 2))
                     . chr(min(255, $gBase + $py * 2))
                     . chr($bBase);
        }
    }
    return $pixels;
}

$jpegPaths = [];
if ($imagePath && file_exists($imagePath)) {
    $jpegPaths = [$imagePath];
} elseif ($imageDir && is_dir($imageDir)) {
    $found = array_merge(
        glob($imageDir . '/*.jpg')  ?: [],
        glob($imageDir . '/*.jpeg') ?: []
    );
    sort($found);
    $jpegPaths = array_slice($found, 0, 6);
}

$doc    = new PdfDocument($lib);
$doc->setTitle('Images from Disk');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Images from Disk', $margin, 40, $h); $h->close();

$capS = new PdfStyle($lib); $capS->setSize(8)->setColor(80, 80, 80);
$y    = 68.0;

if ($jpegPaths) {
    $info = new PdfStyle($lib); $info->setSize(9)->setColor(80, 80, 80);
    $canvas->drawText('Embedding ' . count($jpegPaths) . ' JPEG(s)', $margin, $y, $info);
    $info->close();
    $y += 14;

    [$thumbW, $thumbH] = [140, 105];
    $cols = 3;
    foreach ($jpegPaths as $i => $path) {
        $col = $i % $cols; $row = intdiv($i, $cols);
        $bx  = $margin + $col * ($thumbW + 8);
        $by  = $y + $row * ($thumbH + 24);
        $canvas->drawImage(file_get_contents($path), 0, 0, $bx, $by, $thumbW, $thumbH, true);
        $canvas->drawText(basename($path), $bx, $by + $thumbH + 4, $capS);
    }
} else {
    $info = new PdfStyle($lib); $info->setSize(10)->setColor(160, 80, 0);
    $canvas->drawText('IMAGE_DIR or IMAGE_PATH not set. Using synthetic images.', $margin, $y, $info);
    $info->close();
    $y += 18;

    $synthetics = [
        ['Red-gradient',    200, 80,  0],
        ['Blue-gradient',   0,   80,  200],
        ['Green-gradient',  0,   160, 80],
        ['Purple-gradient', 120, 0,   160],
    ];
    [$thumbW, $thumbH] = [100, 60];
    foreach ($synthetics as $i => [$label, $r, $g, $b]) {
        $data = syntheticRgb($thumbW, $thumbH, $r, $g, $b);
        $bx   = $margin + $i * ($thumbW + 10);
        $canvas->drawImage($data, $thumbW, $thumbH, $bx, $y, $thumbW, $thumbH, false);
        $canvas->drawText($label, $bx, $y + $thumbH + 4, $capS);
    }
    $y += $thumbH + 24;

    $n = new PdfStyle($lib); $n->setSize(9)->setColor(130, 130, 130);
    $canvas->drawTextbox(
        'Set IMAGE_DIR=/path/to/photos to embed real JPEG images, ' .
        'or IMAGE_PATH=/path/to/photo.jpg for a single image.',
        $margin, $y, $W - 2 * $margin, 50, $n
    );
    $n->close();
}
$capS->close();

$canvas->close();
$doc->save($outputDir . '/example_17_images_from_disk.pdf');
$doc->close();

echo "Written to {$outputDir}/example_17_images_from_disk.pdf\n";
