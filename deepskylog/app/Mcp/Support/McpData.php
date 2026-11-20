<?php

namespace App\Mcp\Support;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;

/**
 * What the tools of the MCP server (App\Mcp\Servers\DeepskyLogServer) share:
 * the observer's own equipment and locations by id, and the compact shapes
 * the tools return. Everything here reads only the observer's own data and
 * what DeepskyLog shows publicly.
 */
class McpData
{
    /**
     * Whether the token may write (the 'write' permission): the tools that
     * change something are only offered to such a token and check it again.
     */
    public static function canWrite(Request $request): bool
    {
        return (bool) $request->user()?->tokenCan('write');
    }

    /**
     * The plain-text description of an observing list, keeping its lines
     * (the list page prints it with nl2br); old lists may hold markup.
     */
    public static function listDescription(?string $description): string
    {
        return trim(html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Plain text from a column the old site stored HTML-encoded.
     */
    public static function plain(?string $text): string
    {
        return trim(html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Text from a rich-text column (TinyMCE) without its markup, for a
     * language model.
     */
    public static function text(?string $html, ?int $limit = null): string
    {
        $text = preg_replace('/\s+/u', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', self::plain($html)))) ?? '';
        $text = trim($text);

        return $limit !== null && mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit - 1)) . '…' : $text;
    }

    /**
     * One of the observer's locations, or their standard location when no id
     * is given; null when they have none (or the id is not theirs).
     */
    public static function location(User $user, mixed $id = null): ?Location
    {
        $id = $id ?: $user->stdlocation;

        return $id ? Location::query()->where('user_id', $user->id)->find((int) $id) : null;
    }

    /**
     * One of the observer's instruments, or their standard telescope when no
     * id is given; null when they have none (or the id is not theirs).
     */
    public static function instrument(User $user, mixed $id = null): ?Instrument
    {
        $id = $id ?: $user->stdtelescope;

        return $id ? Instrument::query()->where('user_id', $user->id)->find((int) $id) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function locationRow(Location $location, User $user): array
    {
        return [
            'id'        => $location->id,
            'name'      => self::plain($location->name),
            'latitude'  => round((float) $location->latitude, 4),
            'longitude' => round((float) $location->longitude, 4),
            'elevation' => $location->elevation,
            'country'   => $location->country,
            'timezone'  => $location->timezone,
            'sqm'       => $location->getSqm(),
            'nelm'      => $location->getNelm((float) ($user->fstOffset ?? 0)),
            'bortle'    => $location->getBortle(),
            'standard'  => (int) $user->stdlocation === (int) $location->id,
            'active'    => (bool) $location->active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function instrumentRow(Instrument $instrument, User $user): array
    {
        return [
            'id'                  => $instrument->id,
            'name'                => self::plain($instrument->fullName()),
            'type'                => $instrument->instrument_type?->name,
            'aperture_mm'         => $instrument->aperture_mm     !== null ? (float) $instrument->aperture_mm : null,
            'focal_length_mm'     => $instrument->focal_length_mm !== null ? (float) $instrument->focal_length_mm : null,
            'fixed_magnification' => $instrument->fixedMagnification ?: null,
            'mount'               => $instrument->mount_type?->name,
            'standard'            => (int) $user->stdtelescope === (int) $instrument->id,
            'active'              => (bool) $instrument->active,
        ];
    }

    /**
     * The size of a deep-sky object in arcminutes ("10.2 x 3.1"), from
     * diam1 and diam2 in arcseconds; null without one.
     */
    public static function size(mixed $diam1, mixed $diam2): ?string
    {
        if (! is_numeric($diam1) || (float) $diam1 <= 0) {
            return null;
        }
        $format = fn ($arcsec) => rtrim(rtrim(number_format((float) $arcsec / 60, (float) $arcsec < 60 ? 2 : 1, '.', ''), '0'), '.');

        return $format($diam1) . (is_numeric($diam2) && (float) $diam2 > 0 ? ' x ' . $format($diam2) : '');
    }

    /**
     * The URL of an object's page on DeepskyLog.
     */
    public static function objectUrl(?string $slug): ?string
    {
        return $slug ? url('/object/' . $slug) : null;
    }

    /**
     * The observer's observations, joined to the visibility of a deep-sky
     * observation.
     */
    public static function observations(User $user): \Illuminate\Database\Query\Builder
    {
        return DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $user->username);
    }

    /**
     * Only the observations in which the object was seen.
     */
    public static function seen(\Illuminate\Database\Query\Builder $query): \Illuminate\Database\Query\Builder
    {
        return $query->where(fn ($q) => $q->whereNull('details.visibility')
            ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN));
    }

    /**
     * The words of the visibility scale of a deep-sky observation (1 is
     * easy, 7 not seen).
     */
    public static function visibility(mixed $value): ?string
    {
        return match ((int) $value) {
            1       => 'very simple, prominent object',
            2       => 'object easily perceptible with direct vision',
            3       => 'object perceptible with direct vision',
            4       => 'averted vision required to perceive object',
            5       => 'object barely perceptible with averted vision',
            6       => 'perception of object is very questionable',
            7       => 'object definitely not seen',
            default => null,
        };
    }
}
