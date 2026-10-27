<?php

use App\Models\User;
use App\Services\ObjectMerger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function identityObject(string $name, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'DRA',
        'ra'            => 15.26, 'decl' => 56.33, 'mag' => 10.3, 'subr' => 99.9, 'pa' => 999, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 756.0, 'diam2' => 84.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    [$catalog, $index] = explode(' ', $name, 2);
    DB::table('objectnames')->insert(['objectname' => $name, 'catalog' => $catalog, 'catindex' => $index, 'altname' => $name, 'slug' => Str::slug($name), 'timestamp' => now()]);
}

function identityList(string ...$objects): int
{
    $owner = User::factory()->create();
    $list  = DB::table('observing_lists')->insertGetId(['name' => 'List '.Str::random(6), 'slug' => Str::random(10), 'owner_user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now()]);
    foreach ($objects as $i => $object) {
        DB::table('observing_list_items')->insert(['observing_list_id' => $list, 'object_name' => $object, 'added_by_user_id' => $owner->id, 'sort_order' => $i, 'created_at' => now(), 'updated_at' => now()]);
    }

    return $list;
}

it('merges a duplicate into the object it is', function () {
    identityObject('NGC 5907');
    identityObject('NGC 5906');
    DB::table('objectnames')->insert(['objectname' => 'NGC 5907', 'catalog' => 'NGC', 'catindex' => '5906', 'altname' => 'NGC 5906', 'slug' => 'ngc-5906-1', 'timestamp' => now()]);
    DB::table('objectpartof')->insert(['objectname' => 'NGC 5906', 'partofname' => 'NGC 5907', 'timestamp' => now()]);
    $both        = identityList('NGC 5906', 'NGC 5907');
    $one         = identityList('NGC 5906');
    $observation = DB::table('observations')->insertGetId([
        'observer_username' => 'someone', 'target_type' => 'deepsky', 'target_table' => 'objects',
        'target_key'        => 'NGC 5906', 'target_name' => 'NGC 5906', 'observed_on' => '2013-07-08',
    ]);

    expect(ObjectMerger::merge('NGC 5906', 'NGC 5907'))->toBeTrue();

    expect(DB::table('objects')->where('name', 'NGC 5906')->exists())->toBeFalse()
        ->and(DB::table('observations')->find($observation)->target_key)->toBe('NGC 5907')
        // the duplicate's name row, with the slug of the old links
        ->and(DB::table('objectnames')->where('altname', 'NGC 5906')->get(['objectname', 'slug'])->all())
        ->toEqual([(object) ['objectname' => 'NGC 5907', 'slug' => 'ngc-5906']])
        ->and(DB::table('objectpartof')->count())->toBe(0)
        ->and(DB::table('observing_list_items')->where('observing_list_id', $both)->pluck('object_name')->all())->toBe(['NGC 5907'])
        ->and(DB::table('observing_list_items')->where('observing_list_id', $one)->pluck('object_name')->all())->toBe(['NGC 5907']);
});

it('repairs the identities only with --apply, and deletes objects without observations', function () {
    identityObject('UGC 12889');
    identityObject('MCG +08-01-018');
    identityObject('MCG +01-48-001');
    identityObject('KAZ 50');
    identityList('KAZ 50');

    $this->artisan('objects:repair-identities')->expectsOutputToContain('3 objects to repair.')->assertSuccessful();
    expect(DB::table('objects')->count())->toBe(4);

    $this->artisan('objects:repair-identities', ['--apply' => true])->assertSuccessful();

    expect(DB::table('objects')->orderBy('name')->pluck('name')->all())->toBe(['MCG +01-48-001', 'UGC 12889'])
        ->and(DB::table('objects')->where('name', 'MCG +01-48-001')->value('type'))->toBe('BRTNB')
        ->and(DB::table('objectnames')->where('altname', 'MCG +08-01-018')->value('objectname'))->toBe('UGC 12889')
        ->and(DB::table('objectnames')->where('altname', 'KAZ 50')->exists())->toBeFalse()
        ->and(DB::table('observing_list_items')->count())->toBe(0);

    $this->artisan('objects:repair-identities')->expectsOutputToContain('0 objects to repair.')->assertSuccessful();
});
