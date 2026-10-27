<?php

namespace App\Console\Commands;

use App\Services\DeepskyObjectCreator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Repair the magnitudes and sizes that give objects an impossible contrast
 * reserve:
 *
 * - galaxies (and groups of galaxies) whose size in arcminutes was stored as
 *   arcseconds (Arp 273: 1.9" × 1.0", MCG +04-03-008: 0.5" × 0.3"). Their mean
 *   surface brightness is then far brighter than that of any galaxy, and
 *   Clark's model shows them in any instrument. A galaxy smaller than
 *   MAX_DIAMETER whose surface brightness is brighter than MAX_SB, and lies
 *   between MIN_REPAIRED_SB and MAX_REPAIRED_SB with the size in arcminutes,
 *   gets its size multiplied by 60. Small galaxies too bright that do not
 *   fit are only listed: their magnitude is probably wrong.
 * - the objects of CORRECTIONS, with values checked by hand.
 *
 * The stored metrics of the objects it changes are deleted. Safe to run more
 * than once; run it after an SQL import.
 */
class RepairObjectMeasurements extends Command
{
    /** The types of galaxies whose sizes are checked. */
    public const GALAXY_TYPES = ['GALXY', 'GALCL'];

    /** The largest stored major axis (arcseconds) that may be in arcminutes. */
    public const MAX_DIAMETER = 5.0;

    /** Mean surface brightness (mag/arcsec²) brighter than any galaxy's. */
    public const MAX_SB = 16.0;

    /** The plausible mean surface brightness with the size in arcminutes. */
    public const MIN_REPAIRED_SB = 20.0;

    public const MAX_REPAIRED_SB = 25.0;

    /**
     * Values checked by hand (diameters in arcseconds).
     *
     * - M 24 had the magnitude of the open cluster NGC 6603 in it; the star
     *   cloud (IC 4715) is 4.6.
     * - M 43 had the 20' × 15' of the faint nebulosity merging with M 42, over
     *   which its magnitude made it invisible in any telescope; the visible
     *   nebula is 6' × 3' (Stoyan, Atlas of the Messier Objects).
     * - galaxies far too bright for their size: the size from the D25 and
     *   axis ratio of the PGC (VizieR VII/237), the magnitude from the UGC
     *   (VII/26D) or the CGCG as SIMBAD gives it. MCG -05-18-002 had the size
     *   of the planetary nebula it was once taken for (PK 241-07.1), PK
     *   009-21.01 (MCG -05-46-002) as well; the magnitude of NGC 6745-2
     *   (PGC 200361, a companion of NGC 6745) is not known.
     */
    public const CORRECTIONS = [
        'M 24'           => ['mag' => 4.6],
        'M 43'           => ['diam1' => 360.0, 'diam2' => 180.0],
        'MCG -05-18-002' => ['diam1' => 122.0, 'diam2' => 72.0],
        'MCG +01-27-026' => ['mag' => 15.2, 'diam1' => 52.0, 'diam2' => 48.0],
        'NGC 6745-2'     => ['mag' => 99.9, 'diam1' => 41.0, 'diam2' => 13.0],
        'PK 009-21.01'   => ['mag' => 14.3, 'diam1' => 72.0, 'diam2' => 38.0],
        'UGC 6887'       => ['mag' => 15.5],
        'UGC 11762'      => ['diam1' => 29.0, 'diam2' => 21.0],
    ];

    protected $signature = 'objects:repair-measurements
        {--apply : Write the changes to the database}';

