<?php

namespace App\Services\Import;

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Support\Html;
use App\Support\SpreadsheetCell;
use Illuminate\Support\Facades\DB;

/**
 * Imports observations from a semicolon-delimited CSV file.
 *
 * Expected column order (no header line):
 *  1  Object name
 *  2  Observer (full name or username — used for reference only; logged-in user is the owner)
 *  3  Date (DD-MM-YYYY)
 *  4  UT time (HH:MM)
 *  5  Location name
 *  6  Instrument name (required; "Naked eye" for a naked-eye observation)
 *  7  Eyepiece name (optional)
 *  8  Filter name (optional)
 *  9  Lens/Barlow name (optional)
 * 10  Seeing (1–5, Antoniadi)
 * 11  Limiting magnitude (float, 0 = not measured)
 * 12  SQM value (float, 0 = not measured)
 * 13  Object visibility (1–7; 1=very easy … 7=not seen)
 * 14  Language (ISO 2-letter code, e.g. nl, en)
 * 15  Description (required)
 * 16  Drawing (optional): the file name of the drawing, uploaded with the
 *     file or in a zip file with it. Only read when it ends in an image
 *     extension, as a description with unquoted semicolons runs on into it.
 * 17  Class (optional, #321): A, B or C, the observer's own grade of the
 *     observation. Only read when it is one of these, for the same reason;
 *     without it an imported observation keeps the class it has.
 */
class CsvObservationImporter
{
    use SavesImportedObservations;

    private User $user;

