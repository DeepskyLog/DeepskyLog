<?php

namespace App\Livewire\Profile;

use App\Services\ObserverCountries;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdateUserObservingInformation extends Component
{
    /**
     * Fallback atlas, matching the database default of users.standardAtlasCode,
     * which is a NOT NULL column.
     */
    private const DEFAULT_ATLAS_CODE = 'urano';

    public $stdlocation;

    public $stdtelescope;

    public $stdinstrumentset;

    public $standardAtlasCode;

    public $showInches;

    // New defaults for Aladin preview
    public $stdeyepiece;

    /**
     * The defaults must be the user's own equipment and locations.
     */
    protected function rules(): array
    {
        $owned = fn (string $table) => Rule::exists($table, 'id')->where('user_id', auth()->id());

        return [
            'stdlocation' => ['nullable', 'integer', $owned('locations')],
            'stdtelescope' => ['nullable', 'integer', $owned('instruments')],
            'stdinstrumentset' => ['nullable', 'integer', $owned('instrument_sets')],
            'standardAtlasCode' => 'required|string|exists:atlases,code',
            'showInches' => 'boolean',
            'stdeyepiece' => ['nullable', 'integer', $owned('eyepieces')],
        ];
    }

    /**
     * Sets the database values.
     */
    public function mount(): void
    {
        $this->stdlocation = auth()->user()->stdlocation;
        $this->stdtelescope = auth()->user()->stdtelescope;
        $this->stdinstrumentset = auth()->user()->stdinstrumentset ?? null;
        $this->standardAtlasCode = auth()->user()->standardAtlasCode ?: self::DEFAULT_ATLAS_CODE;
        $this->showInches = boolval(auth()->user()->showInches);
        $this->stdeyepiece = auth()->user()->stdeyepiece ?? null;
    }

    /**
     * Validate and update the given user's observing information.
     */
    public function updateObservingInformation(): void
    {
        // An unset select sends an empty string, which is not numeric, and the
        // "No standard ..." options of the selects have id 0, which exists nowhere.
        foreach (['stdlocation', 'stdtelescope', 'stdinstrumentset', 'stdeyepiece'] as $field) {
            if ($this->{$field} === '' || (string) $this->{$field} === '0') {
                $this->{$field} = null;
            }
        }

        // standardAtlasCode is NOT NULL, so a cleared select falls back to the default.
        if (blank($this->standardAtlasCode)) {
            $this->standardAtlasCode = self::DEFAULT_ATLAS_CODE;
        }

        $this->validate();

        // Saved at once, so that the recompute of the contrast reserve that a
        // changed default instrument or location queues (User::booted()) runs once.
        auth()->user()->forceFill([
            'stdlocation' => $this->stdlocation ?: null,
            'stdtelescope' => $this->stdtelescope ?: null,
            'stdinstrumentset' => $this->stdinstrumentset ?: null,
            'stdeyepiece' => $this->stdeyepiece ?: null,
            'standardAtlasCode' => $this->standardAtlasCode,
            'showInches' => $this->showInches,
        ])->save();

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-observing-information', [
            // Observers of a country that measures in inches are offered the switch.
            'suggestInches' => ! $this->showInches && ObserverCountries::usesInches(auth()->user()->country),
            'countryLabel' => ObserverCountries::label(auth()->user()->country),
        ]);
    }
}
