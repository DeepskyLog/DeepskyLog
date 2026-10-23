<?php

namespace App\Console\Commands;

use App\Services\DeepskyObjectCreator;
use App\Services\ObjectSearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Add the 463 member galaxies of the Hickson compact groups to the database.
 *
 * The members come from database/hickson_galaxies.csv, made from table
 * "galaxies" of Hickson (1989), VizieR VII/213 (J2000 positions, the total B
 * magnitude BT, the diameters in arcseconds and the cross-identifications in
 * NGC, IC and UGC).
 *
 * A member is the alternative name "Hickson <group><letter>" (Hickson 11A) of
 * a galaxy, linked in objectpartof to its group. For each member that is not
 * there yet:
 *
 * - the galaxy is looked up by its NGC, IC or UGC name, within a minute of
 *   arc of the member's position;
 * - else it is the nearest galaxy within 20", where a galaxy of a better known
 *   catalog (NGC, IC, UGC, MCG, CGCG, PGC) wins from one that is at most 3"
 *   further, as the database has several rows for some galaxies;
 * - else a new galaxy "Hickson <group><letter>" is added.
 *
 * A galaxy that already is another Hickson member, or that two members would
 * share, is not used; those members are only listed, to be checked by hand.
 * The members already in the database get their part-of link when it is
 * missing. Safe to run more than once.
 */
class ImportHicksonMembers extends Command
{
    /** The furthest a galaxy found by name may be from the member's position. */
    private const NAME_MATCH_ARCSEC = 60.0;

    /** The furthest a galaxy found by position may be from the member's position. */
    private const POSITION_MATCH_ARCSEC = 20.0;

    /** A better known galaxy at most this much further wins from the nearest. */
    private const PREFERENCE_ARCSEC = 3.0;

    /** The catalogs a galaxy found by position is preferred from, best first. */
    private const CATALOG_PREFERENCE = ['NGC', 'IC', 'UGC', 'MCG', 'CGCG', 'PGC'];

    private const DATASOURCE = 'Hickson 1989 (VizieR VII/213)';

    private ?Collection $hicksonNames = null;

    protected $signature = 'objects:import-hickson-members
        {--apply : Write the members to the database}';

