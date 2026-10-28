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

final class UserPopularObservationsTable extends PowerGridComponent
{
    use LimitsExportRows;

    public string $tableName = 'user-popular-observations';

    public string $username = '';

    protected ?\App\Models\User $observerUser = null;


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
        $aggQuery = ObservationLike::selectRaw(
            'observation_type, observation_id, COUNT(*) as likes, MIN(id) as id, '
            . '(SELECT o.observed_on FROM observations o WHERE o.id = observation_id) as obs_date'
            // Pulled into the aggregate so the object name can be searched:
            // PowerGrid qualifies every searchable column with the table (here:
            // the alias of this subquery) of the query's base model.
            . ', (SELECT o.target_name FROM observations o WHERE o.id = observation_id) as objectname'
        )
            ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->whereIn('observation_id', Observation::select('id')->where('observer_username', $this->username))
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes');

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
            ->add('objectname', function ($row) {
                $obs = Observation::find($row->observation_id);

                $objectName = $obs ? $obs->target_name : null;

                if (!$objectName) {
                    return null;
                }

                $username = $obs->observer_username;
                $this->observerUser ??= \App\Models\User::where('username', $username)->first();
                $observer = $this->observerUser ? ($this->observerUser->slug ?: $username) : $username;
                $link = $obs->detailUrl($observer);
                if (!$link) {
                    return e($objectName);
                }

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
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
            Column::make(__('Object'), 'objectname')
                ->searchable(),

            Column::make(__('Date'), 'date', 'obs_date')
                ->searchable(),

            Column::make(__('Likes'), 'likes'),
        ];
    }
}
