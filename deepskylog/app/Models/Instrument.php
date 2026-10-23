<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use App\Jobs\EnqueueComputeCRForUser;
use App\Models\Traits\DetachesOnDelete;
use App\Models\Traits\HasObservationsDates;
use App\Models\Traits\OwnedByUser;
use App\Traits\ClearsResponseCache;
use App\Traits\HasUniqueSlugPerUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Instrument extends Model
{
    use ClearsResponseCache;
    use DetachesOnDelete;
    use HasObservationsDates;
    use HasUniqueSlugPerUser;
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'name',
        'aperture_mm',
        'type',
        'instrument_type_id',
        'make_id',
        'mount_type_id',
        'focal_length_mm',
        'fixedMagnification',
        'field_of_view_deg',
        'finder_id',
        'lens_id',
        'active',
        'observer',
        'flip_image',
        'flop_image',
        'obstruction_perc',
        'picture',
        'description',
    ];

    protected $casts = [
        'description' => PurifiedHtml::class,
    ];

    protected $with = ['instrument_make', 'mount_type', 'instrument_type'];

    /**
     * Apparent field, in degrees, assumed for the eyepiece of a finder or
     * binoculars whose true field of view is not entered.
     */
    public const FIXED_MAGNIFICATION_APPARENT_FOV = 50.0;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'unique' => true,
            ],
        ];
    }

    /**
     * Adds the link to the observer.
     *
     * @return BelongsTo the observer this instrument belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function instrument_make(): BelongsTo
    {
        return $this->belongsTo(InstrumentMake::class, 'make_id');
    }

    // Add a belongsTo relationship to the InstrumentMake model

    public function mount_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\MountType');
    }

    public function instrument_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\InstrumentType');
    }

    /**
     * The lens that is always used with this telescope (a coma corrector, a
     * Barlow): the default lens of observations and field views.
     */
    public function lens(): BelongsTo
    {
        return $this->belongsTo(Lens::class, 'lens_id');
    }

    /**
     * The contrast reserve and best magnification of the observers with
     * this as their standard telescope are computed with its lens.
     */
    protected static function booted(): void
    {
        static::updated(function (self $instrument) {
            if (! $instrument->wasChanged('lens_id')) {
                return;
            }

            User::where('stdtelescope', $instrument->id)->get(['id', 'stdtelescope', 'stdlocation'])
                ->each(fn (User $user) => EnqueueComputeCRForUser::dispatch($user->id, $instrument->id, $user->stdlocation, $instrument->lens_id));
        });
    }

    /**
     * The finderscope mounted on this telescope.
     */
    public function finder(): BelongsTo
    {
        return $this->belongsTo(Instrument::class, 'finder_id');
    }

    public function first_observation_date(): array
    {
        return $this->first_observation_date_generic('instrumentid');
    }

    public function last_observation_date(): array
    {
        return $this->last_observation_date_generic('instrumentid');
    }

    public function get_used_eyepieces_as_string(): string
    {
        $eyepieces = $this->get_used_eyepieces();

        $to_return = '';

        foreach ($eyepieces as $eyepiece) {
            if ($eyepiece == 0) {
                continue;
            }
            $ep = Eyepiece::where('id', $eyepiece)->first();
            if (!$ep || !$ep->user) {
                continue;
            }
            $to_return .= "<a href='/eyepiece/" . e($ep->user->slug) . '/' . e($ep->slug) . "'>" .
                e(html_entity_decode($ep->fullName())) . '</a>' . ', ';
        }

        // Remove the trailing comma and space (if any)
        if ($to_return === '') {
            return '';
        }

        return substr($to_return, 0, -2);
    }

    public function get_used_eyepieces(): Collection
    {
        return Observation::where('instrument_id', $this->id)->groupby('eyepiece_id')->distinct()->pluck('eyepiece_id');
    }

    /**
     * Returns the name of the instrument type.
     *
     * @return string the name of the instrument type
     */
    public function fullName(): string
    {
        return ltrim($this->instrument_make->name . ' ' . $this->name);
    }

    public function get_used_filters_as_string(): string
    {
        $filters = $this->get_used_filters();

        $to_return = '';

        foreach ($filters as $filter) {
            if ($filter == 0) {
                continue;
            }
            $filt = Filter::where('id', $filter)->first();
            if (!$filt || !$filt->user) {
                continue;
            }
            $to_return .= "<a href='/filter/" . e($filt->user->slug) . '/' . e($filt->slug) . "'>" .
                e(html_entity_decode($filt->name)) . '</a>' . ', ';
        }

        // Remove the trailing comma and space (if any)
        if ($to_return === '') {
            return '';
        }

        return substr($to_return, 0, -2);
    }

    public function get_used_filters(): Collection
    {
        return Observation::where('instrument_id', $this->id)->groupby('filter_id')->distinct()->pluck('filter_id');
    }

    public function get_used_lenses_as_string(): string
    {
        $lenses = $this->get_used_lenses();

        $to_return = '';

        foreach ($lenses as $lens) {
            if ($lens == 0) {
                continue;
            }
            $lns = Lens::where('id', $lens)->first();
            if (!$lns || !$lns->user) {
                continue;
            }
            $to_return .= "<a href='/lens/" . e($lns->user->slug) . '/' . e($lns->slug) . "'>" .
                e(html_entity_decode($lns->name)) . '</a>' . ', ';
        }

        // Remove the trailing comma and space (if any)
        if ($to_return === '') {
            return '';
        }

        return substr($to_return, 0, -2);
    }

    public function get_used_lenses(): Collection
    {
        return Observation::where('instrument_id', $this->id)->groupby('lens_id')->distinct()->pluck('lens_id');
    }

    public function get_used_locations_as_string(): string
    {
        $locations = $this->get_used_locations();

        $to_return = '';

        foreach ($locations as $location) {
            if ($location == 0) {
                continue;
            }
            $loc = Location::where('id', $location)->first();
            if (!$loc || !$loc->user) {
                continue;
            }
            $to_return .= "<a href='/location/" . e($loc->user->slug) . '/' . e($loc->slug) . "'>" .
                e(html_entity_decode($loc->name)) . '</a>' . ', ';
        }

        // Remove the trailing comma and space (if any)
        if ($to_return === '') {
            return '';
        }

        return substr($to_return, 0, -2);
    }

    /**
     * The locations this instrument was used at, for the map on the
     * instrument page: name, link, position and the number of observations
     * with the instrument there. Hidden locations are left out unless
     * $viewer owns them or is an administrator, as on the location page,
     * and so are locations without real coordinates.
     *
     * @return list<array{name: string, url: string, latitude: float, longitude: float, observations: int}>
     */
    public function usedLocationsForMap(?User $viewer): array
    {
        $counts = Observation::where('instrument_id', $this->id)
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->selectRaw('location_id, COUNT(*) as observations')
            ->pluck('observations', 'location_id');

        if ($counts->isEmpty()) {
            return [];
        }

        $isAdmin = (bool) $viewer?->isAdministrator();

        return Location::with('user:id,slug')
            ->whereIn('id', $counts->keys())
            ->orderBy('name')
            ->get()
            // getAttribute(): in a model, $location->hidden is Model::$hidden
            ->filter(fn (Location $location) => $location->user && $location->hasValidCoordinates()
                && (!$location->getAttribute('hidden') || $isAdmin || ($viewer && (int) $location->user_id === (int) $viewer->id)))
            ->map(fn (Location $location) => [
                'name' => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url' => '/location/'.$location->user->slug.'/'.$location->slug,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'observations' => (int) $counts[$location->id],
            ])
            ->values()
            ->all();
    }

    public function get_used_locations(): Collection
    {
        return Observation::where('instrument_id', $this->id)->groupby('location_id')->distinct()->pluck('location_id');
    }

    public function magnification(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        if ($lens) {
            return round($this->focal_length_mm * $lens->factor / ($eyepiece->focal_length_mm)) . 'x';
        }

        return round($this->focal_length_mm / $eyepiece->focal_length_mm) . 'x';
    }

    /**
     * Magnification with this equipment, rounded to a whole number, or null
     * when it cannot be determined. An instrument with a fixed magnification
     * (binoculars, finders) needs no eyepiece.
     */
    public function magnificationFor(?Eyepiece $eyepiece, ?Lens $lens = null): ?int
    {
        $factor = $lens && (float) $lens->factor > 0 ? (float) $lens->factor : 1.0;

        if ((float) $this->fixedMagnification > 0) {
            return (int) round($this->fixedMagnification * $factor);
        }

        if ($eyepiece && (float) $this->focal_length_mm > 0 && (float) $eyepiece->focal_length_mm > 0) {
            return (int) round($this->focal_length_mm * $factor / $eyepiece->focal_length_mm);
        }

        return null;
    }

    public function field_of_view(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        $tfov = $this->eyepieceFieldOfViewDeg($eyepiece, $lens);
        if ($tfov === null) {
            return __('Unknown');
        }

        // Round to whole minutes first, so that 59.6' gives 1° 00' and not 0° 60'
        $minutes = (int) round($tfov * 60);

        return sprintf("%d° %02d'", intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * The true field of view in degrees, or null when it cannot be
     * determined. An instrument with a fixed magnification (binoculars,
     * finders) has its own field: the one entered for it, else an estimate
     * from the magnification.
     */
    public function trueFieldOfViewDeg(?Eyepiece $eyepiece, ?Lens $lens = null): ?float
    {
        if ((float) $this->fixedMagnification > 0) {
            return $this->fixedFieldOfViewDeg();
        }

        return $eyepiece ? $this->eyepieceFieldOfViewDeg($eyepiece, $lens) : null;
    }

    /**
     * The true field of view in degrees of the eyepiece in this instrument:
     * from its field stop, else from its apparent field.
     */
    public function eyepieceFieldOfViewDeg(Eyepiece $eyepiece, ?Lens $lens = null): ?float
    {
        $focal_length = $this->effective_focal_length($lens);
        if ($focal_length <= 0) {
            return null;
        }

        if ($eyepiece->field_stop_mm > 0) {
            return $eyepiece->field_stop_mm / $focal_length * 57.2958;
        }

        if ($eyepiece->apparentFOV > 10 && $eyepiece->focal_length_mm > 0) {
            return $eyepiece->apparentFOV / ($focal_length / $eyepiece->focal_length_mm);
        }

        return null;
    }

    /**
     * The field of view of an instrument with a fixed magnification: the one
     * entered for it, else the field of an ordinary eyepiece of
     * FIXED_MAGNIFICATION_APPARENT_FOV degrees at that magnification.
     */
    public function fixedFieldOfViewDeg(): ?float
    {
        if ((float) $this->field_of_view_deg > 0) {
            return (float) $this->field_of_view_deg;
        }

        if ((float) $this->fixedMagnification > 0) {
            return self::FIXED_MAGNIFICATION_APPARENT_FOV / (float) $this->fixedMagnification;
        }

        return null;
    }

    /**
     * Whether the field of view of this fixed-magnification instrument is
     * estimated rather than entered.
     */
    public function hasEstimatedFieldOfView(): bool
    {
        return (float) $this->fixedMagnification > 0 && ! ((float) $this->field_of_view_deg > 0);
    }

    public function exit_pupil(Eyepiece $eyepiece, ?Lens $lens = null): string
    {
        $focal_length = $this->effective_focal_length($lens);
        if ($focal_length <= 0 || $eyepiece->focal_length_mm <= 0) {
            return __('Unknown');
        }

        return round($this->aperture_mm / ($focal_length / $eyepiece->focal_length_mm), 1) . 'mm';
    }

    /**
     * The focal length with the lens; a lens without a valid factor is ignored.
     */
    private function effective_focal_length(?Lens $lens): float
    {
        $focal_length = (float) $this->focal_length_mm;
        if ($lens && $lens->factor > 0) {
            $focal_length *= $lens->factor;
        }

        return $focal_length;
    }

    /**
     * The users column that holds this as the user's default.
     */
    public function standardUserColumn(): ?string
    {
        return 'stdtelescope';
    }

    /**
     * Many-to-many relationship to InstrumentSet
     */
    public function instrument_sets()
    {
        return $this->belongsToMany(InstrumentSet::class, 'instrument_set_instrument');
    }
}
