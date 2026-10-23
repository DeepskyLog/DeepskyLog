<?php

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A list as the import of April 2026 left it: the given objects in order.
 */
function importedList(User $owner, string $name, array $objects, string $createdAt = '2026-04-21 13:12:25'): ObservingList
{
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => true]);
    foreach (array_values($objects) as $i => $object) {
        ObservingListItem::create([
            'observing_list_id' => $list->id, 'object_name' => $object, 'added_by_user_id' => $owner->id, 'sort_order' => $i + 1,
        ]);
    }
    DB::table('observing_list_items')->where('observing_list_id', $list->id)->update(['created_at' => $createdAt]);
    DB::table('observing_lists')->where('id', $list->id)->update(['created_at' => $createdAt]);

    return $list;
}

function listObjects(ObservingList $list): array
{
    return ObservingListItem::where('observing_list_id', $list->id)->orderBy('sort_order')->pluck('object_name')->all();
}

it('changes nothing without --apply', function () {
    $apus = importedList(User::factory()->create(), 'NSOG Apus', ['M 1', 'IC 4377']);

    $this->artisan('observing-lists:remove-spurious-items')
        ->expectsOutputToContain('Spurious items: 1')
        ->assertSuccessful();

    expect(listObjects($apus))->toBe(['M 1', 'IC 4377']);
});

it('removes the imported M 1 in the first place and deletes the lists it leaves empty', function () {
    $owner = User::factory()->create();
    $apus = importedList($owner, 'NSOG Apus', ['M 1', 'IC 4377']);
    $unnamed = importedList($owner, 'Unnamed', ['M 1']);
    DB::table('observing_lists')->where('id', $unnamed->id)->update(['name' => '']);
    $liked = importedList($owner, 'Liked', ['M 1']);
    DB::table('observing_lists')->where('id', $liked->id)->update(['likes_count' => 1]);
    $owner->forceFill(['active_observing_list_id' => $unnamed->id])->save();

    $this->artisan('observing-lists:remove-spurious-items --apply')->assertSuccessful();

    expect(listObjects($apus))->toBe(['IC 4377'])
        ->and(ObservingList::find($unnamed->id))->toBeNull()
        ->and($owner->fresh()->active_observing_list_id)->toBeNull()
        ->and(ObservingList::find($liked->id))->not->toBeNull()
        ->and(listObjects($liked))->toBe([]);

    $this->artisan('observing-lists:remove-spurious-items --apply')
        ->expectsOutputToContain('Spurious items: 0')
        ->assertSuccessful();
});

it('keeps M 1 where it belongs, later in a list, or added after the import', function () {
    $owner = User::factory()->create();
    $messier = importedList($owner, 'Messier marathon', ['M 1', 'M 2']);
    $crab = importedList($owner, 'Crab and friends', ['M 1', 'NGC 1952']);
    $winter = importedList($owner, 'Winter', ['M 42', 'M 1']);
    $new = importedList($owner, 'Tonight', ['M 1', 'M 42'], '2026-10-01 20:00:00');

    $this->artisan('observing-lists:remove-spurious-items --apply')
        ->expectsOutputToContain('Kept for checking: 2')
        ->assertSuccessful();

    expect(listObjects($messier))->toBe(['M 1', 'M 2'])
        ->and(listObjects($crab))->toBe(['M 1', 'NGC 1952'])
        ->and(listObjects($winter))->toBe(['M 42', 'M 1'])
        ->and(listObjects($new))->toBe(['M 1', 'M 42']);
});
