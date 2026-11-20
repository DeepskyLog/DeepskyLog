<?php

use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

// The old data left observers with a standard telescope or location whose row
// no longer exists, which broke their observer page.

it('shows the observer page of an observer whose standard telescope and location no longer exist', function () {
    $observer = User::factory()->create();
    DB::table('users')->where('id', $observer->id)->update(['stdtelescope' => 999999, 'stdlocation' => 999998]);

    $this->get(route('observer.show', ['observer' => $observer->slug]))->assertOk();
});

it('clears the standard equipment that no longer exists', function () {
    $observer = User::factory()->create();
    $telescope = InstrumentFactory::new()->create(['user_id' => $observer->id, 'observer' => $observer->username]);
    DB::table('users')->where('id', $observer->id)->update(['stdtelescope' => $telescope->id, 'stdlocation' => 999998]);
    $other = User::factory()->create();
    DB::table('users')->where('id', $other->id)->update(['stdtelescope' => 999999]);

    (require database_path('migrations/2026_11_20_000000_clear_dangling_standard_equipment_of_users.php'))->up();

    expect(DB::table('users')->where('id', $observer->id)->value('stdtelescope'))->toBe($telescope->id)
        ->and(DB::table('users')->where('id', $observer->id)->value('stdlocation'))->toBeNull()
        ->and(DB::table('users')->where('id', $other->id)->value('stdtelescope'))->toBeNull();
});
