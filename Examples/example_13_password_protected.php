<?php

declare(strict_types=1);

/**
 * Example 06 — Password Protection (Security)
 *
 * Creates an AES-256 password-protected PDF.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_06_security.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use const MajorsilencePdf\PERM_PRINT;
use const MajorsilencePdf\PERM_COPY_TEXT;
use const MajorsilencePdf\PERM_PRINT_HIGH_QUALITY;

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
$doc->setTitle('Password Protected Document')
    ->setAuthor('Majorsilence PDF')
    ->setSecurity(
        userPassword:  'userpass',
        ownerPassword: 'ownerpass',
        permissions:   PERM_PRINT | PERM_COPY_TEXT | PERM_PRINT_HIGH_QUALITY,
        aes256:        true,
    );

$canvas = $doc->addPage(595.28, 841.89);

$heading = (new PdfStyle($ffi))->setSize(20)->setBold();
$canvas->drawText('Password Protected PDF', 72, 80, $heading);
$heading->close();

$body = (new PdfStyle($ffi))->setSize(12);
$canvas->drawText('This document is encrypted with AES-256.', 72, 120, $body);
$canvas->drawText('Open it with password: userpass', 72, 140, $body);
$canvas->drawText('Full editing requires password: ownerpass', 72, 160, $body);
$canvas->drawText('Allowed operations: Print, CopyText, PrintHighQuality', 72, 180, $body);
$body->close();

$canvas->close();

$out = $outputDir . '/example_06_security.pdf';
$doc->save($out);
$doc->close();

echo "Password-protected PDF written to {$out}\n";
echo "User password: userpass   |   Owner password: ownerpass\n";
