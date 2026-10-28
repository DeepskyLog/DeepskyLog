<?php

use App\Models\Country;
use App\Models\User;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

it('maps country names to the canonical name of the countries table', function (?string $name, ?string $canonical) {
    expect(Country::canonicalName($name))->toBe($canonical);
})->with([
    'name of the table' => ['Belgium', 'Belgium'],
    'English name of the country list' => ['Czechia', 'Czech Republic'],
    'other case and spaces' => [' czechia ', 'Czech Republic'],
    'reverse geocoder name with brackets' => ['Netherlands (the)', 'Netherlands'],
    'two names in the table' => ['United States of America', 'United States'],
    'spelling of the old site' => ['Madagaskar', 'Madagascar'],
    'unknown' => ['Unknown', 'Unknown'],
    'empty' => ['', ''],
    'null' => [null, null],
]);

it('stores the canonical country name of a location', function () {
    $user = User::factory()->create();

    $location = LocationFactory::new()->create([
        'user_id' => $user->id,
        'observer' => $user->username,
        'country' => 'Czechia',
    ]);

    expect(DB::table('locations')->where('id', $location->id)->value('country'))->toBe('Czech Republic');

    $location->update(['country' => 'United Kingdom']);

    expect(DB::table('locations')->where('id', $location->id)->value('country'))
        ->toBe('United Kingdom of Great Britain and Northern Ireland');
});

it('renames the existing location countries to their canonical names', function () {
    $user = User::factory()->create();
    $insert = fn (string $name, string $country, float $latitude = 50.85, float $longitude = 4.35) => DB::table('locations')->insertGetId([
        'name' => $name,
        'longitude' => $longitude,
        'latitude' => $latitude,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => $country,
        'active' => true,
        'user_id' => $user->id,
        'observer' => $user->username,
        'slug' => str($name)->slug()->toString(),
    ]);

    $czechia = $insert('Bulovka', 'Czechia');
    $czechRepublic = $insert('Ondřejov', 'Czech Republic');
    $noviSad = $insert('Novi Sad', 'Serbia and montenegro', 45.25, 19.85);
    $kotor = $insert('Kotor', 'Serbia and montenegro', 42.42, 18.77);
    // The stored longitude of this location on Bonaire is 2 degrees off.
    $seruLargu = $insert('Seru Largu', 'Netherlands antilles', 12.2, -66.2667);
    $willemstad = $insert('Willemstad', 'Netherlands antilles', 12.11, -68.93);
    $unknown = $insert('Nowhere', 'Unknown');

    (require database_path('migrations/2026_10_14_000000_normalize_location_countries.php'))->up();

    expect(DB::table('locations')->where('id', $seruLargu)->value('longitude'))->toEqual(-68.2667);
    expect(DB::table('locations')->whereIn('id', [$czechia, $czechRepublic, $noviSad, $kotor, $seruLargu, $willemstad, $unknown])->pluck('country', 'id')->all())
        ->toBe([
            $czechia => 'Czech Republic',
            $czechRepublic => 'Czech Republic',
            $noviSad => 'Serbia',
            $kotor => 'Montenegro',
            $seruLargu => 'Bonaire, Sint Eustatius and Saba',
            $willemstad => 'Curaçao',
            $unknown => 'Unknown',
        ]);
});
