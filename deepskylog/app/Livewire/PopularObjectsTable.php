<?php

namespace App\Livewire;

use App\Livewire\Concerns\LimitsExportRows;
use App\Services\PopularObjectsRanking;
use Illuminate\Support\Collection;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

/**
 * The objects of /popular-objects, ranked by their number of observations or
 * drawings. The ranking is cached as a whole (PopularObjectsRanking), so
 * searching, sorting and paging work on that list instead of grouping all
 * observations again.
 */
final class PopularObjectsTable extends PowerGridComponent
{
    use LimitsExportRows;

    public string $tableName = 'popular-objects';

    public string $mode = 'observations';

    public string $sortField = 'rank';

    public string $sortDirection = 'asc';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function mount(): void
    {
        if (!in_array($this->mode, PopularObjectsRanking::MODES, true)) {
            $this->mode = 'observations';
        }

        parent::mount();
    }

    public function setUp(): array
    {
        $this->persist(['columns']);

        return [
            PowerGrid::header()
                ->showSearchInput()->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),

            PowerGrid::responsive()->fixedColumns('name_link'),

            PowerGrid::exportable('popular-objects')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Collection
    {
        return collect(app(PopularObjectsRanking::class)->rows($this->mode));
    }

    /**
     * For a collection, PowerGrid keeps the ids of all matching rows in
     * $filtered, which would put the whole ranking into every response. An
     * export fills it again in its own request.
     */
    public function dehydrate(): void
    {
        $this->filtered = [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('rank')
            ->add('name')
            ->add('name_link', fn($row) => sprintf(
                '<a href="%s" class="font-bold hover:underline">%s</a>',
                e(route('object.show', ['slug' => data_get($row, 'slug')])),
                e(html_entity_decode((string) data_get($row, 'name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ))
            ->add('name_plain', fn($row) => html_entity_decode((string) data_get($row, 'name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ->add('kind')
            ->add('kind_label', fn($row) => e($this->kindLabel((string) data_get($row, 'kind'))))
            ->add('count')
            ->add('count_link', fn($row) => sprintf(
                '<a href="%s" class="font-semibold text-sky-300 hover:text-sky-200 hover:underline">%s</a>',
                e($this->countUrl((string) data_get($row, 'kind'), (string) data_get($row, 'slug'))),
                number_format((int) data_get($row, 'count'))
            ));
    }

    public function columns(): array
    {
        return [
            Column::make(__('Rank'), 'rank')
                ->sortable(),

            Column::make(__('Object'), 'name_link', 'name')
                ->searchable()
                ->sortable()
                ->visibleInExport(false),

            Column::make(__('Object'), 'name_plain')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Type'), 'kind_label', 'kind')
                ->sortable(),

            Column::make($this->mode === 'drawings' ? __('Drawings') : __('Observations'), 'count_link', 'count')
                ->sortable()
                ->visibleInExport(false),

            Column::make($this->mode === 'drawings' ? __('Drawings') : __('Observations'), 'count')
                ->hidden()
                ->visibleInExport(true),
        ];
    }

    private function kindLabel(string $kind): string
    {
        return match ($kind) {
            'comet' => __('Comet'),
            'planet' => __('Planet'),
            default => __('Deep-sky'),
        };
    }

    private function countUrl(string $kind, string $slug): string
    {
        if ($kind === 'comet') {
            return $this->mode === 'drawings'
                ? route('cometdrawings.show', ['observer' => $slug])
                : route('observations.comet.object', ['object' => $slug]);
        }

        return $this->mode === 'drawings'
            ? route('observations.drawings.show', ['slug' => $slug])
            : route('observations.object', ['slug' => $slug]);
    }
}
