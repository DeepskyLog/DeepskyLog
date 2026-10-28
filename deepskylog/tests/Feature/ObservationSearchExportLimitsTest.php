<?php

use App\Models\Observation;
use App\Models\User;
use App\Services\Import\OalSchemaValidator;

function insertExportLimitObservations(User $user, int $count, array $overrides = []): void
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = $overrides + [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-01',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 7000',
            'target_name' => 'NGC 7000',
            'has_drawing' => false,
            'legacy_source' => null,
        ];
    }

    foreach (array_chunk($rows, 200) as $chunk) {
        Observation::query()->insert($chunk);
    }
}

beforeEach(function () {
    $this->observer = User::factory()->create([
        'name' => 'Export Observer',
        'username' => 'export-observer',
        'slug' => 'export-observer',
    ]);
});

it('refuses an export that matches more observations than it can build', function () {
    insertExportLimitObservations($this->observer, 501);

    $response = $this->actingAs($this->observer)
        ->from(route('observations.search'))
        ->get(route('observations.search.pdf'));

    $response->assertRedirect(route('observations.search'));
    $response->assertSessionHas('error', 'This search matches 501 observations, but at most 500 observations can be exported as PDF. Please narrow down your search.');
});

it('still exports when the search stays within the maximum', function () {
    insertExportLimitObservations($this->observer, 3);

    $response = $this->get(route('observations.search.skylist', ['q' => 'NGC 7000']));

    $response->assertOk();
    expect($response->streamedContent() ?: $response->getContent())->toContain('NGC 7000');
});

it('removes characters that xml does not allow from the oal export', function () {
    insertExportLimitObservations($this->observer, 1, [
        'description' => "Faint glow\x03 near a star\x1F.",
    ]);

    $response = $this->get(route('observations.search.oal', ['q' => 'NGC 7000']));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)
        ->toContain('Faint glow near a star.')
        ->not->toContain("\x03")
        ->not->toContain("\x1F");

    app(OalSchemaValidator::class)->assertValid($content);
});
