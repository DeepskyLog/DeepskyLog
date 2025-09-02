<?php

namespace App\Models;

use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Model;

class MessagesOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messages';

    protected $fillable = ['sender', 'receiver', 'subject', 'message', 'date'];

    /**
     * Return a human readable formatted date for the message.
     * Supports legacy Ymd numeric format and standard datetime strings.
     */
    public function getFormattedDateAttribute(): string
    {
        if (empty($this->date)) {
            return '';
        }

        try {
            // If the date looks like Ymd (e.g. 20250902)
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

    /**
     * Return the number of unread mails for a given receiver id (username).
     */
    public static function getNumberOfUnreadMails($id): int
    {
        // Include messages addressed to this user or broadcasts addressed to 'all'
        $allMails = MessagesOld::where(function ($q) use ($id) {
            $q->where('receiver', $id)
                ->orWhere('receiver', 'all');
        })->pluck('id');

        $deletedMails = MessagesDeletedOld::where('receiver', $id)->pluck('id');
        $readMails = MessagesReadOld::where('receiver', $id)->pluck('id');

        // Remove deleted and read ids from all mails
        $remaining = $allMails->diff($deletedMails)->diff($readMails);

        return $remaining->count();
    }

    /**
     * Sanitize HTML using HTMLPurifier and a conservative allowed set.
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        static $purifier = null;
        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            // Allow a safe set of elements and attributes
            $config->set('HTML.Allowed', 'a[href|title|target],b,strong,i,em,br,p,ul,ol,li,blockquote,code,pre,span,div,img[src|alt|title|width|height]');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            // Avoid filesystem cache issues on some hosts
            $config->set('Cache.DefinitionImpl', null);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }

    /**
     * Safe message accessor — returns sanitized HTML ready for raw output.
     */
    public function getSafeMessageAttribute(): string
    {
        return self::sanitizeHtml($this->message);
    }

    /**
     * Safe subject accessor — returns sanitized HTML for the subject.
     */
    public function getSafeSubjectAttribute(): string
    {
        return self::sanitizeHtml($this->subject);
    }

    /**
     * Return a safe HTML preview of the message with a character limit.
     * Keeps a small set of tags and preserves tag structure while truncating text content.
     */
    public function getSafePreviewAttribute(): string
    {
        // Start from sanitized HTML, then strip tags to plain text
        $purified = self::sanitizeHtml($this->message);

        // Convert to plain text: remove tags, decode entities, normalize whitespace
        $text = strip_tags($purified);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace (collapse multiple spaces/newlines)
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
     * Truncate HTML by character length while preserving tags.
     * This loads the sanitized HTML into a DOMDocument and walks nodes until the limit.
     */
    private static function truncateHtmlPreserveTags(string $html, int $limit = 100): string
    {
        if ($limit <= 0 || trim($html) === '') {
            return '';
        }

        // Wrap in a container so we always have a single root
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        // Provide proper encoding and avoid adding extra html/body tags when loading
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>');
        libxml_clear_errors();

        $container = $doc->getElementsByTagName('div')->item(0);
        $out = '';
        $length = 0;

        $voidElements = [
            'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr',
        ];

        $walk = function ($node) use (&$walk, &$out, &$length, $limit, $voidElements) {
            foreach ($node->childNodes as $child) {
                if ($length >= $limit) {
                    break;
                }

                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = $child->nodeValue;
                    $remaining = $limit - $length;
                    if (mb_strlen($text) > $remaining) {
                        $out .= htmlspecialchars(mb_substr($text, 0, $remaining), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $length += mb_strlen(mb_substr($text, 0, $remaining));
                    } else {
                        $out .= htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $length += mb_strlen($text);
                    }
                } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                    $tag = $child->nodeName;
                    $out .= '<'.$tag;
                    if ($child->hasAttributes()) {
                        foreach ($child->attributes as $attr) {
                            $out .= ' '.$attr->name.'="'.htmlspecialchars($attr->value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
                        }
                    }
                    $out .= '>';

                    // Void elements don't have children or closing tags
                    if (! in_array(strtolower($tag), $voidElements, true)) {
                        $walk($child);
                        $out .= '</'.$tag.'>';
                    }
                }
                if ($length >= $limit) {
                    break;
                }
            }
        };

        $walk($container);

        if ($length >= $limit) {
            $out .= '…';
        }

        return $out;
    }
}
