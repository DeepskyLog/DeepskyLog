<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Points the sketches of the week and month at the new observations.id.
 *
 * The old site stored the id of the old deep-sky observation, or minus the id
 * of the old comet observation. LegacyObservationsSeeder numbers the
 * observations again, so nearly every old id is also the new id of another
 * observation: whether a row is translated can't be read from the id alone.
 * The sketch's user_id is the observer of the drawing, so each row gets the
 * candidate that is a drawing of that observer:
 *
 * - the stored id as it is (already translated);
 * - the observation with that old deep-sky id, or for a negative id that old
 *   comet id (not translated yet);
 * - the observation whose new id is the old id of the stored one: the earlier
 *   fix migration (2026_04_28_122058) took correct comet ids for old deep-sky
 *   ids, as it only looked for drawings in images/drawings.
 *
 * When several candidates are drawings of the observer, the table decides:
 * if most of its unambiguous rows needed the old id, it isn't translated yet
 * and the old id wins, else the stored id; the last kind comes last, as that
 * migration only moved the links whose drawing it could not find.
 * A row without any candidate is left as it is and listed. Running it again
 * changes nothing.
 */
class BackfillSketchObservationIdsSeeder extends Seeder
{
    public function run(): void
    {
        $usernames = DB::table('users')->pluck('username', 'id')->all();

        foreach (['sketch_of_the_week', 'sketch_of_the_month'] as $table) {
            $this->backfillTable($table, $usernames);
        }
    }

    /**
     * @param  array<int, string>  $usernames
     */
    private function backfillTable(string $table, array $usernames): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id', 'observation_id', 'user_id']);

        // Per row: the candidates that are a drawing of its observer, keyed by kind.
        $matches = [];
        foreach ($rows as $row) {
            $username = $usernames[$row->user_id] ?? null;
            $matches[$row->id] = $username === null
                ? []
                : array_filter(
                    $this->candidates((int) $row->observation_id),
                    fn ($observation) => $observation !== null
                        && $observation->has_drawing
                        && $this->sameName($observation->observer_username, $username)
                );
        }

        $unambiguous = array_filter($matches, fn ($m) => count(array_unique(array_map(fn ($o) => $o->id, $m))) === 1);
        $legacy = count(array_filter($unambiguous, fn ($m) => isset($m['legacy']) && ! isset($m['current'])));
        $current = count(array_filter($unambiguous, fn ($m) => isset($m['current'])));
        $untranslated = $legacy > $current;

        $updated = 0;
        $unchanged = 0;
        $unresolved = [];
        foreach ($rows as $row) {
            $match = $matches[$row->id];
            if ($match === []) {
                $unresolved[] = $row->id;

                continue;
            }

            $order = $untranslated ? ['legacy', 'current', 'undo'] : ['current', 'legacy', 'undo'];
            $kind = current(array_filter($order, fn ($kind) => isset($match[$kind])));
            $newId = $match[$kind]->id;

            if ((int) $newId === (int) $row->observation_id) {
                $unchanged++;

                continue;
            }

            DB::table($table)->where('id', $row->id)->update(['observation_id' => $newId]);
            $updated++;
        }

        $this->command?->info(sprintf(
            '%s: %d rows updated, %d already right, %d not resolved%s.',
            $table,
            $updated,
            $unchanged,
            count($unresolved),
            $unresolved === [] ? '' : ' (ids ' . implode(', ', $unresolved) . ')'
        ));
    }

    /**
     * @return array<string, object|null>
     */
    private function candidates(int $storedId): array
    {
        $columns = ['id', 'legacy_id', 'observer_username', 'has_drawing'];

        if ($storedId < 0) {
            return [
                'legacy' => DB::table('observations')
                    ->where('legacy_source', 'comet')
                    ->where('legacy_id', -$storedId)
                    ->first($columns),
            ];
        }

        $current = DB::table('observations')->where('id', $storedId)->first($columns);

        return [
            'current' => $current,
            'legacy'  => DB::table('observations')
                ->where('legacy_source', 'deepsky')
                ->where('legacy_id', $storedId)
                ->first($columns),
            'undo'    => $current?->legacy_id
                ? DB::table('observations')->where('id', (int) $current->legacy_id)->first($columns)
                : null,
        ];
    }

    private function sameName(?string $a, string $b): bool
    {
        return mb_strtolower(html_entity_decode((string) $a)) === mb_strtolower(html_entity_decode($b));
    }
}
