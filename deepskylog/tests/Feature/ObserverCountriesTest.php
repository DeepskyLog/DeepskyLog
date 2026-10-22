<?php

use App\Livewire\CreateLocation;
use App\Livewire\PopularObjectsTable;
use App\Livewire\PopularObservationsTable;
use App\Models\ObservationLike;
use App\Livewire\Profile\UpdateUserObservingInformation;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObserverCountries;
use App\Services\PopularObjectsRanking;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function insertCountryTestLocation(User $user, string $country): int
{
    return (int) DB::table('locations')->insertGetId([
        'name' => $country.' site '.$user->id,
        'longitude' => 4.35,
        'latitude' => 50.85,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => $country,
        'active' => true,
        'user_id' => $user->id,
        'observer' => $user->username,
        'observations' => 0,
        'picture' => null,
        'slug' => str($country.' site '.$user->id)->slug()->toString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function insertCountryTestObservations(User $user, int $locationId, int $count, string $target = 'M 31', bool $drawing = false): void
{
    for ($i = 0; $i < $count; $i++) {
        Observation::query()->insert([
            'observer_username' => $user->username,
            'location_id' => $locationId,
            'observed_on' => now()->toDateString(),
            'target_type' => 'deepsky',
            'target_table' => 'objectnames',
            'target_key' => str($target)->slug()->toString(),
            'target_name' => $target,
            'has_drawing' => $drawing,
            'language' => 'en',
            'legacy_source' => null,
            'legacy_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

/**
 * Observers with a country of residence and observations at a location in
 * that country, the first with the most observations.
 *
 * @param  array<string, list<int>>  $countries  code => observations per observer
 * @return array<string, list<User>>
 */
function makeCountryTestObservers(array $countries): array
{
    $names = ['BE' => 'Belgium', 'NL' => 'Netherlands', 'FR' => 'France', 'US' => 'United States'];
    $observers = [];
    foreach ($countries as $code => $counts) {
        foreach ($counts as $count) {
            $user = User::factory()->create(['country' => $code]);
            insertCountryTestObservations($user, insertCountryTestLocation($user, $names[$code]), $count);
            $observers[$code][] = $user;
        }
    }

    return $observers;
}

it('fills in the missing countries of residence', function () {
    // The e-mail address comes first.
    $byEmail = User::factory()->create(['country' => '', 'email' => 'b@kuleuven.be']);
    insertCountryTestLocation($byEmail, 'France');
    insertCountryTestLocation($byEmail, 'France');
    $byEmail->forceFill(['stdlocation' => insertCountryTestLocation($byEmail, 'Netherlands')])->save();

    // A generic domain: the country of most locations.
    $byLocations = User::factory()->create(['country' => '', 'email' => 'a@example.com']);
    insertCountryTestLocation($byLocations, 'France');
    insertCountryTestLocation($byLocations, 'France');
    $belgium = insertCountryTestLocation($byLocations, 'Belgium');
    $byLocations->forceFill(['stdlocation' => $belgium])->save();
    insertCountryTestObservations($byLocations, $belgium, 5);

    // A tie in the locations, and a country domain used like .com: the
    // standard location.
    $byStandard = User::factory()->create(['country' => '', 'email' => 'c@example.io']);
    insertCountryTestObservations($byStandard, insertCountryTestLocation($byStandard, 'Netherlands'), 3);
    $byStandard->forceFill(['stdlocation' => insertCountryTestLocation($byStandard, 'France')])->save();

    // Nothing else: the observations, here at a location of someone else.
    $byObservations = User::factory()->create(['country' => '', 'email' => 'd@example.org']);
    insertCountryTestObservations($byObservations, insertCountryTestLocation(User::factory()->create(['country' => 'DE']), 'Germany'), 1);

    $british = User::factory()->create(['country' => '', 'email' => 'e@example.co.uk']);
    $named = User::factory()->create(['country' => 'Belgium']);
    $kept = User::factory()->create(['country' => 'DE']);
    $unknown = User::factory()->create(['country' => '', 'email' => 'f@example.com']);

    $this->artisan('users:fill-country')->assertSuccessful();
    expect($byEmail->fresh()->country)->toBe('');

    $this->artisan('users:fill-country --apply')
        ->expectsOutputToContain('Filled in: 6')
        ->expectsOutputToContain('Without a known country: 1')
        ->assertSuccessful();

    expect($byEmail->fresh()->country)->toBe('BE')
        ->and($byLocations->fresh()->country)->toBe('FR')
        ->and($byStandard->fresh()->country)->toBe('FR')
        ->and($byObservations->fresh()->country)->toBe('DE')
        ->and($british->fresh()->country)->toBe('GB')
        ->and($named->fresh()->country)->toBe('BE')
        ->and($kept->fresh()->country)->toBe('DE')
        ->and($unknown->fresh()->country)->toBe('');
});

it('only offers the countries with enough observers', function () {
    makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [5, 4]]);
    User::factory()->create(['country' => 'FR']);

    expect(ObserverCountries::observerCounts())->toBe(['BE' => 3, 'NL' => 2])
        ->and(array_keys(ObserverCountries::options()))->toBe(['BE']);
});

it('ranks the observers of one country on /observers', function () {
    $observers = makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [9, 8, 7]]);

    $this->get(route('observers.index', ['country' => 'BE']))
        ->assertOk()
        ->assertViewHas('selectedCountry', 'BE')
        ->assertViewHas('observerRows', function ($rows) use ($observers) {
            return $rows->pluck('username')->all() === collect($observers['BE'])->pluck('username')->all()
                && $rows->pluck('rank')->all() === [1, 2, 3];
        });

    // An unknown country shows everybody.
    $this->get(route('observers.index', ['country' => 'XX']))
        ->assertOk()
        ->assertViewHas('selectedCountry', null)
        ->assertViewHas('observerRows', fn ($rows) => $rows->count() === 6);
});

it('shows the national rank and the countries observed in on the observer page', function () {
    $observers = makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [9, 8, 7]]);
    $observer = $observers['BE'][1];
    insertCountryTestObservations($observer, insertCountryTestLocation($observer, 'France'), 2);

    expect($observer->getCountryRanks()['all'])->toBe(['rank' => 1, 'observers' => 3])
        ->and($observers['BE'][0]->getCountryRanks()['all'])->toBe(['rank' => 2, 'observers' => 3])
        ->and($observers['BE'][0]->getCountryRanks()['deepsky'])->toBe(['rank' => 2, 'observers' => 3]);

    $this->actingAs($observer)
        ->get(route('observer.show', ['observer' => $observer->slug]))
        ->assertOk()
        ->assertViewHas('countryRanks', fn ($ranks) => $ranks['all'] === ['rank' => 1, 'observers' => 3])
        ->assertViewHas('observedCountries', ['countries' => 2, 'abroad_percentage' => 50.0])
        ->assertSee('Rank in Belgium');

    // Without enough compatriots there is no national rank.
    $alone = User::factory()->create(['country' => 'FR']);
    expect($alone->getCountryRanks())->toBeNull();
});

it('filters the statistics on the country of residence of the observers', function () {
    $observers = makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [9, 8, 7]]);
    // A Belgian observing in the Netherlands.
    insertCountryTestObservations($observers['BE'][0], insertCountryTestLocation($observers['BE'][0], 'Netherlands'), 4);

    $total = fn ($response) => collect($response->viewData('summaryRows'))->firstWhere('key', 'observations')['values']['total'];

    $response = $this->get(route('statistics.index', ['residence' => 'BE']))->assertOk();
    expect($total($response))->toBe(10)
        ->and($response->viewData('selectedResidence'))->toBe('BE');

    $response = $this->get(route('statistics.index', ['country' => 'Netherlands', 'residence' => 'BE']))->assertOk();
    expect($total($response))->toBe(4);

    $this->get(route('statistics.index', ['residence' => 'XX']))->assertNotFound();
});

