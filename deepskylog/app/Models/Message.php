<?php

namespace App\Models;

use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

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
        return self::sanitizeHtml($this->subject);
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
     * Return the number of unread mails for a given receiver id (username).
     */
    public static function getNumberOfUnreadMails($id): int
    {
        $allMails = self::where(function ($q) use ($id) {
            $q->where('receiver', $id)
                ->orWhere('receiver', 'all');
        })->pluck('id');

        $deletedMails = MessageDeleted::where('receiver', $id)->pluck('id');
        $readMails = MessageRead::where('receiver', $id)->pluck('id');

        $remaining = $allMails->diff($deletedMails)->diff($readMails);

        return $remaining->count();
    }
}
