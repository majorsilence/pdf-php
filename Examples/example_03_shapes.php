<?php

declare(strict_types=1);

/**
 * Example 03 — Shapes
 *
 * Draws lines, rectangles (filled/stroked/both), and ellipses.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_03_shapes.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;

$libPath = getenv('PDFNATIVE_LIB') ?: '';
if ($libPath === '') {
    fwrite(STDERR, "Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");
    exit(1);
}

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$ffi    = PdfLibrary::load($libPath);
$doc    = new PdfDocument($ffi);
$doc->setTitle('Shapes');
$canvas = $doc->addPage(595.28, 841.89);

$y = 50.0;

$canvas->drawText('Lines', 72, $y);
$y += 20;

foreach ([[0, 0, 0, 0.5], [200, 0, 0, 1.0], [0, 0, 200, 2.0], [0, 150, 0, 3.0]] as $i => [$r, $g, $b, $w]) {
    $canvas->drawLine(72, $y + $i * 14, 400, $y + $i * 14, $r, $g, $b, $w);
}
$y += 80;

$canvas->drawText('Filled rectangles', 72, $y);
$y += 16;

$colors = [[220, 50, 50], [50, 150, 50], [50, 50, 220], [200, 150, 0]];
foreach ($colors as $i => [$r, $g, $b]) {
    $canvas->drawRect(72 + $i * 110, $y, 100, 60, [$r, $g, $b]);
}
$y += 80;

$canvas->drawText('Stroked rectangles', 72, $y);
$y += 16;

foreach ($colors as $i => [$r, $g, $b]) {
    $canvas->drawRect(72 + $i * 110, $y, 100, 60, null, [$r, $g, $b], 2.0);
}
$y += 80;

$canvas->drawText('Filled + stroked rectangles', 72, $y);
$y += 16;

$canvas->drawRect(72,  $y, 100, 60, [240, 200, 200], [180, 0, 0],   2.0);
$canvas->drawRect(182, $y, 100, 60, [200, 240, 200], [0, 160, 0],   2.0);
$canvas->drawRect(292, $y, 100, 60, [200, 220, 255], [0, 0, 180],   2.0);
$y += 90;

$canvas->drawText('Ellipses', 72, $y);
$y += 16;

$canvas->drawEllipse(72,  $y, 120, 80, [220, 80, 80]);
$canvas->drawEllipse(210, $y, 120, 80, [80, 200, 80]);
$canvas->drawEllipse(348, $y, 100, 80, null, [0, 0, 200], 2.0);
$y += 100;

$canvas->drawText('Crosshair in circle', 72, $y);
$y += 16;

$cx = 160; $cy = $y + 50; $r = 40;
$canvas->drawEllipse($cx - $r, $cy - $r, $r * 2, $r * 2, null, [0, 0, 0], 1.0);
$canvas->drawLine($cx - $r, $cy, $cx + $r, $cy, 0, 0, 0, 0.5);
$canvas->drawLine($cx, $cy - $r, $cx, $cy + $r, 0, 0, 0, 0.5);

$canvas->close();
$out = $outputDir . '/example_03_shapes.pdf';
$doc->save($out);
$doc->close();

echo "Written to {$out}\n";
