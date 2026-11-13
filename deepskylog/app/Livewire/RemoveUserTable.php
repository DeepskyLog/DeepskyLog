<?php

namespace App\Livewire;

use App\Livewire\Concerns\ExportsInTableOrder;
use App\Models\Team;
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

final class RemoveUserTable extends PowerGridComponent
{
    use ExportsInTableOrder;

    public string $tableName = 'RemoveUserTable';

    /**
     * The relations of a user that make the removal ask for the username:
     * removing the account also removes all of these.
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
        // queries per row in hasData(); the counts are shown in the table and
        // the confirmation.
        return User::query()
            ->withExists(self::OWNED_RELATIONS)
            ->withExists(['teams as is_staff' => fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS])])
            ->withCount(['observations', 'instruments', 'observingLists']);
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

            Column::make(__('Observations'), 'observations_count'),

            Column::make(__('Instruments'), 'instruments_count'),

            Column::make(__('Observing lists'), 'observing_lists_count'),

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
        $button = Button::add('destroy');

        if ($this->hasData($row)) {
            // Livewire's prompt compares the answer with the text after the
            // bar as it stands (PowerGrid's confirmPrompt() puts spaces
            // around it, so no answer would ever match); usernames hold no
            // bar. '\n' is turned into a new line by Livewire.
            $message = __('Removing :username also removes everything they recorded (observations: :observations, instruments: :instruments, observing lists: :lists), with their eyepieces, filters, lenses, locations, sessions, drawings, comments and likes. This cannot be undone.', [
                'username' => $row->username,
                'observations' => (int) $row->observations_count,
                'instruments' => (int) $row->instruments_count,
                'lists' => (int) $row->observing_lists_count,
            ]).'\n\n'.__('Type the username to confirm:').' '.$row->username;
            $button->attributes(['wire:confirm.prompt' => str_replace('|', '', $message).'|'.$row->username]);
        } else {
            $button->confirm(__('Are you sure you want to remove the user account?'));
        }

        return [
            $button
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
     * Any account can be removed, with everything the observer recorded,
     * except the first account, the administrator's own (the profile page
     * has its own Delete Account) and the members of the Administrators and
     * Database Experts teams: take them out of that team first.
     */
    private function canBeRemoved(User $user): bool
    {
        if ($user->id == 1 || $user->id == Auth::id()) {
            return false;
        }

        if (! array_key_exists('is_staff', $user->getAttributes())) {
            $user->loadExists(['teams as is_staff' => fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS])]);
        }

        return ! $user->getAttribute('is_staff');
    }

    /**
     * Whether the account holds anything of its own, so that removing it
     * removes more than the account.
     */
    private function hasData(User $user): bool
    {
        foreach (self::OWNED_RELATIONS as $relation) {
            $attribute = Str::snake($relation).'_exists';
            if (! array_key_exists($attribute, $user->getAttributes())) {
                $user->loadExists($relation);
            }
            if ($user->getAttribute($attribute)) {
                return true;
            }
        }

        return false;
    }

    public function destroy($id): void
    {
        $user = User::find($id);

        // The rules only hide the button, so the conditions are checked again
        if (! $user || ! Auth::user()->can('delete', User::class) || ! $this->canBeRemoved($user)) {
            return;
        }

        // The action of Delete Account on the profile page: removes the
        // observations (with drawings, likes and comments), sessions,
        // equipment, locations, observing lists, messages to them, team
        // memberships, tokens and settings, and refreshes the statistics
        app(DeletesUsers::class)->delete($user);
    }

    public function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'destroy',
            ]
        );
    }
}
