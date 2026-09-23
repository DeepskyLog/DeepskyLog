<?php

namespace App\Services;

use App\Models\Atlas;
use App\Models\DeepskyObject;
use App\Models\ObjectName;
use App\Models\Observation;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Models\UserObjectMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * The value of every report field (ReportLayoutCatalog::FIELDS) for a list of
 * objects, as the old DeepskyLog filled them in.
 *
 * Only what the layout shows is worked out: the ephemerides through the year
 * cost two dozen calculations per object, and a report can hold thousands.
 */
class ObjectReportDataBuilder
{
    public function __construct(private readonly ObjectSetDataBuilder $data)
    {
    }

    /**
     * @param  Collection<int, DeepskyObject>  $objects
     * @param  array<int, string>  $fields  the fields the layout shows
     * @param  callable(int, int): void|null  $progress  called with (done, total)
     * @return array<int, array<string, string>>
     */
    public function rows(Collection $objects, User $user, array $fields, Carbon $date, ?int $listId = null, ?callable $progress = null): array
    {
        $names = $objects->map(fn (DeepskyObject $o) => (string) $o->name)->values()->all();

        $seen = $this->seen($names, $user);
        $altNames = in_array('altname', $fields, true) ? $this->altNames($names) : [];
        $listDescriptions = $listId !== null && in_array('objectdescription', $fields, true)
            ? ObservingListItem::where('observing_list_id', $listId)->whereIn('object_name', $names)->pluck('item_description', 'object_name')->all()
            : [];
        $metrics = array_intersect(ReportLayoutCatalog::CONTRAST_FIELDS, $fields) !== [] ? $this->metrics($names, $user) : [];
        $atlasColumn = $this->atlasColumn($user);
        $withNight = array_intersect(ReportLayoutCatalog::NIGHT_FIELDS, $fields) !== [];
        $withYear = array_intersect(ReportLayoutCatalog::YEAR_FIELDS, $fields) !== [];

        $location = (string) ($user->standardLocation?->name ?? '');
        $instrument = (string) ($user->standardInstrument?->fullName() ?? $user->standardInstrument?->name ?? '');
        $dateText = $date->isoFormat('D MMMM YYYY');

        $rows = [];
        $total = count($names);

        foreach ($objects->values() as $i => $object) {
            $name = (string) $object->name;
            $pa = is_numeric($object->pa) && (int) round((float) $object->pa) !== 999 ? ((int) round((float) $object->pa)) . '°' : '-';
            $size = ObjectSetDataBuilder::formatSize($object->diam1, $object->diam2);
            $description = trim((string) ($listDescriptions[$name] ?? ''));
            if ($description === '') {
                $description = (string) $object->description;
            }

            $row = [
                'con' => (string) $object->con,
                'objectseen' => $seen[$name]['seen'] ?? '-',
                'objectlastseen' => $seen[$name]['last'] ?? '',
                'showname' => $name,
                'objectname' => $name,
                'altname' => implode('/', $altNames[$name] ?? []),
                'objecttype' => (string) $object->type,
                'objecttypefull' => $this->data->typeNameFor($object->type),
                'objectconstellation' => (string) $object->con,
                'objectconstellationfull' => $this->data->constellationNameFor($object->con),
                'objectmagnitude' => self::optional(ObjectSetDataBuilder::knownValue($object->mag)),
                'objectsurfacebrightness' => self::optional(ObjectSetDataBuilder::knownValue($object->subr)),
                'objectradecl' => trim((DeepskyObject::formatRa($object->ra) ?? '') . ' ' . (DeepskyObject::formatDec($object->decl) ?? '')),
                'objectra' => DeepskyObject::formatRa($object->ra) ?? '',
                'objectdecl' => DeepskyObject::formatDec($object->decl) ?? '',
                'objectsizepa' => $size === '-' ? '-' : $size . '/' . $pa,
                'objectdiam1' => self::optional(ObjectSetDataBuilder::formatSize($object->diam1, null)),
                'objectdiam2' => self::optional(ObjectSetDataBuilder::formatSize($object->diam2, null)),
                'objectsize' => self::optional($size),
                'objectpa' => $pa,
                'objectuseratlaspage' => $atlasColumn !== null ? trim((string) ($object->{$atlasColumn} ?? '')) : '',
                'objectdescription' => self::description($description),
            ];

            $row += $this->contrastFields($name, $metrics[$name] ?? null, $location, $instrument);

            if ($withNight) {
                $row += $this->nightFields($name, $this->data->objectNightEphemerides($object, $user, $date), $location, $dateText);
            }

            if ($withYear) {
                $row += self::yearFields($this->data->yearEphemerides($object, $user, $date));
            }

            $rows[] = $row;

            if ($progress !== null && ($i % 25 === 24 || $i === $total - 1)) {
                $progress($i + 1, $total);
            }
        }

        return $rows;
    }

