<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ObservationSession extends Model
{
    use ClearsResponseCache, Sluggable;

    /**
     * The usernames of sessionObservers, set by preloadOtherObservers().
     *
     * @var list<string>|null
     */
    private ?array $preloadedOtherObservers = null;

    protected $table = 'observation_sessions';

    public $timestamps = false;

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

    /**
     * The language a session written by the given user is in: the language
     * they write their observations in, as for observations. Descriptions are
     * translated from this language, so it must not simply default to 'en'.
     */
    public static function languageOf(?User $author): string
    {
        return $author?->observationlanguage ?: $author?->language ?: (string) config('app.locale', 'en');
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                // Names are stored with HTML entities ("M31 &amp; M32").
                'method' => fn (string $string, string $separator) => Str::slug(
                    html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $separator
                ),
            ],
        ];
    }

    /**
     * Sessions are addressed as /session/{user}/{slug}, so a slug only has to
     * be unique among the sessions of one observer.
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model, string $attribute, array $config, string $slug): Builder
    {
        return $query->where('observerid', $model->observerid);
    }

    public static function booted()
    {
        // The id is assigned by the database. The table is AUTO_INCREMENT, so
        // the id of a deleted session is never given to a new one (which would
        // then show the deleted session's photo and cached translations).
        static::saved(fn (ObservationSession $session) => $session->forgetCachedTranslations());
        static::deleted(fn (ObservationSession $session) => $session->forgetCachedTranslations());
    }

    /**
     * The owner may edit, adapt and delete a session; administrators only
     * when sessions.allow_admin_override is set.
     */
    public function canBeEditedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->username === $this->observerid) {
            return true;
        }

        return config('sessions.allow_admin_override', false) && $user->hasAdministratorPrivileges();
    }

    /**
     * Forget the cached translations of the weather, equipment and comments
     * and of the preview text on the session overviews.
     */
    public function forgetCachedTranslations(): void
    {
        $locales = array_unique(array_merge(
            ['en', 'nl', 'fr', 'de', 'es', 'sv'],
            (array) Cache::get('laravel_locales', [])
        ));

        foreach ($locales as $locale) {
            foreach (['session_weather', 'session_equipment', 'session_comments', 'session_preview'] as $prefix) {
                Cache::forget($prefix . ':' . $this->id . ':' . $locale);
            }
        }
    }

    /**
     * Get other observers for this session from the sessionObservers pivot table.
     * Returns an array of username strings.
     */
    public function otherObservers()
    {
        return $this->preloadedOtherObservers ?? \DB::table('sessionObservers')
            ->where('sessionid', $this->id)
            ->pluck('observer')
            ->toArray();
    }

    /**
     * Fetch the other observers (sessionObservers) of the sessions in one
     * query, for pages that list sessions with their number of observers;
     * otherObservers() and otherObserversCount() then query nothing.
     *
     * @param  iterable<ObservationSession>  $sessions
     */
    public static function preloadOtherObservers(iterable $sessions): void
    {
        $sessions = collect($sessions)->filter(fn ($session) => $session instanceof self);
        $ids = $sessions->pluck('id')->filter()->unique()->values()->all();
        if ($ids === []) {
            return;
        }

        $observers = \DB::table('sessionObservers')
            ->whereIn('sessionid', $ids)
            ->get(['sessionid', 'observer'])
            ->groupBy('sessionid');

        foreach ($sessions as $session) {
            $session->preloadedOtherObservers = $observers->get($session->id, collect())->pluck('observer')->all();
        }
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
        $names = $this->otherObservers();

        $unique = array_values(array_unique($names));
        $count = count($unique);

        // If the primary observer (observerid) is not present in the pivot, include them.
        if (! empty($this->observerid) && ! in_array($this->observerid, $unique, true)) {
            $count++;
        }

        return max(1, $count);
    }

    /**
     * Inactive (draft) sessions are only shown to their owner, so changes on
     * them do not clear the response cache.
     */
    protected function affectsCachedPages(): bool
    {
        return (int) $this->active === 1;
    }
}
