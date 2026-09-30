<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    /**
     * The relations of a user that keep the account from being removed.
     */
    private const OWNED_RELATIONS = [
        'locations', 'instruments', 'eyepieces', 'filters', 'lenses',
        'observingLists', 'observations', 'sketchOfTheWeek', 'sketchOfTheMonth',
    ];

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
        // One EXISTS subquery per relation in the page query, instead of nine
        // queries per row in canBeRemoved().
        return User::query()->withExists(self::OWNED_RELATIONS);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($row) {
                return sprintf('<a href="%s">%s</a>', route('observer.show', $row->slug), e($row->name));
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
        if ($user->id == 1 || $user->id == Auth::id()) {
            return false;
        }

        foreach (self::OWNED_RELATIONS as $relation) {
            $attribute = Str::snake($relation) . '_exists';
            if (! array_key_exists($attribute, $user->getAttributes())) {
                $user->loadExists($relation);
            }
            if ($user->getAttribute($attribute)) {
                return false;
            }
        }

        return true;
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
