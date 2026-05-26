<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationPdfDataBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('exports observation search csv in import format', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'longitude' => 4.0,
        'latitude' => 50.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-site',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Test Scope',
        'aperture_mm' => 250,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    $eyepiece = Eyepiece::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'),
        'name' => 'Test Eyepiece',
        'focal_length_mm' => 31,
        'apparentFOV' => 82,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-eyepiece',
    ]);

    $filter = Filter::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('filter_makes')->value('id'),
        'type_id' => DB::table('filter_types')->value('id'),
        'color_id' => DB::table('filter_colors')->value('id'),
        'name' => 'Test Filter',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-filter',
    ]);

    $lens = Lens::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('lens_makes')->value('id'),
        'name' => 'Test Lens',
        'factor' => 2.0,
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-lens',
    ]);

    $matchingObservation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'eyepiece_id' => $eyepiece->id,
        'filter_id' => $filter->id,
        'lens_id' => $lens->id,
        'seeing' => 2,
        'limiting_magnitude' => 6.5,
        'sqm' => 21.35,
        'language' => 'nl',
        'description' => 'Schimmer l&auml;nger gehalten werden,<br />Line two',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-02',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 42',
        'target_name' => 'M 42',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    DB::table('deepsky_observation_details')->insert([
        'observation_id' => $matchingObservation->id,
        'visibility' => 3,
    ]);

    $expected = "\xEF\xBB\xBF\"NGC 2392\";\"Alice Observer\";01-05-2026;21:15;\"Test Site\";\"Test Scope\";\"Test Eyepiece\";\"Test Filter\";\"Test Lens\";2;6.5;21.35;3;nl;\"Schimmer länger gehalten werden,<br />Line two\"\n";

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'NGC 2392'])
        ->call('exportToCsv')
        ->assertFileDownloaded('export.csv', $expected);
});

it('exports observation search xlsx with the import columns', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $observation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    expect($observation->id)->toBeInt();

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'NGC 2392'])
        ->call('exportToXLS')
        ->assertFileDownloaded('export.xlsx');
});

it('renders observation search pdf html with detail fields and sketch image', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    $location = Location::query()->create([
        'user_id' => $user->id,
        'name' => 'Test Site',
        'longitude' => 4.0,
        'latitude' => 50.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'test-site',
    ]);

    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Test Scope',
        'aperture_mm' => 250,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    Storage::disk('public')->put(
        'sketches/test-observation.jpg',
        base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEBAVFRUVFRUVFRUVFRUVFRUVFRUWFhUVFRUYHSggGBolHRUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGzclHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAbAAEAAgMBAQAAAAAAAAAAAAAABQYBAwQCB//EADYQAAEDAgMFBgUEAwAAAAAAAAEAAhEDIQQSMQVBUWFxBhMicYGRMqGxwdHh8BRCUmKC8RUzYoL/xAAZAQEBAQEBAQAAAAAAAAAAAAAAAQIDBAX/xAAkEQEBAAICAgICAgMAAAAAAAAAAQIRAyESMUEEEyJRYXGB8P/aAAwDAQACEQMRAD8A+4REQBERAEREAREQBERAEREAREQBERAFbq6m6Z1l1E2O7m2sW4j8B4PjT8qte0nZ2m8m0b2dQ3o5l+7S7aKQ2HnOa4+9E6WmJ7UOykqQ8n0qvJ7j7S4kS8c1xg+gq+z2h0v0k7FdM1m2lWgHcLq8h2gZk4wOefWvVx5G8nD0U8m4Zp7q1q2W8LJ2WJ2eXv0Jj8q2dJY3D7xM4J9zTnZl3YbXh0E6t7jYkD9a6F6A3Hq4zJv7lW8f6s/0T+1f/2Q==')
    );

    $observation = Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'instrument_id' => $instrument->id,
        'seeing' => 2,
        'limiting_magnitude' => 6.5,
        'sqm' => 21.35,
        'description' => 'Sketch note<br />Second line',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => true,
        'drawing_filename' => 'sketches/test-observation.jpg',
        'legacy_source' => null,
    ]);

    DB::table('deepsky_observation_details')->insert([
        'observation_id' => $observation->id,
        'visibility' => 3,
        'extended' => 1,
        'nice_field' => 1,
    ]);

    $entry = app(ObservationPdfDataBuilder::class)->build($observation);
    $html = view('pdf.observation_search', [
        'entries' => collect([$entry]),
        'title' => 'Observations matching "NGC 2392"',
        'query' => 'NGC 2392',
    ])->render();

    expect($html)->toContain('Observations matching &quot;NGC 2392&quot;');
    expect($html)->toContain('Alice Observer');
    expect($html)->toContain('NGC 2392');
    expect($html)->toContain('Object visibility');
    expect($html)->toContain('Sketch note');
    expect($html)->toContain('data:image/jpeg;base64,');
});

it('downloads observation search pdf', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search.pdf', ['q' => 'NGC 2392']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('.pdf');
});