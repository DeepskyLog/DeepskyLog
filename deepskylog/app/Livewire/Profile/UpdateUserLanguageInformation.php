<?php

namespace App\Livewire\Profile;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class UpdateUserLanguageInformation extends Component
{
    public $observationlanguage;

    public $language;

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->observationlanguage = auth()->user()->observationlanguage;
        $this->language = auth()->user()->language;
    }

    /**
     * Validate and update the given user's language information.
     */
    public function updateLanguageInformation(): void
    {
        auth()->user()->forceFill([
            'observationlanguage' => $this->observationlanguage,
            'language' => $this->language,
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
