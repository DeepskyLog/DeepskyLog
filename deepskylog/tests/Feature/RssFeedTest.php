<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

test('observations RSS feed returns 200 with RSS content type', function () {
    $this->get(route('feed.observations'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('observations RSS feed contains valid RSS structure', function () {
    $this->get(route('feed.observations'))
        ->assertOk()
        ->assertSee('<rss version="2.0"', false)
        ->assertSee('<channel>', false)
        ->assertSee('Latest Observations', false);
});

test('observations RSS feed includes recent observations', function () {
    $user = User::factory()->create(['username' => 'testobserver']);

    DB::table('observations')->insert([
        'observer_user_id' => $user->id,
        'observer_username' => 'testobserver',
        'target_name' => 'M 31',
        'observed_on' => '2026-01-15',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get(route('feed.observations'))
        ->assertOk()
        ->assertSee('M 31', false);
});

test('observing lists RSS feed returns 200 with RSS content type', function () {
    $this->get(route('feed.observing-lists'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('observing lists RSS feed contains valid RSS structure', function () {
    $this->get(route('feed.observing-lists'))
        ->assertOk()
        ->assertSee('<rss version="2.0"', false)
        ->assertSee('<channel>', false)
        ->assertSee('Observing Lists', false);
});

test('observing lists RSS feed only includes public lists', function () {
    $owner = User::factory()->create();

    DB::table('observing_lists')->insert([
        'owner_user_id' => $owner->id,
        'name' => 'My Public List',
        'slug' => 'my-public-list',
        'public' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('observing_lists')->insert([
        'owner_user_id' => $owner->id,
        'name' => 'My Private List',
        'slug' => 'my-private-list',
        'public' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get(route('feed.observing-lists'))
        ->assertOk()
        ->assertSee('My Public List', false)
        ->assertDontSee('My Private List', false);
});
