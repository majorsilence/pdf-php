<?php
/**
 * Example 09 — Invoice
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_09_invoice.php
 */

require_once __DIR__ . '/../src/PdfNative.php';

use MajorsilencePdf\PdfLibrary;
use MajorsilencePdf\PdfDocument;
use MajorsilencePdf\PdfStyle;
use MajorsilencePdf\PdfTable;

$libPath = getenv('PDFNATIVE_LIB');
if (!$libPath) exit("Set PDFNATIVE_LIB to the path of the pdfnative shared library.\n");

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$lib = PdfLibrary::load($libPath);

[$W, $H]       = [595.28, 841.89];
$margin        = 60.0;
[$br, $bg, $bb] = [26, 86, 160];

$doc = new PdfDocument($lib);
$doc->setTitle('Invoice #INV-2025-042');
$doc->setAuthor('Acme Corporation');
$canvas = $doc->addPage($W, $H);

// Header band
$canvas->drawRect(0, 0, $W, 100, $br, $bg, $bb, true, 0, 0, 0, 0, false);
$s = new PdfStyle($lib); $s->setSize(28)->setBold()->setColor(255, 255, 255);
$canvas->drawText('ACME CORPORATION', $margin, 30, $s); $s->close();
$s = new PdfStyle($lib); $s->setSize(11)->setColor(180, 210, 255);
$canvas->drawText('123 Enterprise Drive · Silicon Valley, CA 94025', $margin, 62, $s);
$canvas->drawText('billing@acme.example  ·  +1 (800) 555-0100', $margin, 78, $s); $s->close();
$s = new PdfStyle($lib); $s->setSize(22)->setBold()->setColor(255, 255, 255);
$canvas->drawText('INVOICE', $W - 160, 40, $s); $s->close();

// Invoice metadata
$yMeta = 118.0; $metaRight = $W - $margin - 140;
$labelS = new PdfStyle($lib); $labelS->setSize(9)->setColor(100, 100, 100);
$valueS = new PdfStyle($lib); $valueS->setSize(10)->setBold();
foreach ([
    ['Invoice No.', 'INV-2025-042'],
    ['Date',        '2025-11-15'],
    ['Due Date',    '2025-12-15'],
    ['Currency',    'USD'],
] as [$k, $v]) {
    $canvas->drawText($k, $metaRight, $yMeta, $labelS);
    $canvas->drawText($v, $metaRight + 70, $yMeta, $valueS);
    $yMeta += 16;
}
$labelS->close(); $valueS->close();

// Bill To
$yBill = 118.0;
$s = new PdfStyle($lib); $s->setSize(9)->setBold()->setColor($br, $bg, $bb);
$canvas->drawText('BILL TO', $margin, $yBill, $s); $s->close();
$yBill += 14;
$s = new PdfStyle($lib); $s->setSize(10)->setBold();
$canvas->drawText('Globex Enterprises Ltd.', $margin, $yBill, $s); $s->close();
$yBill += 14;
$s = new PdfStyle($lib); $s->setSize(10);
foreach (['Attn: Mr. H. J. Simpson', '742 Evergreen Terrace', 'Springfield, IL 62701'] as $line) {
    $canvas->drawText($line, $margin, $yBill, $s); $yBill += 14;
}
$s->close();

// Divider and table
$y = 220.0;
$canvas->drawLine($margin, $y, $W - $margin, $y, 200, 200, 200, 0.5);
$y += 12;

$table = new PdfTable($lib, [210, 50, 80, 80, 80]);
$table->setHeaderBg($br, $bg, $bb);
$table->setAlternateBg(245, 248, 255);
$table->setBorder(210, 210, 210, 0.5);
$table->setCellPadding(5);
$table->addRow('Description',     'Qty', 'Unit Price', 'Discount', 'Line Total');
$table->addRow('PDF Library Pro',  '3',   '$400.00',    '10%',      '$1,080.00');
$table->addRow('Report Designer',  '1',   '$250.00',    '—',        '$250.00');
$table->addRow('Integration Pack', '2',   '$180.00',    '—',        '$360.00');
$table->addRow('Priority Support', '1',   '$500.00',    '—',        '$500.00');
$canvas->drawTable($table, $margin, $y);
$table->close();
$y += 185;

// Totals
$canvas->drawLine($W - 220, $y, $W - $margin, $y, $br, $bg, $bb, 0.5);
$y += 6;
foreach ([
    ['Subtotal',  '$2,190.00', false],
    ['Tax (8%)',  '$175.20',   false],
    ['Total Due', '$2,365.20', true],
] as [$lbl, $amt, $isTotal]) {
    $s = new PdfStyle($lib);
    $s->setSize($isTotal ? 11 : 10);
    if ($isTotal) $s->setBold();
    $canvas->drawText($lbl, $W - 220, $y, $s);
    $canvas->drawText($amt, $W - $margin - 60, $y, $s);
    $s->close();
    $y += 18;
}
$canvas->drawLine($W - 220, $y, $W - $margin, $y, $br, $bg, $bb, 1.0);

// Footer
$canvas->drawLine($margin, $H - 60, $W - $margin, $H - 60, 200, 200, 200, 0.5);
$s = new PdfStyle($lib); $s->setSize(8)->setColor(130, 130, 130);
$canvas->drawText('Payment terms: Net 30. Make cheques payable to Acme Corporation.', $margin, $H - 48, $s);
$canvas->drawText('Bank: First National · Routing 021000021 · Account 123456789', $margin, $H - 36, $s);
$s->close();

$canvas->close();
$doc->save($outputDir . '/example_09_invoice.pdf');
$doc->close();

echo "Written to {$outputDir}/example_09_invoice.pdf\n";
