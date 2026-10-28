<?php

namespace App\Livewire;

use App\Livewire\Concerns\SortsEmptyLast;
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

final class EyepieceTable extends PowerGridComponent
{
    use SortsEmptyLast;

    public bool $multiSort = false;

    public string $tableName = 'eyepiece-table';

    // The datasource joins the makes and types, which have an id as well.
    public string $primaryKey = 'eyepieces.id';

    public ?string $primaryKeyAlias = 'id';

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
        // The make and type columns are searchable and sortable, so their tables
        // are joined here: PowerGrid qualifies a column with the table of the
        // query's base model unless the column carries its own table prefix.
        return Eyepiece::query()
            ->select('eyepieces.*')
            ->leftJoin('eyepiece_makes', 'eyepiece_makes.id', '=', 'eyepieces.make_id')
            ->leftJoin('eyepiece_types', 'eyepiece_types.id', '=', 'eyepieces.type_id')
            ->where('eyepieces.user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('make_str', function ($eyepiece) {
                return e(html_entity_decode((string) $eyepiece->eyepiece_make->name));
            })
            ->add('type_str', function ($eyepiece) {
                return e(html_entity_decode((string) $eyepiece->eyepiece_type->name));
            })->add('name_link', function ($eyepiece) {
                return '<a class="font-bold" href="/eyepiece/'.e(Auth()->user()->slug).'/'.e($eyepiece->slug).'">'.e(html_entity_decode((string) $eyepiece->name)).'</a>';
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
            Column::make('Make', 'make_str', 'eyepiece_makes.name')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('eyepiece_makes.name')),
            Column::make('Type', 'type_str', 'eyepiece_types.name')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('eyepiece_types.name')),

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
                ->sortUsing($this->sortEmptyLast('eyepieces.apparentFOV', 'eyepieces.apparentFOV IS NULL'))
                ->searchable(),

            Column::make('Max focal length', 'max_focal_length_mm')
                ->sortUsing($this->sortEmptyLast('eyepieces.max_focal_length_mm', 'eyepieces.max_focal_length_mm IS NULL OR eyepieces.max_focal_length_mm <= 0'))
                ->searchable(),

            Column::make('Field stop', 'field_stop_mm')
                ->sortUsing($this->sortEmptyLast('eyepieces.field_stop_mm', 'eyepieces.field_stop_mm IS NULL OR eyepieces.field_stop_mm <= 0'))
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

    public function actionsFromView($row): View
    {
        return view('actions.eyepiece', ['row' => $row]);
    }

    public function onPluginUpdated(string $plugin, string $event, array $params): void
    {
        // The id and field come from the browser: only the active flag of the
        // user's own records may be toggled.
        if ($plugin !== 'toggleable' || ($params['field'] ?? null) !== 'active') {
            return;
        }

        Eyepiece::query()->where('id', $params['id'] ?? null)->where('user_id', Auth::id())->update([
            'active' => filter_var($params['value'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        // Delete the model, not a query, so it detaches itself from instrument
        // sets and is cleared as the default of its user.
        Eyepiece::where('id', $id)->where('user_id', Auth::id())->first()?->delete();
    }

    #[On('clickToMakeDefault')]
    public function clickToMakeDefault(int $id): void
    {
        if (Eyepiece::where('id', $id)->where('user_id', Auth::id())->exists()) {
            auth()->user()->forceFill([
                'stdeyepiece' => $id,
            ])->save();
        }
    }
}
