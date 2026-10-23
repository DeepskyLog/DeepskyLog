<?php

it('links the register and privacy policy texts in the guest welcome message', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee("<a href='" . route('register') . "'>register</a> to DeepskyLog", false);
    $response->assertSee("in accordance with our <a href='" . route('privacy') . "'>privacy policy</a>.", false);
});

it('links the translated texts in the guest welcome message', function () {
    app()->setLocale('nl');

    $text = __('To start recording your observations and share your observations with other observers, you are kindly requested to :register_linkregister:close_link to DeepskyLog. Your personal data will be handled in accordance with our :privacy_linkprivacy policy:close_link.', [
        'register_link' => '<a href="/register">',
        'privacy_link' => '<a href="/privacy">',
        'close_link' => '</a>',
    ]);

    expect($text)
        ->toContain('<a href="/register">registreren</a>')
        ->toContain('<a href="/privacy">privacybeleid</a>');
});

it('shows a like button with the like count on the sessions of the dashboard', function () {
    $owner = \App\Models\User::factory()->create();
    $viewer = \App\Models\User::factory()->create();
    $other = \App\Models\User::factory()->create();
    $session = \App\Models\ObservationSession::create(['name' => 'Dark night', 'observerid' => $owner->username,
        'begindate' => '2026-09-01 20:00:00', 'enddate' => '2026-09-02 02:00:00', 'locationid' => 0, 'active' => 1]);

    foreach ([$viewer, $other] as $user) {
        \App\Models\ObservationLike::create(['user_id' => $user->id, 'observation_type' => \App\Models\ObservationLike::TYPE_SESSION, 'observation_id' => $session->id]);
    }


    $this->actingAs($viewer)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-observation-id="' . $session->id . '"', false)
        ->assertSeeInOrder(['data-observation-type="session"', '❤️', '<span class="like-count">2</span>'], false);

    $this->actingAs($owner)->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['data-observation-type="session"', '👍', '<span class="like-count">2</span>'], false);

    $this->actingAs($viewer)
        ->postJson(route('observation.like'), ['observation_type' => 'session', 'observation_id' => $session->id])
        ->assertOk()
        ->assertJson(['status' => 'unliked', 'count' => 1]);
});

it('shows a like button with the like count on the all sessions page', function () {
    $owner = \App\Models\User::factory()->create();
    $viewer = \App\Models\User::factory()->create();
    $other = \App\Models\User::factory()->create();
    $session = \App\Models\ObservationSession::create(['name' => 'Dark night', 'observerid' => $owner->username,
        'begindate' => '2026-09-01 20:00:00', 'enddate' => '2026-09-02 02:00:00', 'locationid' => 0, 'active' => 1]);

    foreach ([$viewer, $other] as $user) {
        \App\Models\ObservationLike::create(['user_id' => $user->id, 'observation_type' => \App\Models\ObservationLike::TYPE_SESSION, 'observation_id' => $session->id]);
    }

    $this->actingAs($viewer)->get(route('session.all'))
        ->assertOk()
        ->assertSee('data-observation-id="' . $session->id . '"', false)
        ->assertSeeInOrder(['data-observation-type="session"', '❤️', '<span class="like-count">2</span>'], false);

    $this->actingAs($owner)->get(route('session.all'))
        ->assertOk()
        ->assertSeeInOrder(['data-observation-type="session"', '👍', '<span class="like-count">2</span>'], false);
});

it('shows the newest public observing lists with objects', function () {
    $owner = \App\Models\User::factory()->create(['name' => 'List Maker']);
    $make = function (string $name, bool $public = true, bool $withObject = true, string $created = '2026-10-01') use ($owner) {
        $list = \App\Models\ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => $public]);
        $list->forceFill(['created_at' => $created])->save();
        if ($withObject) {
            $list->items()->create(['object_name' => 'M 31', 'added_by_user_id' => $owner->id]);
        }

        return $list;
    };
    foreach (range(1, 6) as $day) {
        $make("Old list {$day}", created: "2026-09-0{$day}");
    }
    $make('Newest list', created: '2026-10-20');
    $make('Private list', public: false, created: '2026-10-21');
    $make('Empty list', withObject: false, created: '2026-10-22');

    $this->get('/')
        ->assertOk()
        ->assertSee('Newest observing lists')
        ->assertSeeInOrder(['Newest list', 'Old list 6', 'Old list 2'])
        ->assertSee('List Maker')
        ->assertSee(route('observing-lists.discover'))
        // Six lists: the oldest drops out
        ->assertDontSee('Old list 1')
        ->assertDontSee('Private list')
        ->assertDontSee('Empty list');
});

it('shows no observing lists section without public lists', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('Newest observing lists');
});