it('shows a logged-in observer the observations made in their own country first', function () {
    $observers = makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [9, 8, 7]]);

    $this->actingAs($observers['BE'][0]);
    $this->get(route('statistics.index'))->assertOk()->assertViewHas('selectedCountry', 'Belgium');
    $this->get(route('statistics.index', ['country' => '']))->assertOk()->assertViewHas('selectedCountry', null);
});

it('counts the observers per country of residence on the statistics page', function () {
    makeCountryTestObservers(['BE' => [3, 2, 1], 'NL' => [9, 8]]);

    $this->get(route('statistics.index'))
        ->assertOk()
        ->assertSee('Observers per country of residence');
});

it('ranks the popular objects of the observers of one country', function () {
    $observers = makeCountryTestObservers(['BE' => [1, 1, 1], 'NL' => [1, 1, 1]]);
    foreach ($observers['NL'] as $observer) {
        insertCountryTestObservations($observer, insertCountryTestLocation($observer, 'Netherlands'), 3, 'M 42');
    }

    $ranking = app(PopularObjectsRanking::class);
    expect($ranking->rows('observations')[0]['name'])->toBe('M 42')
        ->and(collect($ranking->rows('observations', 'BE'))->pluck('name')->all())->toBe(['M 31'])
        ->and(collect($ranking->rows('observations', 'BE'))->pluck('count')->all())->toBe([3]);

    $this->get(route('objects.popular', ['country' => 'BE']))->assertOk()->assertViewHas('country', 'BE');
    Livewire::test(PopularObjectsTable::class, ['country' => 'ZZ'])->assertSet('country', null);
});