    /**
     * The "seen" code of the old DeepskyLog: - (never seen), X (seen by
     * others) or Y (seen by you), then D (drawn by you), S (drawn by
     * others) or Z (never drawn), and the observation counts: all
     * observations and all drawings for X, all observations and yours for Y.
     *
     * @param  array<int, string>  $names
     * @return array<string, array{seen: string, last: string}>
     */
    private function seen(array $names, User $user): array
    {
        $result = [];

        foreach (array_chunk($names, 500) as $chunk) {
            $all = Observation::query()
                ->where('legacy_source', 'deepsky')
                ->whereIn('target_name', $chunk)
                ->selectRaw('target_name, COUNT(*) as observations, SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings')
                ->groupBy('target_name')
                ->get()
                ->keyBy('target_name');

            $yours = Observation::query()
                ->where('legacy_source', 'deepsky')
                ->where('observer_user_id', $user->id)
                ->whereIn('target_name', $chunk)
                ->selectRaw('target_name, COUNT(*) as observations, SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings, MAX(observed_on) as last_seen')
                ->groupBy('target_name')
                ->get()
                ->keyBy('target_name');

            foreach ($all as $name => $row) {
                $count = (int) $row->observations;
                $drawings = (int) $row->drawings;
                $mine = $yours->get($name);

                if ($mine !== null && (int) $mine->observations > 0) {
                    $result[$name] = [
                        'seen' => 'Y' . ((int) $mine->drawings > 0 ? 'D' : ($drawings > 0 ? 'S' : 'Z')) . '(' . $count . '/' . (int) $mine->observations . ')',
                        'last' => $mine->last_seen ? Carbon::parse($mine->last_seen)->format('Ymd') : '',
                    ];
                } else {
                    $result[$name] = [
                        'seen' => 'X' . ($drawings > 0 ? 'S' : 'Z') . '(' . $count . '/' . $drawings . ')',
                        'last' => '',
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $names
     * @return array<string, array<int, string>>
     */
    private function altNames(array $names): array
    {
        $result = [];

        foreach (array_chunk($names, 500) as $chunk) {
            foreach (ObjectName::whereIn('objectname', $chunk)->get(['objectname', 'altname']) as $row) {
                $alt = trim((string) $row->altname);
                if ($alt !== '' && strcasecmp($alt, (string) $row->objectname) !== 0 && ! in_array($alt, $result[$row->objectname] ?? [], true)) {
                    $result[$row->objectname][] = $alt;
                }
            }
        }

        return $result;
    }

    /**
     * The cached contrast reserves of the observer's standard instrument at
     * the standard location, with the standard lens when there is one --
     * the same values the search results show.
     *
     * @param  array<int, string>  $names
     * @return array<string, UserObjectMetric>
     */
    private function metrics(array $names, User $user): array
    {
        $instrumentId = $user->stdtelescope ?? null;
        $locationId = $user->stdlocation ?? null;
        if (! $instrumentId || ! $locationId) {
            return [];
        }

        $lensId = $user->stdlens ?: null;
        $result = [];

        foreach (array_chunk($names, 500) as $chunk) {
            $rows = UserObjectMetric::where('user_id', $user->id)
                ->where('instrument_id', $instrumentId)
                ->where('location_id', $locationId)
                ->whereIn('object_name', $chunk)
                ->where(fn ($q) => $lensId !== null ? $q->where('lens_id', $lensId)->orWhereNull('lens_id') : $q->whereNull('lens_id'))
                ->get();

            foreach ($rows as $metric) {
                // The metric with the standard lens wins over the one without.
                if (! isset($result[$metric->object_name]) || $metric->lens_id !== null) {
                    $result[$metric->object_name] = $metric;
                }
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function contrastFields(string $name, ?UserObjectMetric $metric, string $location, string $instrument): array
    {
        $fields = [
            'objectcontrast' => '-',
            'objectcontrastpopup' => '',
            'objectcontrasttype' => '-',
            'objectoptimalmagnification' => '-',
            'objectoptimalmagnificationvalue' => '-',
        ];

        if ($metric === null) {
            return $fields;
        }

        $magnification = $metric->optimum_detection_magnification;
        if (is_numeric($magnification) && (float) $magnification > 0) {
            $eyepieces = collect(is_array($metric->optimum_eyepieces) ? $metric->optimum_eyepieces : [])
                ->map(fn ($e) => is_array($e) ? trim((string) ($e['name'] ?? '')) : '')
                ->filter()
                ->implode(', ');

            $fields['objectoptimalmagnificationvalue'] = (string) (int) round((float) $magnification);
            $fields['objectoptimalmagnification'] = ((int) round((float) $magnification)) . 'x' . ($eyepieces !== '' ? ' (' . $eyepieces . ')' : '');
        }

        $contrast = $metric->contrast_reserve;
        if (! is_numeric($contrast)) {
            return $fields;
        }

        $contrast = (float) $contrast;
        [$type, $text] = match (true) {
            $contrast < -0.2 => [__('Not visible'), __(':object is not visible from :location with your :instrument')],
            $contrast < 0.1 => [__('Questionable'), __('Visibility of :object is questionable from :location with your :instrument')],
            $contrast < 0.35 => [__('Difficult'), __(':object is difficult to see from :location with your :instrument')],
            $contrast < 0.5 => [__('Quite difficult'), __(':object is quite difficult to see from :location with your :instrument')],
            $contrast < 1.0 => [__('Easy'), __(':object is easy to see from :location with your :instrument')],
            default => [__('Very easy'), __(':object is very easy to see from :location with your :instrument')],
        };

        $fields['objectcontrast'] = sprintf('%.2f', $contrast);
        $fields['objectcontrasttype'] = $type;
        $fields['objectcontrastpopup'] = strtr($text, [':object' => $name, ':location' => $location, ':instrument' => $instrument]);

        return $fields;
    }

    /**
     * @param  array<string, string>|null  $eph
     * @return array<string, string>
     */
    private function nightFields(string $name, ?array $eph, string $location, string $date): array
    {
        if ($eph === null) {
            return array_fill_keys(ReportLayoutCatalog::NIGHT_FIELDS, '-');
        }

        $neverRises = $eph['rise'] === '-' && $eph['max_altitude'] === '-';
        $replace = ['object' => $name, 'date' => $date, 'location' => $location];

        $riseSet = fn (string $time, string $text) => match (true) {
            $neverRises => __(':object does not rise above the horizon', $replace),
            $time === '-' => __(':object is circumpolar', $replace),
            default => __($text, $replace + ['time' => $time]),
        };

        return [
            'objectrise' => $eph['rise'],
            'objectrisepopup' => $riseSet($eph['rise'], ':object rises at :time on :date in :location'),
            'objecttransit' => $eph['transit'],
            'objecttransitpopup' => $eph['transit'] === '-' ? '-' : __(':object transits at :time on :date in :location', $replace + ['time' => $eph['transit']]),
            'objectset' => $eph['set'],
            'objectsetpopup' => $riseSet($eph['set'], ':object sets at :time on :date in :location'),
            'objectbest' => $eph['best_time'],
            'objectmaxaltitude' => $eph['max_altitude_night'],
            'objectmaxaltitudepopup' => $eph['max_altitude_night'] === '-'
                ? __(':object does not rise above the horizon', $replace)
                : __(':object reaches an altitude of :altitude in :location', $replace + ['altitude' => $eph['max_altitude_night']]),
        ];
    }

    /**
     * When in the year the object stands highest in the dark: its highest
     * altitude at night and the stretch of the year it reaches it, from the
     * ephemerides on the 1st and the 15th of every month.
     *
     * @param  array{dates: array<int, array<string, string>>}|null  $year
     * @return array<string, string>
     */
    public static function yearFields(?array $year): array
    {
        $fields = array_fill_keys(ReportLayoutCatalog::YEAR_FIELDS, '-');
        if ($year === null) {
            return $fields;
        }

        // Half months since 1 January: 0 is 1 January, 1 is 15 January, ...
        $altitudes = array_map(
            fn (array $d) => preg_match('/^-?\d+/', (string) ($d['max_alt_night'] ?? ''), $m) ? (int) $m[0] : null,
            array_values($year['dates'] ?? [])
        );
        $count = count($altitudes);
        $known = array_filter($altitudes, fn ($a) => $a !== null);
        if ($count === 0 || $known === []) {
            return $fields;
        }

        $max = max($known);
        $fields['objectmaxalt'] = $max . '°';

        if (count(array_filter($altitudes, fn ($a) => $a === $max)) === $count) {
            // As high all year round: there is no stretch to speak of.
            return $fields;
        }

        // The longest run of dates at the highest altitude, round the year.
        $bestStart = null;
        $bestLength = 0;
        for ($start = 0; $start < $count; $start++) {
            if ($altitudes[$start] !== $max || $altitudes[($start - 1 + $count) % $count] === $max) {
                continue;
            }

            $length = 1;
            while ($length < $count && $altitudes[($start + $length) % $count] === $max) {
                $length++;
            }

            if ($length > $bestLength) {
                $bestStart = $start;
                $bestLength = $length;
            }
        }

        if ($bestStart === null) {
            return $fields;
        }

        $end = $bestStart + $bestLength - 1;
        $fields['objectmaxaltstarttext'] = self::partOfMonth($bestStart, $count);
        $fields['objectmaxaltmidtext'] = self::partOfMonth(($bestStart + $end) / 2, $count);
        $fields['objectmaxaltendtext'] = self::partOfMonth($end, $count);

        return $fields;
    }

    /**
     * "start Jan", "begin Jan", "mid Jan" or "end Jan" for a position in half
     * months since 1 January.
     */
    private static function partOfMonth(float $halfMonths, int $count): string
    {
        $halfMonths = fmod($halfMonths, $count);
        $month = (int) floor($halfMonths / 2);
        $part = $halfMonths / 2 - $month;

        $when = match (true) {
            $part >= 0.75 => __('end'),
            $part >= 0.5 => __('mid'),
            $part >= 0.25 => __('begin'),
            default => __('start'),
        };

        return $when . ' ' . Carbon::create(2000, $month + 1, 1)->isoFormat('MMM');
    }

    private function atlasColumn(User $user): ?string
    {
        $code = (string) ($user->standardAtlasCode ?? '');
        if ($code === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $code) || ! Atlas::where('code', $code)->exists()) {
            $code = 'urano';
        }

        return $code;
    }

    /**
     * A description as plain text, its paragraphs on lines of their own but
     * without the blank lines between them: a report is meant to be compact.
     */
    private static function description(string $html): string
    {
        $text = preg_replace('#<\s*(br|/p|/div|/li)\b[^>]*>#i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]*\R\s*/u', "\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * A dash for an unknown value becomes nothing, as in the old reports:
     * a field without a value is left out, with its texts before and after.
     */
    private static function optional(string $value): string
    {
        return $value === '-' ? '' : $value;
    }
}
