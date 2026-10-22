<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Observation;
use App\Models\TargetType;
use App\Services\ObserverCountries;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\BarChart as OriginalBarChart;
use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;
use marineusde\LarapexCharts\Options\XAxisOption;
use Monarobase\CountryList\CountryListFacade as Countries;

class StatisticsController extends Controller
{
    /**
     * The counts are fresh for an hour: they are aggregates over all
     * observations (about a second of queries), and a new observation changing
     * them an hour later is no problem. For a day after that the old counts
     * are shown while they are counted again after the response is sent
     * (Cache::flexible()), so that no visitor waits for them.
     */
    private const CACHE_SECONDS = [3600, 86400];

    private const CATEGORIES = ['deepsky', 'comets', 'double', 'planets', 'sun', 'moon'];

    /**
     * The observations can be limited to a country they were made in
     * (locations.country, 'country') and to a country the observers live in
     * (users.country, 'residence'). Without either parameter a logged-in
     * observer sees the observations made in their own country; the "All"
     * option sends an empty country.
     */
    public function index(Request $request)
    {
        $countryOptions = $this->countryOptions();
        $residenceOptions = ObserverCountries::options();

        $selectedCountry = trim((string) $request->query('country', ''));
        if (!$request->has('country') && !$request->has('residence') && $request->user()) {
            $selectedCountry = (string) Country::nameForCode($request->user()->country);
            if (!array_key_exists($selectedCountry, $countryOptions)) {
                $selectedCountry = '';
            }
        }
        if ($selectedCountry === '') {
            $selectedCountry = null;
        }

        $selectedResidence = strtoupper(trim((string) $request->query('residence', '')));
        if ($selectedResidence === '') {
            $selectedResidence = null;
        }

        abort_if($selectedCountry !== null && !array_key_exists($selectedCountry, $countryOptions), 404);
        abort_if($selectedResidence !== null && !array_key_exists($selectedResidence, $residenceOptions), 404);

        $selectedCountryLabel = $selectedCountry ? $countryOptions[$selectedCountry] : null;
        $selectedResidenceLabel = $selectedResidence ? $residenceOptions[$selectedResidence] : null;
        $context = $this->chartContext($selectedCountryLabel, $selectedResidenceLabel);

        // Only numbers are cached; the labels and charts are built per request,
        // in the language of the visitor.
        $counts = Cache::flexible(
            'statistics:counts:' . ($selectedCountry ?? 'all') . ($selectedResidence ? ':residence:' . $selectedResidence : ''),
            self::CACHE_SECONDS,
            fn() => $this->counts($selectedCountry, $selectedResidence)
        );

        return view('statistics.index', [
            'countryOptions' => $countryOptions,
            'selectedCountry' => $selectedCountry,
            'selectedCountryLabel' => $selectedCountryLabel,
            'residenceOptions' => $residenceOptions,
            'selectedResidence' => $selectedResidence,
            'selectedResidenceLabel' => $selectedResidenceLabel,
            'summaryColumns' => $this->summaryColumns(),
            'summaryRows' => $this->summaryRows($counts['summary']),
            'observationsPerYearChart' => $this->buildObservationsPerYearChart($counts['per_year'], $context),
            'observationsPerMonthChart' => $this->buildObservationsPerMonthChart($counts['per_month'], $context),
            'objectTypesChart' => $this->buildObjectTypesChart($counts['object_types'], $context),
            'countriesChart' => $this->buildCountriesChart($counts['countries'], $context),
            'residencesChart' => $this->buildResidencesChart($counts['residences'], $context),
        ]);
    }

    /**
     * All counts of the page for one country (or all countries), as plain
     * arrays so they can be cached. The observations are counted in one pass,
     * grouped per year, month, drawing and recency, with a sum per category:
     * a query per category and count took seconds.
     *
     * @return array{summary: array<string, array<string, int>>, per_year: array<string, mixed>, per_month: array<string, list<int>>, object_types: array<string, mixed>, countries: array<string, int>, residences: array<string, int>}
     */
    private function counts(?string $country, ?string $residence): array
    {
        $groups = $this->groupedCounts($country, $residence);
        $summary = $this->summaryCounts($groups, $country, $residence);

        return [
            'summary' => $summary,
            'per_year' => $this->perYearCounts($groups),
            'per_month' => $this->perMonthCounts($groups),
            'object_types' => $this->objectTypeCounts($summary['observations'], $country, $residence),
            'countries' => $this->countryCounts($country, $residence),
            'residences' => $this->residenceCounts($country, $residence),
        ];
    }

