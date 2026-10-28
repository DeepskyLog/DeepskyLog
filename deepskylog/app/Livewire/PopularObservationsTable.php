<?php

namespace App\Livewire;

use App\Livewire\Concerns\LimitsExportRows;
use App\Models\ObservationLike;
use App\Services\ObserverCountries;
use App\Services\Observations\ObservationCardData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PopularObservationsTable extends PowerGridComponent
{
    use LimitsExportRows;

    public string $tableName = 'popular-observations';

    /** The country of residence of the observers of the observations, null for all. */
    public ?string $country = null;

    public function mount(): void
    {
        if (!ObserverCountries::isOption($this->country)) {
            $this->country = null;
        }

        parent::mount();
    }

    public function setUp(): array
    {
        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()
                ->showSearchInput()->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),

            PowerGrid::exportable()
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        $aggQuery = ObservationLike::selectRaw(
            'observation_type, observation_id, COUNT(*) as likes, MIN(id) as id, '
            . '(SELECT o.observed_on FROM observations o WHERE o.id = observation_id) as obs_date'
            . ', (SELECT o.observer_username FROM observations o WHERE o.id = observation_id) as observerid'
            . ', (SELECT COALESCE((SELECT u.name FROM users u WHERE u.username = o.observer_username LIMIT 1), o.observer_username) FROM observations o WHERE o.id = observation_id) as observer_display'
            . ', (SELECT o.target_name FROM observations o WHERE o.id = observation_id) as objectname_sort'
        )
            ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->when($this->country, fn($query) => $query->whereIn(
                'observation_id',
                DB::table('observations')
                    ->join('users', 'users.username', '=', 'observations.observer_username')
                    ->where('users.country', $this->country)
                    ->select('observations.id')
            ))
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes');

        $sub = $aggQuery->toBase();

        $outer = ObservationLike::query();
        $tableAlias = (new ObservationLike)->getTable();
        $outer->getQuery()->fromSub($sub, $tableAlias);
        $outer->getQuery()->select($tableAlias . '.*');
        $outer->orderByDesc($tableAlias . '.likes');

        // The observations of the page, their observers and same-day indexes
        // (for the detail URL), in one query each.
        $outer->with('observation')->afterQuery(function ($likes) {
            ObservationCardData::instance()->preloadDetailUrls($likes->pluck('observation')->filter());
        });

        return $outer;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('type')
            ->add('id')
            ->add('observerid')
            ->add('observer_display')
            ->add('objectname_sort')
            ->add('objectname', function ($row) {
                $obs = $row->observation;
                $objectName = $obs ? $obs->target_name : null;

                if (!$objectName) {
                    return null;
                }

                $link = $obs->detailUrl();
                if (!$link) {
                    return e($objectName);
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
            })
            ->add('observer_name', function ($row) {
                $username = $this->observerUsername($row);
                if (!$username) {
                    return null;
                }
                $user = ObservationCardData::instance()->user($username);
                if ($user) {
                    return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                }
                return e($username);
            })
            ->add('observer_name_plain', function ($row) {
                $username = $this->observerUsername($row);
                if (!$username) {
                    return null;
                }
                $user = ObservationCardData::instance()->user($username);
                if ($user) {
                    return $user->name ?? $user->username;
                }
                return $username;
            })
            ->add('objectname_plain', function ($row) {
                $obs = $row->observation;
                return $obs ? html_entity_decode((string) $obs->target_name) : null;
            })
            ->add('obs_date')
            ->add('date', function ($row) {
                $value = $row->obs_date ?? null;

                if (!$value) {
                    return null;
                }

                try {
                    return Carbon::parse((string) $value)->format('M j, Y');
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->add('likes');
    }

    private function observerUsername($row): ?string
    {
        $username = $row->observerid ?: $row->observation?->observer_username;

        return $username ? html_entity_decode((string) $username) : null;
    }

    public function columns(): array
    {
        return [
            Column::make(__('Object'), 'objectname', 'objectname_sort')
                ->searchable()
                ->sortable(),

            Column::make(__('Object'), 'objectname_plain')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Observer'), 'observer_name', 'observer_display')
                ->searchable()
                ->sortable()
                ->visibleInExport(false), // hide HTML link in exports

            Column::make(__('Observer'), 'observer_name_plain')
                ->hidden()
                ->visibleInExport(true),

            // Map the displayed formatted date ('date') to the DB field 'obs_date'.
            // Sorting enabled via the derived `obs_date` alias.
            Column::make(__('Date'), 'date', 'obs_date')
                ->searchable()
                ->sortable(),

            Column::make(__('Likes'), 'likes')
                ->sortable(),
        ];
    }
}
