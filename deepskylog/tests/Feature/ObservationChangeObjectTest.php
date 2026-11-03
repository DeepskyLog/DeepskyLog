<?php

use App\Models\Observation;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

function changeObjectComet(string $name, string $slug): int
{
    return DB::table('cometobjects')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'icqname' => '',
        'timestamp' => now(),
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wrongId = changeObjectComet('C/2022 E3 (ZTF)', 'c2022-e3-ztf');
    $this->rightId = changeObjectComet('C/2020 V2 (ZTF)', 'c2020-v2-ztf');
    $this->instrument = InstrumentFactory::new()->create(['observer' => '', 'user_id' => $this->user->id]);
    // Inserted as the old site stored them: a date without a time
    $id = DB::table('observations')->insertGetId([
        'observer_username' => $this->user->username,
        'observer_user_id' => $this->user->id,
        'observed_on' => '2023-01-15',
        'observed_time' => '20:30:00',
        'instrument_id' => $this->instrument->id,
        'description' => 'One tail was clearly visible.',
        'target_type' => 'comet',
        'target_table' => 'cometobjects',
        'target_key' => (string) $this->wrongId,
        'target_name' => 'C/2022 E3 (ZTF)',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->observation = Observation::findOrFail($id);
    $this->editUrl = route('observations.edit', ['observer' => $this->user->slug, 'date' => '2023-01-15', 'object' => 'c2022-e3-ztf', 'index' => 1]);
});

it('offers to change the object when editing an observation', function () {
    $this->actingAs($this->user)->get($this->editUrl)->assertRedirect(route('observation.create', ['slug' => 'c2022-e3-ztf']));

    $this->get(route('observation.create', ['slug' => 'c2022-e3-ztf']))
        ->assertOk()
        ->assertSee('Wrong object? Change the object of this observation')
        ->assertSee('action="' . $this->editUrl . '"', false);
});

it('does not offer to change the object of a new observation', function () {
    $this->actingAs($this->user)->get(route('observation.create', ['slug' => 'c2022-e3-ztf']))
        ->assertOk()
        ->assertDontSee('Wrong object? Change the object of this observation');
});

it('moves a comet observation to another comet', function () {
    $this->actingAs($this->user);

    // The start of a name is enough when only one comet has it
    $this->get($this->editUrl . '?object=' . urlencode('C/2020 V2'))
        ->assertRedirect(route('observation.create', ['slug' => 'c2020-v2-ztf']));
    $this->get(route('observation.create', ['slug' => 'c2020-v2-ztf']))
        ->assertOk()
        ->assertSee('C/2020 V2 (ZTF)')
        ->assertSee('(was C/2022 E3 (ZTF))')
        ->assertSee('name="edit_unified_id" value="' . $this->observation->id . '"', false);

    $this->post(route('observation.store'), [
        'edit_unified_id' => $this->observation->id,
        'target_table' => 'cometobjects',
        'target_slug' => 'c2020-v2-ztf',
        'observed_on' => '2023-01-15',
        'observed_time' => '20:30',
        'instrument_id' => $this->instrument->id,
        'description' => 'One tail was clearly visible.',
    ])->assertSessionHasNoErrors();

    expect($this->observation->fresh()->only(['target_type', 'target_table', 'target_key', 'target_name']))->toBe([
        'target_type' => 'comet',
        'target_table' => 'cometobjects',
        'target_key' => (string) $this->rightId,
        'target_name' => 'C/2020 V2 (ZTF)',
    ])->and(Observation::count())->toBe(1);
});

it('finds a comet by its full name and its slug', function () {
    $this->actingAs($this->user);

    $this->get($this->editUrl . '?object=' . urlencode('C/2020 V2 (ZTF)'))
        ->assertRedirect(route('observation.create', ['slug' => 'c2020-v2-ztf']));
    $this->get($this->editUrl . '?object=c2020-v2-ztf')
        ->assertRedirect(route('observation.create', ['slug' => 'c2020-v2-ztf']));
});

it('keeps the kind of object when changing it', function () {
    DB::table('objectnames')->insert(['objectname' => 'M 31', 'catalog' => 'M', 'catindex' => '31', 'altname' => 'M 31', 'slug' => 'm-31', 'timestamp' => now()]);
    expect(Observation::canonicalDeepskyName('M 31'))->toBe('M 31');

    // A deep-sky object is no comet
    $this->actingAs($this->user)->get($this->editUrl . '?object=M 31')
        ->assertRedirect(route('observation.create', ['slug' => 'c2022-e3-ztf']))
        ->assertSessionHas('error', "No object found for 'M 31'.");
});

it('says so when the start of a name matches several comets', function () {
    changeObjectComet('C/2020 V3 (Test)', 'c2020-v3-test');

    $this->actingAs($this->user)->get($this->editUrl . '?object=' . urlencode('C/2020 V'))
        ->assertSessionHas('error', "No object found for 'C/2020 V'.");
});

it('does not let another observer change the object', function () {
    $this->actingAs(User::factory()->create())->get($this->editUrl . '?object=c2020-v2-ztf')->assertForbidden();
});
