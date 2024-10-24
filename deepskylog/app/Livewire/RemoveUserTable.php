<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
                ->when(fn ($user) => $user->id == 1)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->id == Auth::user()->id)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->locations->count() > 0)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->instruments->count() > 0)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->eyepieces->count() > 0)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->filters->count() > 0)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->lenses->count() > 0)
                ->hide(),
            Rule::button('destroy')
                ->when(fn ($user) => $user->getObservingLists()->count() > 0)
                ->hide(),
        ];
    }

    public function destroy($id): void
    {
        $user = User::where('id', '=', $id);
        $user->delete();
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
