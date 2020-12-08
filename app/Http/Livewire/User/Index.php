<?php

namespace App\Http\Livewire\User;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    protected $listeners = [
        'delete'   => 'delete',
    ];

    public function delete($id)
    {
        $user = User::where('id', $id)->first();

        if (User::find($id)->hasMedia('observer')) {
            User::find($id)
                    ->getFirstMedia('observer')
                    ->delete();
        }
        $user->delete();
        $this->emit('refreshLivewireDatatable');

        session()->flash(
            'message',
            _i('User %s deleted', $user->name)
        );
    }

    public function render()
    {
        return view('livewire.user.index');
    }
}
