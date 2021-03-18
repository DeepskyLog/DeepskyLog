<?php

namespace App\Http\Livewire\Observationlist;

use Livewire\Component;
use App\Models\ObservationList;
use Illuminate\Support\Facades\Auth;

class View extends Component
{
    public string $selected_list_slug = '';

    protected $listeners = [
        'activate'     => 'activate',
        'delete'       => 'delete',
        'discoverable' => 'discoverable',
    ];

    public function mount()
    {
        if (Auth::user()->activeList) {
            $this->selected_list_slug = Auth::user()->activeList;
        }
    }

    public function activate($id)
    {
        $slug                     = ObservationList::where('id', $id)->first()->slug;
        $this->selected_list_slug = $slug;
        Auth::user()->update(['activeList' => $slug]);
        $this->emit('refreshLivewireDatatable');
    }

    public function discoverable($id)
    {
        $list = ObservationList::where('id', $id)->first();
        $list->toggleDiscoverable();

        if ($list->discoverable) {
            session()->flash(
                'message',
                _i('Observation list %s is now discoverable by other observers', $list->name)
            );
        } else {
            session()->flash(
                'message',
                _i('Observation list %s is not discoverable by other observers anymore', $list->name)
            );
        }
    }

    public function delete($id)
    {
        $list = ObservationList::where('id', $id)->first();
        $slug = $list->slug;
        if (Auth::user()->activeList == $slug) {
            Auth::user()->update(['activeList' => null]);
            $this->selected_list_slug = '';
        }
        $list->delete();
        $this->emit('refreshLivewireDatatable');

        session()->flash(
            'message',
            _i('Observation list %s deleted', $list->name)
        );
    }

    public function render()
    {
        return view('livewire.observationlist.view');
    }
}
