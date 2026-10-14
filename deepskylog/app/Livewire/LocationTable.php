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
        return Location::query()->where('user_id', Auth::id());
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

            Column::make('SQM', 'skyBackground')
                ->sortable()
                ->searchable(),

            Column::make('NELM', 'limitingMagnitude')
                ->sortable()
                ->searchable(),

            Column::make('Bortle', 'bortle'),

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
