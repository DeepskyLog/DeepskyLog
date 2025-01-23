<?php

namespace App\Livewire;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

#[Lazy]
class InstrumentTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'instrument-table';

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

            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): ?Builder
    {
        return Instrument::query()->where('user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('makestr', function ($instrument) {
                return $instrument->make->name;
            })->add('name_link', function ($instrument) {
                return '<a href="/instrument/'.$instrument->id.'">'.$instrument->name.'</a>';
            })
            ->add('aperture_mm', function ($instrument) {
                if (Auth::user()->showInches) {
                    return round($instrument->aperture_mm / 25.4, 1);
                } else {
                    return $instrument->aperture_mm;
                }
            })
            ->add('name')
            ->add('active')
            ->add('active_label', fn ($instrument) => $instrument->active ? 'Yes' : 'No')
            ->add('instrument_type_name', function ($instrument) {
                return __($instrument->instrument_type->name);
            })
            ->add('focal_length', function ($instrument) {
                if ($instrument->focal_length_mm <= 1) {
                    return '';
                } else {
                    if (Auth::user()->showInches) {
                        return round($instrument->focal_length_mm / 25.4, 1);
                    } else {
                        return $instrument->focal_length_mm;
                    }
                }
            })
            ->add('focal_ratio', function ($instrument) {
                if ($instrument->fd <= 1) {
                    return '';
                } else {
                    return $instrument->fd;
                }
            })
            ->add('mount', function ($instrument) {
                return __($instrument->mount_type->name);
            })
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Make'), 'makestr')
                ->searchable(),

            Column::make(__('Name'), 'name_link', 'name')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title(__('Active'))
                ->field('active')
                ->toggleable(hasPermission: true, trueLabel: 'Yes', falseLabel: 'No')
                ->sortable(),

            Column::make(__('Type'), 'instrument_type_name')
                ->searchable(),

            Column::make(__('Aperture'), 'aperture_mm')
                ->searchable()
                ->sortable(),

            Column::make(__('Focal Length'), 'focal_length', 'focal_length_mm')
                ->searchable()
                ->sortable(),

            Column::make(__('Fixed X'), 'fixedMagnification')
                ->searchable()
                ->sortable(),

            Column::make(__('F/D'), 'focal_ratio', 'fd')
                ->searchable()
                ->sortable(),

            Column::make(__('Obstr. (%)'), 'obstruction_perc')
                ->searchable()
                ->sortable(),

            Column::make(__('Mount'), 'mount')
                ->searchable(),

            Column::make(__('Flip'), 'flip_image')
                ->sortable(),

            Column::make(__('Flop'), 'flop_image')
                ->sortable(),

            Column::make(__('# obs'), 'observations')
                ->sortable(),

            Column::make('Created At', 'created_at_formatted'),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.delete-instrument', ['row' => $row]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        Instrument::query()->where('id', $id)->update([
            $field => boolval($value),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        $instrument = Instrument::where('id', '=', $id);
        $instrument->delete();
    }
}
