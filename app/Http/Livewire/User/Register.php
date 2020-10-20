<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Request;
use Stevebauman\Location\Facades\Location;
use deepskylog\LaravelGettext\Facades\LaravelGettext;

class Register extends Component
{
    public $username;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public String $cclicense;
    public String $copyright;
    public $country;
    public $observationlanguage;
    public $language;

    protected $rules = [
        'username' => [
            'required', 'string', 'max:255', 'min:2', 'unique:users',
        ],
        'name'  => ['required', 'string', 'max:255', 'min:5'],
        'email' => [
            'required', 'string', 'email', 'max:255', 'unique:users',
        ],
    ];

    protected $licenses = [
        'Attribution CC BY'                                => 0,
        'Attribution-ShareAlike CC BY-SA'                  => 1,
        'Attribution-NoDerivs CC BY-ND'                    => 2,
        'Attribution-NonCommercial CC BY-NC'               => 3,
        'Attribution-NonCommercial-ShareAlike CC BY-NC-SA' => 4,
        'Attribution-NonCommercial-NoDerivs CC BY-NC-ND'   => 5,
    ];

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->cclicense = 0;
        $this->copyright = 'Attribution CC BY';

        $this->observationlanguage = LaravelGettext::getLocaleLanguage();
        $this->language            = LaravelGettext::getLocale();

        if (Location::get(Request::ip())) {
            $this->country = Location::get(Request::ip())->countryCode;
        } else {
            $this->country = Location::get(trim(shell_exec('dig +short myip.opendns.com @resolver1.opendns.com')))->countryCode;
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

        if (in_array($this->cclicense, $this->licenses)) {
            $this->copyright = array_search($this->cclicense, $this->licenses);
        } elseif ($this->cclicense == 6) {
            $this->copyright = '';
        }
    }

    public function render()
    {
        return view('livewire.user.register', ['licenses' => $this->licenses]);
    }
}
