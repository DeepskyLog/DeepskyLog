<?php

namespace App\Livewire;

use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AdminEyepieceTypeTable extends PowerGridComponent
{
    public string $tableName = 'admin-eyepiece-type-table-vxybkg-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return EyepieceType::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('number_of_eyepieces', function ($eyepiece_type) {
                return Eyepiece::where('type_id', $eyepiece_type->id)->count();
            })
            ->add('eyepiece_make', function ($eyepiece_type) {
                return EyepieceMake::where('id', $eyepiece_type->eyepiece_makes_id)->first()->name;
            });

    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Eyepiece make', 'eyepiece_make'),
            Column::make('Number of Eyepieces', 'number_of_eyepieces'),

            // Add update time
            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $this->redirect('/admin/eyepiece_type/'.$rowId.'/edit');
    }

    public function actions(EyepieceType $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id]),
        ];
    }
}
