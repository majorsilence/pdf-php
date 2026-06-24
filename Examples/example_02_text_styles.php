<?php

declare(strict_types=1);

/**
 * Example 02 — Text Styles
 *
 * Demonstrates font sizes, bold, italic, colours, alignment, and decorations.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_02_text_styles.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use const MajorsilencePdf\ALIGN_LEFT;
use const MajorsilencePdf\ALIGN_CENTER;
use const MajorsilencePdf\ALIGN_RIGHT;
use const MajorsilencePdf\DECOR_UNDERLINE;
use const MajorsilencePdf\DECOR_STRIKETHROUGH;
use const MajorsilencePdf\DECOR_OVERLINE;

$libPath = getenv('PDFNATIVE_LIB') ?: '';
if ($libPath === '') {
    fwrite(STDERR, "Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");
    exit(1);
}

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$ffi = PdfLibrary::load($libPath);

$doc    = new PdfDocument($ffi);
$doc->setTitle('Text Styles');
$canvas = $doc->addPage(595.28, 841.89);

$y = 50.0;

$h = (new PdfStyle($ffi))->setSize(24)->setBold();
$canvas->drawText('Text Styles', 72, $y, $h);
$h->close();
$y += 36;

// Font sizes
$h = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Font sizes', 72, $y, $h);
$h->close();
$y += 26;

foreach ([8, 10, 12, 14, 18, 24] as $size) {
    $s = (new PdfStyle($ffi))->setSize((float) $size);
    $canvas->drawText("{$size} pt — The quick brown fox", 72, $y, $s);
    $s->close();
    $y += $size + 6;
}
$y += 12;

// Bold / italic
$h = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Bold and italic', 72, $y, $h);
$h->close();
$y += 26;

$s = (new PdfStyle($ffi))->setSize(12)->setBold();
$canvas->drawText('Bold text', 72, $y, $s);
$s->close();
$y += 18;

$s = (new PdfStyle($ffi))->setSize(12)->setItalic();
$canvas->drawText('Italic text', 72, $y, $s);
$s->close();
$y += 18;

$s = (new PdfStyle($ffi))->setSize(12)->setBold()->setItalic();
$canvas->drawText('Bold italic text', 72, $y, $s);
$s->close();
$y += 28;

// Colours
$h = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Colour', 72, $y, $h);
$h->close();
$y += 26;

foreach ([[220, 0, 0, 'Red'], [0, 160, 0, 'Green'], [0, 0, 200, 'Blue'], [128, 128, 128, 'Gray']] as [$r, $g, $b, $label]) {
    $s = (new PdfStyle($ffi))->setSize(12)->setColor($r, $g, $b);
    $canvas->drawText("{$label} text (r={$r}, g={$g}, b={$b})", 72, $y, $s);
    $s->close();
    $y += 18;
}
$y += 10;

// Alignment
$h = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Alignment', 72, $y, $h);
$h->close();
$y += 26;

$boxWidth = 595.28 - 144;

$s = (new PdfStyle($ffi))->setSize(12)->setAlignment(ALIGN_LEFT);
$canvas->drawText('Left-aligned text', 72, $y, $s);
$s->close();
$y += 18;

$s = (new PdfStyle($ffi))->setSize(12)->setAlignment(ALIGN_CENTER);
$canvas->drawTextbox('Centre-aligned text', 72, $y, $boxWidth, 20.0, $s);
$s->close();
$y += 22;

$s = (new PdfStyle($ffi))->setSize(12)->setAlignment(ALIGN_RIGHT);
$canvas->drawTextbox('Right-aligned text', 72, $y, $boxWidth, 20.0, $s);
$s->close();
$y += 28;

// Decorations
$h = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Decoration', 72, $y, $h);
$h->close();
$y += 26;

$s = (new PdfStyle($ffi))->setSize(12)->setDecoration(DECOR_UNDERLINE);
$canvas->drawText('Underlined text', 72, $y, $s);
$s->close();
$y += 18;

$s = (new PdfStyle($ffi))->setSize(12)->setDecoration(DECOR_STRIKETHROUGH);
$canvas->drawText('Strikethrough text', 72, $y, $s);
$s->close();
$y += 18;

$s = (new PdfStyle($ffi))->setSize(12)->setDecoration(DECOR_OVERLINE);
$canvas->drawText('Overline text', 72, $y, $s);
$s->close();

$canvas->close();
$out = $outputDir . '/example_02_text_styles.pdf';
$doc->save($out);
$doc->close();

echo "Written to {$out}\n";
