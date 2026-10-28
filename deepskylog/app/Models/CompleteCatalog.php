<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * A catalog whose objects are all in DeepskyLog (M, NGC, IC, ...). Observers
 * can't add an object to it, neither with the create wizard nor with an OAL
 * import; administrators and database experts can, and manage the list on
 * /admin/complete-catalogs.
 */
class CompleteCatalog extends Model
{
    private const CACHE_KEY = 'complete_catalogs';

    protected $fillable = ['catalog'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * The codes of the complete catalogs, sorted.
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->orderBy('catalog')->pluck('catalog')->all());
    }

    /**
     * The complete catalog an object name belongs to, or null: the name is the
     * catalog code followed by a space or directly by a digit ("NGC 891",
     * "NGC891"), ignoring case. The longest code wins, so "H400-II 5" is
     * H400-II and not H400.
     */
    public static function catalogOf(string $name): ?string
    {
        $name = trim($name);
        $codes = self::codes();
        usort($codes, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($code, '/') . '(\s+|(?=\d))\S/iu', $name)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * The complete catalog that keeps the user from adding an object with this
     * name, or null when they may add it (administrators and database experts
     * always may).
     */
    public static function refusedCatalog(string $name, ?User $user): ?string
    {
        if ($user && ($user->isAdministrator() || $user->isDatabaseExpert())) {
            return null;
        }

        return self::catalogOf($name);
    }
}
