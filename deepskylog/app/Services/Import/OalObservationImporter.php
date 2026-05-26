<?php

namespace App\Services\Import;

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports observations from an OAL (OpenAstronomyLog) 2.1 XML file.
 *
 * Specification: https://github.com/openastronomylog/openastronomylog
 * Root element: <observations version="2.1">
 */
class OalObservationImporter
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Import from a raw XML string.
     */
    public function import(string $xmlContent): ImportResult
    {
        $result = new ImportResult();
        $result->addInfo('Starting OAL XML import.', '', 0);

        // ── Normalise encoding to UTF-8 ────────────────────────────────────────
        // OAL files produced by older tools (e.g. Cartes du Ciel) are often
        // saved in Latin-1/ISO-8859-1 even when the XML declaration says UTF-8.
        if (!mb_check_encoding($xmlContent, 'UTF-8')) {
            $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
            // Fix the encoding declaration so libxml doesn't re-interpret the bytes.
            $xmlContent = preg_replace('/encoding=["\'][^"\']*["\']/', 'encoding="UTF-8"', $xmlContent, 1) ?? $xmlContent;
        }

        // ── Parse XML ──────────────────────────────────────────────────────────

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            $errors = libxml_get_errors();
            foreach ($errors as $err) {
                $result->addError("XML parse error on line {$err->line}: " . trim($err->message));
            }
            libxml_clear_errors();

            return $result;
        }

        libxml_clear_errors();

        // ── Build lookup maps from the header sections ─────────────────────────

        $sites = $this->indexById($xml->sites->site ?? []);
        $scopes = $this->indexById($xml->scopes->scope ?? []);
        $eyepieces = $this->indexById($xml->eyepieces->eyepiece ?? []);
        $lenses = $this->indexById($xml->lenses->lens ?? []);
        $filters = $this->indexById($xml->filters->filter ?? []);
        $targets = $this->indexById($xml->targets->target ?? []);

        // ── Process each <observation> ─────────────────────────────────────────

        $obsNodes = $xml->observation ?? [];

        if (count($obsNodes) === 0) {
            $result->addWarning('No <observation> elements found in the file.');

            return $result;
        }

        $rowNumber = 0;
        foreach ($obsNodes as $obs) {
            $rowNumber++;

            try {
                $this->processObservation($obs, $sites, $scopes, $eyepieces, $lenses, $filters, $targets, $result, $rowNumber);
            } catch (\Throwable $e) {
                $result->addError("Observation {$rowNumber}: Unexpected error — {$e->getMessage()}", '', $rowNumber);
            }
        }

        $result->addInfo(
            "OAL import complete. Imported: {$result->imported}, Skipped: {$result->skipped}, Errors: {$result->errors}.",
            '',
            0
        );

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process a single <observation> node.
     *
     * @param  array<string, \SimpleXMLElement>  $sites
     * @param  array<string, \SimpleXMLElement>  $scopes
     * @param  array<string, \SimpleXMLElement>  $eyepieces
     * @param  array<string, \SimpleXMLElement>  $lenses
     * @param  array<string, \SimpleXMLElement>  $filters
     * @param  array<string, \SimpleXMLElement>  $targets
     */
    private function processObservation(
        \SimpleXMLElement $obs,
        array $sites,
        array $scopes,
        array $eyepieces,
        array $lenses,
        array $filters,
        array $targets,
        ImportResult $result,
        int $row
    ): void {
        // ── Resolve date/time ─────────────────────────────────────────────────

        $beginStr = trim((string) ($obs->begin ?? ''));
        if (empty($beginStr)) {
            $result->addError("Observation {$row}: Missing <begin> element, skipping.", '', $row);
            $result->skipped++;

            return;
        }

        [$observedOn, $observedTime] = $this->parseOalDateTime($beginStr);
        if ($observedOn === null) {
            $result->addError("Observation {$row}: Cannot parse date '{$beginStr}', skipping.", '', $row);
            $result->skipped++;

            return;
        }

        // ── Resolve target ────────────────────────────────────────────────────

        $targetRef = trim((string) ($obs->target ?? ''));
        $targetNode = !empty($targetRef) ? ($targets[$targetRef] ?? null) : null;
        $targetNameRaw = $targetNode ? trim((string) ($targetNode->name ?? $targetNode->designation ?? '')) : '';

        if (empty($targetNameRaw)) {
            $result->addError("Observation {$row}: Cannot resolve target (ref='{$targetRef}'), skipping.", '', $row);
            $result->skipped++;

            return;
        }

        [$targetTable, $targetKey, $targetType, $canonicalName] = $this->resolveObject($targetNameRaw);

        if ($targetTable === null) {
            $result->addWarning(
                "Observation {$row}: Target '{$targetNameRaw}' not found in database. Saving with original name.",
                '',
                $row
            );
            $targetTable = 'objects';
            $targetKey = $targetNameRaw;
            $targetType = 'deepsky';
            $canonicalName = $targetNameRaw;
        }

        // ── Resolve site → location ───────────────────────────────────────────

        $siteRef = trim((string) ($obs->site ?? ''));
        $siteNode = !empty($siteRef) ? ($sites[$siteRef] ?? null) : null;
        $locationId = null;

        if ($siteNode) {
            $siteName = trim((string) ($siteNode->name ?? ''));
            if (!empty($siteName)) {
                $locationId = $this->resolveOrCreateLocationFromOal($siteNode, $siteName, $result, $row);
                if ($locationId === null) {
                    $result->skipped++;
                    return;
                }
            }
        }

        // ── Resolve scope → instrument ────────────────────────────────────────

        $scopeRef = trim((string) ($obs->scope ?? ''));
        $scopeNode = !empty($scopeRef) ? ($scopes[$scopeRef] ?? null) : null;
        $instrumentId = null;

        if ($scopeNode) {
            $instrumentId = $this->resolveOrCreateInstrumentFromOal($scopeNode, $result, $row);
            if ($instrumentId === null) {
                $result->skipped++;
                return;
            }
        }

        // ── Resolve eyepiece ──────────────────────────────────────────────────

        $eyepieceRef = trim((string) ($obs->eyepiece ?? ''));
        $eyepieceNode = !empty($eyepieceRef) ? ($eyepieces[$eyepieceRef] ?? null) : null;
        $eyepieceId = null;

        if ($eyepieceNode) {
            $eyepieceId = $this->resolveOrCreateEyepieceFromOal($eyepieceNode, $result, $row);
            if ($eyepieceId === null) {
                $result->skipped++;
                return;
            }
        }

        // ── Resolve lens ──────────────────────────────────────────────────────

        $lensRef = trim((string) ($obs->lens ?? ''));
        $lensNode = !empty($lensRef) ? ($lenses[$lensRef] ?? null) : null;
        $lensId = null;

        if ($lensNode) {
            $lensId = $this->resolveOrCreateLensFromOal($lensNode, $result, $row);
            if ($lensId === null) {
                $result->skipped++;
                return;
            }
        }

        // ── Resolve filter ────────────────────────────────────────────────────

        $filterRef = trim((string) ($obs->filter ?? ''));
        $filterNode = !empty($filterRef) ? ($filters[$filterRef] ?? null) : null;
        $filterId = null;

        if ($filterNode) {
            $filterId = $this->resolveOrCreateFilterFromOal($filterNode, $result, $row);
            if ($filterId === null) {
                $result->skipped++;
                return;
            }
        }

        // ── Sky conditions ────────────────────────────────────────────────────

        $seeing = $this->parseInt((string) ($obs->seeing ?? ''), 1, 5);
        $faintStar = $this->parseFloat((string) ($obs->faintestStar ?? ''));
        $sqmNode = $obs->{'sky-quality'} ?? null;
        $sqm = $sqmNode ? $this->parseFloat((string) $sqmNode) : null;

        $limitingMagnitude = $faintStar;

        // ── Result / description ──────────────────────────────────────────────

        $description = '';
        $language = 'en';

        $resultNode = $obs->result ?? null;
        if ($resultNode) {
            // OAL may have multiple <result> nodes; use the first with a description
            if ($resultNode->count() > 1) {
                foreach ($resultNode as $r) {
                    $desc = trim((string) ($r->description ?? ''));
                    if (!empty($desc)) {
                        $description = $desc;
                        $lang = trim((string) ($r->attributes()['lang'] ?? ''));
                        if (!empty($lang)) {
                            $language = strtolower(substr($lang, 0, 10));
                        }
                        break;
                    }
                }
            } else {
                $description = trim((string) ($resultNode->description ?? ''));
                $lang = trim((string) ($resultNode->attributes()['lang'] ?? ''));
                if (!empty($lang)) {
                    $language = strtolower(substr($lang, 0, 10));
                }
            }
        }

        // ── Save or update observation ──────────────────────────────────────────

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
            'transparency' => null,
            'limiting_magnitude' => $limitingMagnitude,
            'sqm' => $sqm,
            'language' => $language,
            'description' => $description,
            'target_table' => $targetTable,
            'target_key' => $targetKey,
            'target_type' => $targetType,
            'target_name' => $canonicalName,
            'has_drawing' => false,
            'legacy_source' => null,
        ];

        // Duplicate check: same observer + object + date + time
        /** @var Observation|null $existing */
        $existing = Observation::where('observer_user_id', $this->user->id)
            ->where('target_name', $canonicalName)
            ->whereDate('observed_on', $observedOn)
            ->whereNull('legacy_source')
            ->when($observedTime !== null, fn($q) => $q->where('observed_time', $observedTime))
            ->first();

        if ($existing) {
            $existing->update($fields);
            $result->imported++;
            $result->importedObservations[] = [
                'row' => $row,
                'object' => $canonicalName,
                'date' => $observedOn,
                'id' => $existing->id,
                'updated' => true,
            ];
            $result->addSuccess(
                "Observation {$row}: Updated existing observation of '{$canonicalName}' on {$observedOn}.",
                '',
                $row
            );
        } else {
            $observation = Observation::create($fields);
            $result->imported++;
            $result->importedObservations[] = [
                'row' => $row,
                'object' => $canonicalName,
                'date' => $observedOn,
                'id' => $observation->id,
                'updated' => false,
            ];
            $result->addSuccess(
                "Observation {$row}: Imported '{$canonicalName}' on {$observedOn}.",
                '',
                $row
            );
        }
    }

    /**
     * Parse an OAL ISO 8601 date/time string into ['Y-m-d', 'H:i:s'|null].
     *
     * @return array{string|null, string|null}
     */
    private function parseOalDateTime(string $value): array
    {
        $value = trim($value);
        // ISO 8601: 2005-01-21T20:45:00 or 2005-01-21T20:45:00Z or with offset
        try {
            $dt = Carbon::parse($value);

            return [$dt->format('Y-m-d'), $dt->format('H:i:s')];
        } catch (\Throwable $_) {
        }

        // Date only: 2005-01-21
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return [$value, null];
        }

        return [null, null];
    }

    /**
     * Index SimpleXML elements by their `id` attribute.
     *
     * @param  iterable<\SimpleXMLElement>|\SimpleXMLElement  $elements
     * @return array<string, \SimpleXMLElement>
     */
    private function indexById($elements): array
    {
        $map = [];
        foreach ($elements as $el) {
            $id = trim((string) ($el->attributes()['id'] ?? ''));
            if ($id !== '') {
                $map[$id] = $el;
            }
        }

        return $map;
    }

    // ── Equipment resolution helpers ──────────────────────────────────────────

    private function resolveOrCreateLocationFromOal(\SimpleXMLElement $node, string $name, ImportResult $result, int $row): ?int
    {
        $loc = Location::where('user_id', $this->user->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($loc) {
            return $loc->id;
        }

        // Extract coordinates if present
        $latitude = $this->parseOalAngle($node->latitude ?? null);
        $longitude = $this->parseOalAngle($node->longitude ?? null);
        $elevation = isset($node->elevation) ? $this->parseFloat((string) $node->elevation) : null;
        $timezone = trim((string) ($node->timezone ?? config('app.timezone', 'UTC')));
        if (empty($timezone)) {
            $timezone = config('app.timezone', 'UTC');
        }

        try {
            $loc = Location::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'country' => '',
                'timezone' => $timezone,
                'latitude' => $latitude ?? 0.0,
                'longitude' => $longitude ?? 0.0,
                'limitingMagnitude' => null,
                'skyBackground' => null,
                'elevation' => $elevation,
                'active' => true,
                'hidden' => false,
            ]);

            $result->newLocations[$name] = $loc->slug;
            $result->addInfo("Observation {$row}: Created new location '{$name}' (id={$loc->id}) from OAL site data.", '', $row);

            return $loc->id;
        } catch (\Throwable $e) {
            $result->addError("Observation {$row}: Could not create location '{$name}': {$e->getMessage()}", '', $row);

            return null;
        }
    }

    private function resolveOrCreateInstrumentFromOal(\SimpleXMLElement $node, ImportResult $result, int $row): ?int
    {
        $model = trim((string) ($node->model ?? ''));
        $vendor = trim((string) ($node->vendor ?? ''));
        $name = trim("{$vendor} {$model}");
        if (empty($name)) {
            return null;
        }

        $inst = Instrument::where('user_id', $this->user->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($inst) {
            return $inst->id;
        }

        // Also try model alone
        if (!empty($model)) {
            $inst = Instrument::where('user_id', $this->user->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($model)])
                ->first();
            if ($inst) {
                return $inst->id;
            }
        }

        $apertureMm = isset($node->aperture) ? $this->parseFloat((string) $node->aperture) : null;
        $focalLengthMm = isset($node->focalLength) ? $this->parseFloat((string) $node->focalLength) : null;

        // Map OAL scope type attribute to instrument_type_id (fallback: 6 = Other)
        $oalType = strtolower(trim((string) ($node->attributes()['type'] ?? $node->type ?? '')));
        $typeMap = [
            'refractor' => 3,
            'reflector' => 4,
            'binoculars' => 2,
            'cassegrain' => 7,
            'sct' => 10,
            'kutter' => 8,
            'maksutov' => 9,
            'finderscope' => 5,
        ];
        $instrumentTypeId = $typeMap[$oalType] ?? 6;

        try {
            $inst = Instrument::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'aperture_mm' => $apertureMm ?? 200,
                'focal_length_mm' => $focalLengthMm,
                'make_id' => 1,
                'instrument_type_id' => $instrumentTypeId,
                'mount_type_id' => 3,
                'active' => true,
            ]);

            $result->newInstruments[$name] = $inst->slug;
            $result->addInfo("Observation {$row}: Created new instrument '{$name}' (id={$inst->id}).", '', $row);

            return $inst->id;
        } catch (\Throwable $e) {
            $result->addError("Observation {$row}: Could not create instrument '{$name}': {$e->getMessage()}", '', $row);

            return null;
        }
    }

    private function resolveOrCreateEyepieceFromOal(\SimpleXMLElement $node, ImportResult $result, int $row): ?int
    {
        $model = trim((string) ($node->model ?? ''));
        $vendor = trim((string) ($node->vendor ?? ''));
        $name = trim("{$vendor} {$model}");
        if (empty($name)) {
            return null;
        }

        $ep = Eyepiece::where('user_id', $this->user->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if (!$ep && !empty($model)) {
            $ep = Eyepiece::where('user_id', $this->user->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($model)])
                ->first();
        }

        if ($ep) {
            return $ep->id;
        }

        $fl = isset($node->focalLength) ? $this->parseFloat((string) $node->focalLength) : null;
        $maxFl = isset($node->maxFocalLength) ? $this->parseFloat((string) $node->maxFocalLength) : null;
        $afov = isset($node->apparentFOV) ? $this->parseFloat((string) $node->apparentFOV) : null;

        try {
            $ep = Eyepiece::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'focal_length_mm' => $fl ?? 25,
                'max_focal_length_mm' => $maxFl,
                'apparentFOV' => $afov,
                'make_id' => 1,
                'type_id' => 1,
                'active' => true,
            ]);

            $result->newEyepieces[$name] = $ep->slug;
            $result->addInfo("Observation {$row}: Created new eyepiece '{$name}' (id={$ep->id}).", '', $row);

            return $ep->id;
        } catch (\Throwable $e) {
            $result->addError("Observation {$row}: Could not create eyepiece '{$name}': {$e->getMessage()}", '', $row);

            return null;
        }
    }

    private function resolveOrCreateLensFromOal(\SimpleXMLElement $node, ImportResult $result, int $row): ?int
    {
        $model = trim((string) ($node->model ?? ''));
        $vendor = trim((string) ($node->vendor ?? ''));
        $name = trim("{$vendor} {$model}");
        if (empty($name)) {
            return null;
        }

        $lens = Lens::where('user_id', $this->user->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if (!$lens && !empty($model)) {
            $lens = Lens::where('user_id', $this->user->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($model)])
                ->first();
        }

        if ($lens) {
            return $lens->id;
        }

        $factor = isset($node->factor) ? $this->parseFloat((string) $node->factor) : null;

        try {
            $lens = Lens::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'factor' => $factor ?? 2.0,
                'make_id' => 1,
                'active' => true,
            ]);

            $result->newLenses[$name] = $lens->slug;
            $result->addInfo("Observation {$row}: Created new lens '{$name}' (id={$lens->id}).", '', $row);

            return $lens->id;
        } catch (\Throwable $e) {
            $result->addError("Observation {$row}: Could not create lens '{$name}': {$e->getMessage()}", '', $row);

            return null;
        }
    }

    private function resolveOrCreateFilterFromOal(\SimpleXMLElement $node, ImportResult $result, int $row): ?int
    {
        $model = trim((string) ($node->model ?? ''));
        $vendor = trim((string) ($node->vendor ?? ''));
        $name = trim("{$vendor} {$model}");
        if (empty($name)) {
            return null;
        }

        $filter = Filter::where('user_id', $this->user->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if (!$filter && !empty($model)) {
            $filter = Filter::where('user_id', $this->user->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($model)])
                ->first();
        }

        if ($filter) {
            return $filter->id;
        }

        try {
            $filter = Filter::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'make_id' => 1,
                'type_id' => 1,
                'active' => true,
            ]);

            $result->newFilters[$name] = $filter->slug;
            $result->addInfo("Observation {$row}: Created new filter '{$name}' (id={$filter->id}).", '', $row);

            return $filter->id;
        } catch (\Throwable $e) {
            $result->addError("Observation {$row}: Could not create filter '{$name}': {$e->getMessage()}", '', $row);

            return null;
        }
    }

    // ── Object resolution ──────────────────────────────────────────────────────

    /**
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function resolveObject(string $name): array
    {
        $lower = mb_strtolower(trim($name));
        $slug = Str::slug($name, '-');
        $compact = str_replace([' ', '-'], '', $lower);
        $noZeros = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower) ?? $lower;
        $candidates = array_values(array_unique(array_filter([$lower, $slug, $compact, $noZeros, str_replace('-', '', $slug)])));

        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                return ['objects', $on->objectname, 'deepsky', $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->whereRaw('LOWER(objectname) = ?', [$cand])->orWhereRaw('LOWER(altname) = ?', [$cand])->first();
            if ($on) {
                return ['objects', $on->objectname, 'deepsky', $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                return ['objects', $o->name, 'deepsky', $o->name];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
            if ($o) {
                return ['objects', $o->name, 'deepsky', $o->name];
            }
        }
        foreach ($candidates as $cand) {
            try {
                $co = DB::table('cometobjects')->whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
                if ($co) {
                    return ['cometobjects', (string) $co->id, 'comet', $co->name];
                }
            } catch (\Throwable $_) {
            }
        }

        return [null, null, null, null];
    }

    // ── Numeric helpers ────────────────────────────────────────────────────────

    private function parseInt(string $value, int $min, int $max): ?int
    {
        $value = trim($value);
        if (!is_numeric($value)) {
            return null;
        }
        $int = (int) $value;

        return ($int >= $min && $int <= $max) ? $int : null;
    }

    private function parseFloat(string $value): ?float
    {
        $value = trim(str_replace(',', '.', $value));
        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Parse an OAL angle element. The element may carry a unit attribute
     * ('deg' | 'rad'). OAL typically uses degrees.
     */
    private function parseOalAngle(?\SimpleXMLElement $node): ?float
    {
        if ($node === null) {
            return null;
        }
        $val = $this->parseFloat((string) $node);
        $unit = trim((string) ($node->attributes()['unit'] ?? 'deg'));

        if ($val === null) {
            return null;
        }

        return $unit === 'rad' ? rad2deg($val) : $val;
    }

}
