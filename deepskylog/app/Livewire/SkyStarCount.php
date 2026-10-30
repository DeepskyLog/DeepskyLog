<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\SkyMeasurement;
use App\Services\StarCountAreas;
use App\Services\TwilightSky;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Measure your sky by counting stars (/sky-quality/star-count): for one of
 * the observer's locations and a moment (default now), the figures of
 * StarCountAreas high enough there, the chosen one drawn as the observer sees
 * it; the number of stars they count inside it gives the naked-eye limiting
 * magnitude at the zenith, which they save as a SkyMeasurement and
 * optionally as the location's limiting magnitude.
 */
class SkyStarCount extends Component
{
    /** The Sun's altitude above which the sky is not dark: the result is marked. */
    private const DARK_SUN_ALTITUDE = -15.0;

    /** The most stars one can enter. */
    public const MAX_COUNT = 400;

    #[Url(as: 'location')]
    public ?int $locationId = null;

    /** Local time at the location, Y-m-d\TH:i; empty for now. */
    #[Url(as: 'at')]
    public string $moment = '';

    #[Url]
    public string $area = '';

    public ?int $count = null;

    public bool $applyToLocation = false;

    public ?int $savedId = null;

    public function mount(): void
    {
        $locations = $this->locations();
        if (! isset($locations[$this->locationId])) {
            $standard = Auth::user()->stdlocation;
            $this->locationId = isset($locations[$standard]) ? (int) $standard : (array_key_first($locations) ?: null);
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['locationId', 'moment'], true)) {
            $this->area = '';
        }
        if ($property !== 'applyToLocation') {
            $this->savedId = null;
        }
    }

    public function now(): void
    {
        $this->moment = '';
        $this->area = '';
        $this->savedId = null;
    }

    public function save(): void
    {
        $location = $this->location();
        abort_unless($location, 403);
        $this->validate(['count' => 'required|integer|min:1|max:' . self::MAX_COUNT]);

        $area = StarCountAreas::area($this->chosenArea($location));
        $timestamp = $this->timestamp($location);
        $result = $area ? StarCountAreas::measure($area, (int) $this->count, $location, $timestamp) : null;
        if (! $result) {
            $this->addError('count', __('This figure is not above the horizon at that moment.'));

            return;
        }

        $measurement = SkyMeasurement::create([
            'user_id' => Auth::id(),
            'location_id' => $location->id,
            'measured_at' => Carbon::createFromTimestampUTC($timestamp),
            'area' => $area['key'],
            'star_count' => (int) $this->count,
            'area_magnitude' => $result['area_magnitude'],
            'altitude' => $result['altitude'],
            'nelm' => $result['nelm'],
        ]);

        if ($this->applyToLocation) {
            // the location stores the NELM without the observer's fstOffset, and the SQM of the same sky
            $fst = (float) (Auth::user()->fstOffset ?? 0);
            $location->update([
                'limitingMagnitude' => round($result['nelm'] + $fst, 2),
                'skyBackground' => round(Magnitude::nelmToSqm($result['nelm'], $fst), 2),
            ]);
        }

        $this->savedId = $measurement->id;
    }

    public function deleteMeasurement(int $id): void
    {
        SkyMeasurement::where('user_id', Auth::id())->whereKey($id)->delete();
        if ($this->savedId === $id) {
            $this->savedId = null;
        }
    }

    /** @return array<int, string> the observer's active locations, id => name */
    private function locations(): array
    {
        return Location::where('user_id', Auth::id())
            ->where('active', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->map(fn ($name) => html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ->all();
    }

    private function location(): ?Location
    {
        return $this->locationId ? Location::where('user_id', Auth::id())->find($this->locationId) : null;
    }

    private function timezone(Location $location): string
    {
        $timezone = (string) $location->timezone;

        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'UTC';
    }

    private function timestamp(Location $location): int
    {
        if ($this->moment !== '') {
            try {
                return Carbon::createFromFormat('Y-m-d\TH:i', $this->moment, $this->timezone($location))->getTimestamp();
            } catch (\Throwable) {
                // an invalid moment is now
            }
        }

        return now()->getTimestamp();
    }

    /** The chosen figure when it is high enough, else the highest one. */
    private function chosenArea(Location $location): string
    {
        $visible = StarCountAreas::visible($location, $this->timestamp($location));
        $keys = array_column($visible, 'key');

        return in_array($this->area, $keys, true) ? $this->area : ($keys[0] ?? '');
    }

    public function render()
    {
        $location = $this->location();
        $data = ['locations' => $this->locations(), 'location' => $location];

        if ($location) {
            $timestamp = $this->timestamp($location);
            $visible = StarCountAreas::visible($location, $timestamp);
            $key = $this->chosenArea($location);
            $area = $key !== '' ? StarCountAreas::area($key) : null;
            $data += [
                'localMoment' => Carbon::createFromTimestamp($timestamp, $this->timezone($location)),
                'visible' => $visible,
                'areaKey' => $key,
                'chart' => $area ? StarCountAreas::chart($area, $location, $timestamp) : null,
                'result' => $area && $this->count !== null && $this->count > 0 && $this->count <= self::MAX_COUNT
                    ? StarCountAreas::measure($area, $this->count, $location, $timestamp)
                    : null,
                'twilight' => TwilightSky::sunAltitude($timestamp, (float) $location->latitude, (float) $location->longitude) > self::DARK_SUN_ALTITUDE,
                'locationNelm' => $location->getNelm((float) (Auth::user()->fstOffset ?? 0)),
                'measurements' => SkyMeasurement::where('user_id', Auth::id())
                    ->where('location_id', $location->id)
                    ->orderByDesc('measured_at')
                    ->limit(10)
                    ->get(),
            ];
        }

        return view('livewire.sky-star-count', $data);
    }
}
