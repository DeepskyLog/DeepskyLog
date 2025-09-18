<?php

namespace App\Livewire;

use App\Models\ObservationLike;
use App\Models\ObservationSession;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class PopularSessionsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'popular-sessions';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()->showSearchInput()->showToggleColumns(),
            PowerGrid::footer()->showPerPage(25)->showRecordCount(),
            PowerGrid::exportable()->striped()->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function datasource(): Builder
    {
        // Aggregate likes for sessions only
        $aggQuery = ObservationLike::selectRaw('observation_type, observation_id, COUNT(*) as likes, MIN(id) as id')
            ->where('observation_type', 'session')
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
            ->add('id')
            ->add('session_name', function ($row) {
                $session = ObservationSession::find($row->observation_id);
                if (! $session) {
                    return null;
                }

                $name = $session->name ?? __('Session :id', ['id' => $row->observation_id]);

                return sprintf('<a href="%s" class="font-bold hover:underline">%s</a>', route('session.show', [$session->user->slug ?? $session->observerid, $session->slug ?? $session->id]), e($name));
            })
            ->add('session_name_plain', function ($row) {
                $session = ObservationSession::find($row->observation_id);
                if (! $session) {
                    return null;
                }

                return $session->name ?? __('Session :id', ['id' => $row->observation_id]);
            })
            ->add('observer_name', function ($row) {
                $session = ObservationSession::find($row->observation_id);
                if (! $session) {
                    return null;
                }
                $username = html_entity_decode($session->observerid);
                $user = \App\Models\User::where('username', $username)->first();
                if ($user) {
                    return sprintf('<a href="%s">%s</a>', route('observer.show', $user->slug), e($user->name ?? $user->username));
                }

                return e($username);
            })
            ->add('observer_name_plain', function ($row) {
                $session = ObservationSession::find($row->observation_id);
                if (! $session) {
                    return null;
                }
                $username = html_entity_decode($session->observerid);
                $user = \App\Models\User::where('username', $username)->first();
                if ($user) {
                    return $user->name ?? $user->username;
                }

                return $username;
            })
            ->add('likes');
    }

    public function columns(): array
    {
        return [
            Column::make(__('Session'), 'session_name')->searchable(),
            Column::make(__('Session'), 'session_name_plain')
                ->hidden()
                ->visibleInExport(true),
            Column::make(__('Observer'), 'observer_name')->searchable(),
            Column::make(__('Observer'), 'observer_name_plain')
                ->hidden()
                ->visibleInExport(true),
            Column::make(__('Likes'), 'likes'),
        ];
    }

    // computed fields moved into fields()
}
