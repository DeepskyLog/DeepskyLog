<?php
require __DIR__ . '/../vendor/autoload.php';
$svg = file_get_contents(__DIR__ . '/../tmp/extracted_chart.svg');
if ($svg === false) { echo "No svg\n"; exit(1); }
$html = '<!doctype html><html><body>' . $svg . '</body></html>';
$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, (297/25.4)*72.0, (210/25.4)*72.0]);
try {
    $dompdf->render();
    file_put_contents(__DIR__ . '/../tmp/test_render_svg_only.pdf', $dompdf->output());
    echo "Wrote test_render_svg_only.pdf\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    file_put_contents(__DIR__ . '/../tmp/test_render_svg_only_error.txt', (string) $e);
}
