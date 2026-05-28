<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\TargetType;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\BarChart as OriginalBarChart;
use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;
use marineusde\LarapexCharts\Options\XAxisOption;
use Monarobase\CountryList\CountryListFacade as Countries;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $countryOptions = $this->countryOptions();

        $selectedCountry = trim((string) $request->query('country', ''));
        if ($selectedCountry === '') {
            $selectedCountry = null;
        }

        abort_if($selectedCountry !== null && !array_key_exists($selectedCountry, $countryOptions), 404);

        $selectedCountryLabel = $selectedCountry ? $countryOptions[$selectedCountry] : null;

        return view('statistics.index', [
            'countryOptions' => $countryOptions,
            'selectedCountry' => $selectedCountry,
            'selectedCountryLabel' => $selectedCountryLabel,
            'summaryColumns' => $this->summaryColumns(),
            'summaryRows' => $this->summaryRows($selectedCountry),
            'observationsPerYearChart' => $this->buildObservationsPerYearChart($selectedCountry, $selectedCountryLabel),
            'observationsPerMonthChart' => $this->buildObservationsPerMonthChart($selectedCountry, $selectedCountryLabel),
            'objectTypesChart' => $this->buildObjectTypesChart($selectedCountry, $selectedCountryLabel),
            'countriesChart' => $this->buildCountriesChart($selectedCountry, $selectedCountryLabel),
        ]);
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

    private function summaryRows(?string $country): array
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
            $values = [];
            foreach (array_keys($this->summaryColumns()) as $category) {
                $query = $this->categoryQuery($category, $country);

                $values[$category] = match ($key) {
                    'observations' => (int) $query->count(),
                    'last_year' => (int) $query->where('observed_on', '>=', now()->subYear())->count(),
                    'drawings' => (int) $query->where('has_drawing', 1)->count(),
                    'drawings_last_year' => (int) $query->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count(),
                    'unique' => (int) $query->distinct()->count('target_name'),
                };
            }

            $rows[] = [
                'key' => $key,
                'label' => $label,
                'values' => $values,
            ];
        }

        return $rows;
    }

    private function buildObservationsPerYearChart(?string $country, ?string $countryLabel): OriginalLineChart
    {
        $yearExpression = $this->datePartExpression('year', 'observed_on');

        $firstYear = (int) ($this->observationQuery($country)
            ->min(DB::raw($yearExpression)) ?? date('Y'));
        $lastYear = (int) date('Y');
        $xAxis = range($firstYear, $lastYear);

        $deepsky = $this->mapYears($this->groupedByYear('deepsky', $country), $firstYear, $lastYear);
        $comets = $this->mapYears($this->groupedByYear('comets', $country), $firstYear, $lastYear);
        $double = $this->mapYears($this->groupedByYear('double', $country), $firstYear, $lastYear);
        $planets = $this->mapYears($this->groupedByYear('planets', $country), $firstYear, $lastYear);
        $sun = $this->mapYears($this->groupedByYear('sun', $country), $firstYear, $lastYear);
        $moon = $this->mapYears($this->groupedByYear('moon', $country), $firstYear, $lastYear);

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

    private function buildObservationsPerMonthChart(?string $country, ?string $countryLabel): OriginalBarChart
    {
        $deepsky = $this->mapMonths($this->groupedByMonth('deepsky', $country));
        $comets = $this->mapMonths($this->groupedByMonth('comets', $country));
        $double = $this->mapMonths($this->groupedByMonth('double', $country));
        $planets = $this->mapMonths($this->groupedByMonth('planets', $country));
        $sun = $this->mapMonths($this->groupedByMonth('sun', $country));
        $moon = $this->mapMonths($this->groupedByMonth('moon', $country));

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

    private function buildObjectTypesChart(?string $country, ?string $countryLabel): OriginalPieChart
    {
        $directRows = $this->observationQuery($country)
            ->join('objects', 'observations.target_name_normalized', '=', 'objects.name_normalized')
            ->where('observations.target_table', 'objects')
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type');

        $aliasRows = $this->observationQuery($country)
            ->join('objectnames', 'observations.target_key', '=', 'objectnames.slug')
            ->join('objects', 'objectnames.objectname', '=', 'objects.name')
            ->where('observations.target_table', 'objectnames')
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type');

        $targetTypes = TargetType::all()->keyBy('id');
        $countsByType = [];

        foreach ($directRows->get()->merge($aliasRows->get()) as $row) {
            $countsByType[$row->type] = ($countsByType[$row->type] ?? 0) + (int) $row->count;
        }

        $values = [];
        $labels = [];
        foreach ($countsByType as $type => $count) {
            $label = match ($row->type) {
                'DS' => __('Double stars'),
                default => __($targetTypes->get($type)?->type ?? $type),
            };

            $labels[] = $label;
            $values[] = $count;
        }

        $extraTypes = [
            __('Comets') => (int) $this->categoryQuery('comets', $country)->count(),
            __('Planets') => (int) $this->categoryQuery('planets', $country)->count(),
            __('Sun') => (int) $this->categoryQuery('sun', $country)->count(),
            __('Moon') => (int) $this->categoryQuery('moon', $country)->count(),
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

    private function buildCountriesChart(?string $country, ?string $countryLabel): OriginalPieChart
    {
        $rows = DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->whereNotNull('locations.country')
            ->where('locations.country', '!=', '')
            ->when($country, fn(Builder $query) => $query->where('locations.country', $country))
            ->select('locations.country', DB::raw('count(*) as count'))
            ->groupBy('locations.country')
            ->orderByDesc('count')
            ->get();

        $localizedCountries = $this->localizedCountryLabels($rows->pluck('country'));
        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $labels[] = $localizedCountries[$row->country] ?? $row->country;
            $values[] = (int) $row->count;
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
            'planets' => $query->where('target_table', 'planets')->whereRaw('LOWER(target_key) != ?', ['sun']),
            'sun' => $query->where('target_table', 'planets')->whereRaw('LOWER(target_key) = ?', ['sun']),
            'moon' => $query->whereIn('target_table', ['moons', 'lunar_features']),
            default => $query,
        };
    }

    private function applyCometScope(Builder $query): Builder
    {
        return $query->where(function (Builder $comets) {
            $comets->where('legacy_source', 'comet')
                ->orWhere('target_table', 'cometobjects')
                ->orWhere('target_type', 'comet');
        });
    }

    private function applyDeepskyScope(Builder $query): Builder
    {
        return $query->where(function (Builder $outer) {
            $outer->where(function (Builder $modern) {
                $modern->whereNull('legacy_source')
                    ->where(function (Builder $targets) {
                        $targets->where(function (Builder $objects) {
                            $objects->where('target_table', 'objects')
                                ->where(function (Builder $notDouble) {
                                    $notDouble->whereNull('target_name_normalized')
                                        ->orWhereNotIn('target_name_normalized', fn(Builder $subquery) => $this->doubleStarSubquery($subquery));
                                });
                        })->orWhere(function (Builder $aliases) {
                            $aliases->where('target_table', 'objectnames')
                                ->whereNotIn('target_key', fn(Builder $subquery) => $this->doubleStarObjectnameSlugSubquery($subquery));
                        });
                    });
            })->orWhere(function (Builder $legacy) {
                $legacy->where('legacy_source', 'deepsky')
                    ->where(function (Builder $allowed) {
                        $allowed->where(function (Builder $nullTable) {
                            $nullTable->whereNull('target_table');
                        })->orWhere(function (Builder $objects) {
                            $objects->where('target_table', 'objects')
                                ->where(function (Builder $notDouble) {
                                    $notDouble->whereNull('target_name_normalized')
                                        ->orWhereNotIn('target_name_normalized', fn(Builder $subquery) => $this->doubleStarSubquery($subquery));
                                });
                        })->orWhere(function (Builder $aliases) {
                            $aliases->where('target_table', 'objectnames')
                                ->whereNotIn('target_key', fn(Builder $subquery) => $this->doubleStarObjectnameSlugSubquery($subquery));
                        });
                    });
            });
        });
    }

    private function applyDoubleScope(Builder $query): Builder
    {
        return $query->where(function (Builder $double) {
            $double->where(function (Builder $objects) {
                $objects->where('target_table', 'objects')
                    ->where(function (Builder $notDouble) {
                        $notDouble->whereIn('target_name_normalized', fn(Builder $subquery) => $this->doubleStarSubquery($subquery));
                    });
            })->orWhere(function (Builder $aliases) {
                $aliases->where('target_table', 'objectnames')
                    ->whereIn('target_key', fn(Builder $subquery) => $this->doubleStarObjectnameSlugSubquery($subquery));
            });
        });
    }

    private function doubleStarSubquery(Builder $query): void
    {
        $query->select('name_normalized')
            ->from('objects')
            ->where('type', 'DS');
    }

    private function doubleStarObjectnameSlugSubquery(Builder $query): void
    {
        $query->select('objectnames.slug')
            ->from('objectnames')
            ->join('objects', 'objectnames.objectname', '=', 'objects.name')
            ->where('objects.type', 'DS');
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
        $countries = DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->whereNotNull('locations.country')
            ->where('locations.country', '!=', '')
            ->distinct()
            ->pluck('locations.country');

        $labels = $this->localizedCountryLabels($countries);
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