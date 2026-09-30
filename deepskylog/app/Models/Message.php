<?php

namespace App\Models;

use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    protected $table = 'messages';

    private const UNREAD_VERSION_KEY = 'unread_mails_version';

    protected $fillable = ['id', 'sender', 'receiver', 'subject', 'message', 'date'];

    // allow mass-assignment of id during seeding
    public $incrementing = true;

    /**
     * Return a human readable formatted date for the message.
     */
    public function getFormattedDateAttribute(): string
    {
        if (empty($this->date)) {
            return '';
        }

        try {
            if (preg_match('/^\d{8}$/', (string) $this->date)) {
                $dt = Carbon::createFromFormat('Ymd', $this->date);
            } else {
                $dt = new Carbon($this->date);
            }

            return $dt->locale(app()->getLocale())->isoFormat('LLL');
        } catch (\Throwable $e) {
            return (string) $this->date;
        }
    }

    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        static $purifier = null;
        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed',
                'a[href|title|target],b,strong,i,em,br,'.
                'p[style|align|class],div[style|align|class],span[style|class],'.
                'ul[style|class],ol[style|class],li[style|class],blockquote,code,pre,'.
                'img[src|alt|title|width|height]'
            );
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('CSS.AllowedProperties', ['text-align', 'margin-left', 'margin', 'padding-left', 'text-indent', 'list-style-type']);
            $config->set('Cache.DefinitionImpl', null);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }

    public function getSafeMessageAttribute(): string
    {
        return self::sanitizeHtml($this->message);
    }

    public function getSafeSubjectAttribute(): string
    {
        // Purify the subject (allow minimal inline formatting if present),
        // then decode any HTML entities so titles containing sequences like
        // '&amp;' or '&eacute;' render as the actual character in the UI.
        $purified = self::sanitizeHtml($this->subject);
        return html_entity_decode($purified, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getSafePreviewAttribute(): string
    {
        $purified = self::sanitizeHtml($this->message);
        $text = strip_tags($purified);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text));

        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) > 100) {
            return mb_substr($text, 0, 100).'…';
        }

        return $text;
    }

    /**
     * Remove the existing broadcast messages (receiver 'all') from a new
     * user's inbox, the same way "delete all" removes them, so a new account
     * does not start with hundreds of old unread messages. Broadcasts sent
     * after the account was created still reach the user.
     */
    public static function dismissExistingBroadcastsFor(string $username): void
    {
        DB::table('messages_deleted')->insertOrIgnoreUsing(
            ['id', 'receiver', 'deleted_at'],
            self::query()
                ->where('receiver', 'all')
                ->select('id')
                ->selectRaw('? as receiver', [$username])
                ->selectRaw('? as deleted_at', [now()])
                ->toBase()
        );
        self::forgetUnreadCount($username);
    }

    /**
     * Return the number of unread mails for a given receiver id (username).
     *
     * Shown as the badge in the menu of every logged-in page, so it is cached
     * per user. The cached count is dropped when a message to the user is
     * sent, read or deleted (forgetUnreadCount()); a broadcast changes the
     * count of everybody, so it moves the version all cached counts carry
     * instead. Both are read in one cache lookup.
     */
    public static function getNumberOfUnreadMails($id): int
    {
        $userKey = 'unread_mails:'.$id;
        $cached = Cache::many([self::UNREAD_VERSION_KEY, $userKey]);
        $version = (string) ($cached[self::UNREAD_VERSION_KEY] ?? '');
        $entry = $cached[$userKey];

        if (is_array($entry) && ($entry['version'] ?? null) === $version) {
            return (int) $entry['count'];
        }

        $count = self::countUnreadMails((string) $id);
        Cache::put($userKey, ['version' => $version, 'count' => $count], now()->addMinutes(10));

        return $count;
    }

    /**
     * Drop the cached unread count of a user, after one of their messages was
     * sent, read or deleted.
     */
    public static function forgetUnreadCount(string $username): void
    {
        Cache::forget('unread_mails:'.$username);
    }

    private static function countUnreadMails(string $username): int
    {
        // Broadcasts count for everybody except the administrator who sent them.
        return self::query()
            ->where(function ($q) use ($username) {
                $q->where('receiver', $username)
                    ->orWhere(fn ($q) => $q->where('receiver', 'all')->where('sender', '<>', $username));
            })
            ->whereNotExists(fn ($q) => $q->from('messages_deleted')
                ->whereColumn('messages_deleted.id', 'messages.id')
                ->where('messages_deleted.receiver', $username))
            ->whereNotExists(fn ($q) => $q->from('messages_read')
                ->whereColumn('messages_read.id', 'messages.id')
                ->where('messages_read.receiver', $username))
            ->count();
    }

    protected static function booted(): void
    {
        $changed = function (Message $message) {
            if (strtolower((string) $message->receiver) === 'all') {
                Cache::forever(self::UNREAD_VERSION_KEY, (string) hrtime(true));
            } else {
                self::forgetUnreadCount((string) $message->receiver);
            }
        };
        static::created($changed);
        static::deleted($changed);
    }
}
