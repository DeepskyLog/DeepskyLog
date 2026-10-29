<?php

namespace App\Livewire;

use App\Models\Location;
use App\Services\LocalHorizon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The local horizon of a location on its page: a panorama of the trees,
 * houses and hills around it (LocalHorizon). The owner (or an administrator)
 * draws it by dragging over the panorama, types the altitude per direction,
 * or pastes the horizon.txt of a Stellarium landscape; the planners then
 * leave out what stands behind it.
 */
class LocationHorizon extends Component
{
    #[Locked]
    public int $locationId;

    #[Locked]
    public bool $canEdit = false;

    /** @var list<int> LocalHorizon::POINTS altitudes in degrees, from the north through the east */
    public array $altitudes = [];

    public bool $editing = false;

    /** A pasted horizon: lines of an azimuth and an altitude. */
    public string $import = '';

    public function mount(Location $location): void
    {
        $this->locationId = $location->id;
        $user             = Auth::user();
        $this->canEdit    = $user !== null && ((int) $user->id === (int) $location->user_id || $user->isAdministrator());
        $this->altitudes  = $this->stored($location);
    }

    /**
     * @return list<int>
     */
    private function stored(Location $location): array
    {
        return array_map('intval', LocalHorizon::normalize($location->horizon) ?? array_fill(0, LocalHorizon::POINTS, 0));
    }

    private function location(): Location
    {
        abort_unless($this->canEdit, 403);

        return Location::findOrFail($this->locationId);
    }

    public function edit(): void
    {
        $this->location();
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->altitudes = $this->stored($this->location());
        $this->import    = '';
        $this->editing   = false;
        $this->resetValidation();
    }

    public function importText(): void
    {
        $this->location();
        $altitudes = LocalHorizon::parse($this->import);
        if ($altitudes === null) {
            $this->addError('import', __('Give at least two lines with an azimuth and an altitude in degrees.'));

            return;
        }
        $this->resetValidation('import');
        $this->altitudes = array_map('intval', $altitudes);
        $this->import    = '';
    }

    public function clear(): void
    {
        $this->location();
        $this->altitudes = array_fill(0, LocalHorizon::POINTS, 0);
    }

    public function save(): void
    {
        $location = $this->location();
        $this->validate([
            'altitudes'   => ['required', 'array', 'size:'.LocalHorizon::POINTS],
            'altitudes.*' => ['required', 'numeric', 'min:0', 'max:'.LocalHorizon::MAX_ALTITUDE],
        ], [], ['altitudes.*' => __('altitude')]);

        $location->horizon = LocalHorizon::normalize($this->altitudes);
        $location->save();

        $this->altitudes = $this->stored($location);
        $this->editing   = false;
        session()->flash('horizon', __('The local horizon is saved.'));
    }

    public function render()
    {
        return view('livewire.location-horizon', [
            'step'       => LocalHorizon::STEP,
            'maxAlt'     => LocalHorizon::MAX_ALTITUDE,
            'hasHorizon' => max($this->altitudes ?: [0]) > 0,
        ]);
    }
}
