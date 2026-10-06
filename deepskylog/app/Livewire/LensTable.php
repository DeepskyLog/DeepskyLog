<?php

namespace App\Livewire;

use App\Models\Lens;
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
class LensTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'lens-table';

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
        // The make column is searchable, so lens_makes is joined here: PowerGrid
        // qualifies a searchable column with the table of the query's base model
        // unless the column carries its own table prefix.
        return Lens::query()
            ->select('lenses.*')
            ->leftJoin('lens_makes', 'lens_makes.id', '=', 'lenses.make_id')
            ->where('lenses.user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('makestr', function ($lens) {
                return e(html_entity_decode((string) $lens->lens_make->name));
            })->add('name_link', function ($lens) {
                return '<a class="font-bold" href="/lens/'.e(Auth()->user()->slug).'/'.e($lens->slug).'">'.e(html_entity_decode((string) $lens->name)).'</a>';
            })
            ->add('name')
            ->add('active')
            ->add('active_label', fn ($lens) => $lens->active ? 'Yes' : 'No')
            ->add('factor')
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Make'), 'makestr', 'lens_makes.name')
                ->searchable(),

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

            Column::make('Factor', 'factor')
                ->sortable()
                ->searchable(),

            Column::make(__('# obs'), 'observations')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.lens', ['row' => $row]);
    }

    public function onUpdatedToggleable($id, $field, $value): void
    {
        // $id and $field come from the browser: only the active flag of the
        // user's own records may be toggled.
        if ($field !== 'active') {
            return;
        }

        Lens::query()->where('id', $id)->where('user_id', Auth::id())->update([
            'active' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        // Delete the model, not a query, so it detaches itself from instrument
        // sets and is cleared as the lens of its telescopes.
        Lens::where('id', $id)->where('user_id', Auth::id())->first()?->delete();
    }
}
