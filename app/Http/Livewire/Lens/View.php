<?php

namespace App\Http\Livewire\Lens;

use App\Models\Lens;
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
        $lens = Lens::where('id', $id)->first();
        $lens->toggleActive();
        if ($lens->active) {
            session()->flash('message', _i('Lens %s is active', $lens->name));
        } else {
            session()->flash('message', _i('Lens %s is not longer active', $lens->name));
        }
    }

    public function delete($id)
    {
        $lens = Lens::where('id', $id)->first();

        if ($lens->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Lens %s has observations. Impossible to delete.',
                    $lens->name
                )
            );
        } else {
            if (Lens::find($id)->hasMedia('lens')) {
                Lens::find($id)
                    ->getFirstMedia('lens')
                    ->delete();
            }
            $lens->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Lens %s deleted', $lens->name)
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
        return view('livewire.lens.view');
    }
}
