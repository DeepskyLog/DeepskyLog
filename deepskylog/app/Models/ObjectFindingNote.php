<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A finding note: a short tip of an observer on how to find a deep-sky
 * object ("use the UHC", "hop from ζ Ori"), keyed on objects.name like the
 * observing list items (ObjectMerger and the object edit page move them with
 * the object). Plain text on one line, printed escaped. Other observers like
 * them; the most liked come first, on the object page (Livewire
 * ObjectFindingNotes) and at the eyepiece.
 */
class ObjectFindingNote extends Model
{
    use ClearsResponseCache;

    /** The longest note, in characters. */
    public const MAX_LENGTH = 300;

    /** The notes one observer may write for one object. */
    public const MAX_PER_OBSERVER = 3;

    protected $fillable = ['object_name', 'user_id', 'text'];

    protected $casts = ['likes_count' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ObjectFindingNoteLike::class);
    }

    /**
     * The notes of an object, the most liked first, then the newest, with
     * their author and whether $viewer liked them (`liked`).
     */
    public function scopeForObject(Builder $query, string $objectName, ?User $viewer = null): Builder
    {
        return $query->where('object_name', $objectName)
            ->with('user:id,name,slug')
            ->when($viewer, fn (Builder $q) => $q->withExists(['likes as liked' => fn ($l) => $l->where('user_id', $viewer->id)]))
            ->orderByDesc('likes_count')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /** The text as stored: whitespace (also new lines) collapsed, without tags. */
    public static function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags($text)));
    }

    /**
     * A new note of $user for the object. Throws a ValidationException
     * (field `text`) for an empty or too long text, an object that is not
     * there, or an observer who already wrote MAX_PER_OBSERVER for it.
     */
    public static function add(User $user, string $objectName, string $text): self
    {
        $text = self::validText($text);

        if (! DeepskyObject::where('name', $objectName)->exists()) {
            throw ValidationException::withMessages(['text' => __('This object is not in DeepskyLog.')]);
        }
        if (self::where('object_name', $objectName)->where('user_id', $user->id)->count() >= self::MAX_PER_OBSERVER) {
            throw ValidationException::withMessages(['text' => __('You can write at most :count finding notes for an object.', ['count' => self::MAX_PER_OBSERVER])]);
        }

        return self::create(['object_name' => $objectName, 'user_id' => $user->id, 'text' => $text]);
    }

    /** A new text for the note. Throws a ValidationException like add(). */
    public function edit(string $text): void
    {
        $this->update(['text' => self::validText($text)]);
    }

    private static function validText(string $text): string
    {
        $text = self::clean($text);
        if ($text === '') {
            throw ValidationException::withMessages(['text' => __('Write a finding note first.')]);
        }
        if (mb_strlen($text) > self::MAX_LENGTH) {
            throw ValidationException::withMessages(['text' => __('A finding note has at most :count characters.', ['count' => self::MAX_LENGTH])]);
        }

        return $text;
    }

    /** The author edits a note. */
    public function canBeEditedBy(?User $user): bool
    {
        return $user !== null && $user->id === $this->user_id;
    }

    /** The author, administrators and database experts delete a note. */
    public function canBeDeletedBy(?User $user): bool
    {
        return $user !== null && ($user->id === $this->user_id || $user->hasModeratorRole());
    }

    /** Any other observer likes a note, never its author. */
    public function canBeLikedBy(?User $user): bool
    {
        return $user !== null && $user->id !== $this->user_id;
    }

    /**
     * Like the note, or take the like back. Returns whether $user likes it
     * now; likes_count follows.
     */
    public function toggleLike(User $user): bool
    {
        $liked = DB::transaction(function () use ($user) {
            $deleted = ObjectFindingNoteLike::where('object_finding_note_id', $this->id)->where('user_id', $user->id)->delete();
            if ($deleted === 0) {
                ObjectFindingNoteLike::firstOrCreate(['object_finding_note_id' => $this->id, 'user_id' => $user->id]);
            }

            $this->forceFill(['likes_count' => $this->likes()->count()])->save();

            return $deleted === 0;
        });

        return $liked;
    }

    /**
     * Remove the likes of an observer (whose account goes), with the counts
     * of the notes they liked.
     */
    public static function removeLikesOf(User $user): void
    {
        $noteIds = ObjectFindingNoteLike::where('user_id', $user->id)->pluck('object_finding_note_id');
        ObjectFindingNoteLike::where('user_id', $user->id)->delete();
        foreach (self::whereIn('id', $noteIds)->get() as $note) {
            $note->forceFill(['likes_count' => $note->likes()->count()])->save();
        }
    }

    /**
     * Move the notes of an object to another one (a renamed or merged
     * object).
     */
    public static function moveObject(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }
        self::where('object_name', $from)->update(['object_name' => $to]);
        self::clearResponseCache();
    }
}
