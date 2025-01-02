<?php

namespace App\Livewire\Profile;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class UpdateUserLanguageInformation extends Component
{
    public $observationlanguage;

    public $language;

    public $translate;

    public $sendMail;

    protected $rules = [
        'observationlanguage' => 'string',
        'language' => 'string',
        'translate' => 'boolean',
        'sendMail' => 'boolean',
    ];

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->observationlanguage = auth()->user()->observationlanguage;
        $this->language = auth()->user()->language;
        $this->translate = boolval(auth()->user()->translate);
        $this->sendMail = boolval(auth()->user()->sendMail);
    }

    /**
     * Validate and update the given user's language information.
     */
    public function updateLanguageInformation(): void
    {
        auth()->user()->forceFill([
            'observationlanguage' => $this->observationlanguage,
            'language' => $this->language,
            'translate' => $this->translate,
            'sendMail' => $this->sendMail,
        ])->save();

        App::setLocale($this->language);
        Carbon::setLocale($this->language);

        $this->dispatch('saved');

        $this->redirect('/language/'.$this->language);
    }

    public function render()
    {
        return view('livewire.profile.update-user-language-information');
    }
}
