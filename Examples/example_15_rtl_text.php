<?php
/**
 * Example 15 — RTL Text
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_15_rtl_text.php
 *   PDFNATIVE_LIB=... RTL_FONT_PATH=/path/to/NotoSansArabic.ttf php example_15_rtl_text.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use const MajorsilencePdf\ALIGN_RIGHT;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$rtlFont   = getenv('RTL_FONT_PATH') ?: '';
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

$rtlSamples = [
    ['Arabic — مرحبا بالعالم',  'مرحبا بالعالم! هذا مثال على النص العربي في ملف PDF.'],
    ['Arabic — رقم',             '١ ٢ ٣ ٤ ٥ ٦ ٧ ٨ ٩ ٠'],
    ['Hebrew — שלום עולם',       'שלום! זהו טקסט עברי בתוך קובץ PDF.'],
    ['Bidirectional — EN/AR',    'Price: ٢٥٠ USD — السعر: ٢٥٠ دولار'],
];

$doc    = new PdfDocument($lib);
$doc->setTitle('RTL Text');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Right-to-Left Text', 72, 50, $h); $h->close();

$info = new PdfStyle($lib); $info->setSize(10);
if ($rtlFont && file_exists($rtlFont)) {
    $info->setColor(0, 100, 0);
    $canvas->drawText('RTL font: ' . basename($rtlFont), 72, 72, $info);
} else {
    $info->setColor(160, 80, 0);
    $canvas->drawText('RTL_FONT_PATH not set. Glyphs may not render correctly.', 72, 72, $info);
}
$info->close();

$y     = 100.0;
$lblS  = new PdfStyle($lib); $lblS->setSize(9)->setColor(100, 100, 100);
$rtlS  = new PdfStyle($lib); $rtlS->setSize(14)->setAlignment(ALIGN_RIGHT);
if ($rtlFont && file_exists($rtlFont)) $rtlS->setFontFile($rtlFont);

foreach ($rtlSamples as [$label, $text]) {
    $canvas->drawText($label, 72, $y, $lblS); $y += 14;
    $canvas->drawText($text, 72, $y, $rtlS);  $y += 24;
    $canvas->drawLine(72, $y, $W - 72, $y, 220, 220, 220, 0.3);
    $y += 8;
}
$lblS->close(); $rtlS->close();

$n = new PdfStyle($lib); $n->setSize(9)->setColor(130, 130, 130);
$canvas->drawTextbox(
    'Note: Full RTL shaping (ligatures, contextual forms) requires an OpenType font with ' .
    'Arabic/Hebrew GSUB/GPOS tables and a shaping engine (e.g. HarfBuzz).',
    72, $y + 10, $W - 144, 60, $n
);
$n->close();

$canvas->close();
$doc->save($outputDir . '/example_15_rtl_text.pdf');
$doc->close();

echo "Written to {$outputDir}/example_15_rtl_text.pdf\n";
