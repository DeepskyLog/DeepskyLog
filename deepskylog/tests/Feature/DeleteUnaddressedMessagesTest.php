<?php

use Illuminate\Support\Facades\DB;

function storedMessage(?string $receiver): int
{
    return (int) DB::table('messages')->insertGetId([
        'sender' => 'DeepskyLog',
        'receiver' => $receiver,
        'subject' => 'Nieuwe DeepskyLog ster!',
        'message' => 'Proficiat',
        'date' => '2016-04-16 11:31:04',
    ]);
}

it('changes nothing without --apply', function () {
    storedMessage('');

    $this->artisan('messages:delete-unaddressed')
        ->expectsOutputToContain('Messages without a receiver: 1')
        ->assertSuccessful();

    expect(DB::table('messages')->count())->toBe(1);
});

it('deletes the messages without a receiver and their marks, and keeps the others', function () {
    $empty = storedMessage('');
    $personal = storedMessage('bob');
    $broadcast = storedMessage('all');
    DB::table('messages_deleted')->insert([
        ['id' => $empty, 'receiver' => 'bob', 'deleted_at' => now()],
        ['id' => $broadcast, 'receiver' => 'bob', 'deleted_at' => now()],
    ]);

    $this->artisan('messages:delete-unaddressed --apply')->assertSuccessful();

    expect(DB::table('messages')->pluck('id')->all())->toEqualCanonicalizing([$personal, $broadcast])
        ->and(DB::table('messages_deleted')->pluck('id')->all())->toBe([$broadcast]);

    $this->artisan('messages:delete-unaddressed --apply')
        ->expectsOutputToContain('Messages without a receiver: 0')
        ->assertSuccessful();
});
