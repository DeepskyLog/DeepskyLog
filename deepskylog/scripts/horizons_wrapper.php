<?php
// Wrapper to call vendor horizons_radec.php and always write diagnostics
// Usage: php horizons_wrapper.php <designation> <YYYY-MM-DD HH:MM> <lon> <lat> <alt_m> [EPHEM]
$argv0 = $argv[0] ?? null;
if ($argc < 6) {
    echo json_encode(['error' => 'usage: designation datetime lon lat alt_m [EPHEM]']) . PHP_EOL;
    exit(1);
}
$des = $argv[1];
$dt = $argv[2];
$lon = $argv[3];
$lat = $argv[4];
$alt = $argv[5];
$ephem = $argv[6] ?? null;

// Local canonicalizer for designation strings (kept here so the CLI wrapper
// normalizes inputs the same way the Laravel helper does without bootstrapping the app).
function canonicalize_designation($in)
{
    if ($in === null) return null;
    $s = trim((string)$in);
    if ($s === '') return null;
    if (preg_match('/\b(\d{1,4})\s*[Pp]\b/', $s, $m)) {
        return strtoupper($m[1] . 'P');
    }
    if (preg_match('/\b([PCpc])\s*\/\s*(\d{4})\s*([A-Z0-9-]+)\b/', $s, $m)) {
        $lead = strtoupper($m[1]);
        $year = $m[2];
        $code = strtoupper($m[3]);
        return "{$lead}/{$year} {$code}";
    }
    if (strpos($s, '/') !== false) {
        $parts = preg_split('/\s*\/\s*/', $s, 2);
        $left = strtoupper(trim($parts[0]));
        $right = isset($parts[1]) ? preg_replace('/\s+/', ' ', trim($parts[1])) : '';
        return $right === '' ? $left : ($left . '/' . $right);
    }
    return preg_replace('/\s+/', ' ', $s);
}

$des_canon = canonicalize_designation($des);

$script = __DIR__ . '/../vendor/deepskylog/laravel-astronomy-library/scripts/horizons_radec.php';
$php = PHP_BINARY;

$cmdParts = [$php, escapeshellarg($script), escapeshellarg($des_canon ?? $des), escapeshellarg($dt), escapeshellarg($lon), escapeshellarg($lat), escapeshellarg($alt)];
if ($ephem !== null && trim($ephem) !== '') {
    $cmdParts[] = escapeshellarg($ephem);
}
$cmd = implode(' ', $cmdParts);

// Run and capture output and exit code
$descriptorspec = [
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];
$proc = proc_open($cmd, $descriptorspec, $pipes, __DIR__);
$out = '';
$err = '';
$ret = null;
if (is_resource($proc)) {
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $ret = proc_close($proc);
} else {
    $err = 'proc_open failed';
    $ret = 255;
}

// Build diagnostic payload
$payload = [
    'ts' => gmdate('c'),
    'designation_raw' => $des,
    'designation' => $des_canon ?? $des,
    'date' => $dt,
    'lon' => $lon,
    'lat' => $lat,
    'alt_m' => (float)$alt,
    'ephem' => $ephem,
    'cmd' => $cmd,
    'exit' => $ret,
    'stdout' => $out,
    'stderr' => $err,
];

// Attempt to parse JSON stdout if present
$parsed = @json_decode($out, true);
if (is_array($parsed)) $payload['parsed_stdout'] = $parsed;

// Write timestamped diagnostic into storage/logs
$ts = gmdate('Ymd\THis');
$fname = __DIR__ . "/../storage/logs/horizons_wrapper_{$ts}.json";
@file_put_contents($fname, json_encode($payload, JSON_PRETTY_PRINT));

// Also echo stdout/stderr for immediate feedback
if ($out !== '') echo $out . PHP_EOL;
if ($err !== '') fwrite(STDERR, $err . PHP_EOL);

// Print location of diagnostic file
fwrite(STDOUT, "WROTE_DIAG: {$fname}\n");

exit($ret);