    /**
     * The number of observations of each category, per year, month, drawing
     * and whether they are of the last year.
     *
     * @return list<array{year: ?int, month: ?int, drawing: bool, recent: bool, counts: array<string, int>}>
     */
    private function groupedCounts(?string $country, ?string $residence): array
    {
        $yearExpression = $this->datePartExpression('year', 'observations.observed_on');
        $monthExpression = $this->datePartExpression('month', 'observations.observed_on');

        $query = $this->observationQuery($country, $residence)
            ->selectRaw($yearExpression . ' as observed_year')
            ->selectRaw($monthExpression . ' as observed_month')
            ->selectRaw('observations.has_drawing as is_drawing')
            ->selectRaw('CASE WHEN observations.observed_on >= ? THEN 1 ELSE 0 END as is_recent', [now()->subYear()]);
        foreach ($this->categoryConditions() as $category => $condition) {
            $query->selectRaw("SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END) as count_{$category}");
        }

        return $this->joinDoubleStars($query)
            ->groupBy('observed_year', 'observed_month', 'is_drawing', 'is_recent')
            ->get()
            ->map(fn($row) => [
                'year' => $row->observed_year === null ? null : (int) $row->observed_year,
                'month' => $row->observed_month === null ? null : (int) $row->observed_month,
                'drawing' => (bool) $row->is_drawing,
                'recent' => (bool) $row->is_recent,
                'counts' => $this->categoryValues($row),
            ])
            ->all();
    }

    /**
     * The SQL condition of each category (the columns of the summary), for
     * the observations joined by joinDoubleStars().
     *
     * @return array<string, string>
     */
    private function categoryConditions(): array
    {
        return [
            'total' => '1 = 1',
            'deepsky' => "observations.target_type = '" . Observation::TYPE_DEEPSKY . "' AND double_stars.name_normalized IS NULL",
            'comets' => "observations.target_type = '" . Observation::TYPE_COMET . "'",
            'double' => "observations.target_type = '" . Observation::TYPE_DEEPSKY . "' AND double_stars.name_normalized IS NOT NULL",
            'planets' => "observations.target_table = 'planets' AND observations.target_key != 'sun'",
            'sun' => "observations.target_table = 'planets' AND observations.target_key = 'sun'",
            'moon' => "observations.target_table IN ('moons', 'lunar_features')",
        ];
    }

    /**
     * Join the double stars on the observed name, so that deep-sky
     * observations of a double star have a double_stars.name_normalized.
     */
    private function joinDoubleStars(Builder $query): Builder
    {
        return $query->leftJoinSub(
            DB::table('objects')->where('type', 'DS')->distinct()->select('name_normalized'),
            'double_stars',
            'double_stars.name_normalized',
            '=',
            'observations.target_name_normalized'
        );
    }

    private function summaryColumns(): array
    {
        return [
            'total' => __('Total'),
            'deepsky' => __('Deepsky'),
            'comets' => __('Comets'),
            'double' => __('Double stars'),
            'planets' => __('Planets'),
            'sun' => __('Sun'),
            'moon' => __('Moon'),
        ];
    }

    /**
     * @param  list<array{year: ?int, month: ?int, drawing: bool, recent: bool, counts: array<string, int>}>  $groups  from groupedCounts()
     * @return array<string, array<string, int>> row => category => count
     */
    private function summaryCounts(array $groups, ?string $country, ?string $residence): array
    {
        $categories = array_keys($this->summaryColumns());
        $counts = [];
        foreach (['observations', 'last_year', 'drawings', 'drawings_last_year'] as $row) {
            $counts[$row] = array_fill_keys($categories, 0);
        }

        foreach ($groups as $group) {
            foreach ($group['counts'] as $category => $count) {
                $counts['observations'][$category] += $count;
                if ($group['recent']) {
                    $counts['last_year'][$category] += $count;
                }
                if ($group['drawing']) {
                    $counts['drawings'][$category] += $count;
                }
                if ($group['drawing'] && $group['recent']) {
                    $counts['drawings_last_year'][$category] += $count;
                }
            }
        }

        $counts['unique'] = $this->differentObjectCounts($country, $residence);

        return $counts;
    }

