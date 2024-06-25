<?php

namespace App\Livewire;

use App\Models\TeamUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class AdminUserTable extends PowerGridComponent
{
    use WithExport;

    public bool $multiSort = true;

    public $team;

    public string $primaryKey = 'users.id';

    public string $tableName = 'AdminUserTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        $this->persist(['columns', 'filters']);

        return [
            Exportable::make('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput()->showToggleColumns(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */

    /**
     * PowerGrid datasource.
     *
     * @return Builder<TeamUser>
     */
    public function datasource(): Builder
    {
        return TeamUser::query()->where('team_id', $this->team)->join('users', function ($users) {
            $users->on('team_user.user_id', '=', 'users.id');
        })->select('users.id', 'users.username', 'users.name', 'users.email', 'users.slug', 'users.created_at');
    }

    /*
    |--------------------------------------------------------------------------
    |  Datasource
    |--------------------------------------------------------------------------
    | Provides data to your Table using a Model or Collection
    |
    */

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [];
    }

    /**
     * Defines the fields for the PowerGrid.
     *
     * This method adds a 'slug' field to the PowerGrid. The value of the 'slug' field is a hyperlink that points to the observer's page.
     * The hyperlink is created using the 'observer.show' route and the observer's slug.
     *
     * @return PowerGridFields The fields for the PowerGrid.
     */
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($row) {
                return sprintf('<a href="%s">%s</a>', route('observer.show', $row->slug), $row->name);
            })->add('created_at_formatted', function ($row) {
                return $row->created_at->format('Y-m-d');
            });
    }

    /*
    |--------------------------------------------------------------------------
    |  Add Column
    |--------------------------------------------------------------------------
    | Make Datasource fields available to be used as columns.
    | You can pass a closure to transform/modify the data.
    |
    | ❗ IMPORTANT: When using closures, you must escape any value coming from
    |    the database using the `e()` Laravel Helper function.
    |
    */

    /**
     * PowerGrid Columns.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->searchable()
                ->sortable(),

            Column::make(__('Name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('User name'), 'username')
                ->searchable()
                ->sortable(),

            Column::make(__('Email'), 'email')
                ->searchable()
                ->sortable(),

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

    /*
    |--------------------------------------------------------------------------
    |  Include Columns
    |--------------------------------------------------------------------------
    | Include the columns added columns, making them visible on the Table.
    | Each column can be configured with properties, filters, actions...
    |
    */

    /**
     * PowerGrid Filters.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [
            Filter::inputText('name'),
            Filter::inputText('username'),
            Filter::inputText('email'),
            Filter::inputText('slug'),
        ];
    }

    /**
     * PowerGrid User Action Buttons.
     *
     * @return array<int, Button>
     */
    public function actions($row): array
    {
        return [
            Button::add('destroy')
                ->slot('Remove')
                ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
                ->tooltip(__('Remove user from team'))
                ->dispatch('remove', [$row->id]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

    /**
     * PowerGrid User Action Rules.
     */
    public function actionRules(): array
    {
        return [
            //Hide button edit for ID 1
            Rule::button('destroy')
                ->when(fn ($user) => $user->id == 1)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->id == Auth::user()->id)
                ->hide(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * Removes a user from the team
     */
    public function remove($id): void
    {
        $teamuser = TeamUser::where('team_id', '=', $this->team)->where('user_id', '=', $id);
        $teamuser->delete();
    }

    protected function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'remove',
            ]
        );
    }
}
