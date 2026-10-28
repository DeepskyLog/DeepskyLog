<?php

namespace App\Livewire;

use App\Livewire\Concerns\SortsEmptyLast;
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

#[Lazy]
class InstrumentTable extends PowerGridComponent
{
    use SortsEmptyLast;

    public bool $multiSort = false;

    public string $tableName = 'instrument-table';

    // The datasource joins the makes and types, which have an id as well.
    public string $primaryKey = 'instruments.id';

    public ?string $primaryKeyAlias = 'id';

    // Instrument types used without a mount: their Mount cell stays empty.
    private const TYPES_WITHOUT_MOUNT = ['Naked Eye', 'Binoculars', 'Finderscope'];

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
        // The make, type and mount columns are searchable, so their tables are
        // joined here: PowerGrid qualifies a searchable column with the table of
        // the query's base model unless the column carries its own table prefix.
        return Instrument::query()
            ->select('instruments.*')
            ->leftJoin('instrument_makes', 'instrument_makes.id', '=', 'instruments.make_id')
            ->leftJoin('instrument_types', 'instrument_types.id', '=', 'instruments.instrument_type_id')
            ->leftJoin('mount_types', 'mount_types.id', '=', 'instruments.mount_type_id')
            ->where('instruments.user_id', Auth::id());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('makestr', function ($instrument) {
                return e(html_entity_decode((string) $instrument->instrument_make->name));
            })->add('name_link', function ($instrument) {
                return '<a class="font-bold" href="/instrument/'.e(Auth()->user()->slug).'/'.e($instrument->slug).'">'.e(html_entity_decode((string) $instrument->name)).'</a>';
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
                if (in_array($instrument->instrument_type->name, self::TYPES_WITHOUT_MOUNT, true)) {
                    return '';
                } else {
                    return e(html_entity_decode((string) $instrument->mount_type->name));
                }
            })
            ->add('created_at_formatted', fn ($dish) => Carbon::parse($dish->created_at)->format('M j, Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Make'), 'makestr', 'instrument_makes.name')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instrument_makes.name')),

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

            Column::make(__('Type'), 'instrument_type_name', 'instrument_types.name')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instrument_types.name')),

            Column::make(__('Aperture'), 'aperture_mm')
                ->searchable()
                ->sortable(),

            Column::make(__('Focal Length'), 'focal_length', 'focal_length_mm')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instruments.focal_length_mm', 'instruments.focal_length_mm IS NULL OR instruments.focal_length_mm <= 1')),

            Column::make(__('Fixed X'), 'fixedMagnification')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instruments.fixedMagnification', 'instruments.fixedMagnification IS NULL')),

            Column::make(__('F/D'), 'focal_ratio', 'fd')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instruments.fd', 'instruments.fd IS NULL OR instruments.fd <= 1')),

            Column::make(__('Obstr. (%)'), 'obstruction_perc')
                ->searchable()
                ->sortUsing($this->sortEmptyLast('instruments.obstruction_perc', 'instruments.obstruction_perc IS NULL')),

            Column::make(__('Mount'), 'mount', 'mount_types.name')
                ->searchable()
                ->sortUsing($this->sortEmptyLast(
                    'mount_types.name',
                    "mount_types.name IS NULL OR mount_types.name = '' OR instrument_types.name IN ("
                        .implode(', ', array_fill(0, count(self::TYPES_WITHOUT_MOUNT), '?')).')',
                    self::TYPES_WITHOUT_MOUNT
                )),

            Column::make(__('Flip'), 'flip_image')
                ->sortable(),

            Column::make('Created At', 'created_at_formatted'),

            Column::make(__('Flop'), 'flop_image')
                ->sortable(),

            Column::make(__('# obs'), 'observations')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function actionsFromView($row): View
    {
        return view('actions.instrument', ['row' => $row]);
    }

    public function onPluginUpdated(string $plugin, string $event, array $params): void
    {
        // The id and field come from the browser: only the active flag of the
        // user's own records may be toggled.
        if ($plugin !== 'toggleable' || ($params['field'] ?? null) !== 'active') {
            return;
        }

        Instrument::query()->where('id', $params['id'] ?? null)->where('user_id', Auth::id())->update([
            'active' => filter_var($params['value'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    #[On('clickToDelete')]
    public function clickToDelete(int $id): void
    {
        // Delete the model, not a query, so it detaches itself from instrument
        // sets and is cleared as the default of its user.
        Instrument::where('id', $id)->where('user_id', Auth::id())->first()?->delete();
    }

    #[On('clickToMakeDefault')]
    public function clickToMakeDefault(int $id): void
    {
        if (Instrument::where('id', $id)->where('user_id', Auth::id())->exists()) {
            auth()->user()->forceFill([
                'stdtelescope' => $id,
            ])->save();
        }
    }
}
