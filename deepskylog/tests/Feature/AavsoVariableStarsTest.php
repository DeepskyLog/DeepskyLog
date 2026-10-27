<?php

use App\Models\DeepskyObject;
use App\Models\NewsItem;
use App\Services\AavsoVariableStars;
use App\Services\News\BrightTransients;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// The variable stars, novae and supernovae of the AAVSO
// (App\Services\AavsoVariableStars): the star of the Variable Star Index an
// object is, its latest estimates on the object page, and the news items of
// the bright supernovae and novae (aavso:refresh).

beforeEach(function () {
    config(['services.aavso.enabled' => true, 'services.aavso.delay' => 0]);
    Carbon::setTestNow('2026-10-27 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
    unset($GLOBALS['aavsoFake']);
});

function aavsoObject(string $name, string $type, float $ra, float $decl, float $mag = 99.9): DeepskyObject
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'CEP',
        'ra'            => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 99.9, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 0, 'diam2' => 0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);

    return DeepskyObject::where('name', $name)->first();
}

function vsxStar(string $name, float $ra, float $decl, string $type, string $max, string $min, string $category = 'Variable'): array
{
    return [
        'Name'            => $name, 'AUID' => '000-AAA-001', 'OID' => (string) crc32($name), 'RA2000' => (string) $ra, 'Declination2000' => (string) $decl,
        'VariabilityType' => $type, 'MaxMag' => $max, 'MinMag' => $min, 'Category' => $category,
    ];
}

/** An api.delim answer with estimates [days ago, magnitude, band, fainter than]. */
function aavsoEstimates(array $rows): string
{
    $lines = ['JD@@@mag@@@uncert@@@band@@@by@@@val@@@fainterThan'];
    foreach ($rows as [$daysAgo, $mag, $band, $fainter]) {
        $jd      = now()->subDays($daysAgo)->getTimestamp() / 86400 + 2440587.5;
        $lines[] = implode('@@@', [round($jd, 4), $mag, '', $band, 'XYZ', 'Z', $fainter ? '1' : '0']);
    }

    return implode("\n", $lines);
}

/**
 * Fakes VSX: $objects by name for api.object, $cone for api.list, $estimates
 * by name for api.delim. A later call replaces the answers (a second
 * Http::fake() would only add to the first).
 */
function fakeVsx(array $objects = [], array $cone = [], array $estimates = []): void
{
    $fresh                = ! isset($GLOBALS['aavsoFake']);
    $GLOBALS['aavsoFake'] = [$objects, $cone, $estimates];
    if (! $fresh) {
        return;
    }
    Http::fake(function (Request $request) {
        if ($GLOBALS['aavsoFake'] === 'refuse') {
            // The captcha of the AAVSO's firewall
            return Http::response('<html><head><title>Human Verification</title></head></html>', 405, ['x-amzn-waf-action' => 'captcha']);
        }
        [$objects, $cone, $estimates] = $GLOBALS['aavsoFake'];
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return match ($query['view'] ?? '') {
            'api.object' => Http::response(['VSXObject' => $objects[$query['ident']] ?? []]),
            'api.list'   => Http::response(['VSXObjects' => $cone === [] ? [] : ['VSXObject' => $cone]]),
            'api.delim'  => Http::response($estimates[$query['ident']] ?? aavsoEstimates([])),
            default      => Http::response('', 404),
        };
    });
}

it('takes the star of a name only when it is at the object and bright enough', function () {
    // The Garnet Star: VSX reads "Mu Cep" as the 12th magnitude MU Cep, far away
    $muCep = aavsoObject('Mu Cep', 'DS', 21.725, 58.78, 3.4);
    fakeVsx(
        ['Mu Cep' => vsxStar('MU Cep', 335.91083, 57.68086, 'DCEP', '11.88 V', '12.58 V')],
        [vsxStar('mu. Cep', 325.87692, 58.78005, 'SRC', '3.43 V', '5.1 V'), vsxStar('NSV 1', 325.8775, 58.7801, 'L', '14.0 V', '15.0 V')],
    );

    expect(AavsoVariableStars::identify($muCep)['name'])->toBe('mu. Cep');

    // The name at the object's position is taken without a cone search
    $chiCyg = aavsoObject('Chi Cyg', 'AA1STAR', 19.8428, 32.9142, 4.3);
    fakeVsx(['Chi Cyg' => vsxStar('khi Cyg', 297.64133, 32.91406, 'M', '3.3 V', '14.2 V')]);
    expect(AavsoVariableStars::identify($chiCyg)['name'])->toBe('khi Cyg');
    Http::assertSentCount(3);

    // A suspected variable, or a faint variable next to a bright star, is none
    $gammaAnd = aavsoObject('Gamma And', 'DS', 2.0649, 42.3297, 2.3);
    fakeVsx(
        ['Gamma And' => vsxStar('gam 1 And', 30.97479, 42.32972, 'LB:', '2.09 V', '2.12 V', 'Suspected')],
        [vsxStar('V999 And', 30.976, 42.330, 'EA', '13.0 V', '14.0 V')],
    );
    expect(AavsoVariableStars::identify($gammaAnd))->toBeFalse();
});

