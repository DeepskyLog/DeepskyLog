<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SketchOfTheMonth extends Model
{
    use ClearsResponseCache;

    public $timestamps = false;

    protected $with = ['observation', 'user'];

    protected $table = 'sketch_of_the_month';

    /**
     * The sketch of the month is shown from this day of the month on.
     */
    public const DAY = 8;

    /**
     * The number of free months offered when a sketch of the month is chosen.
     */
    public const MONTHS_OFFERED = 6;

    /**
     * Sketches of the month whose day has come. A sketch chosen for a later
     * month stays hidden until then.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereDate('date', '<=', Carbon::today()->toDateString());
    }

    public function isPublished(): bool
    {
        return $this->date !== null && $this->date->lte(Carbon::today());
    }

    /**
     * The 8ths from today on of the months that have no sketch of the month yet.
     *
     * @return array<int, Carbon>
     */
    public static function freeDates(int $count = self::MONTHS_OFFERED): array
    {
        $today = Carbon::today();
        $date = $today->copy()->startOfMonth()->day(self::DAY);
        if ($date->lt($today)) {
            $date->addMonthNoOverflow();
        }

        $taken = static::query()
            ->whereDate('date', '>=', $date->copy()->startOfMonth()->toDateString())
            ->pluck('date')
            ->map(fn ($taken) => Carbon::parse($taken)->format('Y-m'))
            ->flip();

        $dates = [];
        while (count($dates) < $count) {
            if (! $taken->has($date->format('Y-m'))) {
                $dates[] = $date->copy();
            }
            $date->addMonthNoOverflow();
        }

        return $dates;
    }

    public static function isFreeDate(Carbon $date): bool
    {
        return $date->day === self::DAY
            && $date->gte(Carbon::today())
            && ! static::query()
                ->whereDate('date', '>=', $date->copy()->startOfMonth()->toDateString())
                ->whereDate('date', '<=', $date->copy()->endOfMonth()->toDateString())
                ->exists();
    }

    /**
     * The sketches of the week of the month before the given date, which are
     * the sketches that can become the sketch of the month on that date.
     *
     * @return Collection<int, SketchOfTheWeek>
     */
    public static function candidates(Carbon $date): Collection
    {
        $month = $date->copy()->startOfMonth()->subMonthNoOverflow();

        return SketchOfTheWeek::query()
            ->whereDate('date', '>=', $month->toDateString())
            ->whereDate('date', '<=', $month->copy()->endOfMonth()->toDateString())
            ->whereNotIn('observation_id', static::query()->select('observation_id'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Sketch links to the unified observations table using observations.id.
     */
    public function observation(): HasOne
    {
        return $this->hasOne(related: Observation::class, foreignKey: 'id', localKey: 'observation_id');
    }

    /**
     * Establishes a relationship between the SketchOfTheMonth model and the User model.
     *
     * This method defines a one-to-one relationship between the SketchOfTheMonth model and the User model.
     * The relationship is established based on the 'user_id' attribute of the SketchOfTheMonth model and the 'id' attribute of the User model.
     *
     * @return HasOne The relationship between the SketchOfTheMonth model and the User model.
     */
    public function user(): HasOne
    {
        return $this->hasOne(related: User::class, foreignKey: 'id', localKey: 'user_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
