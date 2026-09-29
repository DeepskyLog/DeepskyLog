<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\ObservationSession;
use App\Models\SearchIndex;
use App\Models\User;
use App\Models\Observation;
use App\Services\Import\OalSchemaValidator;
use App\Services\ObservationSearchQuery;
use App\Services\Observations\ObservationPdfDataBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ObservationSearchExportController extends Controller
{
    private const OAL_NS = 'http://groups.google.com/group/openastronomylog';
    private const XSI_NS = 'http://www.w3.org/2001/XMLSchema-instance';
    private const OAL_SCHEMA_LOCATION = 'http://groups.google.com/group/openastronomylog oal21.xsd';
    private const OAL_TARGET_METADATA_PREFIX = 'DeepskyLog:';

    /**
     * The most observations each export builds in one web request; larger
     * searches are refused with a request to narrow them down. Every export is
     * built in memory, so these follow measurements on the full database under
     * the web server's 128 MB limit: 13k observations take 105 MB as SkySafari
     * and 123 MB as OAL, AstroPlanner needs ~7 ms per observation, and a
     * 300-observation PDF takes 20 s and 260 MB (the PDF runs with 512 MB).
     */
    private const MAX_EXPORT_OBSERVATIONS = [
        'pdf' => 500,
        'skylist' => 10000,
        'obs' => 2500,
        'oal' => 7500,
    ];

    private const EXPORT_FORMAT_NAMES = [
        'pdf' => 'PDF',
        'skylist' => 'SkySafari',
        'obs' => 'AstroPlanner',
        'oal' => 'OAL',
    ];

    public function pdf(Request $request, ObservationPdfDataBuilder $builder)
    {
        if ($refused = $this->tooManyObservations($request, 'pdf')) {
            return $refused;
        }

        // Building the entries already needs more than the default 128 MB.
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        try {
            $query = trim((string) $request->query('q', ''));
            $entries = $this->resolveMatchingObservations($request)
                ->map(fn(Observation $observation): ?array => $builder->build($observation))
                ->filter()
                ->values();

            $title = $query !== ''
                ? __('Observations matching ":q"', ['q' => $query])
                : __('Observations');

            $html = view('pdf.observation_search', [
                'entries' => $entries,
                'title' => $title,
                'query' => $query,
            ])->render();

            return $this->renderPdf(
                $html,
                'observations_' . now()->format('Ymd_His') . '.pdf',
                'portrait',
            );
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::pdf failed', [
                'query' => (string) $request->query('q', ''),
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate observations PDF'));
        }
    }

    public function skylist(Request $request)
    {
        if ($refused = $this->tooManyObservations($request, 'skylist')) {
            return $refused;
        }

        try {
            $query = trim((string) $request->query('q', ''));
            $observations = $this->resolveMatchingObservations($request);
            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $locations = Location::query()
                ->whereIn('id', $observations->pluck('location_id')->filter()->unique()->all())
                ->get(['id', 'name'])
                ->keyBy('id');

            $instruments = Instrument::query()
                ->whereIn('id', $observations->pluck('instrument_id')->filter()->unique()->all())
                ->get(['id', 'name', 'focal_length_mm'])
                ->keyBy('id');

            $eyepieces = Eyepiece::query()
                ->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique()->all())
                ->get(['id', 'name', 'focal_length_mm'])
                ->keyBy('id');

            $filters = Filter::query()
                ->whereIn('id', $observations->pluck('filter_id')->filter()->unique()->all())
                ->get(['id', 'name'])
                ->keyBy('id');

            $lenses = Lens::query()
                ->whereIn('id', $observations->pluck('lens_id')->filter()->unique()->all())
                ->get(['id', 'name'])
                ->keyBy('id');

            $blocks = $observations
                ->map(function (Observation $observation) use ($filters, $eyepieces, $instruments, $lenses, $locations, $planetNames): ?string {
                    $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));

                    if ($catalog === '') {
                        return null;
                    }

                    $lines = [
                        'SkyObject=BeginObject',
                        '   ObjectID=' . $this->skySafariObjectId($observation, $catalog, $planetNames),
                        '   CatalogNumber=' . $this->skylistFieldValue($catalog),
                        'EndObject=SkyObject',
                    ];

                    $dateObserved = $this->formatSkylistDateObserved($observation);
                    if ($dateObserved !== null) {
                        array_splice($lines, 3 - 1, 0, '   DateObserved=' . $dateObserved);
                    }

                    $locationName = trim((string) ($locations->get($observation->location_id)?->name ?? ''));
                    if ($locationName !== '') {
                        array_splice($lines, count($lines) - 1, 0, '   Location=' . $this->skylistFieldValue($locationName));
                    }

                    $comment = $this->formatSkylistComment((string) ($observation->description ?? ''));
                    if ($comment !== null) {
                        array_splice($lines, count($lines) - 1, 0, '   Comment=' . $this->skylistFieldValue($comment));
                    }

                    $equipment = $this->formatSkylistEquipment(
                        $instruments->get($observation->instrument_id)?->name,
                        $eyepieces->get($observation->eyepiece_id)?->name,
                        $filters->get($observation->filter_id)?->name,
                        $lenses->get($observation->lens_id)?->name,
                    );
                    if ($equipment !== null) {
                        array_splice($lines, count($lines) - 1, 0, '   Equipment=' . $this->skylistFieldValue($equipment));
                    }

                    return implode("\n", $lines);
                })
                ->filter()
                ->values()
                ->all();

            $content = "SkySafariObservingListVersion=3.0\n";

            if ($blocks !== []) {
                $content .= implode("\n\n", $blocks) . "\n";
            }

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, 'observations_' . now()->format('Ymd_His') . '.skylist', ['Content-Type' => 'text/plain']);
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::skylist failed', [
                'query' => (string) $request->query('q', ''),
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate SkySafari export'));
        }
    }

    public function apd(Request $request)
    {
        return $this->obs($request);
    }

    public function obs(Request $request)
    {
        if ($refused = $this->tooManyObservations($request, 'obs')) {
            return $refused;
        }

        try {
            $query = trim((string) $request->query('q', ''));
            $observations = $this->resolveMatchingObservations($request);
            $locations = Location::query()
                ->whereIn('id', $observations->pluck('location_id')->filter()->unique()->all())
                ->get(['id', 'name', 'timezone'])
                ->keyBy('id');

            $users = User::query()
                ->whereIn('id', $observations->pluck('observer_user_id')->filter()->unique()->all())
                ->get(['id', 'name', 'username'])
                ->keyBy('id');

            $instruments = Instrument::query()
                ->whereIn('id', $observations->pluck('instrument_id')->filter()->unique()->all())
                ->get(['id', 'name', 'focal_length_mm'])
                ->keyBy('id');

            $eyepieces = Eyepiece::query()
                ->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique()->all())
                ->get(['id', 'name', 'focal_length_mm'])
                ->keyBy('id');

            $filters = Filter::query()
                ->whereIn('id', $observations->pluck('filter_id')->filter()->unique()->all())
                ->get(['id', 'name'])
                ->keyBy('id');

            $lenses = Lens::query()
                ->whereIn('id', $observations->pluck('lens_id')->filter()->unique()->all())
                ->get(['id', 'name'])
                ->keyBy('id');

            $targetMetadata = $this->resolveApdTargetMetadata($observations);

            $tmp = tempnam(sys_get_temp_dir(), 'dsl_observation_search_obs_');
            if ($tmp === false) {
                throw new \RuntimeException('Failed to create temporary file for OBS export');
            }

            $db = new \PDO('sqlite:' . $tmp);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->exec("CREATE TABLE Observations (KeyVal varchar, DateTime varchar, Rating varchar, Telescope varchar, Eyepiece varchar, Imager text, Filter varchar, Aid varchar, Observed varchar, Notes varchar, UserFields text, Files varchar, Plan varchar, ID varchar, GUID integer, Name varchar, RA double, Dec double, Type varchar, Constellation varchar, GMTOffset double, AssociatedData varchar, Flags integer, UseUT integer, Special varchar, ObsTable varchar, ObjFields varchar)");
            $db->exec("CREATE TABLE Sessions (UniqueID test, start double, finish double, notes text, name text, site text, observer text, seeing text, transparency text, user text, ended boolean, displayUT boolean, AssociatedData varchar)");

            $insertObservation = $db->prepare('INSERT INTO Observations (KeyVal, DateTime, Rating, Telescope, Eyepiece, Imager, Filter, Aid, Observed, Notes, UserFields, Files, Plan, ID, GUID, Name, RA, Dec, Type, Constellation, GMTOffset, AssociatedData, Flags, UseUT, Special, ObsTable, ObjFields) VALUES (:keyval, :datetime, :rating, :telescope, :eyepiece, :imager, :filter, :aid, :observed, :notes, :user_fields, :files, :plan, :id, :guid, :name, :ra, :dec, :type, :constellation, :gmt_offset, :associated_data, :flags, :use_ut, :special, :obs_table, :obj_fields)');
            $insertSession = $db->prepare('INSERT INTO Sessions (UniqueID, start, finish, notes, name, site, observer, seeing, transparency, user, ended, displayUT, AssociatedData) VALUES (:unique_id, :start, :finish, :notes, :name, :site, :observer, :seeing, :transparency, :user, :ended, :display_ut, :associated_data)');

            $guidMap = [];
            $guidCounter = 1;
            $prefix = random_int(1000, 9999) . '-' . (string) $this->astroPlannerEpochSeconds(now()->utc());

            foreach ($observations->values() as $index => $observation) {
                $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));
                if ($catalog === '') {
                    continue;
                }

                $identity = $this->apdObjectIdentity($observation, $catalog);
                $metadataKey = $this->apdTargetMetadataKey($observation, $catalog);
                $metadata = $targetMetadata[$metadataKey] ?? [];
                $guid = $guidMap[$identity] ?? null;

                if ($guid === null) {
                    $guid = $guidCounter;
                    $guidMap[$identity] = $guid;
                    $guidCounter++;
                }

                $location = $locations->get($observation->location_id);
                $timezone = trim((string) ($location?->timezone ?? 'UTC'));
                $moment = $this->astroPlannerObservationMoment($observation, $timezone);
                $epochSeconds = $this->astroPlannerEpochSeconds($moment->utc());
                $gmtOffset = $this->astroPlannerGmtOffsetHours($moment);
                $keySuffix = $index + 1;
                $keyVal = $prefix . '-' . $keySuffix;
                $sessionId = $prefix . '-session-' . $keySuffix;

                $insertObservation->execute([
                    ':keyval' => $keyVal,
                    ':datetime' => $epochSeconds . '.',
                    ':rating' => '0',
                    ':telescope' => trim((string) ($instruments->get($observation->instrument_id)?->name ?? '')),
                    ':eyepiece' => trim((string) ($eyepieces->get($observation->eyepiece_id)?->name ?? '')),
                    ':imager' => '',
                    ':filter' => trim((string) ($filters->get($observation->filter_id)?->name ?? '')),
                    ':aid' => trim((string) ($lenses->get($observation->lens_id)?->name ?? '')),
                    ':observed' => '1',
                    ':notes' => $this->astroPlannerObsNotesRtf((string) ($observation->description ?? '')),
                    ':user_fields' => '',
                    ':files' => '',
                    ':plan' => '',
                    ':id' => $identity,
                    ':guid' => $guid,
                    ':name' => $catalog,
                    ':ra' => $metadata['ra'] ?? null,
                    ':dec' => $metadata['decl'] ?? null,
                    ':type' => $this->astroPlannerType($observation),
                    ':constellation' => $this->astroPlannerConstellation($metadata['con'] ?? null),
                    ':gmt_offset' => $gmtOffset,
                    ':associated_data' => '',
                    ':flags' => 1,
                    ':use_ut' => 0,
                    ':special' => '',
                    ':obs_table' => '',
                    ':obj_fields' => $this->astroPlannerObservationObjFields(
                        $identity,
                        $catalog,
                        $observation,
                        $metadata,
                        $gmtOffset,
                        $moment,
                        $eyepieces->get($observation->eyepiece_id)?->name,
                        $instruments->get($observation->instrument_id),
                        $eyepieces->get($observation->eyepiece_id),
                    ),
                ]);

                $insertSession->execute([
                    ':unique_id' => $sessionId,
                    ':start' => $epochSeconds,
                    ':finish' => $epochSeconds + 900,
                    ':notes' => '',
                    ':name' => $moment->format('n/j/Y \a\t g:i A'),
                    ':site' => trim((string) ($location?->name ?? '')),
                    ':observer' => trim((string) ($users->get($observation->observer_user_id)?->name ?? $observation->observer_username ?? '')),
                    ':seeing' => $this->astroPlannerSeeingLabel($observation->seeing),
                    ':transparency' => $this->astroPlannerTransparencyLabel($observation->transparency),
                    ':user' => '',
                    ':ended' => 1,
                    ':display_ut' => 0,
                    ':associated_data' => '',
                ]);
            }

            $db = null;

            return response()->streamDownload(function () use ($tmp) {
                readfile($tmp);
            }, 'observations_' . now()->format('Ymd_His') . '.obs', ['Content-Type' => 'application/x-sqlite3']);
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::obs failed', [
                'query' => (string) $request->query('q', ''),
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate AstroPlanner OBS export'));
        }
    }

    public function oal(Request $request, OalSchemaValidator $validator)
    {
        if ($refused = $this->tooManyObservations($request, 'oal')) {
            return $refused;
        }

        try {
            $query = trim((string) $request->query('q', ''));
            $content = $this->buildOalXml($this->resolveMatchingObservations($request));
            $validator->assertValid($content);

            return response($content, 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="observations_' . now()->format('Ymd_His') . '.xml"',
            ]);
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::oal failed', [
                'query' => (string) $request->query('q', ''),
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate OAL export'));
        }
    }

    public function sessionOal(string $userSlug, string $sessionSlug, OalSchemaValidator $validator)
    {
        [$session, $observations, $sessionContext] = $this->resolveSessionExportPayload($userSlug, $sessionSlug);

        try {
            $content = $this->buildOalXml($observations, $sessionContext);
            $validator->assertValid($content);

            $filename = Str::slug((string) ($session->slug ?: $session->name ?: 'session-' . $session->id)) ?: ('session-' . $session->id);

            return response($content, 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.oal.xml"',
            ]);
        } catch (\Throwable $exception) {
            Log::error('ObservationSearchExportController::sessionOal failed', [
                'user_slug' => $userSlug,
                'session_slug' => $sessionSlug,
                'session_id' => $session->id,
                'error' => (string) $exception,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Failed to generate OAL export'));
        }
    }

    private function renderPdf(string $html, string $filename, string $orientation)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', $orientation);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();
            $output = $dompdf->output();

            return response()->streamDownload(function () use ($output) {
                echo $output;
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        return redirect()
            ->back()
            ->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
    }

    private function matchingObservations(Request $request): EloquentBuilder
    {
        return app(ObservationSearchQuery::class)->observations(
            trim((string) $request->query('q', '')),
            $this->extractObservationFilters($request)
        );
    }

    private function resolveMatchingObservations(Request $request): Collection
    {
        return $this->matchingObservations($request)->orderBy('observations.id')->get();
    }

    /**
     * Refuse an export that matches more observations than it can build within
     * one web request, with a message asking to narrow the search down.
     */
    private function tooManyObservations(Request $request, string $format)
    {
        $max = self::MAX_EXPORT_OBSERVATIONS[$format];
        $count = $this->matchingObservations($request)->count();

        if ($count <= $max) {
            return null;
        }

        return redirect()
            ->back()
            ->with('error', __('This search matches :count observations, but at most :max observations can be exported as :format. Please narrow down your search.', [
                'count' => number_format($count),
                'max' => number_format($max),
                'format' => self::EXPORT_FORMAT_NAMES[$format],
            ]));
    }

    private function extractObservationFilters(Request $request): array
    {
        $filters = $request->except(['_token', 'page', 'q']);

        return array_filter($filters, function ($value): bool {
            if (is_array($value)) {
                return collect($value)
                    ->flatten()
                    ->contains(fn($item): bool => trim((string) $item) !== '');
            }

            return trim((string) $value) !== '';
        });
    }

    /**
     * @return array{0: ObservationSession, 1: Collection<int, Observation>, 2: array<string, mixed>|null}
     */
    private function resolveSessionExportPayload(string $userSlug, string $sessionSlug): array
    {
        $user = User::where('slug', $userSlug)->firstOrFail();

        $session = ObservationSession::where('observerid', $user->username)
            ->where(function ($query) use ($sessionSlug) {
                $query->where('slug', $sessionSlug);

                if (is_numeric($sessionSlug)) {
                    $query->orWhere('id', $sessionSlug);
                }
            })
            ->firstOrFail();

        if (isset($session->active) && (int) $session->active === 0) {
            $viewer = Auth::user();
            $allowAdmin = config('sessions.allow_admin_override', false);
            $viewerIsOwner = $viewer && ($viewer->username === $session->observerid || ($viewer->slug ?? null) === ($user->slug ?? null));
            $viewerIsAdmin = $viewer && method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges();

            if (!($viewerIsOwner || ($allowAdmin && $viewerIsAdmin))) {
                abort(404);
            }
        }

        $observationIds = DB::table('sessionObservations')
            ->where('sessionid', $session->id)
            ->pluck('observationid')
            ->map(fn($value) => (int) $value)
            ->filter(fn(int $value): bool => $value > 0)
            ->values();

        $observations = collect();
        if ($observationIds->isNotEmpty()) {
            $byId = Observation::query()
                ->whereIn('id', $observationIds->all())
                ->get()
                ->keyBy('id');

            $observations = $observationIds
                ->map(fn(int $id): ?Observation => $byId->get($id))
                ->filter()
                ->values();
        }

        $sessionLocation = null;
        if (!empty($session->locationid)) {
            $sessionLocation = Location::find($session->locationid);
        }

        if (!$sessionLocation) {
            $fallbackLocationId = $observations->pluck('location_id')->filter()->map(fn($value) => (int) $value)->first();
            if ($fallbackLocationId) {
                $sessionLocation = Location::find($fallbackLocationId);
            }
        }

        return [
            $session,
            $observations,
            $this->buildOalSessionContext($session, $sessionLocation),
        ];
    }

    /**
     * @return array{id: string, begin: string, end: string, site_id: string|null, site: ?Location, language: string|null, weather: string|null, equipment: string|null, comments: string|null, observers: array<int, array{id: string, name: string}>, co_observer_ids: array<int, string>}|null
     */
    private function buildOalSessionContext(ObservationSession $session, ?Location $site): ?array
    {
        $begin = $this->formatOalDateTime($session->begindate, $site?->timezone);
        $endSource = $session->enddate ?? $session->begindate;
        $end = $this->formatOalDateTime($endSource, $site?->timezone);

        $participantUsernames = collect([$session->observerid])
            ->merge($session->otherObservers())
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $participantUsers = User::query()
            ->whereIn('username', $participantUsernames->all())
            ->get(['id', 'name', 'username'])
            ->keyBy('username');

        $observers = $participantUsernames
            ->map(function (string $username) use ($participantUsers): array {
                $user = $participantUsers->get($username);

                if ($user) {
                    return [
                        'id' => 'observer-' . $user->id,
                        'name' => $user->name,
                    ];
                }

                return [
                    'id' => 'observer-' . (Str::slug($username) ?: 'observer'),
                    'name' => $username,
                ];
            })
            ->values();

        return [
            'id' => 'session-' . $session->id,
            'begin' => $begin,
            'end' => $end,
            'site_id' => $site ? ('site-' . $site->id) : null,
            'site' => $site,
            'language' => $this->normalizeLanguageTag($session->language ?? null),
            'weather' => $this->sanitizeSessionText($session->weather ?? null),
            'equipment' => $this->sanitizeSessionText($session->equipment ?? null),
            'comments' => $this->sanitizeSessionText($session->comments ?? null),
            'observers' => $observers->all(),
            'co_observer_ids' => $observers->slice(1)->pluck('id')->values()->all(),
        ];
    }

    private function resolveObservedTargets(Request $request): Collection
    {
        $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

        $targets = [];

        foreach ($this->resolveMatchingObservations($request) as $observation) {
            $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));

            if ($catalog === '') {
                continue;
            }

            $identity = $this->skylistIdentity($observation, $catalog);

            if (array_key_exists($identity, $targets)) {
                continue;
            }

            $targets[$identity] = [
                'identity' => $identity,
                'catalog' => $catalog,
                'object_id' => $this->skySafariObjectId($observation, $catalog, $planetNames),
                'type' => $this->astroPlannerType($observation),
            ];
        }

        return collect($targets)->values();
    }

    /**
     * @param  array{id: string, begin: string, end: string, site_id: string|null, site: ?Location, language: string|null, weather: string|null, equipment: string|null, comments: string|null, observers: array<int, array{id: string, name: string}>, co_observer_ids: array<int, string>}|null  $sessionContext
     */
    private function buildOalXml(Collection $observations, ?array $sessionContext = null): string
    {
        $observations = $observations->values();

        $users = User::query()
            ->whereIn('id', $observations->pluck('observer_user_id')->filter()->unique()->all())
            ->get(['id', 'name', 'username'])
            ->keyBy('id');

        $locations = Location::query()
            ->whereIn('id', $observations->pluck('location_id')->filter()->unique()->all())
            ->get(['id', 'name', 'latitude', 'longitude', 'elevation', 'timezone'])
            ->keyBy('id');

        if (($sessionContext['site'] ?? null) instanceof Location && !$locations->has($sessionContext['site']->id)) {
            $locations->put($sessionContext['site']->id, $sessionContext['site']);
        }

        $instruments = Instrument::query()
            ->whereIn('id', $observations->pluck('instrument_id')->filter()->unique()->all())
            ->get(['id', 'name', 'aperture_mm', 'focal_length_mm'])
            ->keyBy('id');

        $eyepieces = Eyepiece::query()
            ->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique()->all())
            ->get(['id', 'name', 'focal_length_mm', 'max_focal_length_mm', 'apparentFOV'])
            ->keyBy('id');

        $lenses = Lens::query()
            ->whereIn('id', $observations->pluck('lens_id')->filter()->unique()->all())
            ->get(['id', 'name', 'factor'])
            ->keyBy('id');

        $filters = Filter::query()
            ->whereIn('id', $observations->pluck('filter_id')->filter()->unique()->all())
            ->get(['id', 'name'])
            ->keyBy('id');

        $targetMetadata = $this->resolveOalTargetMetadata($observations);

        $targetRefs = [];
        foreach ($observations as $observation) {
            $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));
            if ($catalog === '') {
                continue;
            }

            $targetMetadataKey = $this->apdTargetMetadataKey($observation, $catalog);

            $targetRefs[$this->oalTargetId($observation, $catalog)] = [
                'catalog' => $catalog,
                'target_table' => strtolower(trim((string) ($observation->target_table ?? ''))),
                'target_type' => strtolower(trim((string) ($observation->target_type ?? ''))),
                'target_key' => trim((string) ($observation->target_key ?? '')),
                'metadata' => $targetMetadata[$targetMetadataKey] ?? [],
            ];
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElementNS(self::OAL_NS, 'oal:observations');
        $root->setAttribute('version', '2.1');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::XSI_NS);
        $root->setAttributeNS(self::XSI_NS, 'xsi:schemaLocation', self::OAL_SCHEMA_LOCATION);
        $document->appendChild($root);

        $observersNode = $document->createElement('observers');
        $observerDefinitions = [];
        foreach ($observations->groupBy(fn(Observation $observation) => $this->oalObserverId($observation)) as $observerId => $group) {
            /** @var Observation $observation */
            $observation = $group->first();
            $user = $users->get($observation->observer_user_id);

            $observerDefinitions[$observerId] = [
                'id' => $observerId,
                'name' => $user?->name ?? $observation->observer_username ?? 'Observer',
            ];
        }

        foreach (($sessionContext['observers'] ?? []) as $observerDefinition) {
            $observerDefinitions[$observerDefinition['id']] = $observerDefinition;
        }

        foreach ($observerDefinitions as $observerId => $observerDefinition) {
            [$firstName, $surname] = $this->splitObserverName($observerDefinition['name'] ?? 'Observer');

            $observerNode = $document->createElement('observer');
            $observerNode->setAttribute('id', $observerId);
            $this->appendOalTextNode($document, $observerNode, 'name', $firstName);
            $this->appendOalTextNode($document, $observerNode, 'surname', $surname);
            $observersNode->appendChild($observerNode);
        }
        $root->appendChild($observersNode);

        $sitesNode = $document->createElement('sites');
        foreach ($locations as $location) {
            $siteNode = $document->createElement('site');
            $siteNode->setAttribute('id', 'site-' . $location->id);
            $this->appendOalTextNode($document, $siteNode, 'name', $location->name);
            $this->appendOalTextNode($document, $siteNode, 'longitude', $location->longitude ?? 0, ['unit' => 'deg']);
            $this->appendOalTextNode($document, $siteNode, 'latitude', $location->latitude ?? 0, ['unit' => 'deg']);
            $this->appendOalTextNode($document, $siteNode, 'elevation', $location->elevation ?? 0);
            $this->appendOalTextNode($document, $siteNode, 'timezone', $this->oalTimezoneOffsetMinutes($location->timezone));
            $sitesNode->appendChild($siteNode);
        }
        $root->appendChild($sitesNode);

        $sessionsNode = $document->createElement('sessions');
        if ($sessionContext !== null && !empty($sessionContext['site_id'])) {
            $sessionNode = $document->createElement('session');
            $sessionNode->setAttribute('id', $sessionContext['id']);
            if (!empty($sessionContext['language'])) {
                $sessionNode->setAttribute('lang', $sessionContext['language']);
            }
            $this->appendOalTextNode($document, $sessionNode, 'begin', $sessionContext['begin']);
            $this->appendOalTextNode($document, $sessionNode, 'end', $sessionContext['end']);
            $this->appendOalTextNode($document, $sessionNode, 'site', $sessionContext['site_id']);
            foreach ($sessionContext['co_observer_ids'] as $coObserverId) {
                $this->appendOalTextNode($document, $sessionNode, 'coObserver', $coObserverId);
            }
            $this->appendOalTextNode($document, $sessionNode, 'weather', $sessionContext['weather']);
            $this->appendOalTextNode($document, $sessionNode, 'equipment', $sessionContext['equipment']);
            $this->appendOalTextNode($document, $sessionNode, 'comments', $sessionContext['comments']);
            $sessionsNode->appendChild($sessionNode);
        }
        $root->appendChild($sessionsNode);

        $targetsNode = $document->createElement('targets');
        foreach ($targetRefs as $targetId => $targetData) {
            $targetNode = $document->createElement('target');
            $targetNode->setAttribute('id', $targetId);
            $targetType = $this->oalTargetXsiType($targetData);
            if ($targetType !== null) {
                $targetNode->setAttributeNS(self::XSI_NS, 'xsi:type', $targetType);
            }
            $this->appendOalTextNode($document, $targetNode, 'datasource', 'DeepskyLog');
            $this->appendOalTextNode($document, $targetNode, 'name', $targetData['catalog']);
            $this->appendOalTargetPosition($document, $targetNode, $targetData['metadata'] ?? []);
            $this->appendOalTextNode($document, $targetNode, 'constellation', $targetData['metadata']['con'] ?? null);
            $this->appendOalTextNode($document, $targetNode, 'notes', $this->oalTargetMetadata($targetData));
            $this->appendOalDeepSkyTargetDetails($document, $targetNode, $targetType, $targetData['metadata'] ?? []);
            $targetsNode->appendChild($targetNode);
        }
        $root->appendChild($targetsNode);

        $scopesNode = $document->createElement('scopes');
        foreach ($instruments as $instrument) {
            $scopeNode = $document->createElement('scope');
            $scopeNode->setAttribute('id', 'scope-' . $instrument->id);
            $scopeNode->setAttributeNS(self::XSI_NS, 'xsi:type', 'oal:scopeType');
            $this->appendOalTextNode($document, $scopeNode, 'model', $instrument->name);
            $this->appendOalTextNode($document, $scopeNode, 'type', 'R');
            $this->appendOalTextNode($document, $scopeNode, 'aperture', max(1, (float) ($instrument->aperture_mm ?? 1)));
            $this->appendOalTextNode($document, $scopeNode, 'focalLength', max(1, (float) ($instrument->focal_length_mm ?? 1)));
            $scopesNode->appendChild($scopeNode);
        }
        $root->appendChild($scopesNode);

        $eyepiecesNode = $document->createElement('eyepieces');
        foreach ($eyepieces as $eyepiece) {
            $eyepieceNode = $document->createElement('eyepiece');
            $eyepieceNode->setAttribute('id', 'eyepiece-' . $eyepiece->id);
            $this->appendOalTextNode($document, $eyepieceNode, 'model', $eyepiece->name);
            $this->appendOalTextNode($document, $eyepieceNode, 'focalLength', max(1, (float) ($eyepiece->focal_length_mm ?? 1)));
            if (is_numeric($eyepiece->max_focal_length_mm) && (float) $eyepiece->max_focal_length_mm > 0) {
                $this->appendOalTextNode($document, $eyepieceNode, 'maxFocalLength', $eyepiece->max_focal_length_mm);
            }
            if (is_numeric($eyepiece->apparentFOV) && (float) $eyepiece->apparentFOV > 0) {
                $this->appendOalTextNode($document, $eyepieceNode, 'apparentFOV', $eyepiece->apparentFOV, ['unit' => 'deg']);
            }
            $eyepiecesNode->appendChild($eyepieceNode);
        }
        $root->appendChild($eyepiecesNode);

        $lensesNode = $document->createElement('lenses');
        foreach ($lenses as $lens) {
            $lensNode = $document->createElement('lens');
            $lensNode->setAttribute('id', 'lens-' . $lens->id);
            $this->appendOalTextNode($document, $lensNode, 'model', $lens->name);
            $this->appendOalTextNode($document, $lensNode, 'factor', max(0.01, (float) ($lens->factor ?? 1)));
            $lensesNode->appendChild($lensNode);
        }
        $root->appendChild($lensesNode);

        $filtersNode = $document->createElement('filters');
        foreach ($filters as $filter) {
            $filterNode = $document->createElement('filter');
            $filterNode->setAttribute('id', 'filter-' . $filter->id);
            $this->appendOalTextNode($document, $filterNode, 'model', $filter->name);
            $this->appendOalTextNode($document, $filterNode, 'type', 'other');
            $filtersNode->appendChild($filterNode);
        }
        $root->appendChild($filtersNode);

        $imagersNode = $document->createElement('imagers');
        $root->appendChild($imagersNode);

        foreach ($observations as $observation) {
            $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));
            if ($catalog === '') {
                continue;
            }

            $observationNode = $document->createElement('observation');
            $observationNode->setAttribute('id', 'observation-' . $observation->id);
            $this->appendOalTextNode($document, $observationNode, 'observer', $this->oalObserverId($observation));

            if ($observation->location_id && $locations->has($observation->location_id)) {
                $this->appendOalTextNode($document, $observationNode, 'site', 'site-' . $observation->location_id);
            }
            if ($sessionContext !== null && !empty($sessionContext['site_id'])) {
                $this->appendOalTextNode($document, $observationNode, 'session', $sessionContext['id']);
            }
            $this->appendOalTextNode($document, $observationNode, 'target', $this->oalTargetId($observation, $catalog));
            $this->appendOalTextNode(
                $document,
                $observationNode,
                'begin',
                $this->formatOalBegin($observation, $locations->get($observation->location_id)?->timezone)
            );
            if (is_numeric($observation->limiting_magnitude) && (float) $observation->limiting_magnitude > 0) {
                $this->appendOalTextNode($document, $observationNode, 'faintestStar', (string) $observation->limiting_magnitude);
            }
            if (is_numeric($observation->sqm) && (float) $observation->sqm > 0) {
                $this->appendOalTextNode($document, $observationNode, 'sky-quality', (string) $observation->sqm, ['unit' => 'mags-per-squarearcsec']);
            }
            if ($observation->seeing !== null) {
                $this->appendOalTextNode($document, $observationNode, 'seeing', max(1, min(5, (int) $observation->seeing)));
            }
            if ($observation->instrument_id && $instruments->has($observation->instrument_id)) {
                $this->appendOalTextNode($document, $observationNode, 'scope', 'scope-' . $observation->instrument_id);
            }
            if ($observation->eyepiece_id && $eyepieces->has($observation->eyepiece_id)) {
                $this->appendOalTextNode($document, $observationNode, 'eyepiece', 'eyepiece-' . $observation->eyepiece_id);
            }
            if ($observation->lens_id && $lenses->has($observation->lens_id)) {
                $this->appendOalTextNode($document, $observationNode, 'lens', 'lens-' . $observation->lens_id);
            }
            if ($observation->filter_id && $filters->has($observation->filter_id)) {
                $this->appendOalTextNode($document, $observationNode, 'filter', 'filter-' . $observation->filter_id);
            }

            $description = trim((string) ($observation->description ?? ''));
            $resultNode = $document->createElement('result');
            $language = trim((string) ($observation->language ?? 'en'));
            if ($language !== '') {
                $resultNode->setAttribute('lang', strtolower(substr($language, 0, 10)));
            }
            $descriptionNode = $document->createElement('description');
            $descriptionNode->appendChild($document->createTextNode($this->xmlSafeText(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
            $resultNode->appendChild($descriptionNode);
            $observationNode->appendChild($resultNode);

            $root->appendChild($observationNode);
        }

        return $document->saveXML();
    }

    private function skylistIdentity(Observation $observation, string $catalog): string
    {
        $targetTable = strtolower(trim((string) ($observation->target_table ?? '')));
        $targetKey = trim((string) ($observation->target_key ?? ''));

        if ($targetTable !== '' && $targetKey !== '') {
            return $targetTable . ':' . $targetKey;
        }

        return strtolower($catalog);
    }

    private function skySafariObjectId(Observation $observation, string $catalog, array $planetNames): string
    {
        $targetTable = strtolower(trim((string) ($observation->target_table ?? '')));
        $targetType = strtolower(trim((string) ($observation->target_type ?? '')));
        $lowerCatalog = strtolower($catalog);

        if (
            in_array($targetTable, ['planets', 'moons', 'cometobjects', 'comets', 'lunar_features', 'asteroids'], true)
            || in_array($targetType, ['planet', 'solarsystem', 'lunar', 'comet'], true)
            || in_array($lowerCatalog, $planetNames, true)
        ) {
            return '1,-1,-1';
        }

        return '4,-1,-1';
    }

    private function formatSkylistDateObserved(Observation $observation): ?string
    {
        $observedOn = $observation->observed_on;
        $date = $observedOn instanceof \DateTimeInterface
            ? $observedOn->format('Y-m-d')
            : trim((string) $observedOn);

        if ($date === '') {
            return null;
        }

        $time = trim((string) ($observation->observed_time ?? ''));
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches) === 1) {
            $time = sprintf('%02d:%02d:%02d', (int) $matches[1], (int) $matches[2], (int) ($matches[3] ?? 0));
        } else {
            $time = '00:00:00';
        }

        try {
            $dateTime = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, 'UTC');
        } catch (\Throwable $_) {
            return null;
        }

        $jdn = gregoriantojd(
            (int) $dateTime->format('m'),
            (int) $dateTime->format('d'),
            (int) $dateTime->format('Y')
        );
        $seconds = ((int) $dateTime->format('H') * 3600)
            + ((int) $dateTime->format('i') * 60)
            + (int) $dateTime->format('s');
        $julianDate = $jdn - 0.5 + ($seconds / 86400);

        return rtrim(rtrim(number_format($julianDate, 7, '.', ''), '0'), '.');
    }

    private function formatSkylistComment(string $description): ?string
    {
        $description = preg_replace('/<br\s*\/?>/i', "\n", $description) ?? $description;
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = preg_replace("/\r\n?|\n/u", "\n", $description) ?? $description;
        $description = trim($description);

        return $description !== '' ? $description : null;
    }

    private function formatSkylistEquipment(?string $instrument, ?string $eyepiece, ?string $filter, ?string $lens): ?string
    {
        $parts = collect([$instrument, $eyepiece, $filter, $lens])
            ->map(fn(?string $part): string => trim((string) $part))
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return null;
        }

        return $parts->implode(', ');
    }

    private function skylistFieldValue(string $value): string
    {
        return str_replace(["\r\n", "\r", "\n"], '\\n', trim($value));
    }

    private function astroPlannerType(Observation $observation): string
    {
        $targetType = strtolower(trim((string) ($observation->target_type ?? '')));

        return match ($targetType) {
            'planet' => 'Planet',
            'lunar' => 'Solar System',
            'comet' => 'Comet',
            default => 'Observation Target',
        };
    }

    private function resolveApdTargetMetadata(Collection $observations): array
    {
        $metadata = [];
        $targets = $observations
            ->map(function (Observation $observation): ?array {
                $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));
                $table = strtolower(trim((string) ($observation->target_table ?? '')));

                if ($catalog === '' || $table === '') {
                    return null;
                }

                return [
                    'table' => $table,
                    'catalog' => $catalog,
                    'catalog_lower' => Str::lower($catalog),
                ];
            })
            ->filter()
            ->unique(fn(array $target): string => $target['table'] . '|' . $target['catalog_lower'])
            ->values();

        $objectNames = $targets->where('table', 'objects')->pluck('catalog')->all();
        if ($objectNames !== []) {
            $rows = DB::table('objects')
                ->whereIn('name', $objectNames)
                ->get(['name', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'pa', 'con']);

            foreach ($rows as $row) {
                $metadata['objects|' . Str::lower((string) $row->name)] = [
                    'ra' => is_numeric($row->ra) ? (float) $row->ra : null,
                    'decl' => is_numeric($row->decl) ? (float) $row->decl : null,
                    'mag' => is_numeric($row->mag) && (float) $row->mag !== 99.9 ? (float) $row->mag : null,
                    'diam1' => $row->diam1,
                    'diam2' => $row->diam2,
                    'pa' => $row->pa,
                    'con' => $row->con,
                ];
            }
        }

        foreach (['planets', 'moons', 'asteroids'] as $table) {
            $names = $targets->where('table', $table)->pluck('catalog')->all();
            if ($names === []) {
                continue;
            }

            $rows = DB::table($table)
                ->whereIn('name', $names)
                ->get(['name', 'ra', 'decl']);

            foreach ($rows as $row) {
                $metadata[$table . '|' . Str::lower((string) $row->name)] = [
                    'ra' => is_numeric($row->ra) ? (float) $row->ra : null,
                    'decl' => is_numeric($row->decl) ? (float) $row->decl : null,
                    'mag' => null,
                    'diam1' => null,
                    'diam2' => null,
                    'pa' => null,
                    'con' => null,
                ];
            }
        }

        if ($targets->isNotEmpty()) {
            $searchRows = SearchIndex::query()
                ->where(function ($query) use ($targets) {
                    foreach ($targets as $target) {
                        $query->orWhere(function ($inner) use ($target) {
                            $inner->where('source_table', $target['table'])
                                ->where(function ($nameQuery) use ($target) {
                                    $nameQuery->whereRaw('LOWER(name) = ?', [$target['catalog_lower']])
                                        ->orWhereRaw('LOWER(COALESCE(display_name, name)) = ?', [$target['catalog_lower']]);
                                });
                        });
                    }
                })
                ->get(['source_table', 'name', 'display_name', 'ra', 'decl']);

            foreach ($searchRows as $row) {
                $name = trim((string) ($row->display_name ?: $row->name));
                if ($name === '') {
                    continue;
                }

                $key = strtolower(trim((string) $row->source_table)) . '|' . Str::lower($name);

                $existing = $metadata[$key] ?? [
                    'ra' => null,
                    'decl' => null,
                    'mag' => null,
                    'diam1' => null,
                    'diam2' => null,
                    'pa' => null,
                    'con' => null,
                ];

                if ($existing['ra'] === null && is_numeric($row->ra)) {
                    $existing['ra'] = (float) $row->ra;
                }
                if ($existing['decl'] === null && is_numeric($row->decl)) {
                    $existing['decl'] = (float) $row->decl;
                }

                $metadata[$key] = $existing;
            }
        }

        return $metadata;
    }

    private function resolveOalTargetMetadata(Collection $observations): array
    {
        $metadata = [];

        $objectTargets = $observations
            ->filter(fn(Observation $observation): bool => strtolower(trim((string) ($observation->target_table ?? ''))) === 'objects')
            ->map(function (Observation $observation): ?array {
                $catalog = trim((string) ($observation->target_name ?? $observation->target_key ?? ''));
                if ($catalog === '') {
                    return null;
                }

                return [
                    'catalog' => $catalog,
                    'normalized' => $this->normalizeOalCatalogName($catalog),
                ];
            })
            ->filter()
            ->unique('normalized')
            ->values();

        if ($objectTargets->isEmpty()) {
            return $metadata;
        }

        $rows = DB::table('objects')
            ->leftJoin('deepskytypes', 'objects.type', '=', 'deepskytypes.code')
            ->where(function ($query) use ($objectTargets) {
                foreach ($objectTargets as $target) {
                    $query->orWhereRaw('LOWER(REPLACE(objects.name, " ", "")) = ?', [$target['normalized']]);
                }
            })
            ->get([
                'objects.name',
                'objects.type',
                'objects.con',
                'objects.ra',
                'objects.decl',
                'objects.mag',
                'objects.subr',
                'objects.diam1',
                'objects.diam2',
                'objects.pa',
                'deepskytypes.name as type_name',
            ]);

        foreach ($rows as $row) {
            $normalizedRowName = $this->normalizeOalCatalogName((string) $row->name);
            $matchingTargets = $objectTargets->where('normalized', $normalizedRowName);

            foreach ($matchingTargets as $target) {
                $key = 'objects|' . Str::lower($target['catalog']);
                $metadata[$key] = [
                    'ra' => is_numeric($row->ra) ? (float) $row->ra : null,
                    'decl' => is_numeric($row->decl) ? (float) $row->decl : null,
                    'mag' => is_numeric($row->mag) && (float) $row->mag !== 99.9 ? (float) $row->mag : null,
                    'subr' => is_numeric($row->subr) && (float) $row->subr > 0 ? (float) $row->subr : null,
                    'diam1' => is_numeric($row->diam1) && (float) $row->diam1 > 0 ? (float) $row->diam1 : null,
                    'diam2' => is_numeric($row->diam2) && (float) $row->diam2 > 0 ? (float) $row->diam2 : null,
                    'pa' => is_numeric($row->pa) && (int) round((float) $row->pa) !== 999 ? (int) round((float) $row->pa) : null,
                    'con' => trim((string) ($row->con ?? '')) !== '' ? Str::upper(trim((string) $row->con)) : null,
                    'type_code' => trim((string) ($row->type ?? '')),
                    'type_name' => trim((string) ($row->type_name ?? '')),
                ];
            }
        }

        return $metadata;
    }

    private function apdTargetMetadataKey(Observation $observation, string $catalog): string
    {
        return strtolower(trim((string) ($observation->target_table ?? ''))) . '|' . Str::lower($catalog);
    }

    private function apdObjectIdentity(Observation $observation, string $catalog): string
    {
        $catalog = trim($catalog);
        if ($catalog !== '') {
            return $catalog;
        }

        return trim((string) ($observation->target_key ?? ''));
    }

    /**
     * Moment of the observation in the time zone of the location. The date and
     * time are stored in UT. Without a time the stored date is the only thing
     * known: it is given as midnight UT, not shifted to the location, so that
     * the date does not change.
     */
    private function astroPlannerObservationMoment(Observation $observation, ?string $timezone): CarbonImmutable
    {
        $date = $observation->observed_on instanceof \DateTimeInterface
            ? $observation->observed_on->format('Y-m-d')
            : trim((string) $observation->observed_on);

        if ($date === '' && preg_match('/^\d{8}$/', (string) ($observation->legacy_date_int ?? '')) === 1) {
            $date = CarbonImmutable::createFromFormat('Ymd', (string) $observation->legacy_date_int, 'UTC')->format('Y-m-d');
        }

        try {
            $moment = CarbonImmutable::parse(substr($date, 0, 10), 'UTC')->startOfDay();
        } catch (\Throwable $_) {
            return CarbonImmutable::now('UTC');
        }

        $time = $observation->time;
        if ($time === null) {
            return $moment;
        }

        $seconds = preg_match('/^\d{1,2}:\d{2}:(\d{2})/', (string) $observation->observed_time, $m) === 1 ? (int) $m[1] : 0;
        $moment = $moment->setTime(intdiv($time, 100), $time % 100, $seconds);
        try {
            return $moment->setTimezone(trim((string) $timezone) !== '' ? trim((string) $timezone) : 'UTC');
        } catch (\Throwable $_) {
            return $moment;
        }
    }

    private function astroPlannerEpochSeconds(\DateTimeInterface $moment): int
    {
        return $moment->getTimestamp() + 2082844800;
    }

    private function astroPlannerGmtOffsetHours(CarbonImmutable $moment): float
    {
        return round($moment->getOffset() / 3600, 2);
    }

    private function astroPlannerObsNotesRtf(string $notes): string
    {
        $notes = $this->formatSkylistComment($notes);
        if ($notes === null) {
            return '';
        }

        $escaped = preg_replace_callback('/[\\{}]/u', fn(array $matches): string => '\\' . $matches[0], $notes) ?? $notes;
        $escaped = preg_replace_callback('/[^\x00-\x7F]/u', function (array $matches): string {
            $codepoint = mb_ord($matches[0], 'UTF-8');

            return '\\u' . $codepoint . '?';
        }, $escaped) ?? $escaped;
        $escaped = str_replace("\n", '\\par ', $escaped);

        return '{\\rtf1\\ansi\\ansicpg1252{\\fonttbl{\\f0\\fnil Segoe UI;}}{\\colortbl\\red0\\green0\\blue0;}\\uc0 \\ql\\f0\\fs24 ' . $escaped . '}';
    }

    private function astroPlannerObservationObjFields(string $identity, string $catalog, Observation $observation, array $metadata, float $gmtOffset, CarbonImmutable $moment, ?string $eyepieceName, $instrument, $eyepiece): string
    {
        $fields = [
            '1' => $identity,
            '3' => $catalog,
            '4' => $this->astroPlannerType($observation),
            '5' => $this->astroPlannerRaText($metadata['ra'] ?? null),
            '6' => $this->astroPlannerDecText($metadata['decl'] ?? null),
            '16' => $this->astroPlannerNumericText($metadata['mag'] ?? null),
            '21' => $this->formatSizeForApd((object) [
                'diam1' => $metadata['diam1'] ?? null,
                'diam2' => $metadata['diam2'] ?? null,
                'pa' => $metadata['pa'] ?? null,
            ]),
            '22' => $this->astroPlannerConstellation($metadata['con'] ?? null),
            '35' => $moment->format('n/j/Y'),
            '39' => $this->formatSkylistComment((string) ($observation->description ?? '')) ?? '',
            '53' => trim((string) $eyepieceName),
            '54' => $this->astroPlannerMagnificationText($instrument, $eyepiece),
            '59' => $moment->format('n/j/Y'),
        ];

        if ($gmtOffset !== 0.0) {
            $fields['24'] = number_format($gmtOffset, 6, '.', '');
        }

        return json_encode(array_filter($fields, fn($value): bool => $value !== '' && $value !== null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function astroPlannerRaText($ra): string
    {
        if (!is_numeric($ra)) {
            return '';
        }

        $value = (float) $ra;
        if ($value > 24.0) {
            $value /= 15.0;
        }

        $hours = floor($value);
        $minutesFloat = ($value - $hours) * 60.0;
        $minutes = floor($minutesFloat);
        $seconds = round(($minutesFloat - $minutes) * 60.0);

        if ($seconds >= 60) {
            $seconds = 0;
            $minutes++;
        }
        if ($minutes >= 60) {
            $minutes = 0;
            $hours++;
        }

        return sprintf('%02dh %02dm %02ds', ((int) $hours) % 24, (int) $minutes, (int) $seconds);
    }

    private function astroPlannerDecText($decl): string
    {
        if (!is_numeric($decl)) {
            return '';
        }

        $value = (float) $decl;
        $sign = $value < 0 ? '-' : '+';
        $absolute = abs($value);
        $degrees = floor($absolute);
        $minutesFloat = ($absolute - $degrees) * 60.0;
        $minutes = round($minutesFloat, 1);

        if ($minutes >= 60) {
            $minutes = 0.0;
            $degrees++;
        }

        return sprintf('%s%02d°%04.1f\'', $sign, (int) $degrees, $minutes);
    }

    private function astroPlannerNumericText($value): string
    {
        if (!is_numeric($value)) {
            return '';
        }

        $float = (float) $value;

        return floor($float) === $float
            ? (string) (int) $float
            : rtrim(rtrim(number_format($float, 1, '.', ''), '0'), '.');
    }

    private function astroPlannerConstellation($constellation): string
    {
        $constellation = trim((string) $constellation);
        if ($constellation === '') {
            return '';
        }

        return Str::title(Str::lower($constellation));
    }

    private function astroPlannerMagnificationText($instrument, $eyepiece): string
    {
        if ($instrument && $eyepiece && is_numeric($instrument->focal_length_mm ?? null) && is_numeric($eyepiece->focal_length_mm ?? null) && (float) $eyepiece->focal_length_mm > 0.0) {
            return (string) (int) round((float) $instrument->focal_length_mm / (float) $eyepiece->focal_length_mm);
        }

        return ($instrument || $eyepiece) ? 'n/a' : '';
    }

    private function astroPlannerSeeingLabel($seeing): string
    {
        if (!is_numeric($seeing) || (int) $seeing <= 0) {
            return '';
        }

        return (string) __('observations.seeing.' . (int) $seeing);
    }

    private function astroPlannerTransparencyLabel($transparency): string
    {
        if (!is_numeric($transparency) || (int) $transparency <= 0) {
            return '';
        }

        return (string) __('observations.transparency.' . (int) $transparency);
    }

    private function formatApdObservationNotes(Observation $observation, ?string $locationName, ?string $equipment): string
    {
        $lines = [];

        $observedOn = $observation->observed_on instanceof \DateTimeInterface
            ? $observation->observed_on->format('Y-m-d')
            : trim((string) $observation->observed_on);
        $observedTime = trim((string) ($observation->observed_time ?? ''));
        if ($observedOn !== '') {
            $lines[] = $observedTime !== ''
                ? 'Observed: ' . $observedOn . ' ' . substr($observedTime, 0, 5)
                : 'Observed: ' . $observedOn;
        }

        $locationName = trim((string) $locationName);
        if ($locationName !== '') {
            $lines[] = 'Location: ' . $locationName;
        }

        $equipment = trim((string) $equipment);
        if ($equipment !== '') {
            $lines[] = 'Equipment: ' . $equipment;
        }

        $comment = $this->formatSkylistComment((string) ($observation->description ?? ''));
        if ($comment !== null) {
            if ($lines !== []) {
                $lines[] = '';
            }
            $lines[] = $comment;
        }

        return implode("\n", $lines);
    }

    private function formatApdObservationGroupNotes(Collection $observations, Collection $locations, Collection $instruments, Collection $eyepieces, Collection $filters, Collection $lenses): string
    {
        $notes = $observations
            ->map(function (Observation $observation) use ($eyepieces, $filters, $instruments, $lenses, $locations): string {
                return $this->formatApdObservationNotes(
                    $observation,
                    $locations->get($observation->location_id)?->name,
                    $this->formatSkylistEquipment(
                        $instruments->get($observation->instrument_id)?->name,
                        $eyepieces->get($observation->eyepiece_id)?->name,
                        $filters->get($observation->filter_id)?->name,
                        $lenses->get($observation->lens_id)?->name,
                    ),
                );
            })
            ->filter(fn(string $note): bool => trim($note) !== '')
            ->values();

        return $notes->implode("\n\n");
    }

    private function formatSizeForApd($row): string
    {
        $d1 = $row->diam1 ?? null;
        $d2 = $row->diam2 ?? null;
        $pa = $row->pa ?? null;
        $hasD1 = is_numeric($d1) && floatval($d1) > 0;
        $hasD2 = is_numeric($d2) && floatval($d2) > 0;
        if (!$hasD1 && !$hasD2) {
            return '';
        }

        $d1f = $hasD1 ? floatval($d1) : 0.0;
        $d2f = $hasD2 ? floatval($d2) : 0.0;
        $fmt = function ($value) {
            return floor($value) == $value ? sprintf('%d', $value) : sprintf('%.1f', $value);
        };

        if ($hasD1 && $hasD2) {
            if (max($d1f, $d2f) > 60.0) {
                $d1m = $d1f / 60.0;
                $d2m = $d2f / 60.0;
                $d1mFmt = $fmt($d1m);
                $d2mFmt = $fmt($d2m);
                if (in_array($d1mFmt, ['0', '0.0'], true) || in_array($d2mFmt, ['0', '0.0'], true)) {
                    $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                } else {
                    $size = $d1mFmt . "'x" . $d2mFmt . "'";
                }
            } else {
                $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
            }
        } else {
            $single = $hasD1 ? $d1f : $d2f;
            $size = $single > 60.0 ? $fmt($single / 60.0) . "'" : $fmt($single) . "''";
        }

        if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
            $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
        }

        return (string) $size;
    }

    private function formatOalBegin(Observation $observation, ?string $timezone = null): string
    {
        return $this->astroPlannerObservationMoment($observation, $timezone)->format('Y-m-d\TH:i:sP');
    }

    private function formatOalDateTime(mixed $value, ?string $timezone = null): string
    {
        $tz = trim((string) ($timezone ?: 'UTC')) ?: 'UTC';

        if ($value instanceof \DateTimeInterface) {
            return CarbonImmutable::instance($value)->setTimezone($tz)->format('Y-m-d\TH:i:sP');
        }

        $text = trim((string) $value);
        if ($text === '') {
            return CarbonImmutable::now($tz)->format('Y-m-d\TH:i:sP');
        }

        foreach ([$tz, 'UTC'] as $candidateTz) {
            foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d', 'Y-n-j H:i:s', 'Ymd H:i:s'] as $format) {
                try {
                    return CarbonImmutable::createFromFormat($format, $text, $candidateTz)->format('Y-m-d\TH:i:sP');
                } catch (\Throwable $_) {
                }
            }

            try {
                return CarbonImmutable::parse($text, $candidateTz)->format('Y-m-d\TH:i:sP');
            } catch (\Throwable $_) {
            }
        }

        return CarbonImmutable::now($tz)->format('Y-m-d\TH:i:sP');
    }

    private function sanitizeSessionText(mixed $value): ?string
    {
        $text = trim((string) html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $text !== '' ? $text : null;
    }

    private function normalizeLanguageTag(mixed $value): ?string
    {
        $text = strtolower(trim((string) $value));

        return $text !== '' ? substr($text, 0, 10) : null;
    }

    /**
     * Remove the characters XML 1.0 does not allow (control characters other
     * than tab, newline and carriage return, which some legacy observation
     * descriptions contain); the OAL file is otherwise not well-formed.
     */
    private function xmlSafeText(string $text): string
    {
        $clean = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);

        // preg_replace() returns null on invalid UTF-8: repair the encoding first.
        return $clean ?? (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', mb_convert_encoding($text, 'UTF-8', 'UTF-8'));
    }

    private function appendOalTextNode(\DOMDocument $document, \DOMElement $parent, string $name, mixed $value, array $attributes = []): void
    {
        if ($value === null) {
            return;
        }

        $text = trim($this->xmlSafeText((string) $value));
        if ($text === '') {
            return;
        }

        $node = $document->createElement($name);
        foreach ($attributes as $attribute => $attributeValue) {
            $node->setAttribute($attribute, $this->xmlSafeText((string) $attributeValue));
        }

        $node->appendChild($document->createTextNode($text));
        $parent->appendChild($node);
    }

    private function oalObserverId(Observation $observation): string
    {
        if ($observation->observer_user_id) {
            return 'observer-' . $observation->observer_user_id;
        }

        return 'observer-' . (Str::slug((string) ($observation->observer_username ?? 'observer')) ?: 'observer');
    }

    private function oalTargetId(Observation $observation, string $catalog): string
    {
        return 'target-' . substr(sha1($this->skylistIdentity($observation, $catalog)), 0, 12);
    }

    /**
     * @param  array{catalog: string, target_table: string, target_type: string, target_key: string, metadata?: array<string, mixed>}  $targetData
     */
    private function oalTargetXsiType(array $targetData): ?string
    {
        $targetTable = $targetData['target_table'];
        $catalog = strtolower(trim($targetData['catalog']));
        $typeCode = Str::upper(trim((string) ($targetData['metadata']['type_code'] ?? '')));

        return match (true) {
            $targetTable === 'cometobjects' => 'oal:CometTargetType',
            $targetTable === 'asteroids' => 'oal:MinorPlanetTargetType',
            $targetTable === 'moons' => 'oal:MoonTargetType',
            $targetTable === 'planets' && $catalog === 'sun' => 'oal:SunTargetType',
            $targetTable === 'planets' => 'oal:PlanetTargetType',
            $targetTable === 'objects' && in_array($typeCode, ['AA1STAR', 'DS'], true) => 'oal:deepSkyDS',
            $targetTable === 'objects' && $typeCode === 'ASTER' => 'oal:deepSkyAS',
            $targetTable === 'objects' && $typeCode === 'GALCL' => 'oal:deepSkyCG',
            $targetTable === 'objects' && $typeCode === 'DRKNB' => 'oal:deepSkyDN',
            $targetTable === 'objects' && $typeCode === 'GLOCL' => 'oal:deepSkyGC',
            $targetTable === 'objects' && $typeCode === 'PLNNB' => 'oal:deepSkyPN',
            $targetTable === 'objects' && in_array($typeCode, ['BRTNB', 'CLANB', 'EMINB', 'ENRNN', 'ENSTR', 'GXADN', 'HII', 'SNREM'], true) => 'oal:deepSkyGN',
            $targetTable === 'objects' && $typeCode === 'GALXY' => 'oal:deepSkyGX',
            $targetTable === 'objects' && $typeCode === 'OPNCL' => 'oal:deepSkyOC',
            $targetTable === 'objects' && $typeCode === 'QUASR' => 'oal:deepSkyQS',
            $targetTable === 'objects' && in_array($typeCode, ['STARC'], true) => 'oal:deepSkySC',
            $targetTable === 'objects' => 'oal:deepSkyNA',
            default => null,
        };
    }

    /**
     * @param  array{catalog: string, target_table: string, target_type: string, target_key: string}  $targetData
     */
    private function oalTargetMetadata(array $targetData): ?string
    {
        if ($targetData['target_table'] !== 'lunar_features') {
            return null;
        }

        return sprintf(
            '%starget_table=%s;target_key=%s;target_type=%s',
            self::OAL_TARGET_METADATA_PREFIX,
            $targetData['target_table'],
            $targetData['target_key'],
            $targetData['target_type'] !== '' ? $targetData['target_type'] : 'lunar'
        );
    }

    private function splitObserverName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['Unknown', 'Observer'];
        }

        $parts = preg_split('/\s+/u', $name) ?: [$name];
        $first = array_shift($parts) ?: $name;
        $surname = trim(implode(' ', $parts));

        return [$first, $surname !== '' ? $surname : $first];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function appendOalTargetPosition(
        \DOMDocument $document,
        \DOMElement $targetNode,
        array $metadata
    ): void {
        if (!is_numeric($metadata['ra'] ?? null) || !is_numeric($metadata['decl'] ?? null)) {
            return;
        }

        $positionNode = $document->createElement('position');
        $this->appendOalTextNode($document, $positionNode, 'ra', $metadata['ra'], ['unit' => 'deg']);
        $this->appendOalTextNode($document, $positionNode, 'dec', $metadata['decl'], ['unit' => 'deg']);
        $targetNode->appendChild($positionNode);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function appendOalDeepSkyTargetDetails(
        \DOMDocument $document,
        \DOMElement $targetNode,
        ?string $targetType,
        array $metadata
    ): void {
        if ($targetType === null || !str_starts_with($targetType, 'oal:deepSky')) {
            return;
        }

        if (is_numeric($metadata['diam1'] ?? null) && (float) $metadata['diam1'] > 0) {
            $this->appendOalTextNode($document, $targetNode, 'smallDiameter', $metadata['diam1'], ['unit' => 'arcsec']);
        }

        if (is_numeric($metadata['diam2'] ?? null) && (float) $metadata['diam2'] > 0) {
            $this->appendOalTextNode($document, $targetNode, 'largeDiameter', $metadata['diam2'], ['unit' => 'arcsec']);
        }

        if (is_numeric($metadata['mag'] ?? null)) {
            $this->appendOalTextNode($document, $targetNode, 'visMag', $metadata['mag']);
        }

        if ($targetType === 'oal:deepSkyGX' && is_numeric($metadata['pa'] ?? null)) {
            $this->appendOalTextNode($document, $targetNode, 'pa', $metadata['pa']);
        }
    }

    private function normalizeOalCatalogName(string $catalog): string
    {
        return Str::lower(preg_replace('/\s+/u', '', trim($catalog)) ?? trim($catalog));
    }

    private function oalTimezoneOffsetMinutes(?string $timezone): int
    {
        $timezone = trim((string) $timezone);
        if ($timezone === '') {
            return 0;
        }

        try {
            $dateTimeZone = new \DateTimeZone($timezone);
            $reference = new \DateTimeImmutable('first day of january ' . now()->year, $dateTimeZone);

            return (int) round($dateTimeZone->getOffset($reference) / 60);
        } catch (\Throwable $_) {
            return 0;
        }
    }
}