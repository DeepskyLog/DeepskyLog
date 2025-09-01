<?php

namespace App\Livewire;

use App\Models\InstrumentSet;
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
class InstrumentSetTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = false;

    public string $tableName = 'instrumentset-table';

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
        return InstrumentSet::query()
            ->where('user_id', Auth::id())
            ->withCount(['instruments', 'locations', 'eyepieces', 'filters', 'lenses']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name_link', function ($set) {
                return '<a class="font-bold" href="/instrumentset/'.Auth()->user()->slug.'/'.$set->slug.'">'.$set->name.'</a>';
            })
            ->add('name')
            ->add('instruments_count')
            ->add('locations_count')
            ->add('eyepieces_count')
            ->add('filters_count')
            ->add('lenses_count')
            ->add('created_at_formatted', fn ($s) => Carbon::parse($s->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Name'), 'name_link', 'name')
                ->visibleInExport(false)
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->searchable()
                ->hidden()
                ->visibleInExport(true),

            Column::make('Created At', 'created_at_formatted'),

            Column::make('Instruments', 'instruments_count')
                ->sortable(),

            Column::make('Locations', 'locations_count')
                ->sortable(),

            Column::make('Eyepieces', 'eyepieces_count')
                ->sortable(),

            Column::make('Filters', 'filters_count')
                ->sortable(),

            Column::make('Lenses', 'lenses_count')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.instrumentset', ['row' => $row]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        $set = InstrumentSet::find($id);

        if (! $set) {
            return;
        }

        // Detach all related pivot records first to avoid FK constraint failures
        $set->instruments()->detach();
        $set->locations()->detach();
        $set->eyepieces()->detach();
        $set->filters()->detach();
        $set->lenses()->detach();

        $set->delete();
    }

    #[On('clickToMakeDefault')]
    public function clickToMakeDefault(int $id): void
    {
        auth()->user()->forceFill([
            'stdinstrumentset' => $id,
        ])->save();
    }
}