    /**
     * The objects resolved during this import, by object name: a file usually
     * holds the same object many times, and a lookup takes up to 20 queries.
     *
     * @var array<string, array{string|null, string|null, string|null, string|null}>
     */
    private array $resolvedObjects = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Import from a raw CSV string (already read from the uploaded file).
     *
     * @param  string  $content  Raw file contents.
     */
    public function import(string $content): ImportResult
    {
        $result = new ImportResult();
        $result->addInfo('Starting CSV import.', '', 0);

        // Normalise line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);

        $rowNumber = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $rowNumber++;

            // Allow both ; and , as delimiter; prefer ; (as per spec)
            $fields = str_getcsv($line, ';');

            // Trim every field, and undo the escaping of formulas of an export
            $fields = array_map(fn (string $field) => SpreadsheetCell::unescape(trim($field)), $fields);

            // Pad to at least 15 columns
            while (count($fields) < 15) {
                $fields[] = '';
            }

            [
                $objectName,
                $observerName,
                $dateStr,
                $timeStr,
                $locationName,
                $instrumentName,
                $eyepieceName,
                $filterName,
                $lensName,
                $seeingRaw,
                $limMagRaw,
                $sqmRaw,
                $visibilityRaw,
                $language,
                $description,
            ] = array_slice($fields, 0, 15);
            $drawingName = $fields[15] ?? '';
            $observerClass = strtoupper($fields[16] ?? '');

            // ─── Validate required fields ──────────────────────────────────────

            if (empty($objectName)) {
                $result->addError('Object name is empty, skipping row.', $line, $rowNumber);
                $result->skipped++;
                continue;
            }

            if (empty($instrumentName)) {
                $result->addError("Row {$rowNumber}: Instrument is empty, skipping row. Use your naked-eye instrument for an observation with the naked eye.", $line, $rowNumber);
                $result->skipped++;
                continue;
            }

            if (Html::isBlank($description)) {
                $result->addError("Row {$rowNumber}: Description is empty, skipping row.", $line, $rowNumber);
                $result->skipped++;
                continue;
            }

            // ─── Parse date ────────────────────────────────────────────────────

            $observedOn = $this->parseDate($dateStr);
            if ($observedOn === null) {
                $result->addError("Cannot parse date '{$dateStr}', skipping row.", $line, $rowNumber);
                $result->skipped++;
                continue;
            }

            // ─── Parse time ────────────────────────────────────────────────────

            $observedTime = $this->parseTime($timeStr);

            // ─── Resolve astronomical object ────────────────────────────────────

            [$targetTable, $targetKey, $targetType, $canonicalName] = $this->resolveObject($objectName);

            if ($targetTable === null) {
                $result->addError(
                    "Row {$rowNumber}: Object '{$objectName}' not found in the database, skipping row. Check the name of the object.",
                    $line,
                    $rowNumber
                );
                $result->skipped++;
                continue;
            }

            // ─── Resolve location (must exist) ────────────────────────────────

            $locationId = null;
            if (!empty($locationName)) {
                $loc = $this->findLocation($locationName);
                if ($loc === null) {
                    $result->addError(
                        "Row {$rowNumber}: Location '{$locationName}' not found for your account. Add it first in DeepskyLog, then re-import.",
                        $line,
                        $rowNumber
                    );
                    $result->skipped++;
                    continue;
                }
                $locationId = $loc->id;
            }

            // ─── Resolve instrument (required, must exist) ────────────────────

            $inst = $this->findInstrument($instrumentName);
            if ($inst === null) {
                $result->addError(
                    "Row {$rowNumber}: Instrument '{$instrumentName}' not found for your account. Add it first in DeepskyLog, then re-import.",
                    $line,
                    $rowNumber
                );
                $result->skipped++;
                continue;
            }
            $instrumentId = $inst->id;

            // ─── Resolve eyepiece (must exist if provided) ────────────────────

            $eyepieceId = null;
            if (!empty($eyepieceName)) {
                $ep = $this->findEyepiece($eyepieceName);
                if ($ep === null) {
                    $result->addError(
                        "Row {$rowNumber}: Eyepiece '{$eyepieceName}' not found for your account. Add it first in DeepskyLog, then re-import.",
                        $line,
                        $rowNumber
                    );
                    $result->skipped++;
                    continue;
                }
                $eyepieceId = $ep->id;
            }

            // ─── Resolve filter (must exist if provided) ──────────────────────

            $filterId = null;
            if (!empty($filterName)) {
                $filter = $this->findFilter($filterName);
                if ($filter === null) {
                    $result->addError(
                        "Row {$rowNumber}: Filter '{$filterName}' not found for your account. Add it first in DeepskyLog, then re-import.",
                        $line,
                        $rowNumber
                    );
                    $result->skipped++;
                    continue;
                }
                $filterId = $filter->id;
            }

            // ─── Resolve lens (must exist if provided) ────────────────────────

            $lensId = null;
            if (!empty($lensName)) {
                $lens = $this->findLens($lensName);
                if ($lens === null) {
                    $result->addError(
                        "Row {$rowNumber}: Lens '{$lensName}' not found for your account. Add it first in DeepskyLog, then re-import.",
                        $line,
                        $rowNumber
                    );
                    $result->skipped++;
                    continue;
                }
                $lensId = $lens->id;
            }

            // ─── Parse numeric fields ──────────────────────────────────────────

            $seeing = $this->parseInt($seeingRaw, 1, 5);
            $visibility = $this->parseInt($visibilityRaw, 1, 7);

            $limitingMagnitude = null;
            $sqm = null;
            if (!empty($limMagRaw)) {
                $val = (float) str_replace(',', '.', $limMagRaw);
                if ($val > 0) {
                    $limitingMagnitude = $val;
                }
            }
            if (!empty($sqmRaw)) {
                $val = (float) str_replace(',', '.', $sqmRaw);
                if ($val > 0) {
                    $sqm = $val;
                }
            }

            $lang = !empty($language) ? strtolower(substr($language, 0, 10)) : 'en';

            // ─── Save or update observation ────────────────────────────────────

            $fields = [
                'observer_username' => $this->user->username,
                'observer_user_id' => $this->user->id,
                'observed_on' => $observedOn,
                'observed_time' => $observedTime,
                'location_id' => $locationId,
                'instrument_id' => $instrumentId,
                'eyepiece_id' => $eyepieceId,
                'filter_id' => $filterId,
                'lens_id' => $lensId,
                'seeing' => $seeing,
                'limiting_magnitude' => $limitingMagnitude,
                'sqm' => $sqm,
                'language' => $lang,
                'description' => $description,
                'target_table' => $targetTable,
                'target_key' => $targetKey,
                'target_type' => $targetType,
                'target_name' => $canonicalName,
                'has_drawing' => false,
                'legacy_source' => null,
            ];
            if (in_array($observerClass, Observation::OBSERVER_CLASSES, true)) {
                $fields['observer_class'] = $observerClass;
            }

            $this->saveRow($fields, $visibility, $drawingName, $result, $rowNumber, $line);
        }

