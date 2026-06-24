<?php
/**
 * Example 12 — Unicode Text
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_12_unicode.php
 *   PDFNATIVE_LIB=... UNICODE_FONT_PATH=/path/to/NotoSans-Regular.ttf php example_12_unicode.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$uniFont   = getenv('UNICODE_FONT_PATH') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

$samples = [
    ['Latin (basic)',      'Hello, World! 0 1 2 3 4 5 6 7 8 9'],
    ['Latin extended',     'Héllo Wörld — café, naïve, résumé, façade'],
    ['Greek',              'Ελληνικά — Αλφάβητο Αβγδεζηθ'],
    ['Cyrillic',           'Привет мир — кириллица'],
    ['Symbols',            '© ® ™ € £ ¥ § ¶ † ‡ • … ‰'],
    ['Arrows & math',      '← → ↑ ↓ ↔ ∑ ∏ √ ∞ ≠ ≤ ≥ ∈'],
    ['Box drawing',        '┌─┬─┐  │ │ │  ├─┼─┤  └─┴─┘'],
];

$doc    = new PdfDocument($lib);
$doc->setTitle('Unicode Text');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Unicode Text Rendering', 72, 50, $h); $h->close();

if ($uniFont && file_exists($uniFont)) {
    $s = new PdfStyle($lib); $s->setSize(9)->setColor(80, 80, 80);
    $canvas->drawText('Using font: ' . basename($uniFont), 72, 72, $s); $s->close();
}

$y     = 90.0;
$lblS  = new PdfStyle($lib); $lblS->setSize(8)->setColor(100, 100, 100);
$smpS  = new PdfStyle($lib); $smpS->setSize(12);
if ($uniFont && file_exists($uniFont)) $smpS->setFontFile($uniFont);

foreach ($samples as [$script, $text]) {
    $canvas->drawText($script, 72, $y, $lblS);  $y += 12;
    $canvas->drawText($text,   72, $y, $smpS);  $y += 20;
    $canvas->drawLine(72, $y, $W - 72, $y, 220, 220, 220, 0.3);
    $y += 6;
}
$lblS->close(); $smpS->close();

$n = new PdfStyle($lib); $n->setSize(8)->setColor(130, 130, 130);
$canvas->drawText(
    'Tip: set UNICODE_FONT_PATH to a wide-coverage font (e.g. Noto Sans) for full glyph rendering.',
    72, $y + 10, $n
);
$n->close();

$canvas->close();
$doc->save($outputDir . '/example_12_unicode.pdf');
$doc->close();

echo "Written to {$outputDir}/example_12_unicode.pdf\n";
