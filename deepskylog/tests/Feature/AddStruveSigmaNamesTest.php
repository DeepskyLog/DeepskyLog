<?php

use App\Http\Livewire\QuickSearch;
use App\Models\ObjectName;
use App\Services\DeepskyObjectCreator;
use App\Services\ObservingListItemNameResolver;
use App\Services\StruveSigmaNames;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// objects:add-struve-sigma-names gives the double stars of the Struves their
// traditional Σ names as alternative names (#480).

function sigmaStar(string $name, array $aliases = []): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'DS', 'name' => 'Double star']);
    DB::table('objects')->insert([
        'name'           => $name,
        'slug'           => Str::slug($name),
        'type'           => 'DS',
        'con'            => 'LYR',
        'ra'             => 18.7392,
        'decl'           => 39.6700,
        'mag'            => 5.0,
        'subr'           => 99.9,
        'pa'             => 999,
        'urano'          => 0,
        'urano_new'      => 0,
        'sky'            => 0,
        'millenium'      => '',
        'diam1'          => 0.0,
        'diam2'          => 0.0,
        'datasource'     => null,
        'taki'           => '',
        'SBObj'          => -999,
        'description'    => '',
        'psa'            => '',
        'torresB'        => '',
        'torresBC'       => '',
        'torresC'        => '',
        'milleniumbase'  => '0',
        'DSLDL'          => '0',
        'DSLDP'          => '0',
        'DSLLL'          => '0',
        'DSLLP'          => '0',
        'DSLOL'          => '0',
        'DSLOP'          => '0',
        'DeepskyHunter'  => '0',
        'Interstellarum' => '0',
        'timestamp'      => now(),
    ]);
    foreach (array_merge([$name], $aliases) as $altname) {
        [$catalog, $index] = ObjectName::splitCatalogDesignation($altname);
        DB::table('objectnames')->insert([
            'objectname' => $name,
            'catalog'    => $catalog,
            'catindex'   => $index,
            'altname'    => $altname,
            'slug'       => Str::slug($altname),
            'timestamp'  => now(),
        ]);
    }
}

beforeEach(function () {
    sigmaStar('STF 2382');
    sigmaStar('Zeta Psc', ['STF 100']);
    sigmaStar('STFA 1');
    sigmaStar('STFB 2');
    sigmaStar('STT 1');
    sigmaStar('STTA 3');
    // An object of an "S" catalog whose slug Str::slug() would give Σ 1 too.
    sigmaStar('S 1');
    sigmaStar('STF 1');
});

function sigmaNames(): array
{
    return DB::table('objectnames')->where('altname', 'like', '%Σ%')
        ->orderBy('altname')->get()
        ->map(fn ($r) => "{$r->objectname}|{$r->altname}|{$r->catalog}|{$r->slug}")
        ->all();
}

it('only lists the names in a dry run', function () {
    $this->artisan('objects:add-struve-sigma-names')->assertSuccessful();

    expect(sigmaNames())->toBe([]);
});

it('adds the Σ names of the Struve catalogs as alternative names without a catalog', function () {
    $this->artisan('objects:add-struve-sigma-names', ['--apply' => true])->assertSuccessful();

    expect(sigmaNames())->toEqualCanonicalizing([
        'STF 1|Σ 1||sigma-1',
        'Zeta Psc|Σ 100||sigma-100',
        'STF 2382|Σ 2382||sigma-2382',
        'STFA 1|Σ I 1||sigma-i-1',
        'STFB 2|Σ II 2||sigma-ii-2',
        'STT 1|OΣ 1||o-sigma-1',
        'STTA 3|OΣΣ 3||o-sigma-sigma-3',
    ])->and(DB::table('search_index')->where('source_type', 'alias')->where('name', 'Σ 100')->value('source_pk'))
        ->toBe('zeta-psc');
});

it('adds the names only once', function () {
    $this->artisan('objects:add-struve-sigma-names', ['--apply' => true])->assertSuccessful();
    $this->artisan('objects:add-struve-sigma-names', ['--apply' => true])->assertSuccessful();

    expect(sigmaNames())->toHaveCount(7);
});

it('leaves the slug empty when another name has it', function () {
    DB::table('objectnames')->where('altname', 'S 1')->update(['slug' => 'sigma-2382']);

    $this->artisan('objects:add-struve-sigma-names', ['--apply' => true])->assertSuccessful();

    expect(DB::table('objectnames')->where('altname', 'Σ 2382')->value('slug'))->toBeNull()
        ->and(DB::table('objectnames')->where('altname', 'Σ 2382')->value('objectname'))->toBe('STF 2382');
});

