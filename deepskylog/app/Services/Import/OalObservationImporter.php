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
    use SavesImportedObservations;

    private const OAL_TARGET_METADATA_PREFIX = 'DeepskyLog:';

    private User $user;
    private OalSchemaValidator $validator;

    private ?string $defaultNamespace = null;

    /**
     * The targets resolved during this import, by name, type hint and
     * metadata: a file usually holds the same object many times, and a lookup
     * takes up to 20 queries per table.
     *
     * @var array<string, array{string|null, string|null, string|null, string|null}>
     */
    private array $resolvedObjects = [];

    public function __construct(User $user, ?OalSchemaValidator $validator = null)
    {
        $this->user = $user;
        $this->validator = $validator ?? app(OalSchemaValidator::class);
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

        $schemaErrors = $this->validator->validate($xmlContent);
        if ($schemaErrors !== []) {
            foreach ($schemaErrors as $error) {
                $result->addError('OAL 2.1 schema validation error: ' . $error);
            }

            return $result;
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

        $this->defaultNamespace = $xml->getNamespaces(true)[''] ?? null;
        $xml = $this->withDefaultNamespace($xml);

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
        $obs = $this->withDefaultNamespace($obs);

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
        $targetNode = !empty($targetRef) ? $this->withDefaultNamespace($targets[$targetRef] ?? null) : null;
        $targetNameRaw = $targetNode ? trim((string) ($targetNode->name ?? $targetNode->designation ?? '')) : '';
        $targetTypeHint = $this->extractOalTargetTypeHint($targetNode);
        $targetMetadata = $this->extractOalTargetMetadata($targetNode);

        if (empty($targetNameRaw)) {
            $result->addError("Observation {$row}: Cannot resolve target (ref='{$targetRef}'), skipping.", '', $row);
            $result->skipped++;

            return;
        }

        [$targetTable, $targetKey, $targetType, $canonicalName] = $this->resolveObject($targetNameRaw, $targetTypeHint, $targetMetadata);

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
        $siteNode = !empty($siteRef) ? $this->withDefaultNamespace($sites[$siteRef] ?? null) : null;
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
        $scopeNode = !empty($scopeRef) ? $this->withDefaultNamespace($scopes[$scopeRef] ?? null) : null;
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
        $eyepieceNode = !empty($eyepieceRef) ? $this->withDefaultNamespace($eyepieces[$eyepieceRef] ?? null) : null;
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
        $lensNode = !empty($lensRef) ? $this->withDefaultNamespace($lenses[$lensRef] ?? null) : null;
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
        $filterNode = !empty($filterRef) ? $this->withDefaultNamespace($filters[$filterRef] ?? null) : null;
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

        // OAL requires a time: an observation without one is exported at
        // midnight UT, so that is checked as an observation without a time.
        if ($this->skipBelowHorizon($fields, $observedTime === '00:00:00' ? null : $observedTime, $result, "Observation {$row}", '', $row)) {
            return;
        }

        // Updates the observation when it is already there
        [$observation, $updated] = $this->saveImportedObservation($fields, $observedTime);
        $result->imported++;
        $result->importedObservations[] = [
            'row' => $row,
            'object' => $canonicalName,
            'date' => $observedOn,
            'id' => $observation->id,
            'updated' => $updated,
        ];
        $result->addSuccess(
            $updated
                ? "Observation {$row}: Updated existing observation of '{$canonicalName}' on {$observedOn}."
                : "Observation {$row}: Imported '{$canonicalName}' on {$observedOn}.",
            '',
            $row
        );
    }

    /**
     * Parse an OAL ISO 8601 date/time string into ['Y-m-d', 'H:i:s'|null].
     *
     * @return array{string|null, string|null}
     */
    private function parseOalDateTime(string $value): array
    {
        $value = trim($value);
        // Date only: 2005-01-21
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return [$value, null];
        }

        // ISO 8601: 2005-01-21T20:45:00 or 2005-01-21T20:45:00Z or with offset
        try {
            // Stored in UT; a value without an offset is taken as UT.
            $dt = Carbon::parse($value, 'UTC')->utc();

            return [$dt->format('Y-m-d'), $dt->format('H:i:s')];
        } catch (\Throwable $_) {
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
            ->where('name', $name)
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
                'limitingMagnitude' => -999,
                'skyBackground' => -999,
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
            ->where('name', $name)
            ->first();

        if ($inst) {
            return $inst->id;
        }

        // Also try model alone
        if (!empty($model)) {
            $inst = Instrument::where('user_id', $this->user->id)
                ->where('name', $model)
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
            ->where('name', $name)
            ->first();
        if (!$ep && !empty($model)) {
            $ep = Eyepiece::where('user_id', $this->user->id)
                ->where('name', $model)
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
            ->where('name', $name)
            ->first();
        if (!$lens && !empty($model)) {
            $lens = Lens::where('user_id', $this->user->id)
                ->where('name', $model)
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
            ->where('name', $name)
            ->first();
        if (!$filter && !empty($model)) {
            $filter = Filter::where('user_id', $this->user->id)
                ->where('name', $model)
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
                'color_id' => 1,
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
    private function resolveObject(string $name, ?string $targetTypeHint = null, array $targetMetadata = []): array
    {
        $key = serialize([$name, $targetTypeHint, $targetMetadata]);

        return $this->resolvedObjects[$key] ??= $this->lookUpObject($name, $targetTypeHint, $targetMetadata);
    }

    /**
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function lookUpObject(string $name, ?string $targetTypeHint, array $targetMetadata): array
    {
        $lower = mb_strtolower(trim($name));
        $slug = Str::slug($name, '-');
        $compact = str_replace([' ', '-'], '', $lower);
        $noZeros = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower) ?? $lower;
        $metadataKey = trim((string) ($targetMetadata['target_key'] ?? ''));
        if ($metadataKey !== '') {
            $metadataLower = mb_strtolower($metadataKey);
            $candidates = array_values(array_unique(array_filter([$lower, $slug, $compact, $noZeros, str_replace('-', '', $slug), $metadataLower, Str::slug($metadataKey, '-')])));
        } else {
            $candidates = array_values(array_unique(array_filter([$lower, $slug, $compact, $noZeros, str_replace('-', '', $slug)])));
        }

        $preferredTables = [];
        $metadataTable = strtolower(trim((string) ($targetMetadata['target_table'] ?? '')));
        if ($metadataTable !== '') {
            $preferredTables[] = $metadataTable;
        }

        foreach ($this->hintedTargetTables($targetTypeHint) as $table) {
            $preferredTables[] = $table;
        }

        foreach (array_values(array_unique($preferredTables)) as $table) {
            $resolved = $this->resolveObjectInTable($table, $candidates);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach (['objects', 'planets', 'lunar_features', 'moons', 'asteroids', 'cometobjects'] as $table) {
            if (in_array($table, $preferredTables, true)) {
                continue;
            }

            $resolved = $this->resolveObjectInTable($table, $candidates);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return [null, null, null, null];
    }

    private function extractOalTargetTypeHint(?\SimpleXMLElement $targetNode): ?string
    {
        if ($targetNode === null) {
            return null;
        }

        $attributes = $targetNode->attributes('http://www.w3.org/2001/XMLSchema-instance', true);
        $type = trim((string) ($attributes['type'] ?? ''));
        if ($type === '') {
            return null;
        }

        return strtolower(Str::after($type, ':'));
    }

    /**
     * @return array{target_table?: string, target_key?: string, target_type?: string}
     */
    private function extractOalTargetMetadata(?\SimpleXMLElement $targetNode): array
    {
        if ($targetNode === null) {
            return [];
        }

        $notes = trim((string) ($targetNode->notes ?? ''));
        if ($notes === '' || !str_starts_with($notes, self::OAL_TARGET_METADATA_PREFIX)) {
            return [];
        }

        $payload = substr($notes, strlen(self::OAL_TARGET_METADATA_PREFIX));
        $metadata = [];
        foreach (explode(';', $payload) as $entry) {
            [$key, $value] = array_pad(explode('=', $entry, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);
            if ($key !== '' && $value !== '') {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    /**
     * @return list<string>
     */
    private function hintedTargetTables(?string $targetTypeHint): array
    {
        return match ($targetTypeHint) {
            'comettargettype' => ['cometobjects'],
            'minorplanettargettype' => ['asteroids'],
            'moontargettype' => ['moons'],
            'suntargettype', 'planettargettype' => ['planets'],
            default => [],
        };
    }

    /**
     * @param  list<string>  $candidates
     * @return array{string, string, string, string}|null
     */
    private function resolveObjectInTable(string $table, array $candidates): ?array
    {
        return match ($table) {
            'objects' => $this->resolveDeepSkyObject($candidates),
            'planets' => $this->resolveNamedTarget('planets', $candidates, ['name', 'designation'], fn(object $row): array => [
                'planets',
                $this->targetKeyFromRow($row),
                'planet',
                (string) $row->name,
            ]),
            'moons' => $this->resolveNamedTarget('moons', $candidates, ['name', 'designation'], fn(object $row): array => [
                'moons',
                $this->targetKeyFromRow($row),
                'lunar',
                (string) $row->name,
            ]),
            'lunar_features' => $this->resolveNamedTarget('lunar_features', $candidates, ['name'], fn(object $row): array => [
                'lunar_features',
                $this->targetKeyFromRow($row),
                'lunar',
                (string) $row->name,
            ]),
            'asteroids' => $this->resolveNamedTarget('asteroids', $candidates, ['name', 'designation'], fn(object $row): array => [
                'asteroids',
                $this->targetKeyFromRow($row),
                'solarsystem',
                (string) $row->name,
            ]),
            // Comets are stored by id (see App\Models\Observation).
            'cometobjects' => $this->resolveNamedTarget('cometobjects', $candidates, ['name'], fn(object $row): array => [
                'cometobjects',
                (string) $row->id,
                'comet',
                (string) $row->name,
            ]),
            default => null,
        };
    }

    /**
     * @param  list<string>  $candidates
     * @return array{string, string, string, string}|null
     */
    private function resolveDeepSkyObject(array $candidates): ?array
    {
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                return ['objects', (string) $on->objectname, 'deepsky', (string) $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('objectname', $cand)->orWhere('altname', $cand)->first();
            if ($on) {
                return ['objects', (string) $on->objectname, 'deepsky', (string) $on->objectname];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                return ['objects', (string) $o->name, 'deepsky', (string) $o->name];
            }
        }
        foreach ($candidates as $cand) {
            $o = DB::table('objects')->where('name', $cand)->first();
            if ($o) {
                return ['objects', (string) $o->name, 'deepsky', (string) $o->name];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $candidates
     * @param  list<string>  $nameColumns
     * @param  callable(object): array{string, string, string, string}  $mapper
     * @return array{string, string, string, string}|null
     */
    private function resolveNamedTarget(string $table, array $candidates, array $nameColumns, callable $mapper): ?array
    {
        foreach ($candidates as $cand) {
            try {
                $row = DB::table($table)->where('slug', $cand)->first();
            } catch (\Throwable $_) {
                $row = null;
            }

            if ($row) {
                return $mapper($row);
            }
        }

        foreach ($nameColumns as $column) {
            foreach ($candidates as $cand) {
                try {
                    $row = DB::table($table)->where($column, $cand)->first();
                } catch (\Throwable $_) {
                    $row = null;
                }

                if ($row) {
                    return $mapper($row);
                }
            }
        }

        return null;
    }

    private function targetKeyFromRow(object $row): string
    {
        $slug = trim((string) ($row->slug ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        $name = trim((string) ($row->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return (string) ($row->id ?? '');
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

    private function withDefaultNamespace(?\SimpleXMLElement $node): ?\SimpleXMLElement
    {
        if ($node === null) {
            return null;
        }

        if ($this->defaultNamespace === null) {
            return $node;
        }

        $children = $node->children($this->defaultNamespace);

        return count($children) > 0 ? $children : $node;
    }

}
