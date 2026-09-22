<?php
require __DIR__ . '/../vendor/autoload.php';
$html = file_get_contents(__DIR__. '/../tmp/dsl_atlas_page1.html');
if ($html === false) { echo "No HTML file\n"; exit(1);} 
$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, (297/25.4)*72.0, (210/25.4)*72.0]);
try {
    $dompdf->render();
    file_put_contents(__DIR__ . '/../tmp/test_render_from_html.pdf', $dompdf->output());
    echo "Wrote test_render_from_html.pdf\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
    file_put_contents(__DIR__ . '/../tmp/test_render_from_html_error.txt', (string) $e);
}
