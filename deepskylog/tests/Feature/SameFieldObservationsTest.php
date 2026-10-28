<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\SameFieldObjects;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function insertSameFieldObject(string $name, float $ra, float $decl, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => $type,
        'con' => 'UMA',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 9.0,
        'subr' => 13.0,
        'pa' => 0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 300.0,
        'diam2' => 120.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0.0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '0',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => now(),
    ]);
}

function sameFieldPost(User $user, array $extra = []): array
{
    return array_merge([
        'target_table' => 'objects',
        'target_slug' => 'm-81',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:30',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => '<p>Both galaxies in one field.</p>',
        'seeing' => 2,
        'transparency' => 3,
        'magnification' => '64',
        'visibility' => 2,
    ], $extra);
}

beforeEach(function () {
    insertSameFieldObject('M 81', 9.9259, 69.0653);
    insertSameFieldObject('M 82', 9.9319, 69.6797);
    insertSameFieldObject('NGC 3077', 10.0548, 68.7339);
    insertSameFieldObject('M 101', 14.0535, 54.3487);
    insertSameFieldObject('HD 12345', 9.9300, 69.1000, 'AA1STAR');
});

it('stores an observation of each object seen in the same field', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user, [
        'also_seen' => ['M 82'],
        'also_seen_more' => json_encode(['ngc-3077', 'M 81']),
    ]))->assertSessionHasNoErrors();

    $observations = Observation::orderBy('id')->get();
    expect($observations->pluck('target_name')->all())->toBe(['M 81', 'M 82', 'NGC 3077']);

    [$main, $m82, $ngc3077] = $observations->all();
    expect($main->seen_with_observation_id)->toBeNull()
        ->and($m82->seen_with_observation_id)->toBe($main->id)
        ->and($ngc3077->seen_with_observation_id)->toBe($main->id);

    foreach ([$m82, $ngc3077] as $copy) {
        expect($copy->target_key)->toBe($copy->target_name)
            ->and($copy->observed_on)->toEqual($main->observed_on)
            ->and($copy->observed_time)->toEqual($main->observed_time)
            ->and($copy->instrument_id)->toBe($main->instrument_id)
            ->and($copy->description)->toBe($main->description)
            ->and($copy->seeing)->toBe(2)
            ->and($copy->transparency)->toBe(3)
            ->and((int) $copy->has_drawing)->toBe(0);
    }

    // The magnification is shared, the details of the object are not
    $details = DB::table('deepsky_observation_details')->where('observation_id', $m82->id)->first();
    expect($details->magnification)->toBe('64')
        ->and($details->visibility)->toBeNull();

    expect($m82->seenInSameField()->pluck('target_name')->all())->toBe(['M 81', 'NGC 3077'])
        ->and($main->seenInSameField()->pluck('target_name')->all())->toBe(['M 82', 'NGC 3077']);

    expect(DB::table('object_observation_stats')->where('object_name', 'M 82')->value('observations'))->toBe(1);
});

it('keeps the observations together when the first one is deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user, [
        'also_seen' => ['M 82', 'NGC 3077'],
    ]))->assertSessionHasNoErrors();

    Observation::where('target_name', 'M 81')->first()->delete();

    expect(Observation::where('target_name', 'M 82')->first()->seenInSameField()->pluck('target_name')->all())
        ->toBe(['NGC 3077']);
});

it('rejects an object seen in the same field that does not exist, and stores nothing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user, [
        'also_seen_more' => json_encode(['NGC 99999']),
    ]))->assertSessionHasErrors('also_seen');

    expect(Observation::count())->toBe(0);
});

it('ignores the objects seen in the same field when an observation is edited', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user))->assertSessionHasNoErrors();
    $observation = Observation::first();

    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user, [
        'edit_unified_id' => $observation->id,
        'also_seen' => ['M 82'],
    ]))->assertSessionHasNoErrors();

    expect(Observation::count())->toBe(1);
});

it('suggests the objects within the field and those linked to the object, without stars', function () {
    DB::table('objectpartof')->insert(['objectname' => 'M 81', 'partofname' => 'M 101', 'timestamp' => now()]);

    // A field of 2°: M 82 (37') and NGC 3077 (46') are in it
    $names = collect(SameFieldObjects::suggest('M 81', 2.0))->pluck('name')->all();
    expect($names)->toBe(['M 101', 'M 82', 'NGC 3077']);

    // A field of 1°: only the linked object
    expect(collect(SameFieldObjects::suggest('M 81', 1.0))->pluck('name')->all())->toBe(['M 101']);
});

it('shows the suggestions on the form and the linked observations on the detail page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('observation.create', ['slug' => 'm-81']))
        ->assertOk()
        ->assertSee('Also seen in the same field');

    $this->actingAs($user)->post(route('observation.store'), sameFieldPost($user, [
        'also_seen' => ['M 82'],
    ]))->assertSessionHasNoErrors();

    $this->actingAs($user)->get(Observation::where('target_name', 'M 82')->first()->detailUrl())
        ->assertOk()
        ->assertSee('Seen in the same field as')
        ->assertSee(Observation::where('target_name', 'M 81')->first()->detailUrl(), false);
});