it('gives the latest estimate in the visual bands, without the fainter-than ones', function () {
    $estimates = AavsoVariableStars::parseEstimates(aavsoEstimates([
        [10, '7.2', 'Vis.', false],
        [5, '6.8', 'V', false],
        [3, '9.5', 'B', false],
        [1, '<11.0', 'Vis.', false],
        [1, '11.0', 'Vis.', true],
        [2, '7.0', 'Vis.', false],
    ]));
    $latest = AavsoVariableStars::summarize($estimates);

    expect($latest['magnitude'])->toBe(7.0)
        ->and($latest['band'])->toBe('Vis.')
        ->and($latest['count'])->toBe(3)
        ->and($latest['brightest'])->toBe(6.8)
        ->and($latest['faintest'])->toBe(7.2)
        ->and(AavsoVariableStars::summarize([]))->toBeNull()
        ->and(AavsoVariableStars::magnitude('<22 V'))->toBe(22.0)
        ->and(AavsoVariableStars::magnitude('(0.5) V'))->toBeNull()
        ->and(AavsoVariableStars::kind(['type' => 'SN Ia']))->toBe('supernova')
        ->and(AavsoVariableStars::kind(['type' => 'NR+ELL']))->toBe('nova')
        ->and(AavsoVariableStars::kind(['type' => 'NB:']))->toBe('nova')
        ->and(AavsoVariableStars::kind(['type' => 'NL']))->toBe('variable');
});

it('shows the latest estimate after fetching it after the response', function () {
    aavsoObject('Chi Cyg', 'AA1STAR', 19.8428, 32.9142, 4.3);
    fakeVsx(
        ['Chi Cyg' => vsxStar('khi Cyg', 297.64133, 32.91406, 'M', '3.3 V', '14.2 V')],
        [],
        ['khi Cyg' => aavsoEstimates([[4, '6.4', 'Vis.', false], [1, '6.1', 'Vis.', false]])],
    );

    // The page never waits for the AAVSO: nothing is cached at first
    expect(AavsoVariableStars::forObjectName('Chi Cyg', fetch: false))->toBeNull();
    Http::assertNothingSent();

    AavsoVariableStars::forObjectName('Chi Cyg');
    $variable = AavsoVariableStars::forObjectName('Chi Cyg', fetch: false);
    $this->blade('<table><x-aavso-estimate :variable="$variable" /></table>', ['variable' => $variable])
        ->assertSee('khi Cyg')
        ->assertSee('Magnitude 6.1 (Vis.)', false)
        ->assertSee('2 estimates in the last 30 days', false)
        ->assertSee('3.3 V – 14.2 V', false);

    // Not for other objects, nor when turned off
    expect(AavsoVariableStars::appliesTo('GALXY'))->toBeFalse();
    config(['services.aavso.enabled' => false]);
    expect(AavsoVariableStars::forObjectName('Chi Cyg'))->toBeNull();
});

it('pauses after a refusal and does not cache a failure for long', function () {
    $object = aavsoObject('Chi Cyg', 'AA1STAR', 19.8428, 32.9142, 4.3);
    fakeVsx();
    $GLOBALS['aavsoFake'] = 'refuse';

    expect(AavsoVariableStars::forObject($object))->toBeNull()
        ->and(Cache::has('aavso:paused'))->toBeTrue();

    Cache::forget('aavso:paused');
    fakeVsx(['Chi Cyg' => vsxStar('khi Cyg', 297.64133, 32.91406, 'M', '3.3 V', '14.2 V')]);
    $this->travel(16)->minutes();
    expect(AavsoVariableStars::forObject($object)['star']['name'])->toBe('khi Cyg');
});

it('stops the refresh at the first refusal', function () {
    foreach (['SN 2026a', 'SN 2026b', 'SN 2026c'] as $i => $name) {
        aavsoObject($name, 'SNOVA', 10.0 + $i, 20.0);
    }
    fakeVsx();
    $GLOBALS['aavsoFake'] = 'refuse';

    $this->artisan('aavso:refresh')->expectsOutputToContain('stopped')->assertSuccessful();
    Http::assertSentCount(1);
});

