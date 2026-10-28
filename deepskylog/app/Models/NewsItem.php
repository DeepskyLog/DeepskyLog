<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use App\Services\News\EventTexts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An item of the news on the home page and /news (#960): written by an
 * administrator or database expert (kind 'manual'), or made by
 * news:astronomical-events for an eclipse, conjunction, ... (the kind of the
 * event, with its key and data), whose title and text are written when shown,
 * in the reader's language and for the reader's location (EventTexts).
 *
 * An item is shown from published_at until expires_at (forever when null),
 * unless it is hidden; /news keeps the expired ones.
 */
class NewsItem extends Model
{
    public const MANUAL = 'manual';

    protected $fillable = [
        'kind', 'event_key', 'event_at', 'data', 'title', 'body', 'language', 'translations',
        'user_id', 'hidden', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'event_at'     => 'datetime',
        'data'         => 'array',
        'body'         => PurifiedHtml::class,
        'translations' => 'array',
        'hidden'       => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isAutomatic(): bool
    {
        return $this->kind !== self::MANUAL;
    }

    /** The items that are published and not hidden, expired or not. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('hidden', false)->where('published_at', '<=', now());
    }

    /** The published items that have not expired: those of the home page. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->published()->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * The title in the reader's language: the event's for an automatic item,
     * else the translation into the language of the site when there is one.
     */
    public function displayTitle(?Location $location = null): string
    {
        if ($this->isAutomatic()) {
            return EventTexts::title($this, $location);
        }

        return (string) ($this->translation()['title'] ?? $this->title);
    }

    /**
     * The text (HTML) of a written item in the reader's language; automatic
     * items are drawn by the news/events views.
     */
    public function displayBody(): string
    {
        return (string) ($this->translation()['body'] ?? $this->body);
    }

    /** Whether the title and text shown are Google's translation. */
    public function isTranslated(): bool
    {
        return ! $this->isAutomatic() && $this->translation() !== null;
    }

    /** The text without markup, shortened, for the home page and the feed. */
    public function excerpt(int $length = 220, ?Location $location = null): string
    {
        $text = $this->isAutomatic()
            ? EventTexts::summary($this, $location)
            : html_entity_decode(strip_tags($this->displayBody()), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), $length);
    }

    /** The moment the item is about: the event, else its publication. */
    public function date(): \Carbon\Carbon
    {
        return $this->event_at ?? $this->published_at;
    }

    /**
     * @return array{title: string, body: string}|null
     */
    private function translation(): ?array
    {
        $locale = app()->getLocale();
        if ($this->isAutomatic() || $locale === $this->language) {
            return null;
        }
        $translation = $this->translations[$locale] ?? null;

        return is_array($translation) && ! empty($translation['body']) ? $translation : null;
    }
}
