<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Models\ObservationLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PopularObservationsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'popular-observations';

    /**
        use WithExport;
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
        // Use unified observations table for subqueries
        $aggQuery = ObservationLike::selectRaw(
            'observation_type, observation_id, COUNT(*) as likes, MIN(id) as id, '
            . "(SELECT o.legacy_date_int FROM observations o WHERE "
            . "(observation_type = 'deepsky' AND o.legacy_source = 'deepsky' AND o.legacy_id = observation_id) "
            . "OR (observation_type = 'comet' AND o.legacy_source = 'comet' AND o.legacy_id = observation_id) "
            . 'LIMIT 1) as obs_date'
            . ', (SELECT o.observer_username FROM observations o WHERE '
            . "(observation_type = 'deepsky' AND o.legacy_source = 'deepsky' AND o.legacy_id = observation_id) "
            . "OR (observation_type = 'comet' AND o.legacy_source = 'comet' AND o.legacy_id = observation_id) "
            . 'LIMIT 1) as observerid'
            . ', (SELECT COALESCE((SELECT u.name FROM users u WHERE u.username = o.observer_username LIMIT 1), o.observer_username) FROM observations o WHERE '
            . "(observation_type = 'deepsky' AND o.legacy_source = 'deepsky' AND o.legacy_id = observation_id) "
            . "OR (observation_type = 'comet' AND o.legacy_source = 'comet' AND o.legacy_id = observation_id) "
            . 'LIMIT 1) as observer_display'
            . ', (SELECT o.target_name FROM observations o WHERE '
            . "(observation_type = 'deepsky' AND o.legacy_source = 'deepsky' AND o.legacy_id = observation_id) "
            . "OR (observation_type = 'comet' AND o.legacy_source = 'comet' AND o.legacy_id = observation_id) "
            . 'LIMIT 1) as objectname_sort'
        )
            ->whereIn('observation_type', ['deepsky', 'comet'])
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
                $obs = null;
                if ($row->observation_type === 'deepsky') {
                    $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'deepsky')->first();
                } elseif ($row->observation_type === 'comet') {
                    $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'comet')->first();
                }

                $objectName = $obs ? $obs->target_name : null;

                if (!$objectName) {
                    return null;
                }

                // Link to the external (legacy) deepskylog observation detail page for this observation
                if ($row->observation_type === 'deepsky') {
                    $link = config('app.old_url') . '/index.php?indexAction=detail_observation&observation=' . $row->observation_id;
                } else {
                    $link = config('app.old_url') . '/index.php?indexAction=comets_detail_observation&observation=' . $row->observation_id;
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
            })
            ->add('observer_name', function ($row) {
                $username = $row->observerid ? html_entity_decode((string) $row->observerid) : null;
                if (!$username) {
                    $obs = null;
                    if ($row->observation_type === 'deepsky') {
                        $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'deepsky')->first();
                    } elseif ($row->observation_type === 'comet') {
                        $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'comet')->first();
                    }
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
                    $obs = null;
                    if ($row->observation_type === 'deepsky') {
                        $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'deepsky')->first();
                    } elseif ($row->observation_type === 'comet') {
                        $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'comet')->first();
                    }
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
                $obs = null;
                if ($row->observation_type === 'deepsky') {
                    $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'deepsky')->first();
                } elseif ($row->observation_type === 'comet') {
                    $obs = Observation::where('legacy_id', $row->observation_id)->where('legacy_source', 'comet')->first();
                }
                return $obs ? html_entity_decode((string) $obs->target_name) : null;
            })
            ->add('obs_date')
            ->add('date', function ($row) {
                $value = $row->obs_date ?? null;

                if (!$value) {
                    return null;
                }

                try {
                    return Carbon::createFromFormat('Ymd', (string) $value)->format('M j, Y');
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
