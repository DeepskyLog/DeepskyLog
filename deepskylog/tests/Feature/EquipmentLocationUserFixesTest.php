<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Livewire\CreateInstrument;
use App\Livewire\CreateLocation;
use App\Livewire\InstrumentTable;
use App\Livewire\LocationTable;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Location;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

// The location form looks the elevation, country and time zone up online.
class CreateLocationCountingLookups extends CreateLocation
{
    public static int $lookups = 0;

    public function updateElevation($latitude, $longitude): void
    {
        self::$lookups++;
    }

    public function updateCountry($latitude, $longitude): void
    {
        self::$lookups++;
    }
}

it('detaches an instrument from its sets and clears the default when it is deleted', function () {
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $set = InstrumentSet::factory()->create(['user_id' => $user->id]);
    $set->instruments()->attach($instrument->id);
    $user->forceFill(['stdtelescope' => $instrument->id])->save();

    $this->actingAs($user);
    (new InstrumentTable)->clickToDelete($instrument->id);

    expect(Instrument::find($instrument->id))->toBeNull()
        ->and(DB::table('instrument_set_instrument')->count())->toBe(0)
        ->and($user->fresh()->stdtelescope)->toBeNull();
});

it('does not delete the equipment of another user', function () {
    $owner = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $owner->id, 'observer' => $owner->username]);

    $this->actingAs(User::factory()->create());
    (new InstrumentTable)->clickToDelete($instrument->id);

    expect(Instrument::find($instrument->id))->not->toBeNull();
});

it('clears the default location and set when they are deleted', function () {
    $user = User::factory()->create();
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $set = InstrumentSet::factory()->create(['user_id' => $user->id]);
    $set->locations()->attach($location->id);
    $user->forceFill(['stdlocation' => $location->id, 'stdinstrumentset' => $set->id])->save();

    $location->delete();
    $set->delete();

    expect($user->fresh()->stdlocation)->toBeNull()
        ->and($user->fresh()->stdinstrumentset)->toBeNull()
        ->and(DB::table('instrument_set_location')->count())->toBe(0);
});

it('returns the selected instrument from the instrument api, also without a default', function () {
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);

    $this->actingAs($user)
        ->getJson('/api/instrument.api?selected='.$instrument->id)
        ->assertOk()
        ->assertJsonPath('0.id', $instrument->id);

    $this->actingAs($user)->getJson('/api/instrument.api?selected=')->assertOk();
});

it('shows unknown countries in the location table', function () {
    $user = User::factory()->create();
    LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'country' => 'Unknown']);
    LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'country' => '']);

    $this->actingAs($user);
    Livewire::test(LocationTable::class)->assertOk();
});

it('only looks the location data up when the coordinates change', function () {
    $user = User::factory()->create();
    $location = LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'latitude' => 50.5, 'longitude' => 4.5,
        'elevation' => 100, 'country' => 'Belgium', 'timezone' => 'Europe/Brussels',
    ]);
    $this->actingAs($user);

    CreateLocationCountingLookups::$lookups = 0;
    Livewire::test(CreateLocationCountingLookups::class, ['location' => $location])->call('save');

    expect(CreateLocationCountingLookups::$lookups)->toBe(0)
        ->and($location->fresh()->country)->toBe('Belgium')
        ->and($location->fresh()->timezone)->toBe('Europe/Brussels');

    Livewire::test(CreateLocationCountingLookups::class, ['location' => $location->fresh()])
        ->set('latitude', 51.0)
        ->call('save');

    expect(CreateLocationCountingLookups::$lookups)->toBe(2);
});

it('keeps the NELM of a location when it is edited with an fstOffset', function () {
    $user = User::factory()->create(['fstOffset' => 0.3]);
    $location = LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'limitingMagnitude' => 6.2, 'skyBackground' => -999,
        'country' => 'Belgium', 'timezone' => 'Europe/Brussels',
    ]);
    $this->actingAs($user);

    $component = Livewire::test(CreateLocationCountingLookups::class, ['location' => $location]);

    expect((float) $component->get('nelm'))->toEqualWithDelta($location->getNelm(0.3), 0.01)
        ->and($location->getNelm(0.3))->toEqualWithDelta(5.9, 0.001);

    $component->call('save');

    expect($location->fresh()->limitingMagnitude)->toEqualWithDelta(6.2, 0.01);
});