it('announces a bright supernova and a nova in outburst, and keeps the item up to date', function () {
    aavsoObject('SN 2026abc', 'SNOVA', 14.0508, 54.2708);
    aavsoObject('SN 2025xyz', 'SNOVA', 12.0, 30.0);
    aavsoObject('T CrB', 'AA1STAR', 15.9917, 25.9202, 10.0);
    aavsoObject('V1405 Cas', 'AA1STAR', 23.4133, 61.1874, 8.0);
    $objects = [
        'SN 2026abc' => vsxStar('SN 2026abc', 210.762, 54.2708, 'SN Ia', '11.0 V', '<22 V'),
        'SN 2025xyz' => vsxStar('SN 2025xyz', 180.0, 30.0, 'SN II', '14.0 V', '<22 V'),
        'T CrB'      => vsxStar('T CrB', 239.87567, 25.92017, 'NR+ELL', '2.0 V', '10.8 V'),
        'V1405 Cas'  => vsxStar('V1405 Cas', 351.19887, 61.18744, 'NB:', '5.2 V', '15.6 V'),
    ];
    $estimates = [
        'SN 2026abc' => aavsoEstimates([[2, '11.8', 'V', false], [1, '12.1', 'CV', false]]),
        'SN 2025xyz' => aavsoEstimates([[1, '14.5', 'CV', false]]),
        // Quiet: not three magnitudes brighter than the minimum
        'T CrB'     => aavsoEstimates([[1, '9.9', 'Vis.', false]]),
        'V1405 Cas' => aavsoEstimates([[1, '13.1', 'V', false]]),
    ];
    fakeVsx($objects, [], $estimates);

    $this->artisan('aavso:refresh')->assertSuccessful();
    expect(NewsItem::count())->toBe(0);

    $this->artisan('aavso:refresh --apply')->assertSuccessful();
    $item = NewsItem::sole();
    expect($item->kind)->toBe('supernova')
        ->and($item->event_key)->toBe('supernova:sn-2026abc:2026-10-27')
        ->and($item->data['magnitude'])->toBe(12.1)
        ->and($item->data['peak'])->toBe(11.8)
        ->and($item->expires_at->toDateString())->toBe('2026-10-30');

    $this->get(route('news.show', $item))->assertOk()
        ->assertSee('Supernova SN 2026abc at magnitude 12.1')
        ->assertSee('It was as bright as magnitude 11.8.');

    // T CrB erupts; the supernova fades, so its item is no longer extended
    $estimates['T CrB']      = aavsoEstimates([[0.5, '2.5', 'Vis.', false]]);
    $estimates['SN 2026abc'] = aavsoEstimates([[0.5, '13.4', 'CV', false]]);
    fakeVsx($objects, [], $estimates);
    $this->travel(1)->days();
    $this->artisan('aavso:refresh --apply')->assertSuccessful();

    expect(NewsItem::count())->toBe(2)
        ->and(NewsItem::where('kind', 'nova')->sole()->data['name'])->toBe('T CrB')
        ->and(NewsItem::where('kind', 'supernova')->sole()->expires_at->toDateString())->toBe('2026-10-30');

    // A later outburst is a new item; a hidden one stays hidden
    NewsItem::where('kind', 'nova')->update(['hidden' => true]);
    $this->travel(1)->days();
    $this->artisan('aavso:refresh --apply')->assertSuccessful();
    expect(NewsItem::where('kind', 'nova')->sole()->hidden)->toBeTrue();

    $this->travel(10)->days();
    $this->artisan('aavso:refresh --apply')->assertSuccessful();
    expect(NewsItem::where('kind', 'nova')->count())->toBe(2);
});

it('refreshes the stars in observing lists but not the others', function () {
    aavsoObject('Chi Cyg', 'AA1STAR', 19.8428, 32.9142, 4.3);
    aavsoObject('Lacaille 3.1', 'AA1STAR', 10.0, -20.0, 6.0);
    aavsoObject('M 31', 'GALXY', 0.712, 41.27, 3.4);
    $owner = \App\Models\User::factory()->create();
    $list  = DB::table('observing_lists')->insertGetId(['name' => 'Variables', 'slug' => 'variables', 'owner_user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now()]);
    foreach (['Chi Cyg', 'M 31'] as $name) {
        DB::table('observing_list_items')->insert(['observing_list_id' => $list, 'object_name' => $name, 'added_by_user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now()]);
    }
    fakeVsx(['Chi Cyg' => vsxStar('khi Cyg', 297.64133, 32.91406, 'M', '3.3 V', '14.2 V')]);

    $this->artisan('aavso:refresh')->expectsOutputToContain('Objects: 2, variable: 1, refreshed: 1')->assertSuccessful();
    expect(BrightTransients::event(DeepskyObject::find('Chi Cyg'), AavsoVariableStars::forObjectName('Chi Cyg')))->toBeNull();
});