    /**
     * The number of different objects of each category, counted as
     * Observation::countDifferentObjects() does. The categories are counted
     * in groups that the indexes on different_object_key serve (all
     * observations, deep-sky with the double stars, comets, and the Sun,
     * Moon and planets), rather than all in one pass over the observations.
     *
     * @return array<string, int>
     */
    private function differentObjectCounts(?string $country, ?string $residence): array
    {
        $conditions = $this->categoryConditions();
        $groups = [
            [['total'], fn(Builder $query) => $query],
            [['deepsky', 'double'], fn(Builder $query) => $this->joinDoubleStars($query->where('observations.target_type', Observation::TYPE_DEEPSKY))],
            [['comets'], fn(Builder $query) => $query->where('observations.target_type', Observation::TYPE_COMET)],
            [['planets', 'sun', 'moon'], fn(Builder $query) => $query->whereIn('observations.target_table', ['planets', 'moons', 'lunar_features'])],
        ];

        $values = [];
        foreach ($groups as [$categories, $scope]) {
            $query = $scope($this->observationQuery($country, $residence))
                ->whereNotExists(function (Builder $details) {
                    $details->selectRaw('1')
                        ->from('deepsky_observation_details')
                        ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                        ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
                });
            foreach ($categories as $category) {
                $query->selectRaw('COUNT(DISTINCT CASE WHEN ' . $conditions[$category] . ' THEN ' . Observation::DIFFERENT_OBJECT_SQL . " END) as count_{$category}");
            }

            $row = $query->first();
            foreach ($categories as $category) {
                $values[$category] = (int) ($row->{"count_{$category}"} ?? 0);
            }
        }

        return array_merge(array_fill_keys(array_keys($conditions), 0), $values);
    }

    /**
     * The count of each category in a row selected per categoryConditions().
     *
     * @return array<string, int>
     */
    private function categoryValues(?object $row): array
    {
        $values = [];
        foreach (array_keys($this->categoryConditions()) as $category) {
            $values[$category] = (int) ($row->{"count_{$category}"} ?? 0);
        }

        return $values;
    }

