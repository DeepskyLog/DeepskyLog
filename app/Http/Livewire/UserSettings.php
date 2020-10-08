<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserSettings extends Component
{
    public $user;
    public $about;

    /**
     * Sets the database value of the about textarea.
     *
     * @return void
     */
    public function mount()
    {
        $this->about = $this->user->about;
    }

    /**
     * Render the page.
     *
     * @return View|Factory The view for the livewire component
     */
    public function render()
    {
        return view('livewire.user-settings');
    }
}
