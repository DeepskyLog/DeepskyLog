<?php

namespace App\Livewire;

use App\Models\CometObservationsOld;
use App\Models\ObservationLike;
use App\Models\ObservationsOld;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class UserPopularObservationsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'user-popular-observations';

    public string $username = '';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    // PowerGridComponent::mount() has no parameters, so keep signature compatible.
    public function mount(): void
    {
        // Ensure PowerGrid base mount logic runs so theme and setup are initialized.
        parent::mount();

        // When Livewire renders this component in a blade with :username binding,
        // Livewire will hydrate the public property $username automatically.
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
        // Find the user's legacy observation ids for deepsky and comet observations.
        $deepskyIds = ObservationsOld::where('observerid', $this->username)->pluck('id')->toArray();
        $cometIds = CometObservationsOld::where('observerid', $this->username)->pluck('id')->toArray();

        if (empty($deepskyIds) && empty($cometIds)) {
            // Return an empty builder
            return ObservationLike::query()->whereRaw('0 = 1');
        }

        // Include obs_date via subselects. Qualify legacy table names like the popular component.
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
            ->where(function ($q) use ($deepskyIds, $cometIds) {
                if (! empty($deepskyIds)) {
                    $q->orWhere(function ($q2) use ($deepskyIds) {
                        $q2->where('observation_type', 'deepsky')->whereIn('observation_id', $deepskyIds);
                    });
                }

                if (! empty($cometIds)) {
                    $q->orWhere(function ($q2) use ($cometIds) {
                        $q2->where('observation_type', 'comet')->whereIn('observation_id', $cometIds);
                    });
                }
            })
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes');

        $sub = $aggQuery->toBase();

        $outer = ObservationLike::query();
        $tableAlias = (new ObservationLike)->getTable();
        $outer->getQuery()->fromSub($sub, $tableAlias);
        $outer->getQuery()->select($tableAlias.'.*');
        $outer->orderByDesc($tableAlias.'.likes');

        return $outer;
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('type')
            ->add('id')
            ->add('objectname', function ($row) {
                $objectName = null;

                if ($row->observation_type === 'deepsky') {
                    $obs = ObservationsOld::find($row->observation_id);
                    $objectName = $obs ? $obs->objectname : null;
                } elseif ($row->observation_type === 'comet') {
                    $obs = CometObservationsOld::find($row->observation_id);
                    $objectName = $obs && $obs->object ? $obs->object->name : null;
                }

                if (! $objectName) {
                    return null;
                }

                if ($row->observation_type === 'deepsky') {
                    $link = config('app.old_url').'/index.php?indexAction=detail_observation&observation='.$row->observation_id;
                } else {
                    $link = config('app.old_url').'/index.php?indexAction=comets_detail_observation&observation='.$row->observation_id;
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
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
                ->searchable(),

            Column::make(__('Date'), 'date', 'obs_date')
                ->searchable(),

            Column::make(__('Likes'), 'likes'),
        ];
    }
}