    protected $description = 'Add the member galaxies of the Hickson compact groups as alternative names, or as new galaxies';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        // The command object is kept between calls, so forget the last run's rows.
        $this->hicksonNames = null;
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the members.');
        }

        $plan = $this->plan($this->members());

        foreach (['matched' => 'Members matched to a galaxy', 'created' => 'Members added as a new galaxy', 'linked' => 'Members that only get their part-of link', 'skipped' => 'Members to check by hand'] as $key => $title) {
            if ($plan[$key] === []) {
                continue;
            }
            $this->line('');
            $this->info($title.' ('.count($plan[$key]).')');
            $this->table(['Member', 'Group', 'Galaxy', 'Note'], array_map(fn ($p) => [$p['alt'], $p['group'] ?? '-', $p['object'] ?? '-', $p['note'] ?? ''], $plan[$key]));
        }

        $this->line('');
        $this->info(sprintf(
            '%d members already in the database, %d matched, %d new, %d part-of links added, %d to check by hand.',
            $plan['present'], count($plan['matched']), count($plan['created']), count($plan['linked']), count($plan['skipped'])
        ));

        if ($apply) {
            $this->apply($plan);
            $this->info('Done. The search index of the changed objects is updated.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function members(): array
    {
        $handle = fopen(database_path('hickson_galaxies.csv'), 'r');
        $header = fgetcsv($handle);
        $members = [];
        while (($row = fgetcsv($handle)) !== false) {
            $members[] = array_combine($header, $row);
        }
        fclose($handle);

        return $members;
    }

    /**
     * @param  list<array<string, mixed>>  $members
     * @return array<string, mixed>
     */
    private function plan(array $members): array
    {
        $plan = ['present' => 0, 'matched' => [], 'created' => [], 'linked' => [], 'skipped' => []];
        $groups = $this->groupObjects();

        // Every galaxy that is a Hickson member already, by member. Some old
        // rows have a lower case letter (Hickson 1b).
        $memberRows = $this->hicksonNames()->filter(fn ($r) => preg_match('/^\d+[A-Za-z]$/', $r->catindex));
        $taken = $memberRows->mapWithKeys(fn ($r) => [strtoupper($r->altname) => $r->objectname])->all();
        $claimed = $memberRows->pluck('altname', 'objectname')->all();

        // A name of the cross-identifications shared by several members (a UGC
        // number of a whole group) does not tell which galaxy a member is.
        $shared = collect($members)
            ->flatMap(fn ($m) => array_unique($this->catalogNames((string) $m['names'])))
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->all();

        foreach ($members as $m) {
            $alt = 'Hickson '.$m['hcg'].$m['member'];
            $group = $groups[(int) $m['hcg']] ?? null;
            $entry = ['alt' => $alt, 'catindex' => $m['hcg'].$m['member'], 'group' => $group, 'member' => $m];

            if (isset($taken[strtoupper($alt)])) {
                $plan['present']++;
                $entry['object'] = $taken[strtoupper($alt)];
                if ($group !== null && ! $this->linked($entry['object'], (int) $m['hcg'], $group)) {
                    $plan['linked'][] = $entry;
                }

                continue;
            }

            if ($group === null) {
                $plan['skipped'][] = $entry + ['note' => 'group not in the database'];

                continue;
            }

            [$object, $note] = $this->findGalaxy($m, $shared);

            if ($object === null) {
                $plan['created'][] = $entry + ['object' => $alt, 'note' => $note];

                continue;
            }

            if (isset($claimed[$object])) {
                $plan['skipped'][] = $entry + ['object' => $object, 'note' => 'already '.$claimed[$object]];

                continue;
            }

            $claimed[$object] = $alt;
            $plan['matched'][] = $entry + ['object' => $object, 'note' => $note];
        }

        return $plan;
    }

    /**
     * The name of the object of each group, by group number. Most groups are
     * the object "Hickson <n>", some are known under another name (Arp 329 is
     * Hickson 55).
     *
     * @return array<int, string>
     */
    private function groupObjects(): array
    {
        $objects = array_flip(DB::table('objects')->whereIn('name', $this->hicksonNames()->pluck('objectname'))->pluck('name')->all());
        $groups = $this->hicksonNames()
            ->filter(fn ($r) => preg_match('/^\d+$/', $r->catindex) && isset($objects[$r->objectname]))
            ->mapWithKeys(fn ($r) => [(int) $r->catindex => $r->objectname])
            ->all();

        foreach (DB::table('objects')->where('name', 'like', 'Hickson %')->pluck('name') as $name) {
            if (preg_match('/^Hickson (\d+)$/', $name, $n) && (int) $n[1] <= 100) {
                $groups[(int) $n[1]] ??= $name;
            }
        }

        return $groups;
    }

    /**
     * The rows of the Hickson catalog in objectnames: the groups ("12") and
     * the members ("12A").
     */
    private function hicksonNames(): Collection
    {
        return $this->hicksonNames ??= DB::table('objectnames')->where('catalog', 'Hickson')
            ->get(['objectname', 'altname', 'catindex']);
    }

    /**
     * Whether a galaxy is linked to its group, under the group's object name
     * or as "Hickson <n>" (some old links use that name for a group known as
     * an Arp object).
     */
    private function linked(string $object, int $hcg, string $group): bool
    {
        return DB::table('objectpartof')->where('objectname', $object)
            ->whereIn('partofname', array_unique([$group, 'Hickson '.$hcg]))
            ->exists();
    }

    /**
     * The galaxy of a member: by name, else by position. Null when none is
     * found, with the reason.
     *
     * @return array{0: ?string, 1: string}
     */
    private function findGalaxy(array $m, array $shared): array
    {
        $ra = (float) $m['ra_hours'];
        $decl = (float) $m['decl_deg'];

        foreach ($this->catalogNames((string) $m['names']) as $name) {
            if (isset($shared[$name])) {
                continue;
            }
            $objectName = DB::table('objects')->where('name', $name)->exists()
                ? $name
                : DB::table('objectnames')->where('altname', $name)->value('objectname');
            $object = $objectName === null ? null : DB::table('objects')
                ->where('name', $objectName)->where('type', 'GALXY')
                ->first(['name', 'ra', 'decl']);
            if ($object !== null && $this->separation($ra, $decl, (float) $object->ra, (float) $object->decl) <= self::NAME_MATCH_ARCSEC) {
                return [$object->name, 'by name '.$name];
            }
        }

        $near = $this->galaxiesNear($ra, $decl, self::POSITION_MATCH_ARCSEC);
        if ($near->isEmpty()) {
            return [null, 'no galaxy within '.(int) self::POSITION_MATCH_ARCSEC.'"'];
        }

        $nearest = $near->first()->sep;
        $best = $near->filter(fn ($o) => $o->sep <= $nearest + self::PREFERENCE_ARCSEC)
            ->sortBy(fn ($o) => [$this->catalogRank($o->name), $o->sep])
            ->first();

        return [$best->name, sprintf('by position, %.1f"', $best->sep)];
    }

    /**
     * The NGC, IC and UGC names of the cross-identifications of the catalog
     * ("N7578A U12478" gives NGC 7578A and UGC 12478).
     *
     * @return list<string>
     */
    private function catalogNames(string $names): array
    {
        $result = [];
        foreach (preg_split('/\s+/', trim($names), -1, PREG_SPLIT_NO_EMPTY) as $id) {
            if (preg_match('/^([NIU])(\d+)([A-Za-z]?)$/', $id, $p)) {
                $result[] = ['N' => 'NGC', 'I' => 'IC', 'U' => 'UGC'][$p[1]].' '.$p[2].strtoupper($p[3]);
            }
        }

        return $result;
    }

    /**
     * The galaxies within $radius arcseconds, nearest first, with their
     * separation in arcseconds as sep. Groups and the other Hickson objects
     * are left out.
     */
    private function galaxiesNear(float $ra, float $decl, float $radius): Collection
    {
        $dDecl = $radius / 3600;
        $dRa = $dDecl / 15 / max(0.01, cos(deg2rad($decl)));

        return DB::table('objects')
            ->where('type', 'GALXY')
            ->whereBetween('decl', [$decl - $dDecl, $decl + $dDecl])
            ->whereBetween('ra', [$ra - $dRa, $ra + $dRa])
            ->where('name', 'not like', 'Hickson %')
            ->get(['name', 'ra', 'decl'])
            ->each(fn ($o) => $o->sep = $this->separation($ra, $decl, (float) $o->ra, (float) $o->decl))
            ->filter(fn ($o) => $o->sep <= $radius)
            ->sortBy('sep')
            ->values();
    }

    private function catalogRank(string $name): int
    {
        $catalog = strtok($name, ' ');
        $rank = array_search($catalog, self::CATALOG_PREFERENCE, true);

        return $rank === false ? count(self::CATALOG_PREFERENCE) : $rank;
    }

    /**
     * The angular distance in arcseconds between two positions (RA in hours,
     * declination in degrees), flat-sky, which is exact enough for 20".
     */
    private function separation(float $ra1, float $decl1, float $ra2, float $decl2): float
    {
        $dRa = ($ra2 - $ra1) * 15 * cos(deg2rad(($decl1 + $decl2) / 2));

        return sqrt($dRa ** 2 + ($decl2 - $decl1) ** 2) * 3600;
    }

    private function apply(array $plan): void
    {
        $now = now();
        $changed = [];

        DB::transaction(function () use ($plan, $now, &$changed) {
            foreach ($plan['matched'] as $p) {
                $this->addMemberName($p['object'], $p, $now);
                $this->link($p['object'], $p['group'], $now);
                $changed[] = $p['object'];
            }

            foreach ($plan['created'] as $p) {
                $m = $p['member'];
                $mag = $m['bt_mag'] !== '' ? $m['bt_mag'] : $m['b_mag'];
                app(DeepskyObjectCreator::class)->create([
                    'name' => $p['alt'],
                    'type' => 'GALXY',
                    'ra' => (float) $m['ra_hours'],
                    'decl' => (float) $m['decl_deg'],
                    'mag' => $mag,
                    // The creator takes the diameters in arcminutes, as the form does.
                    'diam1' => $m['diam1_arcsec'] !== '' ? (float) $m['diam1_arcsec'] / 60 : null,
                    'diam2' => $m['diam2_arcsec'] !== '' ? (float) $m['diam2_arcsec'] / 60 : null,
                    'datasource' => self::DATASOURCE,
                ]);
                $this->link($p['alt'], $p['group'], $now);
            }

            foreach ($plan['linked'] as $p) {
                $this->link($p['object'], $p['group'], $now);
            }
        });

        foreach (array_unique($changed) as $name) {
            ObjectSearchIndex::syncObject($name);
        }
    }

    private function addMemberName(string $object, array $p, $now): void
    {
        DB::table('objectnames')->insert([
            'objectname' => $object,
            'catalog' => 'Hickson',
            'catindex' => $p['catindex'],
            'altname' => $p['alt'],
            'slug' => Str::slug($p['alt']),
            'timestamp' => $now,
        ]);
    }

    private function link(string $object, string $group, $now): void
    {
        if (! DB::table('objectpartof')->where('objectname', $object)->where('partofname', $group)->exists()) {
            DB::table('objectpartof')->insert(['objectname' => $object, 'partofname' => $group, 'timestamp' => $now]);
        }
    }
}
