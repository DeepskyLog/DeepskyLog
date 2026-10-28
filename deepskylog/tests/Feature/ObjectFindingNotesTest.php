<?php

use App\Actions\Jetstream\DeleteUser;
use App\Livewire\ObjectFindingNotes;
use App\Models\ObjectFindingNote;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObjectMerger;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Finding notes of observers per deep-sky object (ObjectFindingNote): on the
// object page (Livewire ObjectFindingNotes) and at the eyepiece.

function fnObject(string $name, float $ra = 5.588, float $decl = -5.39): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'EMINB', 'con' => 'ORI',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 4.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function fnNote(User $user, string $text, string $object = 'NGC 2024', int $likes = 0): ObjectFindingNote
{
    $note = ObjectFindingNote::create(['object_name' => $object, 'user_id' => $user->id, 'text' => $text]);
    $note->forceFill(['likes_count' => $likes])->save();

    return $note;
}

beforeEach(fn () => fnObject('NGC 2024'));

it('shows guests the notes, the most liked first, without the form', function () {
    $author = User::factory()->create(['name' => 'Tip Writer']);
    fnNote($author, 'Put Alnitak out of the field', likes: 1);
    fnNote($author, 'Hop from zeta Ori', likes: 4);

    Livewire::test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->assertSeeInOrder(['Hop from zeta Ori', 'Put Alnitak out of the field'])
        ->assertSee('Tip Writer')
        ->assertDontSee('Add finding note')
        ->assertSee('Log in to add a finding note or like one.');
});

it('lets an observer add a note as plain text on one line', function () {
    $me = User::factory()->create();

    Livewire::actingAs($me)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->set('text', "  Use the <b>UHC</b>\n filter  ")
        ->call('add')
        ->assertHasNoErrors()
        ->assertSet('text', '')
        ->assertSee('Use the UHC filter');

    expect(ObjectFindingNote::where('object_name', 'NGC 2024')->value('text'))->toBe('Use the UHC filter');
});

it('refuses an empty, too long or one note too many', function () {
    $me = User::factory()->create();
    $component = Livewire::actingAs($me)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024']);

    $component->set('text', '   ')->call('add')->assertHasErrors('text');
    $component->set('text', str_repeat('a', ObjectFindingNote::MAX_LENGTH + 1))->call('add')->assertHasErrors('text');

    foreach (range(1, ObjectFindingNote::MAX_PER_OBSERVER) as $i) {
        fnNote($me, "Tip $i");
    }
    expect(fn () => ObjectFindingNote::add($me, 'NGC 2024', 'One more'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
    $component->call('$refresh')->assertDontSee('Add finding note');
});

it('refuses a note for an object that is not there', function () {
    expect(fn () => ObjectFindingNote::add(User::factory()->create(), 'NGC 99999', 'Somewhere'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('lets other observers like a note once, never its author', function () {
    $author = User::factory()->create();
    $me = User::factory()->create();
    $note = fnNote($author, 'Hop from zeta Ori');

    Livewire::actingAs($me)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('toggleLike', $note->id);
    expect($note->fresh()->likes_count)->toBe(1);

    Livewire::actingAs($me)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('toggleLike', $note->id);
    expect($note->fresh()->likes_count)->toBe(0);

    Livewire::actingAs($author)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('toggleLike', $note->id)
        ->assertForbidden();
});

it('lets the author edit and administrators delete, nobody else', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $author = User::factory()->create();
    $other = User::factory()->create();
    $note = fnNote($author, 'Hop from zeta Ori');

    Livewire::actingAs($other)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('startEdit', $note->id)->assertForbidden();
    Livewire::actingAs($other)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('delete', $note->id)->assertForbidden();

    Livewire::actingAs($author)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('startEdit', $note->id)
        ->set('editText', 'Hop from Alnitak')
        ->call('saveEdit')
        ->assertHasNoErrors()
        ->assertSet('editingId', null);
    expect($note->fresh()->text)->toBe('Hop from Alnitak');

    Livewire::actingAs($admin)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('delete', $note->id);
    expect(ObjectFindingNote::count())->toBe(0);
});

it('does not touch a note of another object', function () {
    fnObject('M 42');
    $me = User::factory()->create();
    $note = fnNote($me, 'Naked eye', 'M 42');

    Livewire::actingAs($me)->test(ObjectFindingNotes::class, ['objectName' => 'NGC 2024'])
        ->call('delete', $note->id)
        ->assertNotFound();
});

it('moves the notes with a merged object and deletes them with a deleted one', function () {
    fnObject('NGC 2024 dup', 5.59, -1.85);
    fnObject('IC 434', 5.68, -2.45);
    $me = User::factory()->create();
    fnNote($me, 'Same nebula', 'NGC 2024 dup');
    fnNote($me, 'Look for the Horsehead', 'IC 434');

    ObjectMerger::merge('NGC 2024 dup', 'NGC 2024');
    ObjectMerger::delete('IC 434');

    expect(ObjectFindingNote::pluck('object_name')->all())->toBe(['NGC 2024']);
});

it('forgets the likes of a deleted account in the counts', function () {
    $author = User::factory()->create();
    $liker = User::factory()->create();
    $note = fnNote($author, 'Hop from zeta Ori');
    $note->toggleLike($liker);
    expect($note->fresh()->likes_count)->toBe(1);

    app(DeleteUser::class)->delete($liker);

    expect($note->fresh()->likes_count)->toBe(0);
});

it('shows the notes at the eyepiece, likes them and adds one', function () {
    Carbon::setTestNow('2026-10-26 21:00:00');
    $team = $this->getTeamFromString('Observers');
    $me = User::factory()->create();
    $me->teams()->attach($team);
    $me->switchTeam($team);
    $location = LocationFactory::new()->create(['user_id' => $me->id, 'observer' => $me->username, 'name' => 'Leuven',
        'latitude' => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'active' => true]);
    $me->forceFill(['stdlocation' => $location->id])->save();
    $list = ObservingList::createWithObjects($me, 'Orion', ['NGC 2024']);

    $author = User::factory()->create();
    $note = fnNote($author, 'Put Alnitak out of the field');

    $this->actingAs($me)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertSee('Finding notes')
        ->assertSee('Put Alnitak out of the field')
        ->assertSee(route('finding-notes.like', $note));

    $this->actingAs($me)->postJson(route('finding-notes.like', $note))
        ->assertOk()
        ->assertJson(['liked' => true, 'count' => 1]);
    $this->actingAs($author)->postJson(route('finding-notes.like', $note))->assertForbidden();

    $page = route('observing-list.eyepiece', $list);
    $this->actingAs($me)->from($page)
        ->post(route('finding-notes.store'), ['object_name' => 'NGC 2024', 'text' => 'Use the H-beta filter'])
        ->assertRedirect($page);
    expect(ObjectFindingNote::where('user_id', $me->id)->value('text'))->toBe('Use the H-beta filter');

    Carbon::setTestNow();
});
