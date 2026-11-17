<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * An observing list an observer is working on ("Working on"): their own list
 * or a public list they subscribed to, at most MAX_PER_USER. Only for the
 * observer: it counts for nothing else (not the popularity of a list).
 * The observer can add a note of their own (plain text, at most NOTE_LENGTH):
 * what the list is for, which instrument, which sky. Next to those, one
 * general note on what they are working on ("sketch every Messier object"),
 * the user_preferences row GENERAL_NOTE_PREFERENCE.
 */
class ObservingListPin extends Model
{
    public const MAX_PER_USER = 20;

    public const NOTE_LENGTH = 500;

    public const GENERAL_NOTE_PREFERENCE = 'working_on_note';

    public const GENERAL_NOTE_LENGTH = 2000;

    protected $fillable = [
        'user_id',
        'observing_list_id',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }

    /**
     * The ids of the lists the observer is working on, most recently marked first.
     *
     * @return array<int, int>
     */
    public static function idsFor(?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return self::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('observing_list_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Marks the list as worked on. False when the observer already works on
     * MAX_PER_USER lists; true when it is (or already was) marked.
     */
    public static function pin(User $user, ObservingList $list): bool
    {
        if (self::where('user_id', $user->id)->where('observing_list_id', $list->id)->exists()) {
            return true;
        }
        if (self::where('user_id', $user->id)->count() >= self::MAX_PER_USER) {
            return false;
        }
        self::create(['user_id' => $user->id, 'observing_list_id' => $list->id]);

        return true;
    }

    /**
     * The notes of the lists the observer is working on, keyed on the list id
     * (only the lists with a note).
     *
     * @return array<int, string>
     */
    public static function notesFor(?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return self::where('user_id', $userId)
            ->whereNotNull('note')
            ->pluck('note', 'observing_list_id')
            ->mapWithKeys(fn ($note, $id) => [(int) $id => (string) $note])
            ->all();
    }

    /**
     * Sets the note of a list the observer is working on (empty removes it).
     * False when the list is not one they are working on.
     */
    public static function setNote(User $user, ObservingList $list, ?string $note): bool
    {
        $note = trim(str_replace(["\r\n", "\r"], "\n", (string) $note));

        return self::where('user_id', $user->id)
            ->where('observing_list_id', $list->id)
            ->update(['note' => $note === '' ? null : $note, 'updated_at' => now()]) > 0;
    }

    /** The observer's general note on what they are working on, null when none. */
    public static function generalNoteFor(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }
        $note = DB::table('user_preferences')->where('user_id', $userId)->where('key', self::GENERAL_NOTE_PREFERENCE)->value('value');

        return $note === null || $note === '' ? null : (string) $note;
    }

    /** Sets the observer's general note (empty removes it). */
    public static function setGeneralNote(User $user, ?string $note): void
    {
        $note = trim(str_replace(["\r\n", "\r"], "\n", (string) $note));
        $query = DB::table('user_preferences')->where('user_id', $user->id)->where('key', self::GENERAL_NOTE_PREFERENCE);
        if ($note === '') {
            $query->delete();

            return;
        }
        DB::table('user_preferences')->upsert(
            [['user_id' => $user->id, 'key' => self::GENERAL_NOTE_PREFERENCE, 'value' => $note, 'created_at' => now(), 'updated_at' => now()]],
            ['user_id', 'key'],
            ['value', 'updated_at']
        );
    }

    public static function unpin(User $user, ObservingList $list): void
    {
        self::where('user_id', $user->id)->where('observing_list_id', $list->id)->delete();
    }

    /**
     * Puts the options of the lists the observer is working on first, keeping
     * the order of the others (options keyed on the list id, or on a key that
     * $idOf turns into one).
     *
     * @param  array<int|string, mixed>  $options
     * @return array<int|string, mixed>
     */
    public static function first(array $options, ?int $userId, ?callable $idOf = null): array
    {
        $pinned = array_flip(self::idsFor($userId));
        if ($pinned === []) {
            return $options;
        }
        $idOf ??= fn ($key) => (int) $key;

        $first = [];
        $rest = [];
        foreach ($options as $key => $value) {
            if (isset($pinned[$idOf($key, $value)])) {
                $first[$key] = $value;
            } else {
                $rest[$key] = $value;
            }
        }

        return $first + $rest;
    }
}
