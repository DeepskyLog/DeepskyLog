<?php

/**
 * Whether robots.txt lets a crawler fetch the path, matched as Google does:
 * '*' matches any characters, '$' anchors the end, the longest matching rule
 * wins and Allow wins a tie.
 */
function robotsTxtAllows(string $path): bool
{
    $best = null;
    foreach (file(base_path('public/robots.txt'), FILE_IGNORE_NEW_LINES) as $line) {
        if (!preg_match('/^(Allow|Disallow):\s*(\S+)/i', trim($line), $m)) {
            continue;
        }
        $regex = '#^' . str_replace(['\*', '\$'], ['.*', '$'], preg_quote($m[2], '#')) . '#';
        if (!preg_match($regex, $path)) {
            continue;
        }
        $rule = ['allow' => strcasecmp($m[1], 'Allow') === 0, 'length' => strlen($m[2])];
        if ($best === null || $rule['length'] > $best['length'] || ($rule['length'] === $best['length'] && $rule['allow'])) {
            $best = $rule;
        }
    }

    return $best === null || $best['allow'];
}

it('keeps crawlers off the exports', function (string $path) {
    expect(robotsTxtAllows($path))->toBeFalse();
})->with([
    '/observations/search/export.pdf',
    '/observations/search/export.skylist',
    '/observations/search/export.obs',
    '/observations/search/export.apd',
    '/observations/search/export.oal.xml',
    '/session/some-user/some-session/export.oal.xml',
    '/search/names.pdf',
    '/search/table.pdf',
    '/search/argo.txt',
    '/search/skylist.skylist',
    '/search/stxt.txt',
    '/search/apd.apd',
    '/observing-list/my-list/export/names.pdf',
    '/observing-list/my-list/export/argo.txt',
    '/observing-list/my-list/export/apd.apd',
    '/object/m-51/nearby-names.pdf',
    '/object/m-51/nearby-table.pdf',
    '/object/m-51/nearby-chart.pdf',
    '/object/m-51/nearby-argo.txt',
    '/object/m-51/nearby-skylist.skylist',
    '/object/m-51/nearby-stxt.txt',
    '/object/m-51/nearby-apd.apd',
    '/object-sets/download/abc',
    '/object-report/download/abc',
]);

it('lets crawlers fetch the pages and the static downloads', function (string $path) {
    expect(robotsTxtAllows($path))->toBeTrue();
})->with([
    '/',
    '/object/m-51',
    '/observations',
    '/observing-list/my-list',
    '/session/some-user/some-session',
    '/downloads/Sketch.pdf',
    '/DSL/atlasses/ENA3DL.pdf',
    '/sitemap.xml',
]);
