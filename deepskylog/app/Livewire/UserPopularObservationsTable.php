<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Models\ObservationLike;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

    protected ?\App\Models\User $observerUser = null;

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
        // Find the user's observation ids from the unified observations table
        $deepskyIds = Observation::where('observer_username', $this->username)
            ->where('legacy_source', 'deepsky')
            ->pluck('legacy_id')
            ->filter()
            ->toArray();
        $cometIds = Observation::where('observer_username', $this->username)
            ->where('legacy_source', 'comet')
            ->pluck('legacy_id')
            ->filter()
            ->toArray();

        if (empty($deepskyIds) && empty($cometIds)) {
            // Return an empty builder
            return ObservationLike::query()->whereRaw('0 = 1');
        }

        $aggQuery = ObservationLike::selectRaw(
            'observation_type, observation_id, COUNT(*) as likes, MIN(id) as id, '
            . "(SELECT legacy_date_int FROM observations o WHERE "
            . "(observation_type = 'deepsky' AND o.legacy_source = 'deepsky' AND o.legacy_id = observation_id) "
            . "OR (observation_type = 'comet' AND o.legacy_source = 'comet' AND o.legacy_id = observation_id) "
            . 'LIMIT 1) as obs_date'
        )
            ->where(function ($q) use ($deepskyIds, $cometIds) {
                if (!empty($deepskyIds)) {
                    $q->orWhere(function ($q2) use ($deepskyIds) {
                        $q2->where('observation_type', 'deepsky')->whereIn('observation_id', $deepskyIds);
                    });
                }

                if (!empty($cometIds)) {
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

                $username = $obs->observer_username;
                $this->observerUser ??= \App\Models\User::where('username', $username)->first();
                $observer = $this->observerUser ? ($this->observerUser->slug ?: $username) : $username;
                $date = $obs->observed_on
                    ? $obs->observed_on->format('Y-m-d')
                    : Carbon::createFromFormat('Ymd', (string) $obs->legacy_date_int)->format('Y-m-d');
                $link = route('observations.detail', [$observer, $date, Str::slug((string) $obs->target_name, '-')]);

                return sprintf('<a href="%s" class="font-bold hover:underline" target="_blank" rel="noopener noreferrer">%s</a>', $link, e($objectName));
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
            Column::make(__('Object'), 'objectname')
                ->searchable(),

            Column::make(__('Date'), 'date', 'obs_date')
                ->searchable(),

            Column::make(__('Likes'), 'likes'),
        ];
    }
}
