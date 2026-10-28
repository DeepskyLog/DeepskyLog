<?php

namespace App\Services;

use App\Models\ObjectName;
use Illuminate\Support\Facades\DB;

/**
 * The traditional names of the double stars of the Struves (#480): the
 * catalogs of the Washington Double Star Catalog use STF 1 where many atlases
 * still write Σ1.
 *
 * Every alternative name of the catalogs below has the Σ name as another
 * alternative name of the same object: STF 1 gives Σ 1, STFA 1 Σ I 1, STFB 1
 * Σ II 1, STT 1 OΣ 1 and STTA 1 OΣΣ 1. The Σ names have no catalog (as other
 * names that are no catalog designation), so they do not add catalogs to
 * /catalogs or the rankings. The searches also take the names written out in
 * ASCII (Sigma 1, Struve 1, O Struve 1), see designation().
 */
class StruveSigmaNames
{
    /** The traditional prefix of each catalog of the WDS. */
    public const PREFIXES = [
        'STF'  => 'Σ',
        'STFA' => 'Σ I',
        'STFB' => 'Σ II',
        'STT'  => 'OΣ',
        'STTA' => 'OΣΣ',
    ];

    /**
     * The traditional name of a designation of one of the catalogs (STF, 1
     * gives Σ 1), null for another catalog.
     */
    public static function sigmaName(string $catalog, string $catindex): ?string
    {
        $prefix   = self::PREFIXES[strtoupper($catalog)] ?? null;
        $catindex = trim($catindex);

        return $prefix === null || $catindex === '' ? null : $prefix.' '.$catindex;
    }

    /**
     * The WDS designation of a traditional name, also written out in ASCII
     * and with or without spaces: Σ1, Sigma 1 and Struve 1 give STF 1,
     * Sigma I 1 STFA 1, Σ II 1 STFB 1, OΣ 1, O Sigma 1, O. Struve 1 and Otto
     * Struve 1 give STT 1, OΣΣ 1 and O Sigma Sigma 1 STTA 1. Null for any
     * other name (Sigma Ori, Sigma 2 UMa).
     */
    public static function designation(string $name): ?string
    {
        $n = str_replace(['σ', 'ς', '.'], [' sigma ', ' sigma ', ' '], mb_strtolower(trim($name)));
        $n = trim((string) preg_replace('/\s+/', ' ', $n));

        if (! preg_match('/^(?:(o|otto) ?)?(sigma|struve)( ?sigma)?(?: (i{1,2}))? ?(\d+)$/', $n, $m)) {
            return null;
        }
        [, $otto, $base, $second, $roman, $number] = $m;
        $number                                    = ltrim($number, '0') ?: '0';

        if ($otto !== '') {
            if ($roman !== '' || ($second !== '' && $base !== 'sigma')) {
                return null;
            }

            return ($second !== '' ? 'STTA ' : 'STT ').$number;
        }
        if ($second !== '') {
            return null;
        }

        return ['' => 'STF ', 'i' => 'STFA ', 'ii' => 'STFB '][$roman].$number;
    }

    /**
     * The Σ names the objects (all objects when null) do not have yet, as
     * rows with objectname, altname (the Σ name), from (the name it was made
     * from), catalog (its catalog) and slug (null when another name has it).
     *
     * @param  list<string>|null  $objects
     * @return list<array{objectname: string, altname: string, from: string, catalog: string, slug: ?string}>
     */
    public static function missing(?array $objects = null): array
    {
        $rows = DB::table('objectnames')
            ->whereIn('catalog', array_keys(self::PREFIXES))
            ->when($objects !== null, fn ($q) => $q->whereIn('objectname', $objects))
            ->orderBy('catalog')
            ->orderBy('id')
            ->get(['objectname', 'catalog', 'catindex', 'altname']);
        if ($rows->isEmpty()) {
            return [];
        }

        // The alternative names the objects have already, by object.
        $present = DB::table('objectnames')
            ->whereIn('objectname', $rows->pluck('objectname')->unique())
            ->get(['objectname', 'altname'])
            ->groupBy('objectname')
            ->map(fn ($names) => $names->pluck('altname')->map(fn ($n) => mb_strtolower($n))->flip()->all())
            ->all();

        $new = [];
        foreach ($rows as $row) {
            $sigma = self::sigmaName($row->catalog, $row->catindex);
            if ($sigma === null || isset($present[$row->objectname][mb_strtolower($sigma)])) {
                continue;
            }
            $present[$row->objectname][mb_strtolower($sigma)] = true;
            $new[]                                            = ['objectname' => $row->objectname, 'altname' => $sigma, 'from' => $row->altname, 'catalog' => strtoupper($row->catalog)];
        }

        // A slug taken by another name (or by the same name of another
        // object, as some stars are in the database twice) is left empty.
        $taken = array_flip(DB::table('objectnames')
            ->whereIn('slug', array_map(fn ($n) => ObjectName::slugFor($n['altname']), $new))
            ->pluck('slug')->all());
        foreach ($new as &$n) {
            $slug         = ObjectName::slugFor($n['altname']);
            $n['slug']    = isset($taken[$slug]) ? null : $slug;
            $taken[$slug] = true;
        }
        unset($n);

        return $new;
    }

    /**
     * Write rows of missing().
     */
    public static function insert(array $new): void
    {
        $now = now();
        foreach (array_chunk($new, 500) as $chunk) {
            DB::table('objectnames')->insert(array_map(fn ($n) => [
                'objectname' => $n['objectname'],
                'catalog'    => '',
                'catindex'   => $n['altname'],
                'altname'    => $n['altname'],
                'slug'       => $n['slug'],
                'timestamp'  => $now,
            ], $chunk));
        }
    }

    /**
     * Give the objects the Σ names of their STF, STFA, STFB, STT and STTA
     * names they do not have yet; used when an object is created or its
     * names are edited. The caller updates the search index.
     *
     * @param  list<string>  $objects
     * @return int the number of names added
     */
    public static function addFor(array $objects): int
    {
        $new = self::missing(array_values(array_filter($objects, fn ($o) => $o !== null && $o !== '')));
        self::insert($new);

        return count($new);
    }
}
