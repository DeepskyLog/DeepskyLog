<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyObjectContoursSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('object_contours')) {
            if ($this->command) {
                $this->command->warn('Skipping LegacyObjectContoursSeeder: object_contours table does not exist.');
            }

            return;
        }

        $sourceConnection = env('LEGACY_CONTOUR_CONNECTION', 'mysqlOld');
        $sourceTable = env('LEGACY_CONTOUR_TABLE', 'objectOutlines');

        $legacy = DB::connection($sourceConnection);
        $legacySchema = $legacy->getSchemaBuilder();

        if (!$legacySchema->hasTable($sourceTable)) {
            if ($this->command) {
                $this->command->warn(sprintf(
                    'Skipping LegacyObjectContoursSeeder: %s.%s does not exist.',
                    $sourceConnection,
                    $sourceTable
                ));
            }

            return;
        }

        $columns = $legacySchema->getColumnListing($sourceTable);
        if ($columns === []) {
            if ($this->command) {
                $this->command->warn(sprintf(
                    'Skipping LegacyObjectContoursSeeder: no columns discovered for %s.%s.',
                    $sourceConnection,
                    $sourceTable
                ));
            }

            return;
        }

        $objectColumn = $this->firstExistingColumn($columns, ['object_name', 'objectname', 'object', 'name']);
        if ($objectColumn === null) {
            if ($this->command) {
                $this->command->warn(sprintf(
                    'Skipping LegacyObjectContoursSeeder: no object-name column found in %s.%s.',
                    $sourceConnection,
                    $sourceTable
                ));
            }

            return;
        }

        $contourColumn = $this->firstExistingColumn($columns, ['contour_index', 'contour', 'line', 'segment', 'path_index']);
        $pointColumn = $this->firstExistingColumn($columns, ['point_index', 'point', 'seq', 'order']);

        $raColumn = $this->firstExistingColumn($columns, ['ra_degrees', 'ra_deg', 'ra']);
        $declColumn = $this->firstExistingColumn($columns, ['decl_degrees', 'decl_deg', 'dec_deg', 'decl', 'dec']);

        $ra0Column = $this->firstExistingColumn($columns, ['ra0_degrees', 'ra0_deg', 'ra0']);
        $decl0Column = $this->firstExistingColumn($columns, ['decl0_degrees', 'decl0_deg', 'dec0_deg', 'decl0', 'dec0']);
        $ra1Column = $this->firstExistingColumn($columns, ['ra1_degrees', 'ra1_deg', 'ra1']);
        $decl1Column = $this->firstExistingColumn($columns, ['decl1_degrees', 'decl1_deg', 'dec1_deg', 'decl1', 'dec1']);

        $serializedColumn = $this->firstExistingColumn($columns, ['coordinates', 'coords', 'points', 'polyline', 'path']);
        // The old objectOutlines marks each outline with a start and an end
        // row: an object can have several (IC 5068 has three).
        $typeColumn = $this->firstExistingColumn($columns, ['type']);

        $supportedPointSchemas =
            ($raColumn !== null && $declColumn !== null)
            || ($ra0Column !== null && $decl0Column !== null && $ra1Column !== null && $decl1Column !== null)
            || ($serializedColumn !== null);

        if (!$supportedPointSchemas) {
            if ($this->command) {
                $this->command->warn(sprintf(
                    'Skipping LegacyObjectContoursSeeder: unable to identify coordinate columns in %s.%s.',
                    $sourceConnection,
                    $sourceTable
                ));
            }

            return;
        }

        $raMode = $this->detectRaMode($legacy, $sourceTable, array_values(array_filter([$raColumn, $ra0Column, $ra1Column])));

        // Running it again replaces the contours of the objects it imports.
        $legacyObjects = $legacy->table($sourceTable)->distinct()->pluck($objectColumn)
            ->map(fn ($name) => trim((string) $name))->filter()->values();
        foreach ($legacyObjects->chunk(500) as $names) {
            DB::table('object_contours')->whereIn('object_name', $names->all())->delete();
        }

        $beforeCount = DB::table('object_contours')->count();

        $nextPointIndex = [];
        $nextContourIndex = [];
        $insertedRows = 0;

        if ($this->command) {
            $this->command->info(sprintf(
                'Importing contours from %s.%s into object_contours...',
                $sourceConnection,
                $sourceTable
            ));
        }

        $legacy->table($sourceTable)->orderBy($objectColumn)->chunk(5000, function ($rows) use ($objectColumn, $typeColumn, $contourColumn, $pointColumn, $raColumn, $declColumn, $ra0Column, $decl0Column, $ra1Column, $decl1Column, $serializedColumn, $raMode, &$nextPointIndex, &$nextContourIndex, &$insertedRows): void {
            $batch = [];

            foreach ($rows as $row) {
                $objectName = trim((string) ($row->{$objectColumn} ?? ''));
                if ($objectName === '') {
                    continue;
                }

                $contourIndex = $this->coerceInt($row->{$contourColumn} ?? null);
                if ($contourIndex === null && $typeColumn !== null
                    && strtolower(trim((string) ($row->{$typeColumn} ?? ''))) === 'start'
                    && isset($nextPointIndex[$objectName . '|' . ($nextContourIndex[$objectName] ?? 0)])
                ) {
                    $nextContourIndex[$objectName] = ($nextContourIndex[$objectName] ?? 0) + 1;
                }
                if ($contourIndex === null) {
                    $contourIndex = $nextContourIndex[$objectName] ?? 0;
                }

                $points = [];

                if ($raColumn !== null && $declColumn !== null) {
                    $ra = $this->normalizeRa($row->{$raColumn} ?? null, $raMode);
                    $decl = $this->normalizeDecl($row->{$declColumn} ?? null);

                    if ($ra !== null && $decl !== null) {
                        $points[] = ['ra_degrees' => $ra, 'decl_degrees' => $decl];
                    }
                } elseif ($ra0Column !== null && $decl0Column !== null && $ra1Column !== null && $decl1Column !== null) {
                    $ra0 = $this->normalizeRa($row->{$ra0Column} ?? null, $raMode);
                    $decl0 = $this->normalizeDecl($row->{$decl0Column} ?? null);
                    $ra1 = $this->normalizeRa($row->{$ra1Column} ?? null, $raMode);
                    $decl1 = $this->normalizeDecl($row->{$decl1Column} ?? null);

                    if ($ra0 !== null && $decl0 !== null && $ra1 !== null && $decl1 !== null) {
                        $points[] = ['ra_degrees' => $ra0, 'decl_degrees' => $decl0];
                        $points[] = ['ra_degrees' => $ra1, 'decl_degrees' => $decl1];
                    }
                } elseif ($serializedColumn !== null) {
                    $pairs = $this->parseSerializedPoints((string) ($row->{$serializedColumn} ?? ''));

                    foreach ($pairs as $pair) {
                        $ra = $this->normalizeRa($pair['ra'], $raMode);
                        $decl = $this->normalizeDecl($pair['decl']);

                        if ($ra !== null && $decl !== null) {
                            $points[] = ['ra_degrees' => $ra, 'decl_degrees' => $decl];
                        }
                    }
                }

                if ($points === []) {
                    continue;
                }

                if ($pointColumn !== null) {
                    $startPoint = $this->coerceInt($row->{$pointColumn} ?? null) ?? 0;
                } else {
                    $key = $objectName . '|' . $contourIndex;
                    $startPoint = $nextPointIndex[$key] ?? 0;
                }

                foreach ($points as $offset => $point) {
                    $batch[] = [
                        'object_name' => $objectName,
                        'contour_index' => $contourIndex,
                        'point_index' => $startPoint + $offset,
                        'ra_degrees' => $point['ra_degrees'],
                        'decl_degrees' => $point['decl_degrees'],
                    ];
                }

                $key = $objectName . '|' . $contourIndex;
                $nextPointIndex[$key] = $startPoint + count($points);

                if ($pointColumn === null && $serializedColumn !== null) {
                    $nextContourIndex[$objectName] = $contourIndex + 1;
                }
            }

            if ($batch !== []) {
                foreach (array_chunk($batch, 1000) as $insertChunk) {
                    DB::table('object_contours')->insertOrIgnore($insertChunk);
                    $insertedRows += count($insertChunk);
                }
            }
        });

        $afterCount = DB::table('object_contours')->count();
        $delta = max(0, $afterCount - $beforeCount);

        if ($this->command) {
            $this->command->info(sprintf(
                'LegacyObjectContoursSeeder complete: object_contours rows before=%d, after=%d, added=%d (attempted=%d).',
                $beforeCount,
                $afterCount,
                $delta,
                $insertedRows
            ));
        }
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $candidates
     */
    private function firstExistingColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $raColumns
     */
    private function detectRaMode($legacy, string $table, array $raColumns): string
    {
        if ($raColumns === []) {
            return 'degrees';
        }

        $max = null;
        $sampleRows = $legacy->table($table)->limit(2000)->get();

        foreach ($sampleRows as $row) {
            foreach ($raColumns as $column) {
                $value = $row->{$column} ?? null;
                if (!is_numeric($value)) {
                    continue;
                }

                $numeric = (float) $value;
                $max = $max === null ? $numeric : max($max, $numeric);
            }
        }

        if ($max !== null && $max <= 24.5) {
            return 'hours';
        }

        return 'degrees';
    }

    private function normalizeRa(mixed $value, string $mode): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;
        $degrees = $mode === 'hours' ? $numeric * 15.0 : $numeric;

        $normalized = fmod($degrees, 360.0);
        if ($normalized < 0.0) {
            $normalized += 360.0;
        }

        return $normalized;
    }

    private function normalizeDecl(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;

        if ($numeric < -90.0 || $numeric > 90.0) {
            return null;
        }

        return $numeric;
    }

    private function coerceInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<int, array{ra: mixed, decl: mixed}>
     */
    private function parseSerializedPoints(string $value): array
    {
        $pairs = [];

        if ($value === '') {
            return $pairs;
        }

        preg_match_all('/([\+\-]?\d+(?:\.\d+)?)\s*[,;:\/]\s*([\+\-]?\d+(?:\.\d+)?)/', $value, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $pairs[] = [
                'ra' => $match[1] ?? null,
                'decl' => $match[2] ?? null,
            ];
        }

        return $pairs;
    }
}
