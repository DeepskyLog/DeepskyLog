<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ObservationSession extends Model
{
    use ClearsResponseCache, Sluggable;

    protected $table = 'observation_sessions';

    public $timestamps = false;

    // Legacy table uses manual ids (not auto-increment). Ensure Eloquent doesn't
    // expect DB to generate ids and assign a new legacy id when creating.
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id', 'name', 'slug', 'observerid', 'begindate', 'enddate', 'locationid', 'weather', 'equipment', 'comments', 'language', 'active', 'picture',
    ];

    /**
     * Cast datetime columns to predictable format when accessing via Eloquent.
     * Using explicit format reduces implicit timezone conversions when attributes
     * are automatically converted to Carbon instances.
     */
    protected $casts = [
        'begindate' => 'datetime:Y-m-d H:i:s',
        'enddate' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Default model attributes.
     * Ensure new observation sessions default to English when not explicitly provided.
     */
    protected $attributes = [
        'language' => 'en',
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
            // Ensure a legacy numeric id exists. Use max(id)+1 as the next id.
            if (empty($model->id)) {
                try {
                    $max = self::max('id');
                    $model->id = $max ? ((int) $max + 1) : 1;
                } catch (\Throwable $e) {
                    // In rare cases (e.g., missing table) leave id empty and let DB report.
                }
            }

            // Ensure slug uniqueness scoped to observerid when name present
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
