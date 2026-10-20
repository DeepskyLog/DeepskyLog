<?php

use App\Support\Timezones;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The OAL import stored the <timezone> of a new site, an offset in minutes
     * from UT ("60"), as the location's time zone. Carbon reads that as an
     * offset in hours, so the OAL export of its observations was days off and
     * failed the schema. Such a location gets the zone with that offset nearest
     * to it (see App\Support\Timezones::fromOffset()).
     */
    public function up(): void
    {
        DB::table('locations')
            ->whereNotNull('timezone')
            ->orderBy('id')
            ->get(['id', 'timezone', 'latitude', 'longitude'])
            ->filter(fn ($row) => preg_match('/^[+-]?\d+$/', trim((string) $row->timezone)) === 1)
            ->each(fn ($row) => DB::table('locations')->where('id', $row->id)->update([
                'timezone' => Timezones::fromOffset(
                    (int) trim((string) $row->timezone),
                    $row->latitude === null ? null : (float) $row->latitude,
                    $row->longitude === null ? null : (float) $row->longitude,
                ),
            ]));
    }

    /**
     * The offsets are not written back: they were never valid time zones.
     */
    public function down(): void
    {
    }
};
