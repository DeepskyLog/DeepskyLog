<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Menu extends Component
{
    public $picture;

    protected $listeners = ['newProfilePicture' => 'newProfilePicture'];

    public function newProfilePicture($userPicture)
    {
        if ($userPicture) {
            $this->picture = reset($userPicture)['previewUrl'];
        }
    }

    public function mount()
    {
        $this->picture = '/users/' . Auth::user()->slug . '/getImage';
    }

    public function render()
    {
        return view('livewire.user.menu');
    }
}
