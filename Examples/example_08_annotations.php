<?php
/**
 * Example 08 — Annotations (Hyperlinks)
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_08_annotations.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use const MajorsilencePdf\DECOR_UNDERLINE;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H] = [595.28, 841.89];

$doc = new PdfDocument($lib);
$doc->setTitle('Annotations');
$doc->setSubject('Hyperlink annotation demo');
$canvas = $doc->addPage($W, $H);

$h = new PdfStyle($lib); $h->setSize(18)->setBold();
$canvas->drawText('Hyperlink Annotations', 72, 50, $h); $h->close();

$body = new PdfStyle($lib); $body->setSize(11);
$canvas->drawText(
    'Click the links below. The blue underlined text is overlaid with a URI annotation.',
    72, 80, $body
);
$body->close();

$y = 120.0;
$links = [
    ['Majorsilence GitHub',          'https://github.com/majorsilence'],
    ['Majorsilence Reporting',        'https://github.com/majorsilence/Reporting'],
    ['PDF Specification (ISO 32000)', 'https://pdfa.org/resource/pdf-specification-archive/'],
    ['Wikipedia — PDF',               'https://en.wikipedia.org/wiki/PDF'],
];

$linkStyle = new PdfStyle($lib);
$linkStyle->setSize(13)->setColor(26, 86, 160)->setDecoration(DECOR_UNDERLINE);
foreach ($links as [$text, $uri]) {
    $canvas->drawText($text, 72, $y, $linkStyle);
    $approxWidth = strlen($text) * 7.5;
    $canvas->addLink(72, $y - 13, $approxWidth, 18, $uri);
    $y += 28;
}
$linkStyle->close();

$y += 10;
$note = new PdfStyle($lib); $note->setSize(10)->setColor(100, 100, 100);
$canvas->drawText('Links use pdf_canvas_add_link(canvas, x, y, width, height, uri).', 72, $y, $note);
$y += 16;
$canvas->drawText('The annotation rectangle is placed over the rendered text.', 72, $y, $note);
$note->close();

$canvas->close();
$doc->save($outputDir . '/example_08_annotations.pdf');
$doc->close();

echo "Written to {$outputDir}/example_08_annotations.pdf\n";
