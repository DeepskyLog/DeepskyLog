<?php

namespace App\Http\Livewire\Filter;

use App\Models\Filter;
use Livewire\Component;

class View extends Component
{
    public $equipment;

    protected $listeners = [
        'activate' => 'activate',
        'delete'   => 'delete',
    ];

    public function activate($id)
    {
        $filter = Filter::where('id', $id)->first();
        $filter->toggleActive();
        if ($filter->active) {
            session()->flash('message', _i('Filter %s is active', $filter->name));
        } else {
            session()->flash('message', _i('Filter %s is not longer active', $filter->name));
        }
    }

    public function delete($id)
    {
        $filter = Filter::where('id', $id)->first();

        if ($filter->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Filter %s has observations. Impossible to delete.',
                    $filter->name
                )
            );
        } else {
            if (Filter::find($id)->hasMedia('filter')) {
                Filter::find($id)
                    ->getFirstMedia('filter')
                    ->delete();
            }
            $filter->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Filter %s deleted', $filter->name)
            );
        }
    }

    /**
     * Real time validation.
     *
     * @param mixed $propertyName The name of the property
     *
     * @return void
     */
    public function updated($propertyName)
    {
        if ($propertyName == 'equipment') {
            // * 0 => all my equipment, -1 => all my active equipment, > 0 => the id of the equipment set

            // Update the list with the filters to only show the filters of the equipment set
            $this->emit('updateLivewireDatatable', $this->equipment);
        }
    }

    public function render()
    {
        return view('livewire.filter.view');
    }
}
