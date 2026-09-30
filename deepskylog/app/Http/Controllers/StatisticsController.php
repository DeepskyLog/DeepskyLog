<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Observation;
use App\Models\TargetType;
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
     * The counts are cached for an hour: they are aggregates over all
     * observations (a few seconds of queries), and a new observation changing
     * them an hour later is no problem.
     */
    private const CACHE_SECONDS = 3600;

    private const CATEGORIES = ['deepsky', 'comets', 'double', 'planets', 'sun', 'moon'];

    public function index(Request $request)
    {
        $countryOptions = $this->countryOptions();

        $selectedCountry = trim((string) $request->query('country', ''));
        if ($selectedCountry === '') {
            $selectedCountry = null;
        }

        abort_if($selectedCountry !== null && !array_key_exists($selectedCountry, $countryOptions), 404);

        $selectedCountryLabel = $selectedCountry ? $countryOptions[$selectedCountry] : null;

        // Only numbers are cached; the labels and charts are built per request,
        // in the language of the visitor.
        $counts = Cache::remember(
            'statistics:counts:' . ($selectedCountry ?? 'all'),
            self::CACHE_SECONDS,
            fn() => $this->counts($selectedCountry)
        );

        return view('statistics.index', [
            'countryOptions' => $countryOptions,
            'selectedCountry' => $selectedCountry,
            'selectedCountryLabel' => $selectedCountryLabel,
            'summaryColumns' => $this->summaryColumns(),
            'summaryRows' => $this->summaryRows($counts['summary']),
            'observationsPerYearChart' => $this->buildObservationsPerYearChart($counts['per_year'], $selectedCountryLabel),
            'observationsPerMonthChart' => $this->buildObservationsPerMonthChart($counts['per_month'], $selectedCountryLabel),
            'objectTypesChart' => $this->buildObjectTypesChart($counts['object_types'], $selectedCountryLabel),
            'countriesChart' => $this->buildCountriesChart($counts['countries'], $selectedCountryLabel),
        ]);
    }

    /**
     * All counts of the page for one country (or all countries), as plain
     * arrays so they can be cached.
     *
     * @return array{summary: array<string, array<string, int>>, per_year: array<string, mixed>, per_month: array<string, list<int>>, object_types: array<string, mixed>, countries: array<string, int>}
     */
    private function counts(?string $country): array
    {
        return [
            'summary' => $this->summaryCounts($country),
            'per_year' => $this->perYearCounts($country),
            'per_month' => $this->perMonthCounts($country),
            'object_types' => $this->objectTypeCounts($country),
            'countries' => $this->countryCounts($country),
        ];
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
     * @return array<string, array<string, int>> row => category => count
     */
    private function summaryCounts(?string $country): array
    {
        $counts = [];
        foreach (array_keys($this->summaryColumns()) as $category) {
            $query = fn() => $this->categoryQuery($category, $country);

            $counts['observations'][$category] = (int) $query()->count();
            $counts['last_year'][$category] = (int) $query()->where('observed_on', '>=', now()->subYear())->count();
            $counts['drawings'][$category] = (int) $query()->where('has_drawing', 1)->count();
            $counts['drawings_last_year'][$category] = (int) $query()->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count();
            $counts['unique'][$category] = Observation::countDifferentObjects($query());
        }

        return $counts;
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
     * @return array{first_year: int, last_year: int, series: array<string, list<int>>}
     */
    private function perYearCounts(?string $country): array
    {
        $yearExpression = $this->datePartExpression('year', 'observed_on');

        $firstYear = (int) ($this->observationQuery($country)
            ->min(DB::raw($yearExpression)) ?? date('Y'));
        $lastYear = (int) date('Y');

        $series = [];
        foreach (self::CATEGORIES as $category) {
            $series[$category] = $this->mapYears($this->groupedByYear($category, $country), $firstYear, $lastYear);
        }

        return ['first_year' => $firstYear, 'last_year' => $lastYear, 'series' => $series];
    }

    /**
     * @return array<string, list<int>> category => 12 monthly counts
     */
    private function perMonthCounts(?string $country): array
    {
        $series = [];
        foreach (self::CATEGORIES as $category) {
            $series[$category] = $this->mapMonths($this->groupedByMonth($category, $country));
        }

        return $series;
    }

    /**
     * @return array{deepsky: array<string, int>, comets: int, planets: int, sun: int, moon: int}
     */
    private function objectTypeCounts(?string $country): array
    {
        $rows = $this->observationQuery($country)
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
            'comets' => (int) $this->categoryQuery('comets', $country)->count(),
            'planets' => (int) $this->categoryQuery('planets', $country)->count(),
            'sun' => (int) $this->categoryQuery('sun', $country)->count(),
            'moon' => (int) $this->categoryQuery('moon', $country)->count(),
        ];
    }

    /**
     * @return array<string, int> country => observations, most first
     */
    private function countryCounts(?string $country): array
    {
        return DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->whereNotNull('locations.country')
            ->where('locations.country', '!=', '')
            ->when($country, fn(Builder $query) => $query->where('locations.country', $country))
            ->select('locations.country', DB::raw('count(*) as count'))
            ->groupBy('locations.country')
            ->orderByDesc('count')
            ->pluck('count', 'country')
            ->map(fn($count) => (int) $count)
            ->all();
    }

    private function buildObservationsPerYearChart(array $counts, ?string $countryLabel): OriginalLineChart
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
            ->setTitle(__('Number of observations per year: ') . $this->chartContext($countryLabel))
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

    private function buildObservationsPerMonthChart(array $counts, ?string $countryLabel): OriginalBarChart
    {
        ['deepsky' => $deepsky, 'comets' => $comets, 'double' => $double, 'planets' => $planets, 'sun' => $sun, 'moon' => $moon] = $counts;

        return (new OriginalBarChart)
            ->setTitle(__('Number of observations per month: ') . $this->chartContext($countryLabel))
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

    private function buildObjectTypesChart(array $counts, ?string $countryLabel): OriginalPieChart
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
            ->setTitle(__('Object types seen: ') . $this->chartContext($countryLabel))
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
    private function buildCountriesChart(array $counts, ?string $countryLabel): OriginalPieChart
    {
        $localizedCountries = $this->localizedCountryLabels(collect(array_keys($counts)));
        $labels = [];
        $values = [];

        foreach ($counts as $country => $count) {
            $labels[] = $localizedCountries[$country] ?? $country;
            $values[] = $count;
        }

        return (new OriginalPieChart)
            ->setTitle(__('Observations per country: ') . $this->chartContext($countryLabel))
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($values)
            ->setLabels($labels)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }

    private function groupedByYear(string $category, ?string $country): Collection
    {
        $yearExpression = $this->datePartExpression('year', 'observed_on');

        return $this->categoryQuery($category, $country)
            ->selectRaw($yearExpression . ' as year, count(*) as cnt')
            ->groupByRaw($yearExpression)
            ->get();
    }

    private function groupedByMonth(string $category, ?string $country): Collection
    {
        $monthExpression = $this->datePartExpression('month', 'observed_on');

        return $this->categoryQuery($category, $country)
            ->selectRaw($monthExpression . ' as month, count(*) as cnt')
            ->groupByRaw($monthExpression)
            ->get();
    }

    private function mapYears(Collection $grouped, int $firstYear, int $lastYear): array
    {
        $series = array_fill(0, $lastYear - $firstYear + 1, 0);

        foreach ($grouped as $row) {
            $year = (int) $row->year;
            if ($year >= $firstYear && $year <= $lastYear) {
                $series[$year - $firstYear] = (int) $row->cnt;
            }
        }

        return $series;
    }

    private function mapMonths(Collection $grouped): array
    {
        $series = array_fill(0, 12, 0);

        foreach ($grouped as $row) {
            $month = (int) $row->month;
            if ($month >= 1 && $month <= 12) {
                $series[$month - 1] = (int) $row->cnt;
            }
        }

        return $series;
    }

    private function categoryQuery(string $category, ?string $country): Builder
    {
        $query = $this->observationQuery($country);

        return match ($category) {
            'total' => $query,
            'deepsky' => $this->applyDeepskyScope($query),
            'comets' => $this->applyCometScope($query),
            'double' => $this->applyDoubleScope($query),
            'planets' => $query->where('target_table', 'planets')->where('target_key', '!=', 'sun'),
            'sun' => $query->where('target_table', 'planets')->where('target_key', 'sun'),
            'moon' => $query->whereIn('target_table', ['moons', 'lunar_features']),
            default => $query,
        };
    }

    private function applyCometScope(Builder $query): Builder
    {
        return $query->where('target_type', Observation::TYPE_COMET);
    }

    private function applyDeepskyScope(Builder $query): Builder
    {
        return $query->where('target_type', Observation::TYPE_DEEPSKY)
            ->where(function (Builder $notDouble) {
                $notDouble->whereNull('target_name_normalized')
                    ->orWhereNotIn('target_name_normalized', fn(Builder $subquery) => $this->doubleStarSubquery($subquery));
            });
    }

    private function applyDoubleScope(Builder $query): Builder
    {
        return $query->where('target_type', Observation::TYPE_DEEPSKY)
            ->whereIn('target_name_normalized', fn(Builder $subquery) => $this->doubleStarSubquery($subquery));
    }

    private function doubleStarSubquery(Builder $query): void
    {
        $query->select('name_normalized')
            ->from('objects')
            ->where('type', 'DS');
    }

    private function observationQuery(?string $country): Builder
    {
        return DB::table('observations')
            ->when($country, function (Builder $query, string $selectedCountry) {
                $query->join('locations', 'observations.location_id', '=', 'locations.id')
                    ->where('locations.country', $selectedCountry);
            });
    }

    private function countryOptions(): array
    {
        $countries = Cache::remember('statistics:countries', self::CACHE_SECONDS, fn() => DB::table('observations')
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

        $countryModels = Country::whereIn('country', $countryNames->all())
            ->get()
            ->keyBy('country');

        $labels = [];
        foreach ($countryNames as $country) {
            $countryModel = $countryModels->get($country);
            $labels[$country] = $countryModel && !empty($countryModel->code)
                ? (Countries::getOne($countryModel->code, app()->getLocale()) ?? $countryModel->country)
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

    private function chartContext(?string $countryLabel): string
    {
        return $countryLabel ?: __('All');
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