    /**
     * @param  array<string, array<string, int>>  $counts  from summaryCounts()
     */
    private function summaryRows(array $counts): array
    {
        $rowDefinitions = [
            'observations' => __('Number of observations'),
            'last_year' => __('Observations last year'),
            'drawings' => __('Number of drawings'),
            'drawings_last_year' => __('Drawings last year'),
            'unique' => __('Different objects'),
        ];

        $rows = [];
        foreach ($rowDefinitions as $key => $label) {
            $rows[] = [
                'key' => $key,
                'label' => $label,
                'values' => $counts[$key] ?? [],
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{year: ?int, month: ?int, drawing: bool, recent: bool, counts: array<string, int>}>  $groups  from groupedCounts()
     * @return array{first_year: int, last_year: int, series: array<string, list<int>>}
     */
    private function perYearCounts(array $groups): array
    {
        $years = array_filter(array_column($groups, 'year'), fn($year) => $year !== null);
        $firstYear = $years === [] ? (int) date('Y') : min($years);
        $lastYear = (int) date('Y');

        $series = array_fill_keys(self::CATEGORIES, $firstYear > $lastYear ? [] : array_fill(0, $lastYear - $firstYear + 1, 0));
        foreach ($groups as $group) {
            if ($group['year'] === null || $group['year'] < $firstYear || $group['year'] > $lastYear) {
                continue;
            }
            foreach (self::CATEGORIES as $category) {
                $series[$category][$group['year'] - $firstYear] += $group['counts'][$category];
            }
        }

        return ['first_year' => $firstYear, 'last_year' => $lastYear, 'series' => $series];
    }

    /**
     * @param  list<array{year: ?int, month: ?int, drawing: bool, recent: bool, counts: array<string, int>}>  $groups  from groupedCounts()
     * @return array<string, list<int>> category => 12 monthly counts
     */
    private function perMonthCounts(array $groups): array
    {
        $series = array_fill_keys(self::CATEGORIES, array_fill(0, 12, 0));
        foreach ($groups as $group) {
            if ($group['month'] === null || $group['month'] < 1 || $group['month'] > 12) {
                continue;
            }
            foreach (self::CATEGORIES as $category) {
                $series[$category][$group['month'] - 1] += $group['counts'][$category];
            }
        }

        return $series;
    }

    /**
     * @param  array<string, int>  $observations  observations per category, from summaryCounts()
     * @return array{deepsky: array<string, int>, comets: int, planets: int, sun: int, moon: int}
     */
    private function objectTypeCounts(array $observations, ?string $country, ?string $residence): array
    {
        $rows = $this->observationQuery($country, $residence)
            ->join('objects', 'observations.target_key', '=', 'objects.name')
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type')
            ->get();

        $deepsky = [];
        foreach ($rows as $row) {
            $deepsky[$row->type] = ($deepsky[$row->type] ?? 0) + (int) $row->count;
        }

        return [
            'deepsky' => $deepsky,
            'comets' => $observations['comets'],
            'planets' => $observations['planets'],
            'sun' => $observations['sun'],
            'moon' => $observations['moon'],
        ];
    }

    /**
     * @return array<string, int> country => observations, most first
     */
    private function countryCounts(?string $country, ?string $residence): array
    {
        return DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->whereNotNull('locations.country')
            ->where('locations.country', '!=', '')
            ->when($country, fn(Builder $query) => $query->where('locations.country', $country))
            ->when($residence, fn(Builder $query) => $this->whereResidence($query, $residence))
            ->select('locations.country', DB::raw('count(*) as count'))
            ->groupBy('locations.country')
            ->orderByDesc('count')
            ->pluck('count', 'country')
            ->map(fn($count) => (int) $count)
            ->all();
    }

    /**
     * The number of observers per country of residence, of the observations
     * of the filters; observers without a country are left out.
     *
     * @return array<string, int> code => observers, most first
     */
    private function residenceCounts(?string $country, ?string $residence): array
    {
        return DB::table('observations')
            ->join('users', 'users.username', '=', 'observations.observer_username')
            ->whereNotNull('users.country')
            ->where('users.country', '!=', '')
            ->when($country, fn(Builder $query) => $query
                ->join('locations', 'observations.location_id', '=', 'locations.id')
                ->where('locations.country', $country))
            ->when($residence, fn(Builder $query) => $query->where('users.country', $residence))
            ->groupBy('users.country')
            ->selectRaw('users.country, COUNT(DISTINCT users.id) as count')
            ->orderByDesc('count')
            ->pluck('count', 'country')
            ->mapWithKeys(fn($count, $code) => [strtoupper((string) $code) => (int) $count])
            ->all();
    }

    private function buildObservationsPerYearChart(array $counts, string $context): OriginalLineChart
    {
        $xAxis = range($counts['first_year'], $counts['last_year']);
        ['deepsky' => $deepsky, 'comets' => $comets, 'double' => $double, 'planets' => $planets, 'sun' => $sun, 'moon' => $moon] = $counts['series'];

        $total = [];
        for ($index = 0; $index < count($xAxis); $index++) {
            $total[] = ($deepsky[$index] ?? 0)
                + ($comets[$index] ?? 0)
                + ($double[$index] ?? 0)
                + ($planets[$index] ?? 0)
                + ($sun[$index] ?? 0)
                + ($moon[$index] ?? 0);
        }

        $chart = (new OriginalLineChart)
            ->setTitle(__('Number of observations per year: ') . $context)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData(__('Total'), $total)
            ->addData(__('Deepsky'), $deepsky)
            ->addData(__('Comets'), $comets)
            ->addData(__('Double stars'), $double)
            ->addData(__('Planets'), $planets)
            ->addData(__('Sun'), $sun)
            ->addData(__('Moon'), $moon)
            ->setXAxisOption(new XAxisOption($xAxis))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);

        return $chart;
    }

    private function buildObservationsPerMonthChart(array $counts, string $context): OriginalBarChart
    {
        ['deepsky' => $deepsky, 'comets' => $comets, 'double' => $double, 'planets' => $planets, 'sun' => $sun, 'moon' => $moon] = $counts;

        return (new OriginalBarChart)
            ->setTitle(__('Number of observations per month: ') . $context)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setStacked(true)
            ->setToolbar(true)
            ->setXAxisOption(new XAxisOption([
                __('January'),
                __('February'),
                __('March'),
                __('April'),
                __('May'),
                __('June'),
                __('July'),
                __('August'),
                __('September'),
                __('October'),
                __('November'),
                __('December'),
            ]))
            ->addData(__('Deepsky'), $deepsky)
            ->addData(__('Comets'), $comets)
            ->addData(__('Double stars'), $double)
            ->addData(__('Planets'), $planets)
            ->addData(__('Sun'), $sun)
            ->addData(__('Moon'), $moon);
    }

    private function buildObjectTypesChart(array $counts, string $context): OriginalPieChart
    {
        $targetTypes = TargetType::all()->keyBy('id');

        $values = [];
        $labels = [];
        foreach ($counts['deepsky'] as $type => $count) {
            $label = match ($type) {
                'DS' => __('Double stars'),
                default => __($targetTypes->get($type)?->type ?? $type),
            };

            $labels[] = $label;
            $values[] = $count;
        }

        $extraTypes = [
            __('Comets') => $counts['comets'],
            __('Planets') => $counts['planets'],
            __('Sun') => $counts['sun'],
            __('Moon') => $counts['moon'],
        ];

        foreach ($extraTypes as $label => $count) {
            if ($count > 0) {
                $labels[] = $label;
                $values[] = $count;
            }
        }

        [$labels, $values] = $this->collapseSmallSlices($labels, $values);

        return (new OriginalPieChart)
            ->setTitle(__('Object types seen: ') . $context)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($values)
            ->setLabels($labels)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }

    /**
     * @param  array<string, int>  $counts  from countryCounts()
     */
    private function buildCountriesChart(array $counts, string $context): OriginalPieChart
    {
        $localizedCountries = $this->localizedCountryLabels(collect(array_keys($counts)));
        $labels = [];
        $values = [];

        foreach ($counts as $country => $count) {
            $labels[] = $localizedCountries[$country] ?? $country;
            $values[] = $count;
        }

        return (new OriginalPieChart)
            ->setTitle(__('Observations per country: ') . $context)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($values)
            ->setLabels($labels)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }

    /**
     * @param  array<string, int>  $counts  from residenceCounts()
     */
    private function buildResidencesChart(array $counts, string $context): OriginalPieChart
    {
        $labels = [];
        $values = [];
        foreach ($counts as $code => $count) {
            $labels[] = ObserverCountries::label($code) ?? $code;
            $values[] = $count;
        }

        [$labels, $values] = $this->collapseSmallSlices($labels, $values);

        return (new OriginalPieChart)
            ->setTitle(__('Observers per country of residence: ') . $context)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($values)
            ->setLabels($labels)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }

    private function observationQuery(?string $country, ?string $residence = null): Builder
    {
        return DB::table('observations')
            ->when($country, function (Builder $query, string $selectedCountry) {
                $query->join('locations', 'observations.location_id', '=', 'locations.id')
                    ->where('locations.country', $selectedCountry);
            })
            ->when($residence, fn(Builder $query) => $this->whereResidence($query, $residence));
    }

    /**
     * Only the observations of observers who live in the country.
     */
    private function whereResidence(Builder $query, string $residence): Builder
    {
        return $query->whereIn(
            'observations.observer_username',
            DB::table('users')->where('country', $residence)->select('username')
        );
    }

    private function countryOptions(): array
    {
        $countries = Cache::flexible('statistics:countries', self::CACHE_SECONDS, fn() => DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->whereNotNull('locations.country')
            ->where('locations.country', '!=', '')
            ->distinct()
            ->pluck('locations.country')
            ->all());

        $labels = $this->localizedCountryLabels(collect($countries));
        asort($labels, SORT_NATURAL | SORT_FLAG_CASE);

        return $labels;
    }

    private function localizedCountryLabels(Collection $countries): array
    {
        $countryNames = $countries
            ->filter(fn($country) => is_string($country) && trim($country) !== '')
            ->map(fn(string $country) => trim($country))
            ->unique()
            ->values();

        if ($countryNames->isEmpty()) {
            return [];
        }

        $labels = [];
        foreach ($countryNames as $country) {
            $code = Country::codeFor($country);
            $labels[$country] = $code !== null
                ? (Countries::getOne($code, app()->getLocale()) ?? $country)
                : $country;
        }

        return $labels;
    }

    private function collapseSmallSlices(array $labels, array $values): array
    {
        $total = array_sum($values);
        if ($total === 0) {
            return [[], []];
        }

        $keptLabels = [];
        $keptValues = [];
        $rest = 0;

        foreach ($values as $index => $value) {
            if ($value < $total / 100) {
                $rest += $value;
                continue;
            }

            $keptLabels[] = $labels[$index];
            $keptValues[] = $value;
        }

        if ($rest > 0) {
            $keptLabels[] = __('Rest');
            $keptValues[] = $rest;
        }

        return [$keptLabels, $keptValues];
    }

    private function chartContext(?string $countryLabel, ?string $residenceLabel = null): string
    {
        $parts = array_filter([
            $countryLabel,
            $residenceLabel ? __('observers from :country', ['country' => $residenceLabel]) : null,
        ]);

        return $parts === [] ? __('All') : implode(', ', $parts);
    }

    private function datePartExpression(string $part, string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return match ($part) {
                'year' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
                'month' => "CAST(strftime('%m', {$column}) AS INTEGER)",
                default => $column,
            };
        }

        return match ($part) {
            'year' => "YEAR({$column})",
            'month' => "MONTH({$column})",
            default => $column,
        };
    }
}