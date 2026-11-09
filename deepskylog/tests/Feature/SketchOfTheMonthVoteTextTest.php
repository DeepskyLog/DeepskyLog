<?php

use App\Models\Observation;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\SketchShareText;
use Carbon\Carbon;

function voteSketch(User $observer, string $object, string $week): SketchOfTheWeek
{
    $observation = Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-09-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'description'       => 'Seen',
        'has_drawing'       => true,
        'drawing_filename'  => 'sketches/'.$object.'.jpg',
    ]);

    return SketchOfTheWeek::forceCreate(['observation_id' => $observation->id, 'date' => $week, 'user_id' => $observer->id]);
}

it('writes the vote posts for Astroforum and the social media with the candidates and the dates', function () {
    $vedran = User::factory()->create(['name' => 'Vedran Vrhovac']);
    $tom    = User::factory()->create(['name' => 'Tom Corstjens']);
    $candidates = collect([voteSketch($vedran, 'NGC 7009', '2026-10-04'), voteSketch($tom, 'IC 5271', '2026-10-11')]);
    $date = Carbon::parse('2026-11-08');

    expect(SketchShareText::voteAstroforum($date, $candidates))
        ->toStartWith('## Verkiezing DeepskyLog schets van de maand (oktober 2026)')
        ->toContain("De kanshebbers deze maand zijn:\n\n- NGC 7009 door Vedran Vrhovac\n- IC 5271 door Tom Corstjens\n\nStemmen kan tot 7 november.")
        ->and(SketchShareText::voteSocial($date, $candidates))
        ->toStartWith('Dear observers,')
        ->toContain("- NGC 7009 by Vedran Vrhovac\n- IC 5271 by Tom Corstjens\n\nPlease vote (by leaving a comment) before November 7.");
});

it('shows the vote texts on the choose page and the Astroforum announcement after choosing', function () {
    $this->travelTo(Carbon::parse('2026-11-02 12:00'));
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $jef   = User::factory()->create(['name' => 'Jef De Wit']);
    $sketch = voteSketch($jef, 'IC 5217', '2026-10-04');

    $this->actingAs($admin)->get(route('sketch-of-the-month.create'))
        ->assertOk()
        ->assertSee('Verkiezing DeepskyLog schets van de maand (oktober 2026)')
        ->assertSee('IC 5217 by Jef De Wit');

    $this->actingAs($admin)
        ->post(route('sketch-of-the-month.store'), ['observation_id' => $sketch->observation_id, 'date' => '2026-11-08'])
        ->assertOk()
        ->assertSee('De DeepskyLog schets van oktober 2026 is de schets geworden van IC 5217 door Jef De Wit. Proficiat Jef!')
        ->assertSee('[URL='.Observation::find($sketch->observation_id)->detailUrl().']DeepskyLog[/URL]', false);

    expect(SketchOfTheMonth::where('observation_id', $sketch->observation_id)->exists())->toBeTrue();
});
