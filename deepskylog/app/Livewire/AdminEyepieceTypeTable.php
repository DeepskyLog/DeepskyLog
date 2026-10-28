<?php

namespace App\Livewire;

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
    public string $tableName = 'admin-eyepiece-type-table';

    public function setUp(): array
    {
        $this->withoutPersist();

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
        return EyepieceType::query()->with('make')->withCount('eyepieces');
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
                return $eyepiece_type->eyepieces_count;
            })
            ->add('eyepiece_make', function ($eyepiece_type) {
                return e(html_entity_decode((string) $eyepiece_type->make?->name));
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
            Column::make('Number of Eyepieces', 'number_of_eyepieces')
                ->sortable()
                ->sortUsing(fn ($query, string $direction) => $query->orderBy('eyepieces_count', $direction)),

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
