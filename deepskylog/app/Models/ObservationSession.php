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
}
