<?php

namespace App\Services\StarParties;

use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\StarParty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * What was observed at a starparty (#315): the sessions and observations at
 * a location within the distance the organiser chose (radiusKm()) of the
 * site, from the first evening to the morning after the last night, of the
 * observers who come (or of anybody, when the organiser chose so).
 * Observers record these with their own locations, so they are found by
 * distance instead of by a link to the starparty.
 */
class StarPartyActivity
{
    /** The observations shown on the page of a starparty. */
    public const MAX_OBSERVATIONS = 50;

    private const TARGET_TYPES = ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar'];

    /** @var list<int>|null */
    private ?array $locationIds = null;

    /** @var list<string>|null|false the observers who count (null: all), false until known */
    private array|null|false $observers = false;

    public function __construct(private StarParty $party)
    {
    }

    /**
     * The ids of the locations near the site: a box of the radius around it,
     * then the great-circle distance.
     *
     * @return list<int>
     */
    public function locationIds(): array
    {
        if ($this->locationIds !== null) {
            return $this->locationIds;
        }

        $lat  = $this->party->latitude;
        $lon  = $this->party->longitude;
        $km   = $this->party->radiusKm();
        $dLat = $km / 111.0;
        $dLon = $km / (111.0 * max(0.01, cos(deg2rad($lat))));

        return $this->locationIds = Location::query()
            ->whereBetween('latitude', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('longitude', [$lon - $dLon, $lon + $dLon])
            ->get(['id', 'latitude', 'longitude'])
            ->filter(fn (Location $location) => StarParty::distanceKm($lat, $lon, (float) $location->latitude, (float) $location->longitude) <= $km)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /** @return list<string>|null the usernames of the observers who count, null for all */
    private function countedObservers(): ?array
    {
        if ($this->observers === false) {
            $this->observers = $this->party->activityObservers();
        }

        return $this->observers;
    }

    /**
     * The active sessions that start in the nights of the starparty near the
     * site, oldest first.
     *
     * @return Collection<int, ObservationSession>
     */
    public function sessions(): Collection
    {
        $ids = $this->locationIds();
        if ($ids === []) {
            return collect();
        }

        return ObservationSession::where('active', 1)
            ->whereIn('locationid', $ids)
            ->when($this->countedObservers() !== null, fn ($q) => $q->whereIn('observerid', $this->countedObservers() ?: ['']))
            ->where('begindate', '>=', $this->party->start_date->toDateString().' 12:00:00')
            ->where('begindate', '<', $this->party->end_date->copy()->addDay()->toDateString().' 12:00:00')
            ->withObserver()
            ->orderBy('begindate')
            ->get();
    }

    /** The observations made near the site in the nights of the starparty. */
    public function observationsQuery(): Builder
    {
        $ids = $this->locationIds();
        // The dates and times of the observations are in UT. The window lasts
        // at least a day, so its first and last date differ; an observation
        // without a time counts on both.
        $from      = $this->party->startsAt()->utc();
        $until     = $this->party->endsAt()->utc();
        $fromDate  = $from->toDateString();
        $untilDate = $until->toDateString();

        return Observation::query()
            ->whereIn('location_id', $ids === [] ? [0] : $ids)
            ->whereIn('target_type', self::TARGET_TYPES)
            ->when($this->countedObservers() !== null, fn (Builder $q) => $q->whereIn('observer_username', $this->countedObservers() ?: ['']))
            ->whereBetween('observed_on', [$fromDate, $untilDate])
            ->where(fn (Builder $q) => $q->where(fn (Builder $q) => $q->where('observed_on', '>', $fromDate)->where('observed_on', '<', $untilDate))
                ->orWhere(fn (Builder $q) => $q->where('observed_on', $fromDate)
                    ->where(fn (Builder $q) => $q->whereNull('observed_time')->orWhere('observed_time', '>=', $from->format('H:i:s'))))
                ->orWhere(fn (Builder $q) => $q->where('observed_on', $untilDate)
                    ->where(fn (Builder $q) => $q->whereNull('observed_time')->orWhere('observed_time', '<', $until->format('H:i:s')))));
    }

    /**
     * The observers with their number of observations, most first.
     *
     * @return Collection<int, object{observer_username: string, total: int}>
     */
    public function observers(): Collection
    {
        return $this->observationsQuery()
            ->toBase()
            ->selectRaw('observer_username, count(*) as total')
            ->groupBy('observer_username')
            ->orderByDesc('total')
            ->get();
    }

    /** @return Collection<int, Observation> the newest observations */
    public function observations(int $limit = self::MAX_OBSERVATIONS): Collection
    {
        return $this->observationsQuery()
            ->orderByDesc('observed_on')
            ->orderByDesc('observed_time')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
