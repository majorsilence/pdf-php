<?php

declare(strict_types=1);

/**
 * Example 01 — Hello World
 *
 * Creates a single A4 page PDF with a title and body text.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_01_hello_world.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use function MajorsilencePdf\ALIGN_CENTER;

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

$doc = new PdfDocument($ffi);
$doc->setTitle('Hello World')
    ->setAuthor('Majorsilence PDF');

$canvas = $doc->addPage(595.28, 841.89);

$heading = new PdfStyle($ffi);
$heading->setSize(24)->setBold();
$canvas->drawText('Hello, PDF!', 72, 80, $heading);
$heading->close();

$body = new PdfStyle($ffi);
$body->setSize(12);
$canvas->drawText('This PDF was created with the Majorsilence pdfnative library.', 72, 120, $body);
$canvas->drawText('No .NET runtime is required — the engine runs in-process via FFI.', 72, 140, $body);
$body->close();

$canvas->close();

$out = $outputDir . '/example_01_hello_world.pdf';
$doc->save($out);
$doc->close();

echo "Written to {$out}\n";
