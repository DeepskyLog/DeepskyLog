<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SketchOfTheWeek extends Model
{
    use ClearsResponseCache;

    public $timestamps = false;

    protected $with = ['observation', 'user'];

    protected $table = 'sketch_of_the_week';

    /**
     * The number of free Sundays offered when a sketch of the week is chosen.
     */
    public const SUNDAYS_OFFERED = 12;

    /**
     * Sketches of the week whose Sunday has come. A sketch chosen for a later
     * Sunday stays hidden until then.
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
     * The Sundays from today on that have no sketch of the week yet.
     *
     * @return array<int, Carbon>
     */
    public static function freeSundays(int $count = self::SUNDAYS_OFFERED): array
    {
        $today = Carbon::today();
        $taken = static::query()
            ->whereDate('date', '>=', $today->toDateString())
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $sunday = $today->isSunday() ? $today->copy() : $today->copy()->next(Carbon::SUNDAY);
        $sundays = [];
        while (count($sundays) < $count) {
            if (! $taken->has($sunday->toDateString())) {
                $sundays[] = $sunday->copy();
            }
            $sunday->addWeek();
        }

        return $sundays;
    }

    public static function isFreeSunday(Carbon $date): bool
    {
        return $date->isSunday()
            && $date->gte(Carbon::today())
            && ! static::query()->whereDate('date', $date->toDateString())->exists();
    }

    /**
     * Sketch links to the unified observations table using observations.id.
     */
    public function observation(): HasOne
    {
        return $this->hasOne(related: Observation::class, foreignKey: 'id', localKey: 'observation_id');
    }

    /**
     * Establishes a relationship between the SketchOfTheWeek model and the User model.
     *
     * This method defines a one-to-one relationship between the SketchOfTheWeek model and the User model.
     * The relationship is established based on the 'user_id' attribute of the SketchOfTheWeek model and the 'id' attribute of the User model.
     *
     * @return HasOne The relationship between the SketchOfTheWeek model and the User model.
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
