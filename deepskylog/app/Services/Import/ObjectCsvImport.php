<?php

namespace App\Services\Import;

use App\Models\CompleteCatalog;
use App\Models\DeepskyObject;
use App\Models\ObjectName;
use App\Services\DeepskyObjectCreator;
use App\Services\MoonlightSky;
use App\Services\NearbyObjects;
use App\Services\ObjectSearchIndex;
use App\Services\ObservingListItemNameResolver;
use App\Services\StruveSigmaNames;
use Illuminate\Support\Facades\DB;

/**
 * Imports deep-sky objects from a CSV file (#131) and compares the objects
 * DeepskyLog already has with the file (#136), for administrators and
 * database experts on /admin/objects/import.
 *
 * check() sorts the rows of the file: recognised (the name or one of the
 * alternative names is an object, with the fields that differ from the
 * file), possible duplicates (a new name, but an object of the same type at
 * the same position), new and invalid. apply() then adds the new objects,
 * adds the names of the file to the objects they are another name of, and
 * writes the differing fields, as chosen.
 */
class ObjectCsvImport
{
    public const MAX_ROWS = 5000;

    /** The columns of the file; only name is always needed. */
    public const COLUMNS = ['name', 'type', 'ra', 'decl', 'mag', 'subr', 'diam1', 'diam2', 'pa', 'alternative_names'];

    /** Other headers for the columns, lower case. */
    private const HEADER_SYNONYMS = [
        'object' => 'name',
        'dec' => 'decl',
        'declination' => 'decl',
        'right ascension' => 'ra',
        'magnitude' => 'mag',
        'surface brightness' => 'subr',
        'surface_brightness' => 'subr',
        'diameter' => 'diam1',
        'size' => 'diam1',
        'position angle' => 'pa',
        'position_angle' => 'pa',
        'alternative names' => 'alternative_names',
        'altnames' => 'alternative_names',
    ];

    /** Default tolerances of the comparison. */
    public const DEFAULT_SETTINGS = [
        'position' => 10.0,  // arcseconds
        'magnitude' => 0.1,
        'size' => 10.0,      // percent
        'duplicate' => 30.0, // arcseconds
    ];

    /** Position angles that differ less than this (degrees) are the same. */
    private const PA_TOLERANCE = 5;

    private const MAX_NEARBY = 5;

    /** The smallest radius (arcminutes) searched for nearby objects. */
    private const NEARBY_RADIUS = 10.0;

    private ?array $types = null;

