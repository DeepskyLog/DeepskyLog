<?php

namespace App\Services\Import;

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\DeepskyObjectCreator;
use App\Support\Html;
use App\Support\Timezones;
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

    /**
     * The deepskytypes code of an object created from an OAL deep-sky target,
     * by xsi:type. deepSkyNA (not available) and deepSkySC (star cloud) have
     * no DeepskyLog type, so such a target is not created.
     */
    private const DEEPSKY_TYPES = [
        'deepskyas' => 'ASTER',
        'deepskycg' => 'GALCL',
        'deepskydn' => 'DRKNB',
        'deepskyds' => 'DS',
        'deepskyms' => 'DS',
        'deepskygc' => 'GLOCL',
        'deepskygn' => 'BRTNB',
        'deepskygx' => 'GALXY',
        'deepskyoc' => 'OPNCL',
        'deepskypn' => 'PLNNB',
        'deepskyqs' => 'QUASR',
    ];

    /** 2.5 log10(3600): mag/arcsec² minus this is mag/arcmin² (the unit of objects.subr). */
    private const ARCSEC2_TO_ARCMIN2 = 8.8907;

    /** The instrument type `Naked Eye` (instrument_types.id). */
    private const NAKED_EYE_TYPE = 1;

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
            [$targetTable, $targetKey, $targetType, $canonicalName] = $this->resolveAliases($targetNode, $targetTypeHint);
        }

        // A target that is not in the database is added when the file defines
        // it: a deep-sky type and a position. It is created with the
        // observation, below, so a skipped observation adds no object.
        $newObject = null;
        if ($targetTable === null) {
            $newObject = $this->oalObjectValues($targetNode, $targetNameRaw, $targetTypeHint, $result, $row);
            if ($newObject === null) {
                $result->skipped++;

                return;
            }
            $targetTable = 'objects';
            $targetKey = $targetNameRaw;
            $targetType = 'deepsky';
            $canonicalName = $targetNameRaw;
        }

        // ── Result / description ──────────────────────────────────────────────

        $description = '';
        $language = 'en';
        // The rating of a deep-sky result (findingsDeepSkyType) is the
        // visibility, on the same scale; 99 (unknown) is left out.
        $visibility = null;

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
                        $visibility = $this->parseInt((string) ($r->rating ?? ''), 1, 7);
                        break;
                    }
                }
            } else {
                $description = trim((string) ($resultNode->description ?? ''));
                $lang = trim((string) ($resultNode->attributes()['lang'] ?? ''));
                if (!empty($lang)) {
                    $language = strtolower(substr($lang, 0, 10));
                }
                $visibility = $this->parseInt((string) ($resultNode->rating ?? ''), 1, 7);
            }
        }

        if (Html::isBlank($description)) {
            $result->addError("Observation {$row}: Description is empty, skipping.", '', $row);
            $result->skipped++;

            return;
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

        // OAL leaves out the scope of an observation with the naked eye
        $instrumentId = $scopeNode
            ? $this->resolveOrCreateInstrumentFromOal($scopeNode, $result, $row)
            : $this->resolveOrCreateNakedEyeInstrument($result, $row);
        if ($instrumentId === null) {
            $result->skipped++;
            return;
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

        if ($newObject !== null) {
            DB::beginTransaction();
        }

        try {
            if ($newObject !== null) {
                // The horizon check reads the position of the new object
                $object = app(DeepskyObjectCreator::class)->create($newObject);
            }

            // OAL requires a time: an observation without one is exported at
            // midnight UT, so that is checked as an observation without a time.
            if ($this->skipBelowHorizon($fields, $observedTime === '00:00:00' ? null : $observedTime, $result, "Observation {$row}", '', $row)) {
                if ($newObject !== null) {
                    DB::rollBack();
                }

                return;
            }

            // Updates the observation when it is already there
            [$observation, $updated] = $this->saveImportedObservation($fields, $observedTime);

            if ($newObject !== null) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if ($newObject !== null) {
                DB::rollBack();
            }

            throw $e;
        }

        if ($visibility !== null && $targetTable === 'objects') {
            DB::table('deepsky_observation_details')->updateOrInsert(
                ['observation_id' => $observation->id],
                ['visibility' => $visibility]
            );
            // Written without model events.
            Observation::clearResponseCache();
        }

        if ($newObject !== null) {
            $this->resolvedObjects[$this->objectCacheKey($targetNameRaw, $targetTypeHint, $targetMetadata)] = ['objects', $object->name, 'deepsky', $object->name];
            $result->newObjects[$object->name] = $object->slug;
            $result->addInfo("Observation {$row}: Added '{$object->name}' to the DeepskyLog database from the target in the file.", '', $row);
        }
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
        // OAL gives the offset in minutes from UT; a location needs a zone name.
        $timezone = trim((string) ($node->timezone ?? ''));
        if (!Timezones::isIdentifier($timezone)) {
            $timezone = preg_match('/^[+-]?\d+$/', $timezone) === 1
                ? Timezones::fromOffset((int) $timezone, $latitude, $longitude)
                : Timezones::orUtc(config('app.timezone', 'UTC'));
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
        // "vendor model", unless the model already starts with the vendor
        // (vendor Celestron, model "Celestron C8").
        $name = $vendor !== '' && mb_stripos($model, $vendor) === 0 ? $model : trim("{$vendor} {$model}");
        if (empty($name)) {
            $result->addError("Observation {$row}: The scope has no model or vendor, skipping.", '', $row);

            return null;
        }

        // Binoculars and finders (fixedMagnificationOpticsType) have a
        // magnification instead of a focal length.
        $magnification = isset($node->magnification) ? $this->parseFloat((string) $node->magnification) : null;
        $instrumentTypeId = $this->oalScopeType((string) ($node->type ?? ''), $magnification !== null);
        if ($instrumentTypeId === self::NAKED_EYE_TYPE) {
            return $this->resolveOrCreateNakedEyeInstrument($result, $row);
        }

        // The aperture column holds whole millimetres.
        $aperture = isset($node->aperture) ? $this->parseFloat((string) $node->aperture) : null;
        $apertureMm = $aperture !== null ? max(1, (int) round($aperture)) : null;

        // An instrument with the name and about the aperture (or no aperture in
        // the file) is the scope; one with another aperture is another scope,
        // which is added under a name with its aperture.
        $sizedName = $apertureMm !== null ? "{$name} ({$apertureMm} mm)" : null;
        $existing = Instrument::where('user_id', $this->user->id)
            ->whereIn('name', array_values(array_unique(array_filter([$name, $model, $sizedName]))))
            ->orderBy('id')
            ->get();
        $match = $existing->first(fn (Instrument $inst) => $apertureMm === null || abs((int) $inst->aperture_mm - $apertureMm) <= 1);
        if ($match) {
            return $match->id;
        }
        if ($existing->isNotEmpty()) {
            $name = $sizedName;
        }

        $focalLengthMm = isset($node->focalLength) ? $this->parseFloat((string) $node->focalLength) : null;
        $trueField = $this->parseOalAngle($node->trueField ?? null);
        $makeId = $vendor !== '' ? DB::table('instrument_makes')->where('name', $vendor)->value('id') : null;

        try {
            $inst = Instrument::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'aperture_mm' => $apertureMm ?? 200,
                'focal_length_mm' => $magnification === null ? $focalLengthMm : null,
                'fixedMagnification' => $magnification !== null ? max(1, (int) round($magnification)) : null,
                'field_of_view_deg' => $magnification !== null && $trueField !== null && $trueField > 0 ? $trueField : null,
                // The unknown make and mount (empty names); OAL has no mount.
                'make_id' => $makeId ?? 1,
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

    /**
     * The instrument_types id of an OAL scope type: the letters of the Deep Sky
     * Liste the schema recommends (A naked eye, B binoculars, N Newton,
     * R refractor, C Cassegrain, S Schmidt-Cassegrain, K Kutter, M Maksutov),
     * or a name. Unknown or missing: binoculars for fixed-magnification optics,
     * else Other.
     */
    private function oalScopeType(string $type, bool $fixedMagnification): int
    {
        $type = strtolower(trim($type));
        $types = [
            'a' => 1, 'naked eye' => 1,
            'b' => 2, 'binoculars' => 2, 'binocular' => 2,
            'r' => 3, 'refractor' => 3,
            'n' => 4, 'newton' => 4, 'newtonian' => 4, 'reflector' => 4, 'dobson' => 4,
            'finder' => 5, 'finderscope' => 5,
            'other' => 6,
            'c' => 7, 'cassegrain' => 7,
            'k' => 8, 'kutter' => 8, 'schiefspiegler' => 8,
            'm' => 9, 'maksutov' => 9,
            's' => 10, 'sct' => 10, 'schmidt-cassegrain' => 10, 'schmidt cassegrain' => 10,
        ];

        return $types[$type] ?? ($fixedMagnification ? 2 : 6);
    }

    /**
     * The observer's naked-eye instrument, created when there is none.
     */
    private function resolveOrCreateNakedEyeInstrument(ImportResult $result, int $row): ?int
    {
        $inst = Instrument::where('user_id', $this->user->id)
            ->where('instrument_type_id', self::NAKED_EYE_TYPE)
            ->orderByDesc('active')
            ->orderBy('id')
            ->first();

        if ($inst) {
            return $inst->id;
        }

        $name = 'Naked eye';

        try {
            $inst = Instrument::create([
                'user_id' => $this->user->id,
                'name' => $name,
                'observer' => $this->user->username,
                'aperture_mm' => 7,
                'make_id' => 1,
                'instrument_type_id' => self::NAKED_EYE_TYPE,
                'focal_length_mm' => 1,
                'fixedMagnification' => 1,
                'mount_type_id' => 1,
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
            $result->addError("Observation {$row}: The eyepiece has no model or vendor, skipping.", '', $row);

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
            $result->addError("Observation {$row}: The lens has no model or vendor, skipping.", '', $row);

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
            $result->addError("Observation {$row}: The filter has no model or vendor, skipping.", '', $row);

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
        return $this->resolvedObjects[$this->objectCacheKey($name, $targetTypeHint, $targetMetadata)]
            ??= $this->lookUpObject($name, $targetTypeHint, $targetMetadata);
    }

    private function objectCacheKey(string $name, ?string $targetTypeHint, array $targetMetadata): string
    {
        return serialize([$name, $targetTypeHint, $targetMetadata]);
    }

    /**
     * The first of the target's <alias> names that is in the database.
     *
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function resolveAliases(\SimpleXMLElement $targetNode, ?string $targetTypeHint): array
    {
        foreach ($targetNode->alias ?? [] as $alias) {
            $alias = trim((string) $alias);
            if ($alias === '') {
                continue;
            }
            $resolved = $this->resolveObject($alias, $targetTypeHint);
            if ($resolved[0] !== null) {
                return $resolved;
            }
        }

        return [null, null, null, null];
    }

    /**
     * The columns of a new deep-sky object from an OAL target that is not in
     * the database (for DeepskyObjectCreator), or null, with the error added,
     * when the file does not define it well enough: it needs a deep-sky type
     * DeepskyLog has and a position.
     *
     * @return array<string, mixed>|null
     */
    private function oalObjectValues(\SimpleXMLElement $targetNode, string $name, ?string $targetTypeHint, ImportResult $result, int $row): ?array
    {
        $notFound = "Observation {$row}: Target '{$name}' is not in the DeepskyLog database";

        // Only a deep-sky object is added from the file; a Solar System target
        // has to be in the database.
        $solarSystem = [
            'comettargettype' => 'Comet',
            'moontargettype' => 'Moon or lunar feature',
            'planettargettype' => 'Planet',
            'minorplanettargettype' => 'Asteroid',
            'suntargettype' => 'Sun',
        ];
        if (isset($solarSystem[$targetTypeHint ?? ''])) {
            $result->addError("Observation {$row}: {$solarSystem[$targetTypeHint]} '{$name}' is not in the DeepskyLog database, skipping.", '', $row);

            return null;
        }

        $type = self::DEEPSKY_TYPES[$targetTypeHint ?? ''] ?? null;
        if ($type === null) {
            $result->addError(
                $targetTypeHint !== null && str_starts_with($targetTypeHint, 'deepsky')
                    ? "{$notFound}, and its type in the file (oal:{$targetTypeHint}) has no DeepskyLog object type, skipping."
                    : "{$notFound}, skipping. A deep-sky object is added when the file gives its deep-sky type and position.",
                '',
                $row
            );

            return null;
        }

        $position = $targetNode->position ?? null;
        $raDegrees = $position ? $this->parseOalAngle($position->ra ?? null) : null;
        $declDegrees = $position ? $this->parseOalAngle($position->dec ?? null) : null;
        if ($raDegrees === null || $declDegrees === null || $raDegrees < 0 || $raDegrees >= 360 || abs($declDegrees) > 90) {
            $result->addError("{$notFound}, and the file gives no valid position for it, skipping.", '', $row);

            return null;
        }

        // The diameters in arcminutes, the larger one first (as objects.diam1)
        $small = $this->parseOalAngle($targetNode->smallDiameter ?? null);
        $large = $this->parseOalAngle($targetNode->largeDiameter ?? null);
        $diameters = array_values(array_filter([$small, $large], fn (?float $d) => $d !== null && $d > 0));
        rsort($diameters);

        $subr = null;
        $surfBr = $targetNode->surfBr ?? null;
        if ($surfBr !== null && ($value = $this->parseFloat((string) $surfBr)) !== null) {
            $subr = (string) ($surfBr->attributes()['unit'] ?? '') === 'mags-per-squarearcsec'
                ? $value - self::ARCSEC2_TO_ARCMIN2
                : $value;
        }

        $pa = $this->parseInt((string) ($targetNode->pa ?? ''), 0, 359);

        return [
            'name' => $name,
            'type' => $type,
            'ra' => $raDegrees / 15.0,
            'decl' => $declDegrees,
            'mag' => $this->parseFloat((string) ($targetNode->visMag ?? '')),
            'subr' => $subr,
            'diam1' => isset($diameters[0]) ? $diameters[0] * 60.0 : null,
            'diam2' => isset($diameters[1]) ? $diameters[1] * 60.0 : null,
            'pa' => $pa,
            'datasource' => mb_substr('OAL import by ' . $this->user->username, 0, 50),
        ];
    }

    /**
     * @return array{string|null, string|null, string|null, string|null}
     */
    private function lookUpObject(string $name, ?string $targetTypeHint, array $targetMetadata): array
    {
        $candidates = $this->objectNameCandidates($name);
        $metadataKey = trim((string) ($targetMetadata['target_key'] ?? ''));
        if ($metadataKey !== '') {
            $candidates = array_values(array_unique([...$candidates, mb_strtolower($metadataKey), Str::slug($metadataKey, '-')]));
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

        foreach (self::TARGET_TABLES as $table) {
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

        $attributes = $targetNode->attributes('http://www.w3.org/2001/XMLSchema-instance');
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
     * Parse an OAL angle element into degrees. The element carries a unit
     * attribute ('arcsec' | 'arcmin' | 'deg' | 'rad'), degrees when missing.
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

        return match ($unit) {
            'rad' => rad2deg($val),
            'arcmin' => $val / 60.0,
            'arcsec' => $val / 3600.0,
            default => $val,
        };
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
