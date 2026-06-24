<?php

declare(strict_types=1);

/**
 * Example 04 — Table
 *
 * Creates a styled table with a header, alternating row colours, and a border.
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_04_table.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use MajorsilencePdf\PdfTable;

$libPath = getenv('PDFNATIVE_LIB') ?: '';
if ($libPath === '') {
    fwrite(STDERR, "Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");
    exit(1);
}

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$ffi    = PdfLibrary::load($libPath);
$doc    = new PdfDocument($ffi);
$doc->setTitle('Table Example');
$canvas = $doc->addPage(595.28, 841.89);

$heading = (new PdfStyle($ffi))->setSize(18)->setBold();
$canvas->drawText('Table Layout', 72, 40, $heading);
$heading->close();

$label = (new PdfStyle($ffi))->setSize(10);
$canvas->drawText('Sales report — styled table with header and alternating rows:', 72, 76, $label);
$label->close();

$table = new PdfTable($ffi, [180.0, 80.0, 90.0, 90.0]);
$table->setHeaderBg(26, 86, 160)
      ->setAlternateBg(240, 245, 252)
      ->setBorder(200, 200, 200, 0.5)
      ->setCellPadding(5.0)
      ->addRow('Product', 'Qty', 'Unit Price', 'Total')
      ->addRow('PDF Library Pro', '3', '$400.00', '$1,200.00')
      ->addRow('Report Designer', '1', '$250.00', '$250.00')
      ->addRow('Integration Pack', '2', '$180.00', '$360.00')
      ->addRow('Support (12 mo.)', '1', '$500.00', '$500.00')
      ->addRow('', '', 'Total:', '$2,310.00');

$canvas->drawTable($table, 72, 92);
$table->close();

$label2 = (new PdfStyle($ffi))->setSize(10);
$canvas->drawText('Borderless table:', 72, 330, $label2);
$label2->close();

$reportTable = new PdfTable($ffi, [200.0, 100.0, 100.0]);
$reportTable->setAlternateBg(245, 245, 245)
            ->setBorder(0, 0, 0, 0.0)
            ->setCellPadding(4.0)
            ->addRow('Region', 'Revenue', 'Growth')
            ->addRow('North America', '$1.24M', '+12%')
            ->addRow('Europe', '$0.89M', '+8%')
            ->addRow('Asia Pacific', '$0.45M', '+18%')
            ->addRow('Other', '$0.12M', '+3%');

$canvas->drawTable($reportTable, 72, 346);
$reportTable->close();

$canvas->close();
$out = $outputDir . '/example_04_table.pdf';
$doc->save($out);
$doc->close();

echo "Written to {$out}\n";
