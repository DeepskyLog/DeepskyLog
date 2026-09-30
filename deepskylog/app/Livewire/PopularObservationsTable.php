<?php

namespace App\Livewire;

use App\Livewire\Concerns\LimitsExportRows;
use App\Models\Observation;
use App\Models\ObservationLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PopularObservationsTable extends PowerGridComponent
{
    use LimitsExportRows;

    public string $tableName = 'popular-observations';

    /**
     * Populated in datasource() for the sample page rows to avoid extra queries.
     *
     * @var array<string, \App\Models\User>
     */
    protected array $userMap = [];

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
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

            PowerGrid::responsive()->fixedColumns('name'),

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
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes');

        // Prefetch a reasonable window of rows (first 200) to build the username -> User map.
        $sample = (clone $aggQuery)->limit(200)->get();

        $usernames = $sample->pluck('observerid')
            ->filter()
            ->map(fn($v) => html_entity_decode((string) $v))
            ->unique()
            ->values()
            ->toArray();

        $this->userMap = [];
        if (!empty($usernames)) {
            $users = \App\Models\User::whereIn('username', $usernames)->get()->keyBy('username');
            foreach ($users as $username => $user) {
                $this->userMap[$username] = $user;
            }
        }

        $sub = $aggQuery->toBase();

        $outer = ObservationLike::query();
        $tableAlias = (new ObservationLike)->getTable();
        $outer->getQuery()->fromSub($sub, $tableAlias);
        $outer->getQuery()->select($tableAlias . '.*');
        $outer->orderByDesc($tableAlias . '.likes');

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
                $obs = Observation::find($row->observation_id);

                $objectName = $obs ? $obs->target_name : null;

                if (!$objectName) {
                    return null;
                }

                $username = $obs->observer_username;
                $user = $this->userMap[$username] ?? null;
                $observer = $user ? ($user->slug ?: $username) : $username;
                $link = $obs->detailUrl($observer);
                if (!$link) {
                    return e($objectName);
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
            })
            ->add('observer_name', function ($row) {
                $username = $row->observerid ? html_entity_decode((string) $row->observerid) : null;
                if (!$username) {
                    $obs = Observation::find($row->observation_id);
                    $username = $obs ? html_entity_decode((string) $obs->observer_username) : null;
                }
                if (!$username) {
                    return null;
                }
                if (isset($this->userMap[$username])) {
                    $user = $this->userMap[$username];
                    return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                }
                return e($username);
            })
            ->add('observer_name_plain', function ($row) {
                $username = $row->observerid ? html_entity_decode((string) $row->observerid) : null;
                if (!$username) {
                    $obs = Observation::find($row->observation_id);
                    $username = $obs ? html_entity_decode((string) $obs->observer_username) : null;
                }
                if (!$username) {
                    return null;
                }
                if (isset($this->userMap[$username])) {
                    $user = $this->userMap[$username];
                    return $user->name ?? $user->username;
                }
                return $username;
            })
            ->add('objectname_plain', function ($row) {
                $obs = Observation::find($row->observation_id);
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
