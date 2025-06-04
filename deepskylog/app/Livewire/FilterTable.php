<?php

namespace App\Livewire;

use App\Models\Filter as DSL_Filter;
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

final class FilterTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'filter-table';

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
        return DSL_Filter::query()->where('user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('makestr', function ($filter) {
                return $filter->filter_make->name;
            })->add('name_link', function ($filter) {
                return '<a class="font-bold" href="/filter/'.Auth()->user()->slug.'/'.$filter->slug.'">'.$filter->name.'</a>';
            })
            ->add('typestr', function ($filter) {
                return $filter->filter_type->name;
            })
            ->add('colorstr', function ($filter) {
                return $filter->filter_color->name;
            })->add('name')
            ->add('active')
            ->add('active_label', fn ($filter) => $filter->active ? 'Yes' : 'No')
            ->add('factor')
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

            Column::make(__('Type'), 'typestr')
                ->searchable(),

            Column::make(__('Color'), 'colorstr')
                ->searchable(),

            Column::add()
                ->title(__('Active'))
                ->field('active')
                ->toggleable(hasPermission: true, trueLabel: 'Yes', falseLabel: 'No')
                ->sortable(),

            Column::make('Wratten', 'wratten')
                ->sortable()
                ->searchable(),

            Column::make('Schott', 'schott')
                ->sortable()
                ->searchable(),

            Column::make(__('# obs'), 'observations')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.filter', ['row' => $row]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        DSL_Filter::query()->where('id', $id)->update([
            $field => boolval($value),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        $filter = DSL_Filter::where('id', '=', $id);
        $filter->delete();
    }
}
