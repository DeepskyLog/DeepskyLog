<?php

namespace App\Console\Commands;

use App\Services\DeepskyObjectCreator;
use App\Services\ObjectSearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Repair the nebulae whose position or LBN/Sharpless name objects:estimate-
 * nebula-magnitudes refused (October 2026): each one was checked against
 * SIMBAD, and the Cederblad (VizieR VII/231) and Sharpless (VII/20) catalogues.
 *
 * - POSITIONS: objects at a wrong position (a declination with the wrong sign,
 *   a wrong hour or minute of right ascension), moved to the right one, with
 *   the constellation, atlas pages and search index; their stored contrast
 *   reserves are deleted. An object already within MOVED_ARCMIN of its new
 *   position is left alone, so the command is safe to run more than once.
 * - NAMES: alternative names that belong to another nebula, removed or
 *   replaced by the right one.
 *
 * The other names it refused are right: Lynds measured the centres of the
 * areas he drew, which for a large nebula can lie more than a degree from
 * where SIMBAD puts it (LBN 56 is Sh 2-42, LBN 589 is NGC 7822).
 */
class RepairNebulaPositions extends Command
{
    /**
     * The right position (RA in hours, declination in degrees) of the objects
     * at a wrong one, and where it comes from.
     */
    public const POSITIONS = [
        'Ced 178'  => [20.1668, 34.9619, 'Cederblad: IC 1310, not 20h14m +34°'],
        'LBN 289'  => [20.8667, 39.0000, 'SIMBAD, Lynds: not 21h12m +38°'],
        'Sh 2-36'  => [16.0933, 0.3833, 'SIMBAD, Sharpless: north of the equator'],
        'Sh 2-67'  => [18.8228, -2.3606, 'SIMBAD, Sharpless: 18h49m, not 18h22m'],
        'Sh 2-117' => [20.9796, 44.3220, 'Sharpless: 20h59m, not 20h00m'],
        'Sh 2-243' => [5.6550, 23.2833, 'SIMBAD, Sharpless: 5h39m, not 5h34m'],
        'Sh 2-283' => [6.6414, 0.7050, 'SIMBAD, Sharpless: north of the equator'],
        'Sh 2-285' => [6.9208, -0.4983, 'SIMBAD, Sharpless: south of the equator'],
    ];

    /** An object closer than this to its new position is not moved. */
    public const MOVED_ARCMIN = 5.0;

    /**
     * Alternative names of an object that belong to another nebula: the
     * object, the wrong name and the right one (null: none).
     */
    public const NAMES = [
        ['Sh 2-115', 'LBN 115', 'LBN 357'],
        ['IC 353', 'LBN 601', null],
    ];

    protected $signature = 'objects:repair-nebula-positions
        {--apply : Write the repairs to the database}';

    protected $description = 'Move the nebulae at a wrong position and remove the LBN/Sharpless names that belong to another nebula';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the repairs.');
        }

        $moves   = $this->moves();
        $renames = $this->renames();

        if ($moves !== []) {
            $this->line('');
            $this->info('Objects moved ('.count($moves).')');
            $this->table(['Object', 'From', 'To', 'Offset (\')', 'Source'], array_map(fn (array $m) => [
                $m['name'],
                sprintf('%.4fh %+.3f°', $m['from'][0], $m['from'][1]),
                sprintf('%.4fh %+.3f°', $m['to'][0], $m['to'][1]),
                round($m['offset']),
                $m['source'],
            ], $moves));
        }
        if ($renames !== []) {
            $this->line('');
            $this->info('Alternative names removed ('.count($renames).')');
            $this->table(['Object', 'Removed', 'Added'], array_map(fn (array $r) => [$r[0], $r[1], $r[2] ?? '-'], $renames));
        }
        if ($moves === [] && $renames === []) {
            $this->info('Nothing to repair.');
        }

        if ($apply && ($moves !== [] || $renames !== [])) {
            $this->apply($moves, $renames);
            $this->info('Written.');
        }

        return self::SUCCESS;
    }

    /**
     * The objects of POSITIONS that are still at a wrong position.
     *
     * @return list<array{name: string, from: array, to: array, offset: float, source: string}>
     */
    private function moves(): array
    {
        $objects = DB::table('objects')->whereIn('name', array_keys(self::POSITIONS))->get(['name', 'ra', 'decl'])->keyBy('name');

        $moves = [];
        foreach (self::POSITIONS as $name => [$ra, $decl, $source]) {
            $object = $objects[$name] ?? null;
            if ($object === null) {
                continue;
            }
            $offset = self::separationArcmin((float) $object->ra, (float) $object->decl, $ra, $decl);
            if ($offset > self::MOVED_ARCMIN) {
                $moves[] = ['name' => $name, 'from' => [(float) $object->ra, (float) $object->decl], 'to' => [$ra, $decl], 'offset' => $offset, 'source' => $source];
            }
        }

        return $moves;
    }

    /**
     * The wrong names of NAMES that are still there; the right name is only
     * added when no object has it yet.
     */
    private function renames(): array
    {
        $renames = [];
        foreach (self::NAMES as [$object, $wrong, $right]) {
            if (! DB::table('objectnames')->where('objectname', $object)->where('altname', $wrong)->exists()) {
                continue;
            }
            if ($right !== null && DB::table('objectnames')->where('altname', $right)->exists()) {
                $right = null;
            }
            $renames[] = [$object, $wrong, $right];
        }

        return $renames;
    }

    private function apply(array $moves, array $renames): void
    {
        DB::transaction(function () use ($moves, $renames) {
            foreach ($moves as $move) {
                [$ra, $decl] = $move['to'];
                DB::table('objects')->where('name', $move['name'])->update(array_merge(
                    ['ra' => $ra, 'decl' => $decl, 'timestamp' => now()],
                    DeepskyObjectCreator::coordinateDerivedFields($ra, $decl, 'repair nebula positions'),
                ));
                // The highest altitude, and so the contrast reserve, depends on the declination
                DB::table('user_object_metrics')->where('object_name', $move['name'])->delete();
            }

            foreach ($renames as [$object, $wrong, $right]) {
                DB::table('objectnames')->where('objectname', $object)->where('altname', $wrong)->delete();
                if ($right !== null) {
                    [$catalog, $index] = explode(' ', $right, 2);
                    DB::table('objectnames')->insert([
                        'objectname' => $object,
                        'catalog'    => $catalog,
                        'catindex'   => $index,
                        'altname'    => $right,
                        'slug'       => Str::slug($right),
                        'timestamp'  => now(),
                    ]);
                }
            }
        });

        foreach (array_column($moves, 'name') as $name) {
            ObjectSearchIndex::syncObject($name);
        }
        // The removed name and its slug no longer lead to the object
        foreach ($renames as [$object, $wrong]) {
            ObjectSearchIndex::syncObject($object, [$wrong, Str::slug($wrong)]);
        }
        ResponseCache::clear();
    }

    private static function separationArcmin(float $ra1, float $decl1, float $ra2, float $decl2): float
    {
        [$a1, $d1, $a2, $d2] = [deg2rad($ra1 * 15), deg2rad($decl1), deg2rad($ra2 * 15), deg2rad($decl2)];

        $cos = sin($d1) * sin($d2) + cos($d1) * cos($d2) * cos($a1 - $a2);

        return rad2deg(acos(max(-1.0, min(1.0, $cos)))) * 60;
    }
}