// MariaDB also matches σ 100 (utf8mb4_unicode_ci); SQLite only folds ASCII.
it('finds a star by its Σ name, with or without a space', function (string $query) {
    $this->artisan('objects:add-struve-sigma-names', ['--apply' => true])->assertSuccessful();

    Livewire::test(QuickSearch::class)
        ->set('query', $query)
        ->call('submit')
        ->assertRedirect(route('object.show', ['slug' => 'zeta-psc']));
})->with(['Σ 100', 'Σ100', 'STF 100', 'STF100', 'Sigma 100', 'sigma100', 'Struve 100']);

it('finds a star by its name written out, before the Σ names are added', function (string $query, string $slug) {
    Livewire::test(QuickSearch::class)
        ->set('query', $query)
        ->call('submit')
        ->assertRedirect(route('object.show', ['slug' => $slug]));
})->with([
    ['Sigma 2382', 'stf-2382'],
    ['Struve 2382', 'stf-2382'],
    ['Σ2382', 'stf-2382'],
    ['O Struve 1', 'stt-1'],
    ['OΣ 1', 'stt-1'],
    ['O Sigma Sigma 3', 'stta-3'],
    ['Sigma I 1', 'stfa-1'],
    ['Σ II 2', 'stfb-2'],
]);

it('reads the traditional names of the Struve double stars', function (string $name, ?string $designation) {
    expect(StruveSigmaNames::designation($name))->toBe($designation);
})->with([
    ['Σ 1', 'STF 1'],
    ['Σ1', 'STF 1'],
    ['σ 12', 'STF 12'],
    ['Sigma 1', 'STF 1'],
    ['sigma1', 'STF 1'],
    ['Struve 0012', 'STF 12'],
    ['Σ I 5', 'STFA 5'],
    ['Sigma II 5', 'STFB 5'],
    ['OΣ 1', 'STT 1'],
    ['O Σ 1', 'STT 1'],
    ['O Sigma 1', 'STT 1'],
    ['OSigma 1', 'STT 1'],
    ['O. Struve 1', 'STT 1'],
    ['Otto Struve 1', 'STT 1'],
    ['OΣΣ 1', 'STTA 1'],
    ['O Sigma Sigma 1', 'STTA 1'],
    ['Sigma Ori', null],
    ['Sigma 2 UMa', null],
    ['Struve\'s Lost Nebula', null],
    ['STF 1', null],
    ['Sigma Sigma 1', null],
    ['O Struve Sigma 1', null],
    ['OΣ I 1', null],
]);

it('resolves the traditional names in imported lists and observations', function () {
    foreach (['STF 2382', 'STT 1'] as $name) {
        App\Services\ObjectSearchIndex::syncObject($name);
    }
    $resolver = app(ObservingListItemNameResolver::class);

    expect($resolver->resolve('Struve 2382'))->toBe('STF 2382')
        ->and($resolver->resolve('O Struve 1'))->toBe('STT 1');
});

it('gives a new STF object its Σ name', function () {
    app(DeepskyObjectCreator::class)->create(['name' => 'STF 3000', 'type' => 'DS', 'ra' => 1.0, 'decl' => 10.0]);

    expect(sigmaNames())->toBe(['STF 3000|Σ 3000||sigma-3000'])
        ->and(DB::table('search_index')->where('name', 'Σ 3000')->value('source_pk'))->toBe('stf-3000');
});

it('gives an object its Σ name when an STT name is added to it', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'zeta-psc']), [
        'name'              => 'Zeta Psc',
        'ra'                => '1.2',
        'decl'              => '7.6',
        'type'              => 'DS',
        'alternative_names' => 'Zeta Psc, STF 100, STT 500',
    ])->assertSessionHasNoErrors();

    expect(sigmaNames())->toEqualCanonicalizing(['Zeta Psc|Σ 100||sigma-100', 'Zeta Psc|OΣ 500||o-sigma-500']);
});

it('spells Σ out in the slug of an alternative name', function () {
    expect(ObjectName::slugFor('Σ 1'))->toBe('sigma-1')
        ->and(ObjectName::slugFor('OΣΣ 12'))->toBe('o-sigma-sigma-12')
        ->and(ObjectName::slugFor('NGC 7000'))->toBe('ngc-7000');
});
