<?php

use App\Jobs\GenerateAtlasPdf;
use App\Jobs\GenerateObjectSetsPdf;
use App\Models\User;
use App\Support\Exports\ExportToCsv;
use App\Support\SpreadsheetCell;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Column;

it('escapes cells a spreadsheet would read as a formula', function () {
    expect(SpreadsheetCell::escape('=HYPERLINK("http://evil","x")'))->toBe('\'=HYPERLINK("http://evil","x")')
        ->and(SpreadsheetCell::escape('+1+2'))->toBe("'+1+2")
        ->and(SpreadsheetCell::escape('-cmd'))->toBe("'-cmd")
        ->and(SpreadsheetCell::escape('@SUM(A1)'))->toBe("'@SUM(A1)")
        ->and(SpreadsheetCell::escape("\tx"))->toBe("'\tx")
        ->and(SpreadsheetCell::escape("\rx"))->toBe("'\rx")
        ->and(SpreadsheetCell::escape('-12.5'))->toBe('-12.5')
        ->and(SpreadsheetCell::escape(-3))->toBe(-3)
        ->and(SpreadsheetCell::escape('M 31'))->toBe('M 31')
        ->and(SpreadsheetCell::escape(''))->toBe('')
        ->and(SpreadsheetCell::unescape("'=1+1"))->toBe('=1+1')
        ->and(SpreadsheetCell::unescape("'quoted"))->toBe("'quoted");
});

it('escapes formulas in the powergrid exports', function () {
    $export = (new ExportToCsv())->setData(
        [Column::make('Name', 'name'), Column::make('Note', 'note')],
        collect([(object) ['name' => '=1+1', 'note' => 'fine']])
    );

    $prepared = $export->prepare($export->data, $export->columns, false);

    expect($prepared['rows'][0])->toBe(['Name' => "'=1+1", 'Note' => 'fine']);
});

it('sends security headers', function () {
    $response = $this->get('/privacy');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    expect($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")
        ->and($response->headers->has('Strict-Transport-Security'))->toBeFalse();

    $this->get('https://localhost/privacy')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('requires a verified address to send a message', function () {
    $user = User::factory()->unverified()->create();
    $bob = User::factory()->create();

    $this->actingAs($user)
        ->post(route('messages.store'), ['receiver' => $bob->username, 'message' => 'Hello'])
        ->assertRedirect(route('verification.notice'));
});

it('limits how many messages an observer sends', function () {
    $user = User::factory()->create();
    $bob = User::factory()->create();

    foreach (range(1, 5) as $_) {
        $this->actingAs($user)->post(route('messages.store'), ['receiver' => $bob->username, 'message' => 'Hello'])->assertRedirect();
    }

    $this->actingAs($user)->post(route('messages.store'), ['receiver' => $bob->username, 'message' => 'Hello'])->assertStatus(429);
});

it('limits registrations per address', function () {
    foreach (range(1, 3) as $_) {
        $this->post(route('register.store'), [])->assertStatus(302);
    }

    $this->post(route('register.store'), [])->assertStatus(429);
});

it('only renders a single atlas page in the request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/downloads/dsl-atlas')
        ->post(route('downloads.dsl-atlas.generate'), ['scope' => 'whole'])
        ->assertRedirect('/downloads/dsl-atlas')
        ->assertSessionHas('error');
});

it('queues an atlas with only the atlas parameters, at most two at a time', function () {
    Queue::fake();
    $user = User::factory()->create();

    foreach (range(1, 2) as $_) {
        $this->actingAs($user)
            ->postJson(route('downloads.dsl-atlas.queue'), ['scope' => 'whole', 'type' => 'overview', 'evil' => str_repeat('x', 100)])
            ->assertStatus(202);
    }

    $this->actingAs($user)
        ->postJson(route('downloads.dsl-atlas.queue'), ['scope' => 'whole'])
        ->assertStatus(429)
        ->assertJsonStructure(['message']);

    Queue::assertPushed(GenerateAtlasPdf::class, 2);
    Queue::assertPushed(GenerateAtlasPdf::class, fn (GenerateAtlasPdf $job) => $job->parameters === ['type' => 'overview', 'scope' => 'whole']);
    expect(GenerateAtlasPdf::openCount($user->id))->toBe(2);

    $this->actingAs($user)->postJson(route('downloads.dsl-atlas.queue'), ['scope' => 'everything'])->assertStatus(422);
});

it('limits how many pdfs an observer asks for in an hour', function () {
    $user = User::factory()->create();

    foreach (range(1, GenerateAtlasPdf::MAX_PER_HOUR) as $_) {
        GenerateObjectSetsPdf::recordQueued($user->id);
    }

    expect(GenerateAtlasPdf::refusal($user->id))->toContain('too many PDFs or zip files');
});

it('skips a pdf that was forgotten for a newer one while it waited', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $forgotten = (string) Str::uuid();
    Cache::put(GenerateObjectSetsPdf::recentKey($user->id), [(string) Str::uuid()], now()->addHour());

    (new GenerateObjectSetsPdf($forgotten, $user->id, ['objects' => [['name' => 'M 31', 'fovs' => [60.0], 'dsos' => [], 'stars' => [], 'photos' => []]]]))->handle();

    expect(Cache::has(GenerateObjectSetsPdf::statusKey($forgotten)))->toBeFalse()
        ->and(Storage::disk('local')->exists(GenerateObjectSetsPdf::path($forgotten)))->toBeFalse();
});
