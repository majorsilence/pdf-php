<?php
/**
 * Example 19 — Text Wrapping
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_19_text_wrapping.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use const MajorsilencePdf\ALIGN_LEFT;
use const MajorsilencePdf\ALIGN_CENTER;
use const MajorsilencePdf\ALIGN_RIGHT;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H]  = [595.28, 841.89];
$margin   = 60.0;
$tw       = $W - 2 * $margin;

$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod '
       . 'tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, '
       . 'quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo '
       . 'consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse '
       . 'cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non '
       . 'proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

$doc    = new PdfDocument($lib);
$doc->setTitle('Text Wrapping');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Text Wrapping with draw_textbox', $margin, 44, $h); $h->close();

$y    = 70.0;
$lblS = new PdfStyle($lib); $lblS->setSize(9)->setBold()->setColor(26, 86, 160);

foreach ([
    ['Left-aligned (full width)', ALIGN_LEFT],
    ['Centred',                   ALIGN_CENTER],
    ['Right-aligned',             ALIGN_RIGHT],
] as [$label, $align]) {
    $canvas->drawText($label, $margin, $y, $lblS); $y += 12;
    $s = new PdfStyle($lib); $s->setSize(11)->setAlignment($align);
    $canvas->drawTextbox($lorem, $margin, $y, $tw, 80, $s); $s->close();
    $canvas->drawRect($margin, $y, $tw, 80, 0, 0, 0, false, 200, 200, 200, 0.3, true);
    $y += 92;
}

// Narrow two-column layout
$canvas->drawText('Narrow column (160 pt wide)', $margin, $y, $lblS); $y += 12;
$s = new PdfStyle($lib); $s->setSize(10)->setAlignment(ALIGN_LEFT);
$canvas->drawTextbox($lorem, $margin, $y, 160, 200, $s); $s->close();
$canvas->drawRect($margin, $y, 160, 200, 0, 0, 0, false, 200, 200, 200, 0.3, true);

$s = new PdfStyle($lib); $s->setSize(10)->setAlignment(ALIGN_LEFT);
$canvas->drawTextbox($lorem, $margin + 180, $y, 160, 200, $s); $s->close();
$canvas->drawRect($margin + 180, $y, 160, 200, 0, 0, 0, false, 200, 200, 200, 0.3, true);

$lblS->close();
$canvas->close();
$doc->save($outputDir . '/example_19_text_wrapping.pdf');
$doc->close();

echo "Written to {$outputDir}/example_19_text_wrapping.pdf\n";
