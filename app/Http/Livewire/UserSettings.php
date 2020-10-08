<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UserSettings extends Component
{
    public $user;
    public $about;

    public function mount()
    {
        $this->about = $this->user->about;
    }

    public function render()
    {
        return view('livewire.user-settings');
    }
}