it('does not shift instrument values in inches on save', function () {
    $user = User::factory()->create(['showInches' => true]);
    $instrument = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 200, 'focal_length_mm' => 1200,
    ]);
    $this->actingAs($user);

    Livewire::test(CreateInstrument::class, ['instrument' => $instrument])->call('save');

    expect($instrument->fresh()->aperture_mm)->toBe(200)
        ->and((float) $instrument->fresh()->focal_length_mm)->toBe(1200.0);
});

it('validates empty inch values instead of throwing', function () {
    $user = User::factory()->create(['showInches' => true]);
    $this->actingAs($user);

    Livewire::test(CreateInstrument::class)
        ->set('name', 'Dobson')
        ->set('aperture_mm', '')
        ->set('focal_length_mm', '')
        ->call('save')
        ->assertHasErrors(['aperture_mm'])
        ->assertSet('focal_length_mm', 0);
});

it('saves the other profile fields when the email changes', function () {
    Notification::fake();
    $user = User::factory()->create(['fstOffset' => 0]);

    (new UpdateUserProfileInformation)->update($user, [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'country' => 'BE',
        'about' => 'About me',
        'fstOffset' => 0.5,
        'copyrightSelection' => 'Enter your own copyright text',
        'copyright' => 'Me',
    ]);

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->country)->toBe('BE')
        ->and((float) $user->fstOffset)->toBe(0.5)
        ->and($user->copyright)->toBe('Me');
});

it('removes a user through the Jetstream action, including instrument sets', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $set = InstrumentSet::factory()->create(['user_id' => $user->id]);
    $user->createToken('test');

    app(\Laravel\Jetstream\Contracts\DeletesUsers::class)->delete($user);

    expect(User::find($user->id))->toBeNull()
        ->and(InstrumentSet::find($set->id))->toBeNull()
        ->and(DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->count())->toBe(0)
        ->and(DB::table('team_user')->where('user_id', $user->id)->count())->toBe(0);
});

it('does not let a non-administrator remove users', function () {
    $victim = User::factory()->create();
    $this->actingAs(User::factory()->create());

    (new \App\Livewire\RemoveUserTable)->destroy($victim->id);

    expect(User::find($victim->id))->not->toBeNull();
});

it('takes the NELM of the observer (with the fstOffset) from the location of an observation', function () {
    $user = User::factory()->create(['fstOffset' => 0.3]);
    $location = LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'limitingMagnitude' => 6.2, 'skyBackground' => -999,
    ]);
    $this->actingAs($user);

    $component = Livewire::test(\App\Livewire\ObservationConditions::class)
        ->call('onLocationSelected', $location->id);

    expect((float) $component->get('nelm'))->toEqualWithDelta(5.9, 0.001)
        // Converted back with the offset, this gives the SQM of the location
        ->and((float) $component->get('sqm'))->toEqualWithDelta($location->getSqm(), 0.01);
});

it('only removes users without anything of their own', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $withSketch = User::factory()->create();
    DB::table('sketch_of_the_week')->insert(['observation_id' => 1, 'user_id' => $withSketch->id, 'date' => '2026-01-01']);
    $withMonthSketch = User::factory()->create();
    DB::table('sketch_of_the_month')->insert(['observation_id' => 1, 'user_id' => $withMonthSketch->id, 'date' => '2026-01-01']);
    $withLocation = User::factory()->create();
    LocationFactory::new()->create(['user_id' => $withLocation->id, 'observer' => $withLocation->username]);
    $withInstrument = User::factory()->create();
    InstrumentFactory::new()->create(['user_id' => $withInstrument->id, 'observer' => $withInstrument->username]);
    $empty = User::factory()->create();
    InstrumentSet::factory()->create(['user_id' => $empty->id]);

    $this->actingAs($admin);
    $table = new \App\Livewire\RemoveUserTable;
    foreach ([$withSketch, $withMonthSketch, $withLocation, $withInstrument, $empty, $admin] as $user) {
        $table->destroy($user->id);
    }

    expect(User::whereIn('id', [$withSketch->id, $withMonthSketch->id, $withLocation->id, $withInstrument->id, $admin->id])->count())->toBe(5)
        ->and(User::find($empty->id))->toBeNull();
});
