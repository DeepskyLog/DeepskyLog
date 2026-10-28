<?php

use App\Console\Commands\RepairMessageUsernames;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function oldMessage(string $sender, string $receiver): int
{
    return (int) DB::table('messages')->insertGetId([
        'sender' => $sender,
        'receiver' => $receiver,
        'subject' => 'Hello',
        'message' => '<p>Clear skies</p>',
        'date' => '2015-03-20 10:00:00',
    ]);
}

beforeEach(function () {
    $this->torbjorn = User::factory()->create(['username' => 'Torbjörn Holmqvist']);
    $this->bob = User::factory()->create(['username' => 'bob']);
});

it('repairs HTML entities and UTF-8 read as Latin-1', function () {
    expect(RepairMessageUsernames::repairedName('Torbj&ouml;rn Holmqvist'))->toBe('Torbjörn Holmqvist')
        ->and(RepairMessageUsernames::repairedName('TorbjÃ¶rn Holmqvist'))->toBe('Torbjörn Holmqvist')
        ->and(RepairMessageUsernames::repairedName('Ren&eacute;'))->toBe('René')
        ->and(RepairMessageUsernames::repairedName('René'))->toBe('René')
        ->and(RepairMessageUsernames::repairedName('bob'))->toBe('bob')
        ->and(RepairMessageUsernames::repairedName('all'))->toBe('all');
});

it('changes nothing without --apply', function () {
    $id = oldMessage('bob', 'Torbj&ouml;rn Holmqvist');

    $this->artisan('messages:repair-usernames')
        ->expectsOutputToContain('Names renamed to the username of their account: 1')
        ->assertSuccessful();

    expect(DB::table('messages')->find($id)->receiver)->toBe('Torbj&ouml;rn Holmqvist');
});

it('gives the observer the messages sent to and by their old name', function () {
    $received = oldMessage('bob', 'Torbj&ouml;rn Holmqvist');
    $garbled = oldMessage('bob', 'TorbjÃ¶rn Holmqvist');
    $sent = oldMessage('Torbj&ouml;rn Holmqvist', 'bob');

    $this->artisan('messages:repair-usernames --apply')->assertSuccessful();

    expect(DB::table('messages')->find($received)->receiver)->toBe('Torbjörn Holmqvist')
        ->and(DB::table('messages')->find($garbled)->receiver)->toBe('Torbjörn Holmqvist')
        ->and(DB::table('messages')->find($sent)->sender)->toBe('Torbjörn Holmqvist');

    $this->actingAs($this->torbjorn)->get(route('messages.show', $received))->assertOk();
});

it('moves the read and deleted marks, so old broadcasts are not unread again', function () {
    $read = oldMessage('DeepskyLog', 'all');
    $deleted = oldMessage('DeepskyLog', 'all');
    $both = oldMessage('DeepskyLog', 'all');
    DB::table('messages_read')->insert(['id' => $read, 'receiver' => 'Torbj&ouml;rn Holmqvist', 'read_at' => '2015-03-21 10:00:00']);
    DB::table('messages_deleted')->insert(['id' => $deleted, 'receiver' => 'Torbj&ouml;rn Holmqvist', 'deleted_at' => '2015-03-21 10:00:00']);
    // Already marked under both names: the account keeps its own mark.
    DB::table('messages_read')->insert(['id' => $both, 'receiver' => 'Torbj&ouml;rn Holmqvist', 'read_at' => '2015-03-21 10:00:00']);
    DB::table('messages_read')->insert(['id' => $both, 'receiver' => 'Torbjörn Holmqvist', 'read_at' => '2024-01-01 10:00:00']);

    expect(Message::getNumberOfUnreadMails('Torbjörn Holmqvist'))->toBe(2);

    $this->artisan('messages:repair-usernames --apply')->assertSuccessful();

    expect(Message::getNumberOfUnreadMails('Torbjörn Holmqvist'))->toBe(0)
        ->and(DB::table('messages_read')->where('receiver', 'Torbj&ouml;rn Holmqvist')->exists())->toBeFalse()
        ->and(DB::table('messages_deleted')->where('receiver', 'Torbjörn Holmqvist')->value('deleted_at'))->toBe('2015-03-21 10:00:00')
        ->and(DB::table('messages_read')->where('id', $both)->where('receiver', 'Torbjörn Holmqvist')->value('read_at'))->toBe('2024-01-01 10:00:00');
});

it('leaves the names of deleted accounts alone, so a new observer does not get their messages', function () {
    $id = oldMessage('bob', 'Ren&eacute;');

    $this->artisan('messages:repair-usernames --apply')
        ->expectsOutputToContain('Names without an account, left as they are: 1')
        ->assertSuccessful();

    expect(DB::table('messages')->find($id)->receiver)->toBe('Ren&eacute;');
});

it('is safe to run twice', function () {
    oldMessage('bob', 'Torbj&ouml;rn Holmqvist');

    $this->artisan('messages:repair-usernames --apply')->assertSuccessful();
    $this->artisan('messages:repair-usernames --apply')
        ->expectsOutputToContain('Names renamed to the username of their account: 0')
        ->assertSuccessful();
});

it('shows the decoded name of a sender without an account', function () {
    $id = oldMessage('J&ouml;rg Meyer', 'bob');

    $this->actingAs($this->bob)->get(route('messages.index'))->assertSee('Jörg Meyer')->assertDontSee('J&amp;ouml;rg', false);
    $this->actingAs($this->bob)->get(route('messages.show', $id))->assertSee('Jörg Meyer')->assertDontSee('J&amp;ouml;rg', false);
});
