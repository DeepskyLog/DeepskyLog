<?php

use App\Http\Controllers\ObservationImportController;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

it('shows schema validation errors for invalid oal uploads through the import controller', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $user->forceFill([
        'name' => 'OAL Import User',
        'username' => 'oal-import-user',
        'slug' => 'oal-import-user',
    ])->save();

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<oal:observations xmlns:oal="http://groups.google.com/group/openastronomylog" version="2.1">
  <observers />
  <sites />
  <sessions />
  <targets />
  <scopes />
  <eyepieces />
  <lenses />
  <filters />
  <imagers />
  <observation id="observation-1">
    <observer>observer-1</observer>
    <begin>2026-05-01T21:15:00</begin>
    <result lang="en">
      <description>Invalid because target is missing.</description>
    </result>
  </observation>
</oal:observations>
XML;

    $upload = UploadedFile::fake()->createWithContent('invalid-oal.xml', $xml);
    $request = Request::create('/observation/import', 'POST', [
        'format' => 'oal',
    ], [], [
        'import_file' => $upload,
    ]);
    $request->setLaravelSession(app('session')->driver());
    $request->setUserResolver(fn() => $user);

    Auth::login($user);

    $response = app(ObservationImportController::class)->import($request);

    expect($response)->toBeInstanceOf(View::class);
    expect($response->name())->toBe('observations.import-result');

    $data = $response->getData();

    expect($data['format'])->toBe('oal');
    expect($data['result']->imported)->toBe(0);
    expect($data['result']->errors)->toBeGreaterThan(0);
    expect($response->render())->toContain('Import Results');
    expect($response->render())->toContain('OAL 2.1 schema validation error:');

    expect(Observation::query()->where('observer_user_id', $user->id)->count())->toBe(0);
});