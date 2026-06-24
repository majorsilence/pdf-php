<?php

declare(strict_types=1);

/**
 * Example 05 — PDF Merge
 *
 * Creates two PDF documents in memory and merges them into a single file.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_05_merge.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use MajorsilencePdf\PdfMerger;

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

function makePage(\FFI $ffi, string $title, string $body): string
{
    $doc    = new PdfDocument($ffi);
    $doc->setTitle($title);
    $canvas = $doc->addPage(595.28, 841.89);

    $h = (new PdfStyle($ffi))->setSize(20)->setBold();
    $canvas->drawText($title, 72, 80, $h);
    $h->close();

    $b = (new PdfStyle($ffi))->setSize(12);
    $canvas->drawText($body, 72, 120, $b);
    $b->close();

    $canvas->close();
    $bytes = $doc->saveToMemory();
    $doc->close();
    return $bytes;
}

$pdf1 = makePage($ffi, 'Document 1 — Cover',    'This is the first document, rendered into memory.');
$pdf2 = makePage($ffi, 'Document 2 — Appendix', 'This is the second document, also rendered into memory.');

$merger = new PdfMerger($ffi);
$merger->addBytes($pdf1)
       ->addBytes($pdf2);

$out = $outputDir . '/example_05_merge.pdf';
$merger->save($out);
$merger->close();

echo "Merged PDF written to {$out}\n";
