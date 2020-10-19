<?php

namespace App\Http\Livewire\User;

use Livewire\Component;

class ResetPassword extends Component
{
    public $password;
    public $password_confirmation;
    public $email;

    protected $rules = [
        'email' => [
            'required', 'string', 'email', 'max:255',
        ],
    ];

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

        if (strlen($this->password) > 0) {
            $this->validate(
                ['password' => ['required',
                    'min:8',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&^]/',
                    'confirmed', ]]
            );
        }
    }

    public function render()
    {
        return view('livewire.user.reset-password');
    }
}
