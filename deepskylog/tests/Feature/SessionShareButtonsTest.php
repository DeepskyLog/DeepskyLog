<?php

use App\Models\ObservationSession;
use App\Models\User;

it('shares a session with decoded text, one button per service and no empty share block', function () {
    $owner = User::factory()->create([
        'name' => 'Wim De Meester',
        'username' => 'wim',
        'slug' => 'wim-de-meester',
    ]);

    $session = ObservationSession::query()->create([
        'id' => 24002,
        'name' => 'M31 &amp; friends',
        'slug' => 'm31-friends',
        'observerid' => $owner->username,
        'begindate' => '2024-01-12 19:30:00',
        'enddate' => '2024-01-13 00:45:00',
        'locationid' => 0,
        'weather' => '',
        'equipment' => '',
        'comments' => '',
        'language' => 'en',
        'active' => 1,
    ]);

    $html = $this->get(route('session.show', [$owner->slug, $session->slug]))
        ->assertOk()
        ->getContent();

    $url = route('session.show', [$owner->slug, $session->slug]);
    $text = 'Look at this session M31 & friends by Wim De Meester';
    $tags = '#astronomy #deepsky #deepskylog';

    expect($html)
        ->not->toContain('<div id="social-buttons"></div>')
        ->and(substr_count($html, 'id="social-buttons"'))->toBe(1)
        ->and($html)->toContain('<meta property="og:url" content="' . $url . '" />')
        ->toContain('https://twitter.com/intent/tweet?text=' . e(rawurlencode($text . "\n\n" . $url . "\n\n" . $tags)))
        ->toContain('https://www.facebook.com/sharer/sharer.php?u=' . e(rawurlencode($url)) . '"')
        ->toContain('https://wa.me/?text=' . e(rawurlencode($text . "\n\n" . $url . "\n\n" . $tags)))
        ->toContain('mailto:?subject=' . e(rawurlencode($text)) . '&amp;body=' . e(rawurlencode($text . "\n\n" . $url)))
        ->toContain('data-share-text="' . e($text . "\n\n" . $tags) . '"')
        ->toContain('title="Share on X"')
        ->toContain('title="Share on Facebook"')
        ->toContain('title="Share on WhatsApp"')
        ->toContain('title="Share by email"')
        ->toContain('class="social-button native-share hover"')
        ->not->toContain('%26amp%3B');
});
