<?php
/**
 * Example 04 — Lines and Strokes
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_04_lines_and_strokes.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

$doc = new PdfDocument($lib);
$doc->setTitle('Lines and Strokes');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib);
$h->setSize(18)->setBold();
$canvas->drawText('Lines and Strokes', 72, 40, $h);
$h->close();

$label = new PdfStyle($lib);
$label->setSize(10);

$y = 80.0;

$canvas->drawText('Line widths (0.5 → 6 pt)', 72, $y, $label);
$y += 16;
foreach ([0.5, 1.0, 1.5, 2.0, 3.0, 4.0, 6.0] as $w) {
    $canvas->drawLine(72, $y, 420, $y, 0, 0, 0, $w);
    $canvas->drawText("{$w} pt", 430, $y - 4, $label);
    $y += 18;
}
$y += 12;

$canvas->drawText('Coloured lines (2 pt)', 72, $y, $label);
$y += 16;
foreach ([
    ['Black',  0,   0,   0],
    ['Red',    220, 0,   0],
    ['Blue',   0,   0,   200],
    ['Green',  0,   160, 0],
    ['Orange', 220, 120, 0],
    ['Purple', 130, 0,   180],
] as [$name, $r, $g, $b]) {
    $canvas->drawLine(72, $y, 300, $y, $r, $g, $b, 2);
    $canvas->drawText($name, 310, $y - 4, $label);
    $y += 18;
}
$y += 12;

$canvas->drawText('Diagonal lines', 72, $y, $label);
$y += 16;
$canvas->drawLine(72, $y, 300, $y + 80, 0, 0, 0, 1);
$canvas->drawLine(300, $y, 72, $y + 80, 0, 0, 0, 1);
$canvas->drawLine(72, $y + 40, 300, $y + 40, 180, 180, 180, 0.5);
$y += 100;

$canvas->drawText('Rectangle drawn from four lines', 72, $y, $label);
$y += 16;
[$x0, $x1, $y1] = [72, 300, $y + 60];
foreach ([
    [$x0, $y, $x1, $y],
    [$x1, $y, $x1, $y1],
    [$x1, $y1, $x0, $y1],
    [$x0, $y1, $x0, $y],
] as [$ax, $ay, $bx, $by]) {
    $canvas->drawLine($ax, $ay, $bx, $by, 60, 60, 60, 2);
}
$canvas->drawText('Border from 4 draw_line calls', $x0 + 10, $y + 26, $label);
$y += 80;

$canvas->drawText('Heavy rule separator', 72, $y, $label);
$y += 12;
$canvas->drawLine(72, $y, $W - 72, $y, 26, 86, 160, 3);
$y += 8;
$canvas->drawLine(72, $y, $W - 72, $y, 26, 86, 160, 0.5);

$label->close();
$canvas->close();
$doc->save($outputDir . '/example_04_lines_and_strokes.pdf');
$doc->close();

echo "Written to {$outputDir}/example_04_lines_and_strokes.pdf\n";
