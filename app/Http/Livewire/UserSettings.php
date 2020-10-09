<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserSettings extends Component
{
    public $user;
    public $selected_country = '';
    public $about;
    public $email;
    public $username;
    public $name;

    protected $rules = [
        'username' => 'required|unique|min:2',
        'name' => 'required|max:64|min:4',
        'email' => 'required|email',
        // 'type' => 'required',
    ];

    /**
     * Sets the database value of the about textarea.
     *
     * @return void
     */
    public function mount()
    {
        $this->username = $this->user->username;
        $this->about = $this->user->about;
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->selected_country = $this->user->country;
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
        $this->validateOnly($propertyName);
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