it('shows the popular observations of the observers of one country', function () {
    $observers = makeCountryTestObservers(['BE' => [1, 1, 1], 'NL' => [1, 1, 1]]);
    $liker = User::factory()->create();
    foreach ([$observers['BE'][0], $observers['NL'][0]] as $observer) {
        ObservationLike::create([
            'user_id' => $liker->id,
            'observation_type' => ObservationLike::TYPE_OBSERVATION,
            'observation_id' => Observation::where('observer_username', $observer->username)->value('id'),
        ]);
    }

    $observerIds = fn (?string $country) => Livewire::test(PopularObservationsTable::class, ['country' => $country])
        ->instance()->datasource()->get()->pluck('observerid')->all();

    expect($observerIds('NL'))->toBe([$observers['NL'][0]->username])
        ->and($observerIds(null))->toHaveCount(2);
});

it('filters the public observing lists on the country of their owner', function () {
    $observers = makeCountryTestObservers(['BE' => [1, 1, 1], 'NL' => [1, 1, 1]]);
    foreach (['BE' => 'Belgian list', 'NL' => 'Dutch list'] as $code => $name) {
        DB::table('observing_lists')->insert([
            'owner_user_id' => $observers[$code][0]->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'public' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->get(route('observing-lists.discover', ['country' => 'NL']))
        ->assertOk()
        ->assertSee('Dutch list')
        ->assertDontSee('Belgian list');
});

it('lists the observers of the own country first as co-observers', function () {
    $viewer = User::factory()->create(['country' => 'NL', 'name' => 'Viewer']);
    User::factory()->create(['country' => 'BE', 'name' => 'Anna']);
    User::factory()->create(['country' => 'NL', 'name' => 'Zoe']);

    $names = collect($this->actingAs($viewer)->getJson(route('users.select.api', ['compatriots_first' => 1]))->assertOk()->json())->pluck('name');
    expect($names->search('Zoe'))->toBeLessThan($names->search('Anna'));

    $names = collect($this->actingAs($viewer)->getJson(route('users.select.api'))->assertOk()->json())->pluck('name');
    expect($names->search('Anna'))->toBeLessThan($names->search('Zoe'));
});

it('starts a new location in the country of residence', function () {
    $user = User::factory()->create(['country' => 'BE']);

    Livewire::actingAs($user)->test(CreateLocation::class)->assertSet('country', 'Belgium');
});

it('suggests inches to observers from the United States', function () {
    $this->actingAs(User::factory()->create(['country' => 'US', 'showInches' => 0])->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->assertSee('usually give the diameter in inches')
        ->call('$set', 'showInches', true)
        ->assertDontSee('usually give the diameter in inches');
});

it('returns the asked country as the selected option of the country select', function () {
    $this->actingAs(User::factory()->create(['country' => '']));

    $this->getJson(route('countries.index', ['lang' => 'en', 'selected' => ['NL']]))
        ->assertOk()
        ->assertJsonPath('0', ['id' => 'NL', 'name' => 'Netherlands']);
});
