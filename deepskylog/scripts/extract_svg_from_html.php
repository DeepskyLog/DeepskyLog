<?php
$html = file_get_contents(__DIR__ . '/../tmp/dsl_atlas_page1.html');
if ($html === false) { echo "No html\n"; exit(1);} 
$matches = [];
if (preg_match('/<img\s+[^>]*src\s*=\s*"data:image\/svg\+xml;base64,([^"\']+)"/i', $html, $matches)) {
    $b64 = $matches[1];
    $svg = base64_decode($b64);
    file_put_contents(__DIR__ . '/../tmp/extracted_chart.svg', $svg);
    echo "Wrote: ../tmp/extracted_chart.svg\n";
    exit(0);
}

if (preg_match('/<svg[\s\S]*<\/svg>/i', $html, $matches)) {
    file_put_contents(__DIR__ . '/../tmp/extracted_chart.svg', $matches[0]);
    echo "Wrote inline svg\n";
    exit(0);
}

echo "No svg found\n";
