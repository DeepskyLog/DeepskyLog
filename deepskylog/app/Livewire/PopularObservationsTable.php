<?php

namespace App\Livewire;

use App\Models\CometObservationsOld;
use App\Models\ObservationLike;
use App\Models\ObservationsOld;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        // Use Eloquent aggregation on ObservationLike to return an Eloquent Builder.
        // Preload the observer usernames and corresponding User records from the default DB
        // to avoid nested eager-loading that runs against the legacy mysqlOld connection.

        // Include obs_date via subselects so we can sort on it.
        // Qualify the legacy table names with the mysqlOld database name if available so
        // the subqueries run against the correct database instead of the default connection.
        $oldDbName = config('database.connections.mysqlOld.database') ?? env('DB_DATABASE_OLD');

        if ($oldDbName) {
            $obsTable = "`{$oldDbName}`.`observations`";
            $cometTable = "`{$oldDbName}`.`cometobservations`";
        } else {
            $obsTable = '`observations`';
            $cometTable = '`cometobservations`';
        }

        $aggQuery = ObservationLike::selectRaw(
            'observation_type, observation_id, COUNT(*) as likes, MIN(id) as id, '
            ."(CASE WHEN observation_type = 'deepsky' "
            ."THEN CAST((SELECT o.date FROM {$obsTable} o WHERE o.id = observation_id LIMIT 1) AS UNSIGNED) "
            ."WHEN observation_type = 'comet' "
            ."THEN CAST((SELECT c.date FROM {$cometTable} c WHERE c.id = observation_id LIMIT 1) AS UNSIGNED) "
            .'ELSE NULL END) as obs_date'
        )
            ->whereIn('observation_type', ['deepsky', 'comet'])
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes');

        // Prefetch a reasonable window of rows (first 200) to build the username -> User map.
        $sample = (clone $aggQuery)->limit(200)->get();

        $deepskyIds = $sample->filter(fn ($r) => $r->observation_type === 'deepsky')->pluck('observation_id')->unique()->toArray();
        $cometIds = $sample->filter(fn ($r) => $r->observation_type === 'comet')->pluck('observation_id')->unique()->toArray();

        $usernames = [];
        if (! empty($deepskyIds)) {
            $obs = ObservationsOld::whereIn('id', $deepskyIds)->get(['id', 'observerid']);
            $usernames = array_merge($usernames, $obs->pluck('observerid')->toArray());
        }
        if (! empty($cometIds)) {
            $cobs = CometObservationsOld::whereIn('id', $cometIds)->get(['id', 'observerid']);
            $usernames = array_merge($usernames, $cobs->pluck('observerid')->toArray());
        }

        $usernames = array_values(array_filter(array_unique(array_map(fn ($v) => html_entity_decode((string) $v), $usernames))));

        $this->userMap = [];
        if (! empty($usernames)) {
            $users = \App\Models\User::whereIn('username', $usernames)->get()->keyBy('username');
            foreach ($users as $username => $user) {
                $this->userMap[$username] = $user;
            }
        }

        // Convert the grouped aggregation into a derived table so outer queries (like export)
        // can ORDER BY the selected aliases without tripping ONLY_FULL_GROUP_BY.
        $sub = $aggQuery->toBase();

        // Build an Eloquent builder that selects from the aggregation subquery so we
        // return the expected Illuminate\Database\Eloquent\Builder instance.
        // Use the model table name as the derived-table alias so any ORDER BY that
        // qualifies with the model table (e.g. observation_likes.obs_date) still works.
        $outer = ObservationLike::query();
        $tableAlias = (new ObservationLike)->getTable();
        $outer->getQuery()->fromSub($sub, $tableAlias);
        $outer->getQuery()->select($tableAlias.'.*');

        // Ensure the table is initially sorted by number of likes (descending).
        $outer->orderByDesc($tableAlias.'.likes');

        return $outer;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('type')
            ->add('id')
            ->add('objectname', function ($row) {
                // $row is the aggregated ObservationLike model instance
                $objectName = null;

                if ($row->observation_type === 'deepsky') {
                    // use eager-loaded relation if present (use relationLoaded to avoid lazy loading)
                    if ($row->relationLoaded('deepsky') && $row->deepsky) {
                        $objectName = $row->deepsky->objectname;
                    } else {
                        // fallback: try to load single model
                        $obs = ObservationsOld::find($row->observation_id);
                        $objectName = $obs ? $obs->objectname : null;
                    }
                } elseif ($row->observation_type === 'comet') {
                    if ($row->relationLoaded('comet') && $row->comet) {
                        $objectName = $row->comet->object ? $row->comet->object->name : null;
                    } else {
                        $obs = CometObservationsOld::find($row->observation_id);
                        $objectName = $obs && $obs->object ? $obs->object->name : null;
                    }
                }

                if (! $objectName) {
                    return null;
                }

                // Link to the external (legacy) deepskylog observation detail page for this observation
                if ($row->observation_type === 'deepsky') {
                    $link = config('app.old_url').'/index.php?indexAction=detail_observation&observation='.$row->observation_id;
                } else {
                    $link = config('app.old_url').'/index.php?indexAction=comets_detail_observation&observation='.$row->observation_id;
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
            })
            ->add('observer_name', function ($row) {
                if ($row->observation_type === 'deepsky') {
                    if ($row->relationLoaded('deepsky') && $row->deepsky) {
                        $username = html_entity_decode($row->deepsky->observerid);

                        if (isset($this->userMap[$username])) {
                            $user = $this->userMap[$username];

                            return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                        }

                        return e($row->deepsky->observerid);
                    }

                    $obs = ObservationsOld::find($row->observation_id);
                    if ($obs) {
                        $username = html_entity_decode($obs->observerid);
                        if (isset($this->userMap[$username])) {
                            $user = $this->userMap[$username];

                            return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                        }

                        return e($obs->observerid);
                    }

                    return null;
                }

                if ($row->observation_type === 'comet') {
                    if ($row->relationLoaded('comet') && $row->comet) {
                        $username = html_entity_decode($row->comet->observerid);

                        if (isset($this->userMap[$username])) {
                            $user = $this->userMap[$username];

                            return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                        }

                        return e($row->comet->observerid);
                    }

                    $obs = CometObservationsOld::find($row->observation_id);
                    if ($obs) {
                        $username = html_entity_decode($obs->observerid);
                        if (isset($this->userMap[$username])) {
                            $user = $this->userMap[$username];

                            return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                        }

                        return e($obs->observerid);
                    }

                    return null;
                }

                return null;
            })
            ->add('observer_name_plain', function ($row) {
                // Same logic as observer_name but return plain text (no anchor) for exports.
                $username = null;

                if ($row->relationLoaded('deepsky') && $row->deepsky) {
                    $username = html_entity_decode($row->deepsky->observerid);
                } elseif ($row->relationLoaded('comet') && $row->comet) {
                    $username = html_entity_decode($row->comet->observerid);
                } else {
                    // Fallback to looking up the observation record
                    if ($row->observation_type === 'deepsky') {
                        $obs = ObservationsOld::find($row->observation_id);
                        $username = $obs ? html_entity_decode($obs->observerid) : null;
                    } elseif ($row->observation_type === 'comet') {
                        $obs = CometObservationsOld::find($row->observation_id);
                        $username = $obs ? html_entity_decode($obs->observerid) : null;
                    }
                }

                if (! $username) {
                    return null;
                }

                if (isset($this->userMap[$username])) {
                    $user = $this->userMap[$username];

                    return $user->name ?? $user->username;
                }

                return $username;
            })
            ->add('objectname_plain', function ($row) {
                // Plain text version of the object name for exports
                $objectName = null;

                if ($row->observation_type === 'deepsky') {
                    if ($row->relationLoaded('deepsky') && $row->deepsky) {
                        $objectName = html_entity_decode((string) $row->deepsky->objectname);
                    } else {
                        $obs = ObservationsOld::find($row->observation_id);
                        $objectName = $obs ? html_entity_decode((string) $obs->objectname) : null;
                    }
                } elseif ($row->observation_type === 'comet') {
                    if ($row->relationLoaded('comet') && $row->comet) {
                        $objectName = $row->comet->object ? html_entity_decode((string) $row->comet->object->name) : null;
                    } else {
                        $obs = CometObservationsOld::find($row->observation_id);
                        $objectName = $obs && $obs->object ? html_entity_decode((string) $obs->object->name) : null;
                    }
                }

                return $objectName;
            })
            ->add('obs_date')
            ->add('date', function ($row) {
                $value = $row->obs_date ?? null;

                if (! $value) {
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
            Column::make(__('Object'), 'objectname')
                ->searchable(), // objectname is computed from related models; sorting requires a DB join. Disable here.

            Column::make(__('Object'), 'objectname_plain')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Observer'), 'observer_name')
                ->searchable()
                ->visibleInExport(false), // hide HTML link in exports

            Column::make(__('Observer'), 'observer_name_plain')
                ->hidden()
                ->visibleInExport(true),

            // Map the displayed formatted date ('date') to the DB field 'obs_date'.
            // Sorting is disabled because the date is computed/derived.
            Column::make(__('Date'), 'date', 'obs_date')
                ->searchable(),

            Column::make(__('Likes'), 'likes'),
        ];
    }
}
