<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ObservationSession extends Model
{
    use Sluggable;

    protected $table = 'observation_sessions';

    public $timestamps = false;

    protected $fillable = [
        'id', 'name', 'slug', 'observerid', 'begindate', 'enddate', 'locationid', 'weather', 'equipment', 'comments', 'language', 'active',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    // Ensure slug uniqueness scoped to observerid
    public static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->name)) {
                $base = Str::slug($model->name);
                $slug = $base;
                $i = 2;
                while (self::where('slug', $slug)->where('observerid', $model->observerid)->exists()) {
                    $slug = $base.'-'.$i;
                    $i++;
                }
                $model->slug = $slug;
            }
        });
    }

    /**
     * Get other observers for this session from the sessionObservers pivot table.
     * Returns an array of username strings.
     */
    public function otherObservers()
    {
        return \DB::table('sessionObservers')
            ->where('sessionid', $this->id)
            ->pluck('observer')
            ->toArray();
    }

    /**
     * Relation to the User model for the primary observer.
     * observation_sessions.observerid stores the legacy username which maps to users.username
     */
    public function observer()
    {
        return $this->belongsTo(\App\Models\User::class, 'observerid', 'username');
    }

    /**
     * Scope to eager-load the observer relation.
     */
    public function scopeWithObserver($query)
    {
        return $query->with('observer');
    }

    /**
     * Return the number of distinct observers for this session.
     * Ensures the primary observer (observerid) is counted at least once.
     */
    public function otherObserversCount()
    {
        $names = \DB::table('sessionObservers')
            ->where('sessionid', $this->id)
            ->pluck('observer')
            ->toArray();

        $unique = array_values(array_unique($names));
        $count = count($unique);

        // If the primary observer (observerid) is not present in the pivot, include them.
        if (! empty($this->observerid) && ! in_array($this->observerid, $unique, true)) {
            $count++;
        }

        return max(1, $count);
    }
}
