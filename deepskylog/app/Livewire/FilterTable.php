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
        // The make, type and color columns are searchable, so their tables are
        // joined here: PowerGrid qualifies a searchable column with the table of
        // the query's base model unless the column carries its own table prefix.
        return DSL_Filter::query()
            ->select('filters.*')
            ->leftJoin('filter_makes', 'filter_makes.id', '=', 'filters.make_id')
            ->leftJoin('filter_types', 'filter_types.id', '=', 'filters.type_id')
            ->leftJoin('filter_colors', 'filter_colors.id', '=', 'filters.color_id')
            ->where('filters.user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('makestr', function ($filter) {
                return e(html_entity_decode((string) $filter->filter_make->name));
            })->add('name_link', function ($filter) {
                return '<a class="font-bold" href="/filter/'.e(Auth()->user()->slug).'/'.e($filter->slug).'">'.e(html_entity_decode((string) $filter->name)).'</a>';
            })
            ->add('typestr', function ($filter) {
                return e(html_entity_decode((string) $filter->filter_type->name));
            })
            ->add('colorstr', function ($filter) {
                return e(html_entity_decode((string) $filter->filter_color->name));
            })->add('name')
            ->add('active')
            ->add('active_label', fn ($filter) => $filter->active ? 'Yes' : 'No')
            ->add('factor')
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Make'), 'makestr', 'filter_makes.name')
                ->searchable(),

            Column::make('Name', 'name_link', 'name')
                ->visibleInExport(false)
                ->sortable(),

            // Hidden in the grid, but included in the exported file
            Column::make('Name', 'name')
                ->searchable()
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Type'), 'typestr', 'filter_types.name')
                ->searchable(),

            Column::make(__('Color'), 'colorstr', 'filter_colors.name')
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
        // $id and $field come from the browser: only the active flag of the
        // user's own records may be toggled.
        if ($field !== 'active') {
            return;
        }

        DSL_Filter::query()->where('id', $id)->where('user_id', Auth::id())->update([
            'active' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        // Delete the model, not a query, so it detaches itself from instrument
        // sets and is cleared as the default of its user.
        DSL_Filter::where('id', $id)->where('user_id', Auth::id())->first()?->delete();
    }
}
