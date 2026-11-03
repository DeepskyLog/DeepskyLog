<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Location;
use App\Models\MessageBlock;
use App\Models\StarParty;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Observing partners nearby (/observing-partners): the observers who turned
 * on users.partner_search and whose standard location is near the viewer's.
 *
 * Privacy: a partner's location is never shown, only how far the centre of
 * its cell of CELL_DEG (that of the sky-brightness map) is from the centre
 * of the viewer's cell, rounded to ROUND_KM. Moving one's own standard
 * location around therefore tells at most which cell a partner is in, which
 * they agreed to when turning it on. Only an observer who turned it on
 * finds others (an observer who did not only sees how many there are), and
 * observers who blocked each other never see each other. Contact goes
 * through DeepskyLog messages, so no e-mail address is shown either.
 *
 * Next to the partners, the page suggests the coming starparties near the
 * viewer's standard location (public ones and those visible to them) and the
 * clubs the partners nearby are members of.
 */
class ObservingPartners
{
    /** The size of a cell in degrees of latitude and longitude. */
    public const CELL_DEG = SkyBrightnessMap::CELL_DEG;

    /** The distances (km) the viewer chooses from. */
    public const RADII = [25, 50, 100, 250];

    public const DEFAULT_RADIUS = 100;

    /** Distances are shown rounded to this many km. */
    public const ROUND_KM = 10;

    /** A partner closer than this (km, about a cell) is shown as "within NEAR_KM". */
    public const NEAR_KM = 30;

    /** The most partners shown. */
    public const MAX_PARTNERS = 50;

    /** The longest note of a partner. */
    public const NOTE_LENGTH = 300;

    public static function radius(mixed $radius): int
    {
        return in_array((int) $radius, self::RADII, true) ? (int) $radius : self::DEFAULT_RADIUS;
    }

    /** The standard location of the user, when it has coordinates. */
    public static function home(User $user): ?Location
    {
        $location = $user->standardLocation;

        return $location && $location->hasValidCoordinates() ? $location : null;
    }

    /**
     * The centre of the cell the point lies in.
     *
     * @return array{0: float, 1: float}
     */
    public static function cellCentre(float $latitude, float $longitude): array
    {
        return [
            (floor($latitude / self::CELL_DEG) + 0.5)  * self::CELL_DEG,
            (floor($longitude / self::CELL_DEG) + 0.5) * self::CELL_DEG,
        ];
    }

    /** The distance (km) between the centres of the cells of two points. */
    public static function cellDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        [$lat1, $lon1] = self::cellCentre($lat1, $lon1);
        [$lat2, $lon2] = self::cellCentre($lat2, $lon2);

        return StarParty::distanceKm($lat1, $lon1, $lat2, $lon2);
    }

    /** The distance as shown: rounded, and null for "within NEAR_KM". */
    public static function shownDistance(float $km): ?int
    {
        return $km < self::NEAR_KM ? null : (int) (round($km / self::ROUND_KM) * self::ROUND_KM);
    }

    /**
     * The observers who can be found whose cell is within the radius of the
     * viewer's, nearest first: the user and the distance shown (null: within
     * NEAR_KM). Empty when the viewer has no standard location.
     *
     * @return Collection<int, array{user: User, distance: ?int, km: float}>
     */
    public static function nearby(User $viewer, int $radius): Collection
    {
        $home = self::home($viewer);
        if (! $home) {
            return collect();
        }

        // Observers who blocked the viewer, or whom the viewer blocked
        $blocked = MessageBlock::where('blocked_user_id', $viewer->id)->pluck('user_id')
            ->merge(MessageBlock::where('user_id', $viewer->id)->pluck('blocked_user_id'))
            ->map(fn ($id) => (int) $id)
            ->all();

        return User::query()
            ->where('partner_search', true)
            ->whereKeyNot($viewer->id)
            ->whereNotIn('id', $blocked)
            ->whereNotNull('stdlocation')
            ->with(['standardLocation', 'standardInstrument.instrument_make', 'clubs'])
            ->get()
            ->filter(fn (User $user) => self::home($user) !== null)
            ->map(function (User $user) use ($home) {
                $location = $user->standardLocation;
                $km       = self::cellDistanceKm((float) $home->latitude, (float) $home->longitude, (float) $location->latitude, (float) $location->longitude);

                return ['user' => $user, 'distance' => self::shownDistance($km), 'km' => $km];
            })
            ->filter(fn (array $partner) => $partner['km'] <= $radius)
            ->sortBy([['km', 'asc'], [fn ($a, $b) => strcasecmp($a['user']->name, $b['user']->name)]])
            ->take(self::MAX_PARTNERS)
            ->values();
    }

    /**
     * The coming starparties the viewer may see, not cancelled, whose site
     * is within the radius of the viewer's standard location, soonest first.
     *
     * @return Collection<int, array{party: StarParty, km: float}>
     */
    public static function starParties(User $viewer, int $radius): Collection
    {
        $home = self::home($viewer);
        if (! $home) {
            return collect();
        }

        return StarParty::visibleTo($viewer)
            ->upcoming()
            ->whereNull('cancelled_at')
            ->orderBy('start_date')
            ->get()
            ->map(fn (StarParty $party) => [
                'party' => $party,
                'km'    => StarParty::distanceKm((float) $home->latitude, (float) $home->longitude, (float) $party->latitude, (float) $party->longitude),
            ])
            ->filter(fn (array $row) => $row['km'] <= $radius)
            ->values();
    }

    /**
     * The clubs the partners are members of, with how many of them, most
     * first; only clubs of partners, who agreed to be found.
     *
     * @param  Collection<int, array{user: User}>  $partners
     * @return Collection<int, array{club: Club, partners: int}>
     */
    public static function clubs(Collection $partners): Collection
    {
        return $partners
            ->flatMap(fn (array $partner) => $partner['user']->clubs)
            ->groupBy('id')
            ->map(fn (Collection $clubs) => ['club' => $clubs->first(), 'partners' => $clubs->count()])
            ->sortBy([['partners', 'desc'], [fn ($a, $b) => strcasecmp($a['club']->name, $b['club']->name)]])
            ->values();
    }
}
