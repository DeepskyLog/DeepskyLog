<?php

use App\Models\Message;
use App\Models\User;
use App\Support\Html;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

const XSS_PAYLOAD = '<p style="text-align: center; background: url(x)"><strong>Bold</strong> <em>italic</em> <u>under</u></p>'
    .'<ul><li style="padding-left: 40px;">item</li></ul>'
    .'<img src="x" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">link</a>'
    .'<span onmouseover="alert(4)">hover</span>';

it('keeps the editor formatting and removes scripts, event handlers and javascript links', function () {
    $html = Html::purify(XSS_PAYLOAD);

    expect($html)
        ->toContain('<strong>Bold</strong>', '<em>italic</em>', '<u>under</u>', 'text-align:center;', 'padding-left:40px;', '<ul><li')
        ->not->toContain('onerror', 'onmouseover', '<script', 'javascript:', 'background');
});

it('decodes the entity-encoded markup of old descriptions before purifying', function () {
    $html = Html::purify('Line&lt;br /&gt;next &eacute; &lt;img src=x onerror=alert(1)&gt;', true);

    expect($html)->toContain('Line<br />next é')->not->toContain('onerror');
});

it('purifies rich-text attributes when they are saved', function () {
    $user = User::factory()->create(['about' => XSS_PAYLOAD]);

    expect($user->fresh()->about)->toContain('<strong>Bold</strong>')->not->toContain('onerror', '<script');
});

it('shows the formatted profile text without the injected script', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    // Written with SQL, as old rows are, so only the output purification applies.
    DB::table('users')->where('id', $user->id)->update(['about' => XSS_PAYLOAD]);

    $this->get(route('observer.show', $user->slug))
        ->assertOk()
        ->assertSee('<strong>Bold</strong>', false)
        ->assertDontSee('onerror="alert(1)"', false)
        ->assertDontSee('<script>alert(2)</script>', false)
        ->assertDontSee('onmouseover', false);
});

it('shows the formatted instrument description without the injected script', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    DB::table('instruments')->where('id', $instrument->id)->update(['description' => htmlentities(XSS_PAYLOAD)]);

    $this->actingAs($user)->get('/instrument/'.$user->slug.'/'.$instrument->slug)
        ->assertOk()
        ->assertSee('<strong>Bold</strong>', false)
        ->assertDontSee('onerror="alert(1)"', false)
        ->assertDontSee('<script>alert(2)</script>', false);
});

it('escapes a custom copyright text', function () {
    $user = User::factory()->make(['copyright' => '&copy; Me <img src=x onerror=alert(1)>']);

    expect($user->getCopyright())->toBe('© Me &lt;img src=x onerror=alert(1)&gt;');
});

it('returns the message subject as plain text', function () {
    $message = new Message(['subject' => 'Clear &amp; dark &lt;img src=x onerror=alert(1)&gt; <b>skies</b>']);

    expect($message->safe_subject)->toBe('Clear & dark <img src=x onerror=alert(1)> skies');
    // Printed with {{ }}, so the decoded "<img" is escaped again.
    expect(e($message->safe_subject))->not->toContain('<img');
});
