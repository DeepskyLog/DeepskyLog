<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = '<!doctype html><html><head><meta charset="utf-8"/><style>@page{size:297mm 210mm;margin:0;}html,body{margin:0;height:100%;} .page{width:100%;height:100%;}</style></head><body><div class="page">';
$html .= '<svg xmlns="http://www.w3.org/2000/svg" width="1120" height="780" viewBox="0 0 1120 780"><rect width="100%" height="100%" fill="#ff0000"/></svg>';
$html .= '</div></body></html>';

$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, (297/25.4)*72.0, (210/25.4)*72.0]);
$dompdf->render();
file_put_contents(__DIR__ . '/../tmp/test_rect.pdf', $dompdf->output());
echo "Wrote test_rect.pdf\n";
