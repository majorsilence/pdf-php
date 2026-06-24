<?php
/**
 * Example 06 — Custom Font
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so \
 *   CUSTOM_FONT_PATH=/path/to/MyFont.ttf \
 *   php example_06_custom_font.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$fontPath  = getenv('CUSTOM_FONT_PATH') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

$doc    = new PdfDocument($lib);
$doc->setTitle('Custom Font');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib);
$h->setSize(18)->setBold();
$canvas->drawText('Custom Font Embedding', 72, 50, $h);
$h->close();

if ($fontPath && file_exists($fontPath)) {
    $s = new PdfStyle($lib);
    $s->setSize(10)->setColor(80, 80, 80);
    $canvas->drawText('Font file: ' . basename($fontPath), 72, 78, $s);
    $s->close();

    $y = 100.0;
    foreach ([10, 12, 14, 18, 24, 32] as $size) {
        $s = new PdfStyle($lib);
        $s->setFontFile($fontPath)->setSize($size);
        $canvas->drawText("{$size} pt — The quick brown fox jumps over the lazy dog", 72, $y, $s);
        $s->close();
        $y += $size + 8;
    }
    $y += 10;
    $s = new PdfStyle($lib);
    $s->setFontFile($fontPath)->setSize(14)->setBold();
    $canvas->drawText('Bold variant (if supported by the font file):', 72, $y, $s);
    $s->close();
    $y += 22;
    $s = new PdfStyle($lib);
    $s->setFontFile($fontPath)->setSize(12)->setItalic();
    $canvas->drawText('Italic variant (if supported by the font file):', 72, $y, $s);
    $s->close();
} else {
    $s = new PdfStyle($lib);
    $s->setSize(11)->setColor(180, 0, 0);
    $canvas->drawText('CUSTOM_FONT_PATH not set or file not found.', 72, 100, $s);
    $canvas->drawText('Set CUSTOM_FONT_PATH=/path/to/a/TrueType.ttf and re-run.', 72, 118, $s);
    $s->close();

    $y = 152.0;
    $s = new PdfStyle($lib);
    $s->setSize(11)->setColor(80, 80, 80);
    $canvas->drawText('Falling back to built-in Helvetica:', 72, $y, $s);
    $s->close();
    $y += 18;
    foreach ([10, 12, 14, 18, 24] as $size) {
        $s = new PdfStyle($lib);
        $s->setSize($size);
        $canvas->drawText("{$size} pt — The quick brown fox (Helvetica)", 72, $y, $s);
        $s->close();
        $y += $size + 8;
    }
}

$canvas->close();
$doc->save($outputDir . '/example_06_custom_font.pdf');
$doc->close();

echo "Written to {$outputDir}/example_06_custom_font.pdf\n";
