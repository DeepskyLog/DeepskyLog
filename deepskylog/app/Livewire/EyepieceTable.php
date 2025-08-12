<?php

namespace App\Livewire;

use App\Models\Eyepiece;
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

final class EyepieceTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'eyepiece-table';

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

    public function datasource(): Builder
    {
        return Eyepiece::query()->where('user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('make_str', function ($eyepiece) {
                return $eyepiece->eyepiece_make->name;
            })
            ->add('type_str', function ($eyepiece) {
                return $eyepiece->eyepiece_type->name;
            })->add('name_link', function ($eyepiece) {
                return '<a class="font-bold" href="/eyepiece/'.Auth()->user()->slug.'/'.$eyepiece->slug.'">'.$eyepiece->name.'</a>';
            })
            ->add('active')
            ->add('active_label', fn ($instrument) => $instrument->active ? 'Yes' : 'No')
            ->add('focal_length_mm')
            ->add('apparentFOV')
            ->add('max_focal_length_mm', function ($eyepiece) {
                if ($eyepiece->max_focal_length_mm > 0) {
                    return $eyepiece->max_focal_length_mm;
                } else {
                    return '';
                }
            })
            ->add('field_stop_mm', function ($eyepiece) {
                if ($eyepiece->field_stop_mm > 0) {
                    return $eyepiece->field_stop_mm;
                } else {
                    return '';
                }
            })
            ->add('observations')
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make('Make', 'make_str'),
            Column::make('Type', 'type_str'),

            Column::make('Name', 'name_link', 'name')
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
                ->toggleable(hasPermission: true, trueLabel: 'Yes', falseLabel: 'No')
                ->sortable(),

            Column::make('Focal length', 'focal_length_mm')
                ->sortable()
                ->searchable(),

            Column::make('ApparentFOV', 'apparentFOV')
                ->sortable()
                ->searchable(),

            Column::make('Max focal length', 'max_focal_length_mm')
                ->sortable()
                ->searchable(),

            Column::make('Field stop', 'field_stop_mm')
                ->sortable()
                ->searchable(),

            Column::make('Observations', 'observations')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actionsFromView($row): View
    {
        return view('actions.eyepiece', ['row' => $row]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        Eyepiece::query()->where('id', $id)->update([
            $field => boolval($value),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        $eyepiece = Eyepiece::where('id', '=', $id);
        $eyepiece->delete();
    }
}
