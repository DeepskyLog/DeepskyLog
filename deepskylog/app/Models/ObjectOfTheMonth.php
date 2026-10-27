<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An object of the month of Astroforum: one thread of
 * https://www.astroforum.nl/forums/objecten-van-de-maand.49/, about the
 * months from starts_on to ends_on, with the objects proposed from its first
 * post (candidates, best first; unresolved: designations DeepskyLog does not
 * know) and the objects an administrator or database expert confirmed
 * (objects). Each object is an array with name, table, slug, type and key
 * (observations.target_type and target_key) and object_type.
 *
 * Shown once confirmed (confirmed_at) and not hidden, with everyone's
 * observations of its objects in its months.
 */
class ObjectOfTheMonth extends Model
{
    /** Days after its months a thread is still shown on the home page, as threads are often posted late. */
    public const HOMEPAGE_GRACE_DAYS = 31;

    protected $table = 'objects_of_the_month';

    protected $fillable = [
        'thread_id', 'url', 'title', 'starts_on', 'ends_on', 'candidates', 'unresolved', 'objects',
        'hidden', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'candidates' => 'array',
        'unresolved' => 'array',
        'objects' => 'array',
        'hidden' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /** The threads whose objects are confirmed and that are not hidden. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->where('hidden', false);
    }

    /**
     * The object of the month of the home page: the published thread of the
     * latest months that started, until HOMEPAGE_GRACE_DAYS after them.
     */
    public static function current(): ?self
    {
        return self::published()
            ->whereDate('starts_on', '<=', today())
            ->whereDate('ends_on', '>=', today()->subDays(self::HOMEPAGE_GRACE_DAYS))
            ->orderByDesc('starts_on')
            ->orderByDesc('thread_id')
            ->first();
    }

    public function isPublished(): bool
    {
        return $this->confirmed_at !== null && ! $this->hidden;
    }

    /**
     * The objects proposed to the administrators when they confirm: those
     * the title names, else all.
     *
     * @return list<string> the keys of key()
     */
    public function proposedKeys(): array
    {
        $candidates = collect($this->candidates ?? []);
        $inTitle = $candidates->filter(fn (array $object) => ! empty($object['in_title']));

        return ($inTitle->isNotEmpty() ? $inTitle : $candidates)->map(fn (array $object) => self::key($object))->values()->all();
    }

    /** The key of an object in the candidates and objects. */
    public static function key(array $object): string
    {
        return $object['type'].':'.$object['key'];
    }

    /**
     * The months, in the language of the request: "October 2026",
     * "July – August 2026".
     */
    public function periodLabel(): string
    {
        $format = fn (Carbon $date, string $pattern) => $date->copy()->locale(app()->getLocale())->isoFormat($pattern);
        if ($this->starts_on->isSameMonth($this->ends_on)) {
            return $format($this->starts_on, 'MMMM YYYY');
        }

        return $this->starts_on->year === $this->ends_on->year
            ? $format($this->starts_on, 'MMMM').' – '.$format($this->ends_on, 'MMMM YYYY')
            : $format($this->starts_on, 'MMMM YYYY').' – '.$format($this->ends_on, 'MMMM YYYY');
    }

    /** The title without the "Object van de Maand <month>" every thread starts with. */
    public function subtitle(): string
    {
        $subtitle = preg_replace('/^\s*object(?:en)?\s+van\s+de\s+maand\b[^:–\-]*[:–\-]?\s*/iu', '', $this->title);

        return trim((string) $subtitle) !== '' ? trim((string) $subtitle) : $this->title;
    }

    /**
     * Everyone's observations of the objects in the months, newest first.
     */
    public function observationsQuery(): Builder
    {
        $objects = collect($this->objects ?? [])->groupBy('type');

        return Observation::query()
            ->whereBetween('observations.observed_on', [$this->starts_on->toDateString(), $this->ends_on->toDateString()])
            ->where(function (Builder $query) use ($objects) {
                $query->whereRaw('1 = 0');
                foreach ($objects as $type => $ofType) {
                    $query->orWhere(fn (Builder $q) => $q->where('observations.target_type', $type)
                        ->whereIn('observations.target_key', $ofType->pluck('key')->all()));
                }
            })
            ->orderByDesc('observations.observed_on')
            ->orderByDesc('observations.id');
    }

    /**
     * Per object the number of observations, observers and drawings in the
     * months, keyed by key().
     *
     * @return array<string, array{observations: int, observers: int, drawings: int}>
     */
    public function counts(): array
    {
        $rows = $this->observationsQuery()
            ->reorder()
            ->toBase()
            ->selectRaw('target_type, target_key, COUNT(*) as observations, COUNT(DISTINCT observer_username) as observers, SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings')
            ->groupBy('target_type', 'target_key')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row->target_type.':'.mb_strtolower((string) $row->target_key)] = [
                'observations' => (int) $row->observations,
                'observers' => (int) $row->observers,
                'drawings' => (int) $row->drawings,
            ];
        }

        $result = [];
        foreach ($this->objects ?? [] as $object) {
            $result[self::key($object)] = $counts[$object['type'].':'.mb_strtolower($object['key'])]
                ?? ['observations' => 0, 'observers' => 0, 'drawings' => 0];
        }

        return $result;
    }

    /** The number of observers who observed one of the objects in the months. */
    public function observerCount(): int
    {
        return (int) $this->observationsQuery()->reorder()->toBase()->selectRaw('COUNT(DISTINCT observer_username) as aggregate')->value('aggregate');
    }

    /** The names to put the objects on an observing list under (objects.name, else the search index name). */
    public function listNames(): array
    {
        return collect($this->objects ?? [])->pluck('name')->all();
    }
}