        $this->reportDrawings($result);

        $result->addInfo(
            "CSV import complete. Imported: {$result->imported}, Skipped: {$result->skipped}, Errors: {$result->errors}.",
            '',
            0
        );

        return $result;
    }

    /**
     * Save one observation of which the location and equipment are already
     * known: the observations logged at the eyepiece (App\Services\AtTheEyepiece),
     * also those kept while offline. The object is looked up, the horizon
     * checked and an observation that is already there updated as for a row
     * of a file, so sending the same observation twice stores it once. Date
     * and time are UT.
     *
     * @param  array{object: string, observed_on: string, observed_time: ?string, location_id: ?int, instrument_id: int, eyepiece_id?: ?int, filter_id?: ?int, lens_id?: ?int, visibility?: ?int, language?: ?string, description: string}  $row
     */
    public function importOne(array $row): ImportResult
    {
        $result = new ImportResult();

        [$targetTable, $targetKey, $targetType, $canonicalName] = $this->resolveObject($row['object']);
        if ($targetTable === null) {
            $result->addError("Object '{$row['object']}' not found in the database.", '', 1);
            $result->skipped++;

            return $result;
        }

        $this->saveRow([
            'observer_username' => $this->user->username,
            'observer_user_id' => $this->user->id,
            'observed_on' => $row['observed_on'],
            'observed_time' => $row['observed_time'],
            'location_id' => $row['location_id'],
            'instrument_id' => $row['instrument_id'],
            'eyepiece_id' => $row['eyepiece_id'] ?? null,
            'filter_id' => $row['filter_id'] ?? null,
            'lens_id' => $row['lens_id'] ?? null,
            'language' => $row['language'] ?? 'en',
            'description' => $row['description'],
            'target_table' => $targetTable,
            'target_key' => $targetKey,
            'target_type' => $targetType,
            'target_name' => $canonicalName,
            'has_drawing' => false,
            'legacy_source' => null,
        ], $row['visibility'] ?? null, '', $result, 1, '');

        return $result;
    }

    /**
     * Save the observation of one row (an observation that is already there
     * is updated), with its drawing and the visibility of a deep-sky object.
     *
     * @param  array<string, mixed>  $fields
     */
    private function saveRow(array $fields, ?int $visibility, string $drawingName, ImportResult $result, int $rowNumber, string $line): void
    {
        $targetTable = $fields['target_table'];
        $canonicalName = $fields['target_name'];
        $observedOn = $fields['observed_on'];
        $observedTime = $fields['observed_time'];

        try {
            if ($this->skipBelowHorizon($fields, $observedTime, $result, "Row {$rowNumber}", $line, $rowNumber)) {
                return;
            }

            // Updates the observation when it is already there
            [$obs, $updated] = $this->saveImportedObservation($fields, $observedTime);
            $obsId = $obs->id;
            $result->imported++;
            $result->importedObservations[] = [
                'row' => $rowNumber,
                'object' => $canonicalName,
                'date' => $observedOn,
                'id' => $obsId,
                'updated' => $updated,
            ];

            if ($updated) {
                $result->addSuccess(
                    "Row {$rowNumber}: Updated existing observation of '{$canonicalName}' on {$observedOn}.",
                    '',
                    $rowNumber
                );
            } else {
                $result->addSuccess(
                    "Row {$rowNumber}: Imported observation of '{$canonicalName}' on {$observedOn}.",
                    '',
                    $rowNumber
                );
            }

            if (self::isDrawingName($drawingName)) {
                $this->attachImportedDrawing($obs, [$drawingName], $result, "Row {$rowNumber}", $line, $rowNumber);
            }

            // Write deepsky visibility to detail table (for deepsky objects only)
            if ($visibility !== null && in_array($targetTable, ['objects', 'objectnames'])) {
                DB::table('deepsky_observation_details')->updateOrInsert(
                    ['observation_id' => $obsId],
                    ['visibility' => $visibility]
                );
                // Written without model events.
                Observation::clearResponseCache();
            }
        } catch (\Throwable $e) {
            $result->addError(
                "Row {$rowNumber}: Database error saving observation — {$e->getMessage()}",
                $line,
                $rowNumber
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Whether the 16th column names a drawing: a file name with an image
     * extension, not the rest of a description.
     */
    private static function isDrawingName(string $value): bool
    {
        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        return $value !== '' && strlen($value) <= 255 && in_array($extension, ImportDrawings::IMAGE_EXTENSIONS, true);
    }

    /** Parse DD-MM-YYYY (or YYYY-MM-DD) into YYYY-MM-DD string. */
    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }

        // DD-MM-YYYY (most common in CSV spec) or YYYY-MM-DD. Checked with
        // checkdate(): Carbon would roll 31-13-2026 over to 31-01-2027.
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $m)) {
            [$day, $month, $year] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m)) {
            [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } else {
            return null;
        }

        return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
    }

    /** Parse HH:MM into HH:MM:00 string, or null. */
    private function parseTime(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d:00', $m[1], $m[2]);
        }
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3]);
        }

        return null;
    }

    /**
     * Resolve an object name to (targetTable, targetKey, targetType, canonicalName).
     * Returns [null, null, null, null] when not found.
     *
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function resolveObject(string $name): array
    {
        return $this->resolvedObjects[$name] ??= $this->lookUpObject($name);
    }

    /**
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function lookUpObject(string $name): array
    {
        $candidates = $this->objectNameCandidates($name);
        foreach (self::TARGET_TABLES as $table) {
            $resolved = $this->resolveObjectInTable($table, $candidates);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return [null, null, null, null];
    }

    /**
     * Find a location by name for this user (case-insensitive). Returns null if not found.
     */
    private function findLocation(string $name): ?Location
    {
        return Location::where('user_id', $this->user->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Find an instrument by name for this user (case-insensitive). Returns null if not found.
     */
    private function findInstrument(string $name): ?Instrument
    {
        return Instrument::where('user_id', $this->user->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Find an eyepiece by name for this user (case-insensitive). Returns null if not found.
     */
    private function findEyepiece(string $name): ?Eyepiece
    {
        return Eyepiece::where('user_id', $this->user->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Find a filter by name for this user (case-insensitive). Returns null if not found.
     */
    private function findFilter(string $name): ?Filter
    {
        return Filter::where('user_id', $this->user->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Find a lens by name for this user (case-insensitive). Returns null if not found.
     */
    private function findLens(string $name): ?Lens
    {
        return Lens::where('user_id', $this->user->id)
            ->where('name', $name)
            ->first();
    }

    /** Parse integer within [min, max], return null if invalid. */
    private function parseInt(string $value, int $min, int $max): ?int
    {
        $value = trim($value);
        if (!is_numeric($value)) {
            return null;
        }
        $int = (int) $value;

        return ($int >= $min && $int <= $max) ? $int : null;
    }
}
