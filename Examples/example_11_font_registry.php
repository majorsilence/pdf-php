<?php
/**
 * Example 11 — Font Registry
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so \
 *   FONT_DIR=/usr/share/fonts/truetype/liberation \
 *   php example_11_font_registry.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$fontDir   = getenv('FONT_DIR') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];
$sample  = 'The quick brown fox jumps over the lazy dog  0123456789';

$doc    = new PdfDocument($lib);
$doc->setTitle('Font Registry');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Font Registry', 72, 50, $h); $h->close();

$y = 80.0;
$fontFiles = [];
if ($fontDir && is_dir($fontDir)) {
    $fontFiles = array_slice(glob($fontDir . '/*.ttf') ?: [], 0, 12);
    sort($fontFiles);
}

if ($fontFiles) {
    $s = new PdfStyle($lib); $s->setSize(9)->setColor(100, 100, 100);
    $canvas->drawText('Loaded ' . count($fontFiles) . ' font(s) from ' . $fontDir, 72, $y, $s);
    $s->close();
    $y += 16;

    foreach ($fontFiles as $fontPath) {
        $name = pathinfo($fontPath, PATHINFO_FILENAME);
        $s = new PdfStyle($lib); $s->setSize(8)->setColor(100, 100, 100);
        $canvas->drawText($name, 72, $y, $s); $s->close();
        $y += 11;
        $s = new PdfStyle($lib); $s->setFontFile($fontPath)->setSize(12);
        $canvas->drawText($sample, 72, $y, $s); $s->close();
        $y += 20;
        $canvas->drawLine(72, $y, $W - 72, $y, 220, 220, 220, 0.3);
        $y += 6;
    }
} else {
    $s = new PdfStyle($lib); $s->setSize(10)->setColor(180, 0, 0);
    $canvas->drawText('FONT_DIR not set — falling back to built-in Helvetica.', 72, $y, $s);
    $y += 16;
    $canvas->drawText('Set FONT_DIR to a directory of .ttf files.', 72, $y, $s);
    $s->close();
    $y += 30;

    foreach ([
        ['Regular',     false, false],
        ['Bold',        true,  false],
        ['Italic',      false, true],
        ['Bold-Italic', true,  true],
    ] as [$variant, $bold, $italic]) {
        $lbl = new PdfStyle($lib); $lbl->setSize(8)->setColor(100, 100, 100);
        $canvas->drawText("Helvetica {$variant}", 72, $y, $lbl); $lbl->close();
        $y += 11;
        $s = new PdfStyle($lib); $s->setSize(12);
        if ($bold) $s->setBold();
        if ($italic) $s->setItalic();
        $canvas->drawText($sample, 72, $y, $s); $s->close();
        $y += 20;
    }
}

$canvas->close();
$doc->save($outputDir . '/example_11_font_registry.pdf');
$doc->close();

echo "Written to {$outputDir}/example_11_font_registry.pdf\n";