    /**
     * The rows of a CSV file, keyed by column, with the line number under
     * 'line', or the errors that keep the whole file from being read. The
     * separator is a comma, semicolon or tab, whichever the header uses.
     *
     * @return array{rows: list<array<string, string|int>>, errors: list<string>}
     */
    public function read(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $counts = ['tab' => substr_count($firstLine, "\t"), ';' => substr_count($firstLine, ';'), ',' => substr_count($firstLine, ',')];
        arsort($counts);
        $separator = array_key_first($counts) === 'tab' ? "\t" : array_key_first($counts);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = fgetcsv($stream, null, $separator, '"', '');
        $columns = [];
        foreach ($header ?: [] as $index => $title) {
            $key = mb_strtolower(trim((string) $title));
            $key = self::HEADER_SYNONYMS[$key] ?? $key;
            if (in_array($key, self::COLUMNS, true) && ! in_array($key, $columns, true)) {
                $columns[$index] = $key;
            }
        }

        if (! in_array('name', $columns, true)) {
            fclose($stream);

            return ['rows' => [], 'errors' => [__('The first line of the file has to name the columns, and one of them has to be name.')]];
        }

        $rows = [];
        $line = 1;
        while (($fields = fgetcsv($stream, null, $separator, '"', '')) !== false) {
            $line++;
            if ($fields === [null] || implode('', array_map('trim', array_map('strval', $fields))) === '') {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                fclose($stream);

                return ['rows' => [], 'errors' => [__('The file has more than :max objects. Split it into smaller files.', ['max' => self::MAX_ROWS])]];
            }

            $row = ['line' => $line];
            foreach ($columns as $index => $key) {
                $row[$key] = trim((string) ($fields[$index] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($stream);

        if ($rows === []) {
            return ['rows' => [], 'errors' => [__('The file has no objects.')]];
        }

        return ['rows' => $rows, 'errors' => []];
    }

    /**
     * Sort the rows of a file. $settings holds the tolerances of
     * DEFAULT_SETTINGS.
     *
     * @param  list<array<string, string|int>>  $rows  from read()
     * @param  array<string, float>  $settings
     * @return list<array<string, mixed>> per row: line, name, status (recognised, duplicate, new, invalid),
     *                                    values (the parsed fields), object (the object it is), names (the names of
     *                                    the file the object does not have yet), differences, nearby, suggestion
     *                                    (the possible duplicate), errors and warnings
     */
    public function check(array $rows, array $settings = []): array
    {
        $settings = array_merge(self::DEFAULT_SETTINGS, $settings);
        $resolver = new ObservingListItemNameResolver();

        $parsed = array_map(fn (array $row) => $this->parseRow($row), $rows);

        $names = [];
        foreach ($parsed as $row) {
            array_push($names, $row['name'], ...$row['values']['alternative_names']);
        }
        $resolved = $resolver->resolveMany(array_filter($names, fn ($n) => $n !== ''));

        // The resolver also knows comets and planets: only deep-sky objects count
        $objects = [];
        foreach (array_chunk(array_values(array_unique(array_filter($resolved))), 500) as $chunk) {
            foreach (DB::table('objects')->whereIn('name', $chunk)->get(['name', 'slug', 'type', 'ra', 'decl', 'mag', 'subr', 'diam1', 'diam2', 'pa']) as $object) {
                $objects[mb_strtolower($object->name)] = $object;
            }
        }
        $objectOf = function (string $name) use ($resolved, $resolver, $objects) {
            $canonical = $resolved[$resolver->clean($name)] ?? null;

            return $canonical !== null ? ($objects[mb_strtolower($canonical)] ?? null) : null;
        };

        $seen = [];
        $result = [];
        foreach ($parsed as $row) {
            $key = mb_strtolower($row['name']);
            if ($row['name'] !== '' && isset($seen[$key])) {
                $row['errors'][] = __('The name is also on line :line of the file.', ['line' => $seen[$key]]);
            }
            $seen[$key] ??= $row['line'];

            $object = $row['name'] !== '' ? $objectOf($row['name']) : null;
            foreach ($row['values']['alternative_names'] as $alternative) {
                $other = $objectOf($alternative);
                if ($other !== null && $object === null) {
                    $object = $other;
                    $row['warnings'][] = __('Recognised by its alternative name :name.', ['name' => $alternative]);
                } elseif ($other !== null && $other->name !== $object->name) {
                    $row['warnings'][] = __('The alternative name :name is :object in DeepskyLog.', ['name' => $alternative, 'object' => $other->name]);
                }
            }

            // The names of the file that are no spelling of an object yet
            $newNames = array_values(array_filter([$row['name'], ...$row['values']['alternative_names']], fn ($n) => $n !== '' && $objectOf($n) === null));

            $result[] = $object !== null
                ? $this->recognised($row, $object, $newNames, $settings)
                : $this->unknown($row, $settings);
        }

        return $result;
    }

    /**
     * Carry out the choices for checked rows: create the new objects
     * ($options['create']), do with the possible duplicates as
     * $options['duplicates'] says (skip, alias: add the names of the file to
     * the object, create: add them as a new object), add the names of the
     * file the recognised objects lack ($options['names']) and write the
     * fields that differ from the file ($options['update']). New objects get
     * $options['datasource'].
     *
     * @param  list<array<string, mixed>>  $rows  from check()
     * @param  array<string, mixed>  $options
     * @return array{created: int, names: int, updated: int, skipped: int}
     */
    public function apply(array $rows, array $options): array
    {
        $counts = ['created' => 0, 'names' => 0, 'updated' => 0, 'skipped' => 0];
        $changed = [];

        DB::transaction(function () use ($rows, $options, &$counts, &$changed) {
            foreach ($rows as $row) {
                $action = match ($row['status']) {
                    'new' => ($options['create'] ?? false) ? 'create' : 'skip',
                    'duplicate' => $options['duplicates'] ?? 'skip',
                    'recognised' => 'recognised',
                    default => 'skip',
                };

                if ($action === 'create') {
                    // The file was checked earlier: an object added since then is left alone
                    if (DB::table('objects')->where('name', $row['name'])->exists()) {
                        $counts['skipped']++;

                        continue;
                    }
                    $values = $row['values'];
                    app(DeepskyObjectCreator::class)->create([
                        'name' => $row['name'],
                        'type' => $values['type'],
                        'ra' => $values['ra'],
                        'decl' => $values['decl'],
                        'mag' => $values['mag'],
                        'subr' => $values['subr'],
                        'diam1' => $values['diam1'],
                        'diam2' => $values['diam2'],
                        'pa' => $values['pa'],
                        'datasource' => mb_substr((string) ($options['datasource'] ?? ''), 0, 50),
                    ]);
                    $counts['created']++;
                    $counts['names'] += $this->addNames($row['name'], $values['alternative_names']);
                    $changed[] = $row['name'];
                } elseif ($action === 'alias') {
                    $counts['names'] += $this->addNames($row['suggestion'], [$row['name'], ...$row['values']['alternative_names']]);
                    $changed[] = $row['suggestion'];
                } elseif ($action === 'recognised') {
                    $done = false;
                    if (($options['names'] ?? false) && $row['names'] !== []) {
                        $counts['names'] += $this->addNames($row['object'], $row['names']);
                        $done = true;
                    }
                    if (($options['update'] ?? false) && $row['differences'] !== [] && $this->update($row['object'], $row['values'], array_keys($row['differences']))) {
                        $counts['updated']++;
                        $done = true;
                    }
                    if ($done) {
                        $changed[] = $row['object'];
                    }
                } else {
                    $counts['skipped']++;
                }
            }
        });

        foreach (array_unique($changed) as $name) {
            StruveSigmaNames::addFor([$name]);
            ObjectSearchIndex::syncObject($name);
        }

        return $counts;
    }

    /**
     * The fields of a row parsed, with the errors of the values that can't be
     * read.
     *
     * @param  array<string, string|int>  $row
     * @return array{line: int, name: string, values: array<string, mixed>, errors: list<string>, warnings: list<string>}
     */
    private function parseRow(array $row): array
    {
        $errors = [];
        $field = fn (string $key) => (string) ($row[$key] ?? '');

        $type = mb_strtoupper($field('type'));
        if ($type !== '' && ! isset($this->types()[$type])) {
            $errors[] = __('Unknown object type :type.', ['type' => $field('type')]);
            $type = '';
        }

        $ra = null;
        if ($field('ra') !== '') {
            $degrees = DeepskyObject::raToDecimal($this->sexagesimal($field('ra'), '/[hms:′″\'"]/u'));
            // raToDecimal() wraps 25h00m around to 1h
            if ($degrees === null || $degrees < 0 || $degrees >= 360 || (! is_numeric($field('ra')) && (float) $field('ra') >= 24)) {
                $errors[] = __('Invalid right ascension :value.', ['value' => $field('ra')]);
            } else {
                $ra = $degrees / 15.0;
            }
        }

        $decl = null;
        if ($field('decl') !== '') {
            $decl = DeepskyObject::decToDecimal($this->sexagesimal(str_replace('−', '-', $field('decl')), '/[dms°:′″\'"]/u'));
            if ($decl === null || abs($decl) > 90) {
                $errors[] = __('Invalid declination :value.', ['value' => $field('decl')]);
                $decl = null;
            }
        }

        $numbers = [];
        foreach (['mag' => [-30, 30], 'subr' => [-30, 40], 'diam1' => [0, 6000], 'diam2' => [0, 6000], 'pa' => [0, 360]] as $key => [$min, $max]) {
            $value = str_replace(',', '.', $field($key));
            $numbers[$key] = null;
            if ($value === '') {
                continue;
            }
            if (! is_numeric($value) || (float) $value < $min || (float) $value > $max) {
                $errors[] = __('Invalid :field :value.', ['field' => $key, 'value' => $field($key)]);

                continue;
            }
            $numbers[$key] = (float) $value;
        }
        if ($numbers['pa'] !== null) {
            $numbers['pa'] = (int) round($numbers['pa']) % 180;
        }
        // diam1 is the major axis
        if ($numbers['diam1'] !== null && $numbers['diam2'] !== null && $numbers['diam2'] > $numbers['diam1']) {
            [$numbers['diam1'], $numbers['diam2']] = [$numbers['diam2'], $numbers['diam1']];
        }

        $name = trim((string) preg_replace('/\s+/u', ' ', $field('name')));
        if ($name === '') {
            $errors[] = __('The object has no name.');
        } elseif (mb_strlen($name) > 128) {
            $errors[] = __('The name is longer than 128 characters.');
        }

        $alternatives = array_values(array_unique(array_filter(
            array_map(fn ($n) => trim((string) preg_replace('/\s+/u', ' ', $n)), preg_split('/[;|]/', $field('alternative_names'))),
            fn ($n) => $n !== '' && mb_strtolower($n) !== mb_strtolower($name) && mb_strlen($n) <= 128
        )));

        return [
            'line' => (int) $row['line'],
            'name' => $name,
            'values' => ['type' => $type, 'ra' => $ra, 'decl' => $decl, ...$numbers, 'alternative_names' => $alternatives],
            'errors' => $errors,
            'warnings' => [],
            'given' => ['type' => $field('type') !== '', 'position' => $field('ra') !== '' && $field('decl') !== ''],
        ];
    }

    /**
     * A sexagesimal angle with its unit signs ("05h34m31.9s", "22°00'52\"",
     * "05:34:31.9") as numbers separated by spaces, and the seconds added
     * when it only has minutes ("05 34.5"), as raToDecimal() and
     * decToDecimal() read it. A decimal number is left alone.
     */
    private function sexagesimal(string $value, string $units): string
    {
        if (is_numeric($value)) {
            return $value;
        }
        $parts = preg_split('/\s+/', trim((string) preg_replace($units, ' ', $value)));
        if (count($parts) === 2 && is_numeric($parts[1])) {
            $minutes = (float) $parts[1];
            $parts = [$parts[0], (string) floor($minutes), (string) round(($minutes - floor($minutes)) * 60, 3)];
        }

        return implode(' ', $parts);
    }

    /**
     * A row whose name is an object: the fields beyond the tolerances, and the
     * names of the file the object does not have yet.
     *
     * @param  list<string>  $newNames  the names of the file DeepskyLog does not know
     */
    private function recognised(array $row, object $object, array $newNames, array $settings): array
    {
        $values = $row['values'];
        $differences = [];

        if ($values['ra'] !== null && $values['decl'] !== null) {
            $separation = MoonlightSky::separation($values['ra'] * 15, $values['decl'], $object->ra * 15, (float) $object->decl) * 3600;
            if ($separation > $settings['position']) {
                $differences['position'] = [
                    'file' => DeepskyObject::formatRa($values['ra']) . ' ' . DeepskyObject::formatDec($values['decl']),
                    'deepskylog' => DeepskyObject::formatRa($object->ra) . ' ' . DeepskyObject::formatDec($object->decl),
                    'difference' => round($separation, 1) . '"',
                ];
            }
        } elseif ($values['ra'] !== null || $values['decl'] !== null) {
            $row['warnings'][] = __('The position is only compared when the file gives both the right ascension and the declination.');
        }

        if ($values['type'] !== '' && mb_strtoupper((string) $object->type) !== $values['type']) {
            $differences['type'] = ['file' => $values['type'], 'deepskylog' => (string) $object->type, 'difference' => ''];
        }

        // The objects table marks a missing value with 99.9 (mag, subr), 999 (pa) or 0 (diameters, in arcseconds)
        $stored = [
            'mag' => $object->mag !== null && (float) $object->mag < 99 ? (float) $object->mag : null,
            'subr' => $object->subr !== null && (float) $object->subr < 99 ? (float) $object->subr : null,
            'diam1' => (float) $object->diam1 > 0 ? (float) $object->diam1 / 60 : null,
            'diam2' => (float) $object->diam2 > 0 ? (float) $object->diam2 / 60 : null,
            'pa' => $object->pa !== null && (int) $object->pa < 360 ? (int) $object->pa : null,
        ];
        foreach ($stored as $key => $value) {
            $new = $values[$key];
            if ($new === null) {
                continue;
            }
            if ($value === null) {
                $differences[$key] = ['file' => $this->format($key, $new), 'deepskylog' => '', 'difference' => __('missing in DeepskyLog')];

                continue;
            }

            $difference = match ($key) {
                'mag', 'subr' => abs($new - $value) > $settings['magnitude'] + 1e-9 ? sprintf('%+.2f', $new - $value) : null,
                'diam1', 'diam2' => abs($new - $value) > $value * $settings['size'] / 100 + 1e-9 ? sprintf('%+.0f%%', ($new - $value) / $value * 100) : null,
                'pa' => min(abs($new - $value) % 180, 180 - abs($new - $value) % 180) > self::PA_TOLERANCE ? ((int) $new - $value) . '°' : null,
            };
            if ($difference !== null) {
                $differences[$key] = ['file' => $this->format($key, $new), 'deepskylog' => $this->format($key, $value), 'difference' => $difference];
            }
        }

        return [
            ...$row,
            'status' => $row['errors'] === [] ? 'recognised' : 'invalid',
            'object' => $object->name,
            'slug' => (string) $object->slug,
            'names' => $newNames,
            'differences' => $differences,
            'nearby' => [],
            'suggestion' => null,
        ];
    }

    /**
     * A row whose name DeepskyLog does not know: a possible duplicate when an
     * object of the same type is at its position, else a new object. It needs a
     * type and a position.
     */
    private function unknown(array $row, array $settings): array
    {
        $values = $row['values'];
        if (! $row['given']['type']) {
            $row['errors'][] = __('A new object needs a type.');
        }
        if (! $row['given']['position']) {
            $row['errors'][] = __('A new object needs a right ascension and a declination.');
        }

        $result = [...$row, 'status' => 'invalid', 'object' => null, 'slug' => null, 'names' => [], 'differences' => [], 'nearby' => [], 'suggestion' => null];
        if ($row['errors'] !== []) {
            return $result;
        }

        if (($catalog = CompleteCatalog::catalogOf($row['name'])) !== null) {
            $result['warnings'][] = __('The :catalog catalog is complete in DeepskyLog: check the name of the object.', ['catalog' => $catalog]);
        }

        // An object of the same type within the duplicate distance, or overlapping it
        $diameter = $values['diam1'];
        $distance = $settings['duplicate'] / 60; // arcminutes
        // Wide enough for the large objects nearby that may overlap it
        $radius = max($distance, ($diameter ?? 0) / 2, self::NEARBY_RADIUS) / 60; // degrees
        $nearby = NearbyObjects::within($values['ra'], $values['decl'], min($radius, 1.0), self::MAX_NEARBY);
        foreach ($nearby as $object) {
            $limit = max($distance, ($diameter ?? 0) / 2, ($object['diameter'] ?? 0) / 2);
            if ($object['type'] === $values['type'] && $object['separation'] <= $limit) {
                $result['suggestion'] = $object['name'];
                break;
            }
        }

        $result['nearby'] = $nearby;
        $result['status'] = $result['suggestion'] !== null ? 'duplicate' : 'new';

        return $result;
    }

    /**
     * Write the fields of a row that differ to the object. Returns whether
     * the object was found.
     *
     * @param  list<string>  $fields  keys of the differences
     */
    private function update(string $name, array $values, array $fields): bool
    {
        $object = DeepskyObject::find($name);
        if ($object === null) {
            return false;
        }

        if (in_array('position', $fields, true)) {
            $object->ra = $values['ra'];
            $object->decl = $values['decl'];
            $object->fill(DeepskyObjectCreator::coordinateDerivedFields($values['ra'], $values['decl'], 'object import'));
        }
        if (in_array('type', $fields, true)) {
            $object->type = $values['type'];
        }
        foreach (['mag', 'subr', 'pa'] as $key) {
            if (in_array($key, $fields, true)) {
                $object->{$key} = $values[$key];
            }
        }
        foreach (['diam1', 'diam2'] as $key) {
            if (in_array($key, $fields, true)) {
                $object->{$key} = $values[$key] * 60.0;
            }
        }
        // mag is a float column: the 99.9 of a missing magnitude comes back as 99.90000153
        $mag = (float) $object->mag < 99 ? (float) $object->mag : null;
        $object->SBObj = DeepskyObjectCreator::surfaceBrightness($mag, (float) $object->diam1, (float) $object->diam2) ?? -999;
        $object->timestamp = now();
        $object->save();

        return true;
    }

    /**
     * Add names to an object as alternative names, unless another object has
     * the name or its slug. Returns the number added.
     *
     * @param  list<string>  $names
     */
    private function addNames(string $object, array $names): int
    {
        $added = 0;
        foreach (array_unique($names) as $name) {
            $slug = ObjectName::slugFor($name);
            if ($name === '' || $slug === ''
                || DB::table('objectnames')->where('altname', $name)->orWhere('slug', $slug)->exists()
                || DB::table('objects')->where('name', $name)->where('name', '!=', $object)->exists()) {
                continue;
            }

            [$catalog, $catindex] = ObjectName::splitCatalogDesignation($name);
            DB::table('objectnames')->insert([
                'objectname' => $object,
                'catalog' => $catalog,
                'catindex' => $catindex,
                'altname' => $name,
                'slug' => $slug,
                'timestamp' => now(),
            ]);
            $added++;
        }

        return $added;
    }

    /**
     * @return array<string, string> the deep-sky type codes with their names
     */
    public function types(): array
    {
        return $this->types ??= DB::table('deepskytypes')->pluck('name', 'code')
            ->mapWithKeys(fn ($name, $code) => [mb_strtoupper($code) => (string) $name])->all();
    }

    private function format(string $key, float|int $value): string
    {
        return match ($key) {
            'mag', 'subr' => number_format($value, 2),
            'diam1', 'diam2' => rtrim(rtrim(number_format($value, 2), '0'), '.') . "'",
            'pa' => $value . '°',
        };
    }
}
