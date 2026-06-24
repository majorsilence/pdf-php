<?php
/**
 * Example 10 — Dashboard
 *
 * Usage:
 *   PDFNATIVE_LIB=/path/to/libpdfnative.so php example_10_dashboard.php
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

[$W, $H]  = [595.28, 841.89];
$margin   = 40.0;

$doc    = new PdfDocument($lib);
$doc->setTitle('Q4 2025 Sales Dashboard');
$canvas = $doc->addPage($W, $H);

// Title bar
$canvas->drawRect(0, 0, $W, 52, 30, 30, 50, true, 0, 0, 0, 0, false);
$s = new PdfStyle($lib); $s->setSize(18)->setBold()->setColor(255, 255, 255);
$canvas->drawText('Q4 2025  ·  Sales Dashboard', $margin, 16, $s); $s->close();
$s = new PdfStyle($lib); $s->setSize(9)->setColor(160, 180, 220);
$canvas->drawText('Generated 2025-12-31', $margin, 38, $s); $s->close();

// KPI tiles
$kpis = [
    ['Total Revenue', '$4.2M',  '+12%', [26,  86,  160]],
    ['New Customers', '1,840',  '+8%',  [0,   140, 80]],
    ['Avg Order',     '$2,283', '+5%',  [180, 80,  0]],
    ['NPS Score',     '72',     '+4pt', [120, 0,   160]],
];
[$tileW, $tileH] = [110, 75];
foreach ($kpis as $i => [$title, $value, $delta, [$r, $g, $b]]) {
    $col = $i % 2; $row = intdiv($i, 2);
    $bx  = $margin + $col * ($tileW + 8);
    $by  = 62 + $row * ($tileH + 8);
    $canvas->drawRect($bx, $by, $tileW, $tileH, $r, $g, $b, true, 0, 0, 0, 0, false);
    $s = new PdfStyle($lib); $s->setSize(8)->setColor(200, 220, 255);
    $canvas->drawText($title, $bx + 6, $by + 10, $s); $s->close();
    $s = new PdfStyle($lib); $s->setSize(20)->setBold()->setColor(255, 255, 255);
    $canvas->drawText($value, $bx + 6, $by + 34, $s); $s->close();
    $s = new PdfStyle($lib); $s->setSize(9)->setColor(200, 255, 200);
    $canvas->drawText($delta, $bx + 6, $by + 58, $s); $s->close();
}

// Regional table
$tableX = $margin + 2 * ($tileW + 8) + 16;
$s = new PdfStyle($lib); $s->setSize(11)->setBold();
$canvas->drawText('Regional Breakdown', $tableX, 64, $s); $s->close();

$table = new PdfTable($lib, [110, 60, 60, 50]);
$table->setHeaderBg(30, 30, 50);
$table->setAlternateBg(245, 245, 250);
$table->setBorder(210, 210, 210, 0.4);
$table->setCellPadding(4);
$table->addRow('Region',        'Revenue', 'Units', 'Chg');
$table->addRow('North America', '$1.7M',   '612',   '+14%');
$table->addRow('Europe',        '$1.2M',   '441',   '+9%');
$table->addRow('Asia Pacific',  '$0.9M',   '320',   '+18%');
$table->addRow('LATAM',         '$0.3M',   '110',   '+6%');
$table->addRow('Other',         '$0.1M',   '40',    '+2%');
$canvas->drawTable($table, $tableX, 78);
$table->close();

// Bar chart
$chartTop = 230.0;
$s = new PdfStyle($lib); $s->setSize(11)->setBold();
$canvas->drawText('Quarterly Revenue', $margin, $chartTop, $s); $s->close();
$chartTop += 16;
$chartH  = 120.0; $chartBot = $chartTop + $chartH;
$barW    = 40.0;  $gap      = 20.0;
$revenues = [2.2, 2.8, 3.5, 4.2];
$quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
$maxRev   = max($revenues);
$canvas->drawLine($margin, $chartTop, $margin, $chartBot, 150, 150, 150, 0.5);
$canvas->drawLine($margin, $chartBot, $margin + count($revenues) * ($barW + $gap) + $gap, $chartBot, 150, 150, 150, 0.5);
$lbl = new PdfStyle($lib); $lbl->setSize(9)->setColor(80, 80, 80);
foreach ($revenues as $i => $rev) {
    $bx = $margin + $gap + $i * ($barW + $gap);
    $bh = $chartH * $rev / $maxRev;
    $by = $chartBot - $bh;
    $canvas->drawRect($bx, $by, $barW, $bh, 26, 86, 160, true, 0, 0, 0, 0, false);
    $canvas->drawText("\${$rev}M", $bx + 2, $by - 12, $lbl);
    $canvas->drawText($quarters[$i], $bx + 12, $chartBot + 6, $lbl);
}
$lbl->close();

// Product mix
$mixY = $chartBot + 40;
$s = new PdfStyle($lib); $s->setSize(11)->setBold();
$canvas->drawText('Product Mix (% of Revenue)', $margin, $mixY, $s); $s->close();
$mixY += 14;
$products = [
    ['PDF Library',   42, [26,  86,  160]],
    ['Report Engine', 28, [0,   140, 80]],
    ['Integration',   18, [220, 120, 0]],
    ['Support',       12, [160, 0,   80]],
];
$barTotalW = $W - 2 * $margin;
$xCur      = $margin;
$ws = new PdfStyle($lib); $ws->setSize(8)->setColor(255, 255, 255);
foreach ($products as [$name, $pct, [$r, $g, $b]]) {
    $segW = $barTotalW * $pct / 100;
    $canvas->drawRect($xCur, $mixY, $segW, 22, $r, $g, $b, true, 0, 0, 0, 0, false);
    if ($segW > 30) $canvas->drawText("{$pct}%", $xCur + 4, $mixY + 7, $ws);
    $xCur += $segW;
}
$ws->close();
$mixY += 30;
$ls = new PdfStyle($lib); $ls->setSize(9);
foreach ($products as $i => [$name, $pct, [$r, $g, $b]]) {
    $lx = $margin + $i * 115;
    $canvas->drawRect($lx, $mixY, 10, 10, $r, $g, $b, true, 0, 0, 0, 0, false);
    $canvas->drawText("{$name} ({$pct}%)", $lx + 14, $mixY + 2, $ls);
}
$ls->close();

$canvas->close();
$doc->save($outputDir . '/example_10_dashboard.pdf');
$doc->close();

echo "Written to {$outputDir}/example_10_dashboard.pdf\n";
