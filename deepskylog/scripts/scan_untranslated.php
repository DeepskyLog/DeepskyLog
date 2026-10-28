<?php
// Lists translation keys used in the source that have no translation in a locale.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dirs = $argv[1] ?? 'app,routes,resources/views';
$keys = [];
foreach (explode(',', $dirs) as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir), FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!str_ends_with($f, '.php')) continue;
        $src = file_get_contents($f);
        $rel = substr($f, strlen(base_path()) + 1);
        // single- and double-quoted literal first argument
        preg_match_all("/(?<![\\w>:])(?:__|trans|trans_choice|@lang|Lang::get)\\(\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*[,)]/", $src, $m1);
        preg_match_all('/(?<![\w>:])(?:__|trans|trans_choice|@lang|Lang::get)\(\s*"((?:[^"\\\\$]|\\\\.)*)"\s*[,)]/', $src, $m2);
        foreach ($m1[1] as $k) $keys[str_replace(["\\'", '\\\\'], ["'", '\\'], $k)][$rel] = true;
        foreach ($m2[1] as $k) $keys[stripcslashes($k)][$rel] = true;
    }
}
$locales = ['nl','fr','de','es','sv'];
$json = [];
foreach ($locales as $l) $json[$l] = json_decode(file_get_contents(lang_path("$l.json")), true);
$out = [];
foreach ($keys as $k => $files) {
    if ($k === '') continue;
    $missing = [];
    foreach ($locales as $l) {
        $has = (isset($json[$l][$k]) && $json[$l][$k] !== '') || app('translator')->hasForLocale($k, $l);
        if (!$has) $missing[] = $l;
    }
    if ($missing) $out[$k] = ['missing' => $missing, 'files' => array_keys($files)];
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
