<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserSettings extends Component
{
    use WithFileUploads;

    public $user;
    public $selected_country = '';
    public $about;
    public $email;
    public $username;
    public $name;
    public $photo;

    protected $rules = [
        'username' => 'required|unique|min:2',
        'name' => 'required|max:64|min:4',
        'email' => 'required|email',
        'photo' => 'image|max:10240',
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
     * Method that is called when the submit button is pushed.
     *
     * @return void
     */
    public function save()
    {
        // Only for the upload of the image
        $this->validate([
            'photo' => 'image|max:1024',
        ]);

        if (User::find($this->user->id)->getFirstMedia('observer') != null
        ) {
            // First remove the current image
            User::find($this->user->id)
            ->getFirstMedia('observer')
            ->delete();
        }

        // Update the picture
        User::find($this->user->id)
            ->addMedia($this->photo->getRealPath())
            ->usingFileName($this->user->id.'.png')
            ->toMediaCollection('observer');

        // TODO: Also update the database with the name, email, about,...
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
