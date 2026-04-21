<?php

namespace App\Services;

use App\Models\Constellation;
use App\Models\DeepskyObject;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminObjectCheckService
{
    private const DISPLAY_LIMIT = 250;

    public function buildReport(): array
    {
        $constellations = Constellation::query()->pluck('name', 'id')->map(fn($name) => (string) $name);

        return [
            'constellation_check' => $this->buildConstellationCheck($constellations),
            'orphan_objectnames' => $this->buildOrphanObjectNamesCheck(),
            'unknown_observations' => $this->buildUnknownObservationsCheck(),
        ];
    }

    public function deleteOrphanObjectNames(): int
    {
        $ids = DB::table('objectnames')
            ->leftJoin('objects', 'objectnames.objectname', '=', 'objects.name')
            ->whereNull('objects.name')
            ->orderBy('objectnames.id')
            ->pluck('objectnames.id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return DB::table('objectnames')->whereIn('id', $ids)->delete();
    }

    public function repairConstellationMismatches(): int
    {
        $constellations = Constellation::query()->pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtoupper(trim((string) $name)) => (string) $id]);

        $updated = 0;

        DB::table('objects')
            ->select('name', 'ra', 'decl')
            ->orderBy('name')
            ->chunk(500, function (Collection $objects) use ($constellations, &$updated) {
                foreach ($objects as $object) {
                    $raHours = $this->normalizeRaToHours($object->ra ?? null);
                    $declination = DeepskyObject::decToDecimal($object->decl ?? null);

                    if ($raHours === null || $declination === null) {
                        continue;
                    }

                    $expectedCode = strtoupper((new EquatorialCoordinates($raHours, $declination))->getConstellation());

                    if (DB::table('objects')->where('name', $object->name)->update(['con' => $expectedCode]) > 0) {
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    public function exportConstellationMismatches(): string
    {
        $constellations = Constellation::query()->pluck('name', 'id')->map(fn($name) => (string) $name);
        $report = $this->buildConstellationCheck($constellations);

        $rows = ['Object', 'Stored Code', 'Stored Name', 'Expected Code', 'Expected Name', 'RA', 'Declination'];
        $lines = [implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $rows))];

        foreach ($report['examples'] as $row) {
            $lines[] = implode(',', array_map(
                fn($cell) => '"' . str_replace('"', '""', (string) ($cell ?? '')) . '"',
                [
                    $row['name'],
                    $row['stored_code'],
                    $row['stored_name'] ?? '',
                    $row['expected_code'],
                    $row['expected_name'] ?? '',
                    $row['ra'],
                    $row['decl'],
                ]
            ));
        }

        return implode("\n", $lines);
    }

    public function exportOrphanObjectNames(): string
    {
        $report = $this->buildOrphanObjectNamesCheck();

        $rows = ['ID', 'Object Name', 'Catalog', 'Index', 'Alternative Name'];
        $lines = [implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $rows))];

        foreach ($report['examples'] as $row) {
            $lines[] = implode(',', array_map(
                fn($cell) => '"' . str_replace('"', '""', (string) ($cell ?? '')) . '"',
                [
                    $row->id,
                    $row->objectname,
                    $row->catalog,
                    $row->catindex,
                    $row->altname,
                ]
            ));
        }

        return implode("\n", $lines);
    }

    public function exportAliasFixableObservationMappings(): string
    {
        $headers = ['Observed Name', 'Primary Name', 'Observation Count'];
        $lines = [implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $headers))];

        if (!array_key_exists('mysqlOld', config('database.connections', []))) {
            return implode("\n", $lines);
        }

        if (!Schema::connection('mysqlOld')->hasTable('observations')) {
            return implode("\n", $lines);
        }

        $knownObjects = $this->buildKnownObjectsMap();
        $aliasToPrimary = $this->buildAliasToPrimaryMap($knownObjects);

        if (empty($aliasToPrimary)) {
            return implode("\n", $lines);
        }

        DB::connection('mysqlOld')->table('observations')
            ->select('objectname', DB::raw('COUNT(*) as observation_count'))
            ->groupBy('objectname')
            ->orderBy('objectname')
            ->chunk(500, function (Collection $rows) use (&$lines, $knownObjects, $aliasToPrimary) {
                foreach ($rows as $row) {
                    $observedName = (string) ($row->objectname ?? '');
                    $normalized = $this->normalizeObjectNameKey($observedName);

                    if ($normalized === '' || isset($knownObjects[$normalized])) {
                        continue;
                    }

                    $primaryName = $aliasToPrimary[$normalized] ?? null;

                    if ($primaryName === null) {
                        continue;
                    }

                    $lines[] = implode(',', array_map(
                        fn($cell) => '"' . str_replace('"', '""', (string) ($cell ?? '')) . '"',
                        [
                            $observedName,
                            $primaryName,
                            (int) $row->observation_count,
                        ]
                    ));
                }
            });

        return implode("\n", $lines);
    }

    public function repairObservationObjectNamesToPrimary(): int
    {
        if (!array_key_exists('mysqlOld', config('database.connections', []))) {
            return 0;
        }

        if (!Schema::connection('mysqlOld')->hasTable('observations')) {
            return 0;
        }

        $knownObjects = $this->buildKnownObjectsMap();
        $aliasToPrimary = $this->buildAliasToPrimaryMap($knownObjects);

        if (empty($aliasToPrimary)) {
            return 0;
        }

        $updated = 0;

        DB::connection('mysqlOld')->table('observations')
            ->select('objectname')
            ->whereNotNull('objectname')
            ->distinct()
            ->orderBy('objectname')
            ->chunk(500, function (Collection $rows) use ($knownObjects, $aliasToPrimary, &$updated) {
                foreach ($rows as $row) {
                    $rawObjectName = (string) ($row->objectname ?? '');
                    $normalized = $this->normalizeObjectNameKey($rawObjectName);

                    if ($normalized === '' || isset($knownObjects[$normalized])) {
                        continue;
                    }

                    $primaryName = $aliasToPrimary[$normalized] ?? null;

                    if ($primaryName === null || $rawObjectName === $primaryName) {
                        continue;
                    }

                    $updated += DB::connection('mysqlOld')->table('observations')
                        ->where('objectname', $rawObjectName)
                        ->update(['objectname' => $primaryName]);
                }
            });

        return $updated;
    }

    private function buildConstellationCheck(Collection $constellations): array
    {
        // Quick counts (fast indexed queries)
        $totalCount = DB::table('objects')->count();
        $validCount = DB::table('objects')->whereNotNull('ra')->whereNotNull('decl')->count();
        $skipped = $totalCount - $validCount;

        // Find mismatches by sampling first 500 objects (fast preview)
        // Full check should be run as a background job for comprehensive audit
        $mismatchCount = 0;
        $examples = [];
        $sampleLimit = 500; // Sample only 500 objects for fast preview
        $objectsScanned = 0;

        DB::table('objects')
            ->select('name', 'con', 'ra', 'decl')
            ->whereNotNull('ra')
            ->whereNotNull('decl')
            ->orderBy('name')
            ->limit($sampleLimit)
            ->chunk(500, function (Collection $objects) use ($constellations, &$mismatchCount, &$examples, &$objectsScanned) {
                foreach ($objects as $object) {
                    $objectsScanned++;

                    $raHours = $this->normalizeRaToHours($object->ra);
                    $declination = DeepskyObject::decToDecimal($object->decl);

                    if ($raHours === null || $declination === null) {
                        continue;
                    }

                    $expectedCode = strtoupper((new EquatorialCoordinates($raHours, $declination))->getConstellation());
                    $storedCode = strtoupper(trim((string) ($object->con ?? '')));

                    if ($storedCode === $expectedCode) {
                        continue;
                    }

                    $mismatchCount++;

                    if (count($examples) >= self::DISPLAY_LIMIT) {
                        continue;
                    }

                    $examples[] = [
                        'name' => $object->name,
                        'stored_code' => $storedCode,
                        'stored_name' => $constellations->get($storedCode),
                        'expected_code' => $expectedCode,
                        'expected_name' => $constellations->get($expectedCode),
                        'ra' => DeepskyObject::formatRa($object->ra),
                        'decl' => DeepskyObject::formatDec($object->decl),
                    ];
                }
            });

        return [
            'total' => $totalCount,
            'checked' => $objectsScanned,
            'correct' => $objectsScanned - $mismatchCount,
            'skipped' => $skipped,
            'mismatch_count' => $mismatchCount,
            'examples' => $examples,
            'display_limit' => self::DISPLAY_LIMIT,
            'note' => $objectsScanned < $validCount ? "Preview: Scanned first {$objectsScanned} objects. Found {$mismatchCount} mismatches in this sample. Run the repair action to fix all constellation issues." : null,
        ];
    }

    private function buildOrphanObjectNamesCheck(): array
    {
        $query = DB::table('objectnames')
            ->leftJoin('objects', 'objectnames.objectname', '=', 'objects.name')
            ->whereNull('objects.name');

        return [
            'count' => (clone $query)->count(),
            'examples' => (clone $query)
                ->select('objectnames.id', 'objectnames.objectname', 'objectnames.catalog', 'objectnames.catindex', 'objectnames.altname')
                ->orderBy('objectnames.objectname')
                ->orderBy('objectnames.catalog')
                ->limit(self::DISPLAY_LIMIT)
                ->get(),
            'display_limit' => self::DISPLAY_LIMIT,
        ];
    }

    private function buildUnknownObservationsCheck(): array
    {
        try {
            if (!array_key_exists('mysqlOld', config('database.connections', []))) {
                return $this->unavailableObservationsResult('The mysqlOld connection is not configured.');
            }

            if (!Schema::connection('mysqlOld')->hasTable('observations')) {
                return $this->unavailableObservationsResult('The observations table is not available on the mysqlOld connection.');
            }

            $knownObjects = $this->buildKnownObjectsMap();
            $aliasToPrimary = $this->buildAliasToPrimaryMap($knownObjects);

            $examples = [];
            $unknownCount = 0;
            $unknownObservationTotal = 0;
            $aliasCount = 0;
            $aliasObservationTotal = 0;

            DB::connection('mysqlOld')->table('observations')
                ->select('objectname', DB::raw('COUNT(*) as observation_count'))
                ->groupBy('objectname')
                ->orderBy('objectname')
                ->chunk(500, function (Collection $rows) use ($knownObjects, $aliasToPrimary, &$examples, &$unknownCount, &$unknownObservationTotal, &$aliasCount, &$aliasObservationTotal) {
                    foreach ($rows as $row) {
                        $objectName = (string) ($row->objectname ?? '');
                        $normalized = $this->normalizeObjectNameKey($objectName);
                        $observationCount = (int) $row->observation_count;

                        if ($normalized === '' || isset($knownObjects[$normalized])) {
                            continue;
                        }

                        $primaryName = $aliasToPrimary[$normalized] ?? null;

                        if ($primaryName !== null) {
                            $aliasCount++;
                            $aliasObservationTotal += $observationCount;
                        } else {
                            $unknownCount++;
                            $unknownObservationTotal += $observationCount;
                        }

                        if (count($examples) >= self::DISPLAY_LIMIT) {
                            continue;
                        }

                        $examples[] = [
                            'objectname' => $objectName,
                            'primary_name' => $primaryName,
                            'status' => $primaryName !== null ? 'alias' : 'unknown',
                            'observation_count' => $observationCount,
                        ];
                    }
                });

            return [
                'available' => true,
                'count' => $unknownCount,
                'observation_total' => $unknownObservationTotal,
                'alias_count' => $aliasCount,
                'alias_observation_total' => $aliasObservationTotal,
                'examples' => $examples,
                'display_limit' => self::DISPLAY_LIMIT,
                'message' => null,
            ];
        } catch (\Throwable $throwable) {
            return $this->unavailableObservationsResult($throwable->getMessage());
        }
    }

    private function unavailableObservationsResult(string $message): array
    {
        return [
            'available' => false,
            'count' => 0,
            'observation_total' => 0,
            'alias_count' => 0,
            'alias_observation_total' => 0,
            'examples' => [],
            'display_limit' => self::DISPLAY_LIMIT,
            'message' => $message,
        ];
    }

    private function buildKnownObjectsMap(): array
    {
        $known = [];

        DB::table('objects')
            ->select('name')
            ->orderBy('name')
            ->chunk(1000, function (Collection $rows) use (&$known) {
                foreach ($rows as $row) {
                    $name = (string) ($row->name ?? '');
                    $normalized = $this->normalizeObjectNameKey($name);

                    if ($normalized === '') {
                        continue;
                    }

                    $known[$normalized] = trim($name);
                }
            });

        return $known;
    }

    private function buildAliasToPrimaryMap(array $knownObjects): array
    {
        $map = [];

        DB::table('objectnames')
            ->join('objects', 'objectnames.objectname', '=', 'objects.name')
            ->select('objectnames.altname', 'objectnames.objectname')
            ->whereNotNull('objectnames.altname')
            ->orderBy('objectnames.id')
            ->chunk(1000, function (Collection $rows) use (&$map, $knownObjects) {
                foreach ($rows as $row) {
                    $alias = (string) ($row->altname ?? '');
                    $primary = trim((string) ($row->objectname ?? ''));

                    if ($primary === '') {
                        continue;
                    }

                    $aliasKey = $this->normalizeObjectNameKey($alias);
                    $primaryKey = $this->normalizeObjectNameKey($primary);

                    if ($aliasKey === '' || $aliasKey === $primaryKey || !isset($knownObjects[$primaryKey])) {
                        continue;
                    }

                    if (!isset($map[$aliasKey])) {
                        $map[$aliasKey] = $knownObjects[$primaryKey];
                    }
                }
            });

        return $map;
    }

    private function normalizeObjectNameKey(string $name): string
    {
        return strtoupper(trim($name));
    }

    private function normalizeRaToHours(mixed $ra): ?float
    {
        if ($ra === null || $ra === '') {
            return null;
        }

        $value = trim((string) $ra);

        if (is_numeric($value)) {
            $numeric = (float) $value;

            return $numeric > 24.0 ? $numeric / 15.0 : $numeric;
        }

        $decimal = DeepskyObject::raToDecimal($value);

        if ($decimal === null) {
            return null;
        }

        return $decimal / 15.0;
    }
}