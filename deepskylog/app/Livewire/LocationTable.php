<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\Location;
use Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class LocationTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'location-table';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()
                ->showSearchInput()->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),

            PowerGrid::responsive()->fixedColumns('name'),

            PowerGrid::exportable()
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        // SQM, NELM and Bortle are shown as computed by Location::getSqm(), getNelm()
        // and getBortle() (the missing value derived from the other one, with the
        // formulas and thresholds of the astronomy library's Magnitude class). They
        // are computed in SQL as well, in a derived table that keeps the name
        // `locations`, so that sorting, searching and exporting use the shown values.
        // The FLOAT columns are cast to DECIMAL so that MariaDB compares e.g. 19.1
        // to the thresholds as PHP does, not as 19.100000381.
        $sqm = 'CAST(skyBackground AS DECIMAL(6,3))';
        $nelm = 'CAST(limitingMagnitude AS DECIMAL(6,3))';
        $sqmFromNelm = "21.58 - 5 * LOG10(POWER(10, 1.586 - {$nelm} / 5.0) - 1)";
        $nelmFromSqm = "7.93 - 5 * LOG10(1 + POWER(10, 4.316 - {$sqm} / 5.0))";
        $fstOffset = (float) (Auth::user()->fstOffset ?? 0);

        $locations = Location::query()
            ->select('locations.*')
            ->selectRaw("CASE
                WHEN {$sqm} > 0 THEN {$sqm}
                WHEN {$nelm} >= 7.93 THEN 22
                WHEN {$nelm} > 0 THEN ROUND(CASE WHEN {$sqmFromNelm} > 22 THEN 22 ELSE {$sqmFromNelm} END, 2)
            END AS sqm")
            ->selectRaw("CASE
                WHEN {$nelm} > 0 THEN ROUND({$nelm} - ?, 2)
                WHEN {$sqm} > 0 THEN ROUND(CASE WHEN {$nelmFromSqm} < 2.5 THEN 2.5 ELSE {$nelmFromSqm} END - ?, 1)
            END AS nelm", [$fstOffset, $fstOffset])
            ->selectRaw("CASE
                WHEN {$sqm} > 0 THEN CASE
                    WHEN {$sqm} <= 17.5 THEN 9 WHEN {$sqm} <= 18.0 THEN 8 WHEN {$sqm} <= 18.5 THEN 7
                    WHEN {$sqm} <= 19.1 THEN 6 WHEN {$sqm} <= 20.4 THEN 5 WHEN {$sqm} <= 21.3 THEN 4
                    WHEN {$sqm} <= 21.5 THEN 3 WHEN {$sqm} <= 21.7 THEN 2 ELSE 1 END
                WHEN {$nelm} > 0 THEN CASE
                    WHEN {$nelm} < 3.6 THEN 9 WHEN {$nelm} < 3.9 THEN 8 WHEN {$nelm} < 4.4 THEN 7
                    WHEN {$nelm} < 4.9 THEN 6 WHEN {$nelm} < 5.8 THEN 5 WHEN {$nelm} < 6.3 THEN 4
                    WHEN {$nelm} < 6.4 THEN 3 WHEN {$nelm} < 6.5 THEN 2 ELSE 1 END
            END AS bortle")
            ->where('user_id', Auth::id());

        return Location::query()->fromSub($locations, 'locations');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name_link', function ($location) {
                return '<a class="font-bold" href="/location/'.e(Auth()->user()->slug).'/'.e($location->slug).'">'.e(html_entity_decode((string) $location->name)).'</a>';
            })
            ->add('name')
            ->add('active')
            ->add('country', function ($location) {
                $country = (string) $location->country;
                $code = Country::codeFor($country);

                // Unknown or empty countries (e.g. 'Unknown' after a failed lookup) are shown as is
                if ($code === null) {
                    return ($country === '' || $country === 'Unknown') ? __('Unknown') : e($country);
                }

                // Translate country using the current language
                return Countries::getOne($code, app()->getLocale());
            })
            ->add('elevation', function ($location) {
                // Convert the elevation to feet if it is set
                if (Auth()->user()->showInches) {
                    $elevation = round($location->elevation * 3.28084).' ft';
                } else {
                    $elevation = $location->elevation.' m';
                }

                return $elevation;
            })
            ->add('skyBackground', function ($location) {
                $sqm = $location->getSqm();

                return $sqm !== null ? $sqm : __('Unknown');
            })
            ->add('limitingMagnitude', function ($location) {
                $fstOffset = Auth()->user()->fstOffset ?? 0;
                $nelm = $location->getNelm($fstOffset);

                return $nelm !== null ? $nelm : __('Unknown');
            })
            ->add('bortle', function ($location) {
                $bortle = $location->getBortle();

                return $bortle !== null ? $bortle : __('Unknown');
            })
            ->add('weather', function ($location) {
                return '<a href="https://clearoutside.com/forecast/'
                    .round($location->latitude, 2).'/'
                    .round($location->longitude, 2).'">
                        <img alt="Weather forecast" src="https://clearoutside.com/forecast_image_small/'
                    .round($location->latitude, 2).'/'
                    .round($location->longitude, 2).'/forecast.png" />
                        </a>';
            })
            ->add('active_label', fn ($location) => $location->active ? 'Yes' : 'No')
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Name'), 'name_link', 'name')
                ->visibleInExport(false)
                ->sortable(),

            // Hidden in the grid, but included in the exported file
            Column::make('Name', 'name')
                ->searchable()
                ->hidden()
                ->visibleInExport(true),

            Column::add()
                ->title(__('Active'))
                ->field('active')
                ->toggleable()
                ->sortable(),

            Column::make(__('Country'), 'country')
                ->searchable()
                ->sortable(),

            Column::make(__('Elevation'), 'elevation')
                ->searchable()
                ->sortable(),

            Column::make('SQM', 'skyBackground', 'sqm')
                ->sortable()
                ->searchable(),

            Column::make('NELM', 'limitingMagnitude', 'nelm')
                ->sortable()
                ->searchable(),

            Column::make('Bortle', 'bortle', 'bortle')
                ->sortable(),

            Column::make(__('Weather'), 'weather')
                ->visibleInExport(false),

            Column::make(__('# obs'), 'observations')
                ->sortable(),

            Column::make('Created At', 'created_at_formatted'),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.location', ['row' => $row]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        // $id and $field come from the browser: only the active flag of the
        // user's own records may be toggled.
        if ($field !== 'active') {
            return;
        }

        Location::query()->where('id', $id)->where('user_id', Auth::id())->update([
            'active' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        // Delete the model, not a query, so it detaches itself from instrument
        // sets and is cleared as the default of its user.
        Location::where('id', $id)->where('user_id', Auth::id())->first()?->delete();
    }

    #[On('clickToMakeDefault')]
    public function clickToMakeDefault(int $id): void
    {
        if (Location::where('id', $id)->where('user_id', Auth::id())->exists()) {
            auth()->user()->forceFill([
                'stdlocation' => $id,
            ])->save();
        }
    }
}
