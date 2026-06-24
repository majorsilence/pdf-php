<?php
/**
 * Example 07 — Image
 *
 * Embeds a synthetic RGB24 image and (optionally) a JPEG from disk.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_07_image.php
 *   PDFNATIVE_LIB=... JPEG_PATH=/path/to/photo.jpg php example_07_image.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$jpegPath  = getenv('JPEG_PATH') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

function makeGradient(int $w, int $h): string {
    $pixels = '';
    for ($py = 0; $py < $h; $py++) {
        for ($px = 0; $px < $w; $px++) {
            $pixels .= chr((int)(255 * $px / $w))
                     . chr((int)(255 * $py / $h))
                     . chr(180);
        }
    }
    return $pixels;
}

function makeCheckerboard(int $w, int $h, int $cell = 20): string {
    $pixels = '';
    for ($py = 0; $py < $h; $py++) {
        for ($px = 0; $px < $w; $px++) {
            $v = ((intdiv($px, $cell) + intdiv($py, $cell)) % 2 === 0) ? 255 : 60;
            $pixels .= chr($v) . chr($v) . chr($v);
        }
    }
    return $pixels;
}

$doc    = new PdfDocument($lib);
$doc->setTitle('Image Embedding');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Image Embedding', 72, 40, $h); $h->close();

$label = new PdfStyle($lib); $label->setSize(10);
$y = 70.0;

// Gradient
$canvas->drawText('Synthetic gradient (raw RGB24, 300×150 pixels):', 72, $y, $label);
$y += 14;
$grad = makeGradient(300, 150);
$canvas->drawImage($grad, 300, 150, 72, $y, 300, 150, false);
$y += 165;

// Checkerboard
$canvas->drawText('Checkerboard pattern (raw RGB24, 200×100):', 72, $y, $label);
$y += 14;
$checker = makeCheckerboard(200, 100);
$canvas->drawImage($checker, 200, 100, 72, $y, 200, 100, false);
$y += 115;

// Scaled
$canvas->drawText('Same gradient at different scales:', 72, $y, $label);
$y += 14;
$xPos = 72;
foreach ([[80, 40], [120, 60], [160, 80]] as [$dw, $dh]) {
    $canvas->drawImage($grad, 300, 150, $xPos, $y, $dw, $dh, false);
    $canvas->drawText("{$dw}×{$dh} pts", $xPos, $y + $dh + 2, $label);
    $xPos += $dw + 10;
}
$y += 100;

// JPEG from disk
if ($jpegPath && file_exists($jpegPath)) {
    $canvas->drawText('JPEG from disk: ' . basename($jpegPath), 72, $y, $label);
    $y += 14;
    $canvas->drawImage(file_get_contents($jpegPath), 0, 0, 72, $y, 200, 150, true);
} else {
    $canvas->drawText('Set JPEG_PATH=/path/to/photo.jpg to embed a JPEG from disk.', 72, $y, $label);
}

$label->close();
$canvas->close();
$doc->save($outputDir . '/example_07_image.pdf');
$doc->close();

echo "Written to {$outputDir}/example_07_image.pdf\n";
