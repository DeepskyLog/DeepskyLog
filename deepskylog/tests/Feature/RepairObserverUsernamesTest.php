<?php

use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

function oldSession(string $observer): int
{
    return (int) DB::table('observation_sessions')->insertGetId(['name' => 'Night', 'observerid' => $observer,
        'begindate' => '2015-01-01 20:00:00', 'enddate' => '2015-01-02 02:00:00', 'locationid' => 0, 'active' => 1]);
}

beforeEach(function () {
    $this->torbjorn = User::factory()->create(['username' => 'Torbjörn Holmqvist']);
    $this->bob = User::factory()->create(['username' => 'bob']);
});

it('changes nothing without --apply', function () {
    $session = oldSession('Torbj&ouml;rn Holmqvist');

    $this->artisan('observers:repair-usernames')
        ->expectsOutputToContain('Names renamed to the username of their account: 1')
        ->assertSuccessful();

    expect(DB::table('observation_sessions')->find($session)->observerid)->toBe('Torbj&ouml;rn Holmqvist');
});

it('gives the observer their sessions, co-observed sessions and equipment under their old name', function () {
    $encoded = oldSession('Torbj&ouml;rn Holmqvist');
    $garbled = oldSession('TorbjÃ¶rn Holmqvist');
    $bobs = oldSession('bob');
    $both = oldSession('bob');
    DB::table('sessionObservers')->insert([
        ['sessionid' => $bobs, 'observer' => 'TorbjÃ¶rn Holmqvist'],
        // Listed under both names: one row stays
        ['sessionid' => $both, 'observer' => 'TorbjÃ¶rn Holmqvist'],
        ['sessionid' => $both, 'observer' => 'Torbjörn Holmqvist'],
    ]);
    $instrument = InstrumentFactory::new()->create(['user_id' => $this->torbjorn->id, 'observer' => 'Torbj&ouml;rn Holmqvist']);

    $this->artisan('observers:repair-usernames --apply')->assertSuccessful();

    expect(DB::table('observation_sessions')->find($encoded)->observerid)->toBe('Torbjörn Holmqvist')
        ->and(DB::table('observation_sessions')->find($garbled)->observerid)->toBe('Torbjörn Holmqvist')
        ->and(DB::table('observation_sessions')->find($bobs)->observerid)->toBe('bob')
        ->and(DB::table('sessionObservers')->where('sessionid', $bobs)->pluck('observer')->all())->toBe(['Torbjörn Holmqvist'])
        ->and(DB::table('sessionObservers')->where('sessionid', $both)->pluck('observer')->all())->toBe(['Torbjörn Holmqvist'])
        ->and($instrument->fresh()->observer)->toBe('Torbjörn Holmqvist');

    $this->artisan('observers:repair-usernames')
        ->expectsOutputToContain('Names renamed to the username of their account: 0')
        ->assertSuccessful();
});

it('leaves the names of deleted accounts as they are', function () {
    $session = oldSession('Ren&eacute;');

    $this->artisan('observers:repair-usernames --apply')
        ->expectsOutputToContain('Names without an account, left as they are: 1')
        ->assertSuccessful();

    expect(DB::table('observation_sessions')->find($session)->observerid)->toBe('Ren&eacute;');
});
