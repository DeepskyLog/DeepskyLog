<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Copies the settings of the old site's observers that users has no column
 * for into user_preferences, where the view legacy_observers reads them:
 * UT (dates in UT or local time), usedLanguages (the languages of the
 * observations shown) and club. The last read observation of the old site is
 * an old observation id, which means nothing in this database: everyone
 * starts with all current observations read.
 *
 * Safe to run more than once: existing values are kept, so a setting changed
 * on the old site since is not overwritten.
 */
class LegacyObserverSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id', 'username')->all();
        $lastObservation = (string) (DB::table('observations')->max('id') ?? 0);
        $now = now();
        $rows = [];

        foreach (DB::connection('mysqlOld')->table('observers')->get(['id', 'UT', 'usedLanguages', 'club']) as $observer) {
            $userId = $userIds[html_entity_decode((string) $observer->id, ENT_QUOTES | ENT_HTML5, 'UTF-8')] ?? null;
            if ($userId === null) {
                continue;
            }

            $values = [
                'legacy_ut' => (string) (int) $observer->UT,
                'legacy_used_languages' => (string) $observer->usedLanguages,
                'legacy_club' => trim((string) $observer->club),
            ];
            foreach ($values as $key => $value) {
                if ($value !== '') {
                    $rows[] = ['user_id' => $userId, 'key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now];
                }
            }
        }

        foreach ($userIds as $userId) {
            $rows[] = ['user_id' => $userId, 'key' => 'legacy_last_read_observation', 'value' => $lastObservation, 'created_at' => $now, 'updated_at' => $now];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('user_preferences')->insertOrIgnore($chunk);
        }

        $this->command?->info('Settings copied: '.count($rows));
    }
}