    protected $description = 'Repair galaxy sizes stored in arcminutes as arcseconds, and the magnitudes and sizes of CORRECTIONS';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the changes.');
        }

        ['changes' => $changes, 'unsure' => $unsure] = self::plan();

        if ($this->output->isVerbose() && $changes !== []) {
            $this->table(['Object', 'Magnitude', 'Size (")', 'SB'], array_map(fn (array $c) => [
                $c['name'],
                $c['old']['mag'] == $c['new']['mag'] ? $c['new']['mag'] : $c['old']['mag'].' → '.$c['new']['mag'],
                self::size($c['old']).($c['old']['diam1'] == $c['new']['diam1'] && $c['old']['diam2'] == $c['new']['diam2'] ? '' : ' → '.self::size($c['new'])),
                self::sb($c['old']).' → '.self::sb($c['new']),
            ], $changes));
        }
        if ($unsure !== []) {
            $this->line('');
            $this->warn('Small galaxies too bright for their size, not changed ('.count($unsure).'):');
            $this->table(['Object', 'Magnitude', 'Size (")', 'SB'], array_map(fn (array $o) => [$o['name'], $o['mag'], self::size($o), self::sb($o)], $unsure));
        }

        $this->line('');
        $this->info(count($changes).' objects to repair.');

        if ($apply && $changes !== []) {
            self::apply($changes);
            $this->info('Written; the contrast reserves of these objects are calculated again when needed.');
        }

        return self::SUCCESS;
    }

    /**
     * The changes, and the small galaxies too bright that are left as they are.
     *
     * @return array{changes: list<array{name: string, old: array, new: array}>, unsure: list<array>}
     */
    public static function plan(): array
    {
        $changes = [];
        $unsure  = [];
        $rows    = DB::table('objects')
            ->whereIn('type', self::GALAXY_TYPES)
            ->where('diam1', '>', 0)
            ->where('diam1', '<', self::MAX_DIAMETER)
            ->whereNotIn('name', array_keys(self::CORRECTIONS))
            ->orderBy('name')
            ->get(['name', 'mag', 'diam1', 'diam2']);
        foreach ($rows as $row) {
            $old = self::values($row);
            $sb  = self::sb($old);
            if ($sb === null || $sb >= self::MAX_SB) {
                continue;
            }
            $new      = [...$old, 'diam1' => round($old['diam1'] * 60, 1), 'diam2' => $old['diam2'] > 0 ? round($old['diam2'] * 60, 1) : 0.0];
            $repaired = self::sb($new);
            if ($repaired >= self::MIN_REPAIRED_SB && $repaired <= self::MAX_REPAIRED_SB) {
                $changes[] = ['name' => $row->name, 'old' => $old, 'new' => $new];
            } else {
                $unsure[] = ['name' => $row->name, ...$old];
            }
        }

        foreach (DB::table('objects')->whereIn('name', array_keys(self::CORRECTIONS))->get(['name', 'mag', 'diam1', 'diam2']) as $row) {
            $old = self::values($row);
            $new = [...$old, ...self::CORRECTIONS[$row->name]];
            if ($new != $old) {
                $changes[] = ['name' => $row->name, 'old' => $old, 'new' => $new];
            }
        }

        return ['changes' => $changes, 'unsure' => $unsure];
    }

    /**
     * Write the changes with their surface brightness, and delete the stored
     * metrics of the objects.
     *
     * @param  list<array{name: string, old: array, new: array}>  $changes
     */
    public static function apply(array $changes): void
    {
        foreach (array_chunk($changes, 200) as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $change) {
                    $new = $change['new'];
                    DB::table('objects')->where('name', $change['name'])->update([
                        'mag'   => $new['mag'],
                        'diam1' => $new['diam1'],
                        'diam2' => $new['diam2'],
                        'SBObj' => DeepskyObjectCreator::surfaceBrightness($new['mag'], $new['diam1'], $new['diam2']) ?? -999,
                    ]);
                }
                DB::table('user_object_metrics')->whereIn('object_name', array_column($chunk, 'name'))->delete();
            });
        }
        ResponseCache::clear();
    }

    /** @return array{mag: float, diam1: float, diam2: float} */
    private static function values(object $row): array
    {
        return ['mag' => (float) $row->mag, 'diam1' => (float) $row->diam1, 'diam2' => (float) $row->diam2];
    }

    /** The mean surface brightness (mag/arcsec²), null without a magnitude. */
    private static function sb(array $values): ?float
    {
        if (! ($values['mag'] > 0) || $values['mag'] >= 99) {
            return null;
        }
        $sb = DeepskyObjectCreator::surfaceBrightness($values['mag'], $values['diam1'], $values['diam2']);

        return $sb === null ? null : round($sb, 1);
    }

    private static function size(array $values): string
    {
        return $values['diam1'].' × '.($values['diam2'] > 0 ? $values['diam2'] : $values['diam1']);
    }
}
