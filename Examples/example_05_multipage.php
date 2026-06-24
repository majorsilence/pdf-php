<?php
/**
 * Example 05 — Multi-Page Document
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_05_multipage.php
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

[$W, $H] = [595.28, 841.89];

$doc = new PdfDocument($lib);
$doc->setTitle('Multi-Page Document');
$doc->setAuthor('Majorsilence PDF');

// ── Page 1: Cover ─────────────────────────────────────────────────────────────
$canvas = $doc->addPage($W, $H);
$canvas->drawRect(0, 0, $W, 200, 26, 86, 160, true, 0, 0, 0, 0, false);
$s = new PdfStyle($lib);
$s->setSize(32)->setBold()->setColor(255, 255, 255);
$canvas->drawText('Annual Report 2025', 72, 80, $s); $s->close();
$s = new PdfStyle($lib);
$s->setSize(14)->setColor(200, 220, 255);
$canvas->drawText('Majorsilence Corporation', 72, 130, $s); $s->close();
$s = new PdfStyle($lib);
$s->setSize(12);
$canvas->drawText('This document demonstrates a multi-page PDF with cover, content, and summary.', 72, 250, $s); $s->close();
$s = new PdfStyle($lib);
$s->setSize(10)->setColor(120, 120, 120);
$canvas->drawText('Page 1 of 3', 72, $H - 40, $s); $s->close();
$canvas->close();

// ── Page 2: Content ───────────────────────────────────────────────────────────
$canvas = $doc->addPage($W, $H);
$s = new PdfStyle($lib);
$s->setSize(18)->setBold();
$canvas->drawText('Section 1 — Overview', 72, 60, $s); $s->close();
$canvas->drawLine(72, 80, $W - 72, 80, 26, 86, 160, 1.5);

$s = new PdfStyle($lib);
$s->setSize(11);
$canvas->drawTextbox(
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor ' .
    'incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud ' .
    'exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
    72, 96, $W - 144, 80, $s
);
$s->close();

$s = new PdfStyle($lib);
$s->setSize(14)->setBold();
$canvas->drawText('Key Metrics', 72, 200, $s); $s->close();

$metrics = [
    ['Revenue', '$4.2M'], ['Customers', '1,840'],
    ['New Products', '12'], ['Net Score', '72'],
];
foreach ($metrics as $i => [$name, $value]) {
    $col = $i % 2; $row = intdiv($i, 2);
    $bx = 72 + $col * 230; $by = 220 + $row * 80;
    $canvas->drawRect($bx, $by, 210, 65, 240, 245, 252, true, 200, 210, 230, 0.5, true);
    $s = new PdfStyle($lib); $s->setSize(10)->setColor(80, 80, 80);
    $canvas->drawText($name, $bx + 10, $by + 14, $s); $s->close();
    $s = new PdfStyle($lib); $s->setSize(20)->setBold()->setColor(26, 86, 160);
    $canvas->drawText($value, $bx + 10, $by + 40, $s); $s->close();
}
$s = new PdfStyle($lib); $s->setSize(10)->setColor(120, 120, 120);
$canvas->drawText('Page 2 of 3', 72, $H - 40, $s); $s->close();
$canvas->close();

// ── Page 3: Summary table ─────────────────────────────────────────────────────
$canvas = $doc->addPage($W, $H);
$s = new PdfStyle($lib); $s->setSize(18)->setBold();
$canvas->drawText('Section 2 — Regional Summary', 72, 60, $s); $s->close();
$canvas->drawLine(72, 80, $W - 72, 80, 26, 86, 160, 1.5);

$table = new PdfTable($lib, [160, 90, 90, 90, 90]);
$table->setHeaderBg(26, 86, 160);
$table->setAlternateBg(240, 245, 252);
$table->setBorder(200, 200, 200, 0.5);
$table->setCellPadding(5);
$table->addRow('Region',        'Q1',    'Q2',    'Q3',    'Q4');
$table->addRow('North America', '$1.1M', '$1.0M', '$1.2M', '$1.4M');
$table->addRow('Europe',        '$0.7M', '$0.8M', '$0.9M', '$0.8M');
$table->addRow('Asia Pacific',  '$0.3M', '$0.4M', '$0.4M', '$0.5M');
$table->addRow('Other',         '$0.1M', '$0.1M', '$0.1M', '$0.1M');
$table->addRow('Total',         '$2.2M', '$2.3M', '$2.6M', '$2.8M');
$canvas->drawTable($table, 72, 96);
$table->close();

$s = new PdfStyle($lib); $s->setSize(10)->setColor(120, 120, 120);
$canvas->drawText('Page 3 of 3', 72, $H - 40, $s); $s->close();
$canvas->close();

$doc->save($outputDir . '/example_05_multipage.pdf');
$doc->close();

echo "Written to {$outputDir}/example_05_multipage.pdf\n";
