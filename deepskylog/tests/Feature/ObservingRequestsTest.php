<?php

use App\Jobs\NotifyObservingRequestAnswer;
use App\Livewire\ObjectObservingRequests;
use App\Livewire\ObservingRequestsPage;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\Message;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\ObservingRequest;
use App\Models\User;
use App\Services\ObjectMerger;
use App\Services\ObservingRequests;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Observing requests: an observer asks the others to observe an object; new
// observations of others answer it (ObservingRequest, ObservingRequests).

function orqObject(string $name, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'PEG',
        'ra'            => 22.6, 'decl' => 34.0, 'mag' => 13.0, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 60, 'diam2' => 60,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function orqInstrument(User $user, int $apertureMm): Instrument
{
    return InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => $apertureMm . ' mm', 'aperture_mm' => $apertureMm, 'focal_length_mm' => 1500,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
}

/** An observer with a standard telescope of $apertureMm and a standard location. */
function orqObserver(int $apertureMm = 400): User
{
    $user = User::factory()->create();
    $instrument = orqInstrument($user, $apertureMm);
    $location = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();

    return $user->fresh();
}

function orqMetric(User $user, string $object, float $reserve): void
{
    DB::table('user_object_metrics')->insert([
        'user_id'                         => $user->id, 'instrument_id' => $user->stdtelescope, 'location_id' => $user->stdlocation, 'lens_id' => null,
        'object_name'                     => $object, 'contrast_reserve' => $reserve, 'contrast_reserve_category' => 'easy',
        'optimum_detection_magnification' => 100, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

/** An observation through the model, so ObservationObserver runs, with its visibility. */
function orqObserve(User $observer, string $object, ?int $instrumentId, int $visibility = 1): Observation
{
    $observation = Observation::forceCreate([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'observed_on' => now()->toDateString(),
        'instrument_id'     => $instrumentId, 'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object,
        'target_name'       => $object, 'has_drawing' => false, 'language' => 'en', 'legacy_source' => 'deepsky',
    ]);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => $visibility]);

    return $observation;
}

beforeEach(fn () => orqObject('NGC 7317'));

it('lets an observer ask for observations on the object page', function () {
    $me = User::factory()->create();

    Livewire::actingAs($me)->test(ObjectObservingRequests::class, ['objectName' => 'NGC 7317'])
        ->set('asking', true)
        ->set('note', "  Not seen with my <b>8-inch</b>\n ")
        ->set('minApertureMm', 350)
        ->set('months', 2)
        ->call('ask')
        ->assertHasNoErrors()
        ->assertSee('Not seen with my 8-inch')
        ->assertDontSee('Ask others to observe this object');

    $request = ObservingRequest::sole();
    expect($request->object_name)->toBe('NGC 7317')
        ->and($request->note)->toBe('Not seen with my 8-inch')
        ->and($request->min_aperture_mm)->toBe(350)
        ->and($request->expires_on->toDateString())->toBe(today()->addMonthsNoOverflow(2)->toDateString())
        ->and($request->isOpen())->toBeTrue();
});

it('refuses a second open request, an aperture not offered and an unknown object', function () {
    $me = User::factory()->create();
    ObservingRequest::ask($me, 'NGC 7317', null, null);

    expect(fn () => ObservingRequest::ask($me, 'NGC 7317', null, null))->toThrow(Illuminate\Validation\ValidationException::class);
    expect(fn () => ObservingRequest::ask($me, 'NGC 7317', null, 123))->toThrow(Illuminate\Validation\ValidationException::class);
    expect(fn () => ObservingRequest::ask($me, 'Nothing 1', null, null))->toThrow(Illuminate\Validation\ValidationException::class);

    // once closed, the observer may ask again
    ObservingRequest::sole()->close();
    expect(ObservingRequest::ask($me, 'NGC 7317', null, null)->isOpen())->toBeTrue();
});

it('records an observation of another observer with a large enough aperture as an answer and tells the observer who asked', function () {
    $asker = User::factory()->create(['sendMail' => false]);
    $request = ObservingRequest::ask($asker, 'NGC 7317', 'Who can try?', 350);

    $small = User::factory()->create();
    orqObserve($small, 'NGC 7317', orqInstrument($small, 200)->id);
    orqObserve($asker, 'NGC 7317', orqInstrument($asker, 500)->id);
    expect($request->answers()->count())->toBe(0);

    // the message is queued, as the form saves the visibility after the observation
    Queue::fake();
    $large = User::factory()->create(['name' => 'Big Dob']);
    $observation = orqObserve($large, 'NGC 7317', orqInstrument($large, 406)->id, Observation::VISIBILITY_NOT_SEEN);

    expect($request->answers()->pluck('observations.id')->all())->toBe([$observation->id])
        ->and($request->fresh()->answered_at)->not->toBeNull();
    Queue::assertPushed(NotifyObservingRequestAnswer::class, 1);
    (new NotifyObservingRequestAnswer($request->id, $observation->id))->handle();

    $message = Message::where('receiver', $asker->username)->sole();
    expect($message->subject)->toContain('NGC 7317')
        ->and($message->message)->toContain('Big Dob looked for NGC 7317 but did not see it');
});

it('does not answer a closed or expired request', function () {
    $asker = User::factory()->create();
    $closed = ObservingRequest::ask($asker, 'NGC 7317', null, null);
    $closed->close();
    orqObject('NGC 7318');
    $expired = ObservingRequest::ask($asker, 'NGC 7318', null, null);
    $expired->forceFill(['expires_on' => today()->subDay()])->save();

    $other = User::factory()->create();
    orqObserve($other, 'NGC 7317', null);
    orqObserve($other, 'NGC 7318', null);

    expect(DB::table('observing_request_answers')->count())->toBe(0)
        ->and(Message::count())->toBe(0);
});

it('lists the requests the standard telescope can probably answer', function () {
    foreach (['Shown', 'Too faint', 'Not calculated', 'Too large aperture', 'Answered'] as $name) {
        orqObject($name);
    }
    orqObject('A double', 'DS');
    $asker = User::factory()->create();
    foreach (['Shown', 'Too faint', 'Not calculated', 'Answered', 'A double'] as $name) {
        ObservingRequest::ask($asker, $name, null, null);
    }
    ObservingRequest::ask($asker, 'Too large aperture', null, 500);

    $me = orqObserver(400);
    // creating the telescope calculated the metrics of the objects already there
    DB::table('user_object_metrics')->delete();
    Queue::fake();
    ObservingRequest::ask($me, 'NGC 7317', null, null);
    orqMetric($me, 'Shown', 0.8);
    orqMetric($me, 'Too faint', -0.3);
    orqMetric($me, 'Too large aperture', 2.0);
    orqObserve($me, 'Answered', $me->stdtelescope);

    $requests = ObservingRequests::forViewer($me)->keyBy('object_name');

    expect($requests->keys()->sort()->values()->all())->toBe(['A double', 'Not calculated', 'Shown'])
        ->and($requests['Shown']->verdict)->toBe('visible')
        ->and($requests['Not calculated']->verdict)->toBe('pending');

    $home = ObservingRequests::forHomePage($me);
    expect($home['count'])->toBe(3)
        ->and($home['requests'])->toHaveCount(3)
        ->and($home['requests'][0]->slug)->not->toBeNull();
});

it('shows all open requests to an observer without standard equipment', function () {
    $asker = User::factory()->create();
    ObservingRequest::ask($asker, 'NGC 7317', null, 600);

    $me = User::factory()->create();
    expect(ObservingRequests::forViewer($me)->pluck('object_name')->all())->toBe(['NGC 7317']);
});

it('marks requested objects among a list of names, not the viewer\'s own', function () {
    orqObject('NGC 7318');
    $asker = User::factory()->create();
    ObservingRequest::ask($asker, 'NGC 7317', null, null);

    expect(ObservingRequests::requestedAmong(['NGC 7317', 'NGC 7318'], null))->toBe(['ngc 7317' => true])
        ->and(ObservingRequests::requestedAmong(['NGC 7317'], $asker))->toBe([]);
});

it('shows the page with the observer\'s own requests and their answers, and makes a list of the requests for the telescope', function () {
    $me = orqObserver(400);
    $mine = ObservingRequest::ask($me, 'NGC 7317', 'Compare please', null);
    $other = User::factory()->create(['name' => 'Answering Observer']);
    orqObserve($other, 'NGC 7317', orqInstrument($other, 254)->id);

    orqObject('NGC 7331');
    ObservingRequest::ask($other, 'NGC 7331', 'Spiral arms?', null);
    orqMetric($me, 'NGC 7331', 1.2);

    Livewire::actingAs($me)->test(ObservingRequestsPage::class)
        ->assertSee('NGC 7331')
        ->assertSee('Spiral arms?')
        ->assertDontSee('Compare please')
        ->set('view', 'mine')
        ->assertSee('Compare please')
        ->assertSee('Answering Observer')
        ->assertSee('seen')
        ->call('closeRequest', $mine->id)
        ->assertSee('closed');

    expect($mine->fresh()->closed_at)->not->toBeNull();

    Livewire::actingAs($me)->test(ObservingRequestsPage::class)->call('createList');
    expect(ObservingList::where('owner_user_id', $me->id)->sole()->items()->pluck('object_name')->all())->toBe(['NGC 7331']);
});

it('does not let another observer close a request', function () {
    $asker = User::factory()->create();
    $request = ObservingRequest::ask($asker, 'NGC 7317', null, null);

    Livewire::actingAs(User::factory()->create())->test(ObservingRequestsPage::class, ['view' => 'all'])
        ->call('closeRequest', $request->id)
        ->assertForbidden();
});

it('moves the requests with a merged object and deletes them with a deleted one', function () {
    orqObject('MCG 1');
    $asker = User::factory()->create();
    ObservingRequest::ask($asker, 'MCG 1', null, null);

    ObjectMerger::merge('MCG 1', 'NGC 7317');
    expect(ObservingRequest::sole()->object_name)->toBe('NGC 7317');

    ObjectMerger::delete('NGC 7317');
    expect(ObservingRequest::count())->toBe(0);
});

it('opens the pages', function () {
    $me = orqObserver();
    $this->actingAs($me)->get(route('observing-requests'))->assertOk()->assertSee('Observing requests');
    $this->actingAs($me)->get(route('observing-requests', ['view' => 'mine']))->assertOk();
});
