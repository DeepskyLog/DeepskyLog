<?php

namespace App\Livewire;

use App\Models\DeepskyObject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

/**
 * The deep-sky objects of one review status (#526) on /admin/object-reviews,
 * for administrators and database experts: the name opens the edit page,
 * where the object is reviewed; new objects and those that need attention
 * can be marked as checked from the table.
 */
final class ObjectReviewTable extends PowerGridComponent
{
    public string $tableName = 'object-review-table';

    public string $primaryKey = 'name';

    public string $sortField = 'name';

    /** A review status, 'none' for the objects nobody reviewed, or 'all'. */
    public string $status = DeepskyObject::REVIEW_NEW;

    public function setUp(): array
    {
        $this->showCheckBox('name');

        if (! in_array($this->status, [...DeepskyObject::REVIEW_STATUSES, 'none', 'all'], true)) {
            $this->status = DeepskyObject::REVIEW_NEW;
        }

        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        if ($this->status === DeepskyObject::REVIEW_CHECKED) {
            return [];
        }

        return [
            Button::add('mark-selected-checked')
                ->slot(__('Mark selected as checked'))
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('markSelectedChecked', []),
        ];
    }

    public function datasource(): Builder
    {
        $query = DeepskyObject::query()->with('reviewer:id,name');

        return match ($this->status) {
            'all' => $query,
            'none' => $query->whereNull('review_status'),
            default => $query->where('review_status', $this->status),
        };
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('name_link', fn (DeepskyObject $row) => sprintf(
                '<a href="%s" class="text-blue-400 hover:text-blue-300">%s</a>',
                e(route('object.edit', ['slug' => $row->slug ?: $row->name])),
                e($row->name)
            ))
            ->add('type', fn (DeepskyObject $row) => e($row->type))
            ->add('con', fn (DeepskyObject $row) => e($row->con))
            ->add('review_status_label', fn (DeepskyObject $row) => e(DeepskyObject::reviewStatusLabel($row->review_status)))
            ->add('review_note', fn (DeepskyObject $row) => e($row->review_note ?? ''))
            ->add('reviewer_name', fn (DeepskyObject $row) => e(html_entity_decode($row->reviewer?->name ?? '')))
            ->add('reviewed_at_formatted', fn (DeepskyObject $row) => $row->reviewed_at?->format('Y-m-d') ?? '');
    }

    public function columns(): array
    {
        return [
            Column::make(__('Name'), 'name_link', 'name')
                ->sortable()
                ->searchable(),

            Column::make(__('Type'), 'type')
                ->sortable(),

            Column::make(__('Constellation'), 'con')
                ->sortable(),

            Column::make(__('Review status'), 'review_status_label', 'review_status')
                ->sortable(),

            Column::make(__('Review note'), 'review_note')
                ->searchable(),

            Column::make(__('Reviewed by'), 'reviewer_name'),

            Column::make(__('Reviewed on'), 'reviewed_at_formatted', 'reviewed_at')
                ->sortable(),

            Column::action(__('Action')),
        ];
    }

    public function actions(DeepskyObject $row): array
    {
        return [
            Button::add('mark-checked')
                ->slot(__('Mark as checked'))
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('markChecked', ['name' => $row->name]),
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('mark-checked')
                ->when(fn (DeepskyObject $row) => $row->review_status === DeepskyObject::REVIEW_CHECKED)
                ->hide(),
        ];
    }

    #[On('markChecked')]
    public function markChecked(string $name): void
    {
        $this->markAsChecked([$name]);
    }

    #[On('markSelectedChecked')]
    public function markSelectedChecked(): void
    {
        $this->markAsChecked($this->checkboxValues);
        $this->checkboxValues = [];
        $this->checkboxAll = false;
    }

    /**
     * Marks the objects as checked by the current user. An object that needs
     * attention keeps its note, so it is clear what was checked.
     *
     * @param  array<int, string>  $names
     */
    private function markAsChecked(array $names): void
    {
        if (! Auth::user()?->can('review_objects', User::class)) {
            abort(403);
        }

        DeepskyObject::whereIn('name', $names)
            ->get()
            ->each(function (DeepskyObject $object) {
                $object->setReview(DeepskyObject::REVIEW_CHECKED, $object->review_note, Auth::id());
                $object->save();
            });
    }
}
