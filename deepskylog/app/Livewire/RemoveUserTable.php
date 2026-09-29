<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\DeletesUsers;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class RemoveUserTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'RemoveUserTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()->showSearchInput()->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::responsive()->fixedColumns('name'),
        ];
    }

    public function datasource(): Builder
    {
        return User::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($row) {
                return sprintf('<a href="%s">%s</a>', route('observer.show', $row->slug), $row->name);
            })->add('created_at_formatted', function ($row) {
                return $row->created_at->format('Y-m-d');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')
                ->sortable(),

            Column::make(__('Name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('Username'), 'username')
                ->sortable()
                ->searchable(),

            Column::make(__('Email'), 'email')
                ->sortable()
                ->searchable(),

            Column::make(__('Slug'), 'slug')
                ->searchable()
                ->sortable(),

            Column::make(__('Created at'), 'created_at')
                ->hidden(),

            Column::make(__('Created at'), 'created_at_formatted', 'created_at')
                ->searchable()
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name'),
            Filter::inputText('username'),
            Filter::inputText('email'),
            Filter::inputText('slug'),
        ];
    }

    public function actions(User $row): array
    {
        return [
            Button::add('destroy')
                ->confirm(__('Are you sure you want to remove the user account?'))
                ->slot(__('Delete'))
                ->id()
                ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
                ->dispatch('destroy', ['id' => $row->id]),
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('destroy')
                ->when(fn ($user) => ! $this->canBeRemoved($user))
                ->hide(),
        ];
    }

    /**
     * A user can only be removed when it is not the first account or the
     * administrator itself, and has nothing of its own in DeepskyLog.
     */
    private function canBeRemoved(User $user): bool
    {
        return $user->id != 1
            && $user->id != Auth::id()
            && ! $user->locations()->exists()
            && ! $user->instruments()->exists()
            && ! $user->eyepieces()->exists()
            && ! $user->filters()->exists()
            && ! $user->lenses()->exists()
            && ! $user->observingLists()->exists()
            && ! $user->observations()->exists()
            && ! $user->sketchOfTheWeek()->exists()
            && ! $user->sketchOfTheMonth()->exists();
    }

    public function destroy($id): void
    {
        $user = User::find($id);

        // The rules only hide the button, so the conditions are checked again
        if (! $user || ! Auth::user()->can('delete', User::class) || ! $this->canBeRemoved($user)) {
            return;
        }

        // Jetstream's action also removes the team memberships, owned teams,
        // tokens, connected accounts, profile photo and instrument sets
        app(DeletesUsers::class)->delete($user);
    }

    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'destroy',
            ]
        );
    }
}
