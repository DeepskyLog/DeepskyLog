<?php

use App\Models\InstrumentSet;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Support\Api\MeApiSpec;
use App\Support\Api\OpenApiValidator;
use Database\Factories\EyepieceFactory;
use Database\Factories\FilterFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

// The OpenAPI description of /api/v1/me (App\Support\Api\MeApiSpec) against the routes and responses.

function openApiResponseSchema(array $spec, string $path): array
{
    return $spec['paths'][$path]['get']['responses']['200']['content']['application/json']['schema'];
}

it('describes every route of the token API', function () {
    $spec = MeApiSpec::spec();

    $routes = collect(Route::getRoutes()->getRoutesByName())
        ->filter(fn ($route, $name) => str_starts_with($name, 'api.me.'))
        ->map(fn ($route) => '/'.$route->uri())
        ->sort()->values()->all();

    expect($spec['openapi'])->toBe('3.1.0')
        ->and($routes)->not->toBeEmpty()
        ->and(array_keys($spec['paths']))->toEqualCanonicalizing($routes);
    foreach ($spec['paths'] as $operations) {
        expect($operations['get']['responses'])->toHaveKeys(['200', '401', '403']);
    }
});

it('serves the description as JSON without a token and shows the documentation page', function () {
    $this->getJson(route('api.openapi'))
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0')
        ->assertJsonPath('info.title', 'DeepskyLog API')
        ->assertJsonPath('components.securitySchemes.bearerAuth.scheme', 'bearer');

    $this->get(route('api.documentation'))
        ->assertOk()
        ->assertSee(route('api.openapi'), false);
});

it('returns responses with the fields of the description', function () {
    $spec = MeApiSpec::spec();
    $user = User::factory()->create(['country' => 'BE']);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'skyBackground' => 21.2, 'description' => '<p>Dark</p>']);
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 300, 'focal_length_mm' => 1500]);
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => 25, 'apparentFOV' => 52]);
    LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    FilterFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $set = InstrumentSet::factory()->create(['user_id' => $user->id, 'name' => 'Travel kit']);
    $set->instruments()->attach($instrument->id);
    $user->forceFill(['stdlocation' => $location->id, 'stdtelescope' => $instrument->id])->save();

    $deepsky = Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-03-01', 'observed_time' => '21:30:00',
        'instrument_id' => $instrument->id, 'location_id' => $location->id, 'eyepiece_id' => $eyepiece->id, 'seeing' => 2, 'sqm' => 21.1,
        'description' => '<p>Seen</p>', 'language' => 'en', 'observer_class' => 'B',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65',
    ]);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $deepsky->id, 'visibility' => 2, 'small_diameter' => 30, 'magnification' => '120', 'stellar' => -1, 'extended' => 1, 'component1' => -1]);
    $comet = Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-03-02',
        'description' => 'Tail', 'target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => '12', 'target_name' => '12P/Pons-Brooks',
    ]);
    DB::table('comet_observation_details')->insert(['observation_id' => $comet->id, 'comet_object_id' => 12, 'method' => 'S', 'magnitude' => 4.5, 'magnitude_uncertain' => 1, 'dc' => '5', 'coma' => 8]);
    Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-03-03',
        'description' => 'Bands', 'target_type' => 'planet', 'target_table' => 'planets', 'target_key' => 'jupiter', 'target_name' => 'Jupiter',
    ]);

    $session = ObservationSession::create([
        'name' => 'Messier marathon', 'slug' => 'messier-marathon', 'observerid' => $user->username, 'locationid' => $location->id, 'active' => 1,
        'begindate' => '2026-03-01 20:00:00', 'enddate' => '2026-03-02 04:00:00', 'weather' => '<p>Clear</p>', 'equipment' => '',
    ]);
    DB::table('sessionObservations')->insert(['sessionid' => $session->id, 'observationid' => $deepsky->id]);

    $list = ObservingList::create(['owner_user_id' => $user->id, 'name' => 'Galaxies', 'public' => true]);
    ObservingListItem::create(['observing_list_id' => $list->id, 'object_name' => 'M 65', 'sort_order' => 1, 'added_by_user_id' => $user->id, 'planned_at' => '2026-03-01 21:00:00']);

    $party = new StarParty([
        'user_id' => $user->id, 'name' => 'Autumn starparty', 'description' => '', 'language' => 'en', 'visibility' => StarParty::PUBLIC,
        'start_date' => today()->addDays(10)->toDateString(), 'end_date' => today()->addDays(11)->toDateString(),
    ]);
    $party->useLocation($location);
    $party->save();
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $user->id, 'status' => StarPartyParticipant::GOING]);

    Sanctum::actingAs($user, ['read']);

    $requests = [
        '/api/v1/me' => route('api.me.profile'),
        '/api/v1/me/locations' => route('api.me.locations'),
        '/api/v1/me/instruments' => route('api.me.instruments'),
        '/api/v1/me/eyepieces' => route('api.me.eyepieces'),
        '/api/v1/me/lenses' => route('api.me.lenses'),
        '/api/v1/me/filters' => route('api.me.filters'),
        '/api/v1/me/instrument-sets' => route('api.me.instrument-sets'),
        '/api/v1/me/observations' => route('api.me.observations', ['per_page' => 2]),
        '/api/v1/me/observations/{id}' => route('api.me.observation', $deepsky->id),
        '/api/v1/me/sessions' => route('api.me.sessions'),
        '/api/v1/me/sessions/{id}' => route('api.me.session', $session->id),
        '/api/v1/me/observing-lists' => route('api.me.observing-lists'),
        '/api/v1/me/observing-lists/{id}' => route('api.me.observing-list', $list->id),
        '/api/v1/me/star-parties' => route('api.me.star-parties'),
    ];
    expect(array_keys($requests))->toEqualCanonicalizing(array_keys($spec['paths']));

    foreach ($requests as $path => $url) {
        $json = $this->getJson($url)->assertOk()->json();
        expect($json)->not->toBeEmpty("{$path} returned nothing to check");
        expect(OpenApiValidator::problems($json, openApiResponseSchema($spec, $path), $spec))->toBe([], $path);
    }

    // the deep-sky observation, on the second page
    $json = $this->getJson(route('api.me.observations', ['per_page' => 2, 'page' => 2]))->assertOk()->json();
    expect($json['data'])->toHaveCount(1)
        ->and(OpenApiValidator::problems($json, openApiResponseSchema($spec, '/api/v1/me/observations'), $spec))->toBe([]);
});
