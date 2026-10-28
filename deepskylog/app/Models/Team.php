<?php

namespace App\Models;

use Closure;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;
    use Sluggable;

    /**
     * Ids of the groups created by GroupSeeder. Roles are checked on these ids,
     * never on the team name, which a team owner can change.
     */
    public const OBSERVERS = 1;

    public const ADMINISTRATORS = 2;

    public const DATABASE_EXPERTS = 3;

    /**
     * Names of the groups, which no other team may take.
     *
     * @var array<int, string>
     */
    public const RESERVED_NAMES = ['Observers', 'Administrators', 'Database Experts'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Validation rule rejecting the names of the groups (case-insensitively).
     */
    public static function notReservedNameRule(?self $ignore = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($ignore) {
            $name = mb_strtolower(trim((string) $value));
            foreach (self::RESERVED_NAMES as $reserved) {
                if ($name === mb_strtolower($reserved) && ! ($ignore && $ignore->name === $reserved)) {
                    $fail(__('This team name is reserved.'));
                }
            }
        };
    }

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }
}
