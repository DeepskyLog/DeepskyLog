<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('object_contours')) {
            Schema::create('object_contours', function (Blueprint $table) {
                $table->id();
                $table->string('object_name', 255)->index();
                $table->unsignedSmallInteger('contour_index')->default(0);
                $table->unsignedInteger('point_index');
                $table->double('ra_degrees');
                $table->double('decl_degrees');

                $table->unique(['object_name', 'contour_index', 'point_index'], 'object_contours_unique_point');
                $table->index(['decl_degrees', 'ra_degrees'], 'object_contours_decl_ra_idx');
            });
        }

        $this->importLegacyObjectOutlines();
    }

    public function down(): void
    {
        Schema::dropIfExists('object_contours');
    }

    private function importLegacyObjectOutlines(): void
    {
        if (!Schema::hasTable('objectoutlines')) {
            return;
        }

        if (DB::table('object_contours')->exists()) {
            return;
        }

        $columns = Schema::getColumnListing('objectoutlines');
        if ($columns === []) {
            return;
        }

        $rows = DB::table('objectoutlines')->get();
        if ($rows->isEmpty()) {
            return;
        }

        $objectColumn = $this->firstExistingColumn($columns, ['object_name', 'objectname', 'object', 'name']);
        if ($objectColumn === null) {
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

        $raMode = $this->detectRaMode($rows, array_values(array_filter([$raColumn, $ra0Column, $ra1Column])));

        $nextPointIndex = [];
        $nextContourIndex = [];
        $batch = [];

        foreach ($rows as $row) {
            $objectName = trim((string) ($row->{$objectColumn} ?? ''));
            if ($objectName === '') {
                continue;
            }

            $contourIndex = $this->coerceInt($row->{$contourColumn} ?? null);
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
            DB::table('object_contours')->insertOrIgnore($batch);
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
     * @param Collection<int, object> $rows
     * @param array<int, string> $raColumns
     */
    private function detectRaMode(Collection $rows, array $raColumns): string
    {
        if ($raColumns === []) {
            return 'degrees';
        }

        $max = null;
        $sampleCount = 0;

        foreach ($rows as $row) {
            foreach ($raColumns as $column) {
                $value = $row->{$column} ?? null;
                if (!is_numeric($value)) {
                    continue;
                }

                $numeric = (float) $value;
                $max = $max === null ? $numeric : max($max, $numeric);
                $sampleCount++;

                if ($sampleCount >= 1000) {
                    break 2;
                }
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
};
