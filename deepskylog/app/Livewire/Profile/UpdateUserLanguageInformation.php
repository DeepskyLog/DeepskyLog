<?php

namespace App\Livewire\Profile;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdateUserLanguageInformation extends Component
{
    public $observationlanguage;

    public $language;

    public $translate;

    public $sendMail;

    public $followDigest;

    public $clearNightAlert;

    public $sessionReminder;

    public $yearReview;

    protected function rules(): array
    {
        return [
            // The current value stays valid, so an observer with a language
            // that is no longer offered can still save the other settings.
            'observationlanguage' => ['required', 'string', Rule::in([
                ...config('app.observation_languages'),
                auth()->user()->observationlanguage,
            ])],
            'language' => ['required', 'string', Rule::in(array_values(config('app.available_locales')))],
            'translate' => 'boolean',
            'sendMail' => 'boolean',
            'followDigest' => 'boolean',
            'clearNightAlert' => 'boolean',
            'sessionReminder' => 'boolean',
            'yearReview' => 'boolean',
        ];
    }

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
        $this->followDigest = boolval(auth()->user()->follow_digest);
        $this->clearNightAlert = boolval(auth()->user()->clear_night_alert);
        $this->sessionReminder = boolval(auth()->user()->session_reminder);
        $this->yearReview = boolval(auth()->user()->year_review);
    }

    /**
     * Validate and update the given user's language information.
     */
    public function updateLanguageInformation(): void
    {
        $this->validate();

        auth()->user()->forceFill([
            'observationlanguage' => $this->observationlanguage,
            'language' => $this->language,
            'translate' => $this->translate,
            'sendMail' => $this->sendMail,
            'follow_digest' => $this->followDigest,
            'clear_night_alert' => $this->clearNightAlert,
            'session_reminder' => $this->sessionReminder,
            'year_review' => $this->yearReview,
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
