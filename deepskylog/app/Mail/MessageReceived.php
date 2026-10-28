<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The notification mail of a DeepskyLog message. Queued, so a broadcast to
 * every observer doesn't keep the request waiting; send it with the locale of
 * the recipient (->locale($user->language)), as the worker doesn't know the
 * language of the request.
 */
class MessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var string */
    public $sender;

    /** @var string|null */
    public $subject;

    /** @var string */
    public $messageHtml;

    /** @var string|null */
    public $recipientName;

    /** @var string|null The page of the message on DeepskyLog. */
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct(string $sender, ?string $subject, string $messageHtml, ?string $recipientName = null, ?string $url = null)
    {
        $this->url = $url;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->messageHtml = self::absoluteLinks($messageHtml);
        $this->recipientName = $recipientName;
    }

    /**
     * Make the relative links of a message absolute, so they work in a mail
     * client. TinyMCE stored links relative to the page the message was
     * written on ('../observation/…' from /messages/create).
     */
    public static function absoluteLinks(string $html, ?string $base = null): string
    {
        $base ??= url('/messages/create');

        return preg_replace_callback(
            '/\b(href|src)=(["\'])(.*?)\2/i',
            fn (array $m) => $m[1].'='.$m[2].self::resolveUrl(html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5), $base, $m[3]).$m[2],
            $html
        ) ?? $html;
    }

    private static function resolveUrl(string $url, string $base, string $original): string
    {
        // Absolute (http:, mailto:, …), protocol-relative or a fragment: keep.
        if ($url === '' || preg_match('#^([a-z][a-z0-9+.-]*:|//|\#)#i', $url)) {
            return $original;
        }

        $parts = parse_url($base);
        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($url, '/')) {
            $path = $url;
        } else {
            $dir = preg_replace('#/[^/]*$#', '/', $parts['path'] ?? '/');
            $path = $dir.$url;
        }

        // Split off query and fragment, then remove the '.' and '..' segments.
        preg_match('#^([^?\#]*)(.*)$#s', $path, $split);
        $segments = [];
        foreach (explode('/', $split[1]) as $segment) {
            if ($segment === '..') {
                if (count($segments) > 1) {
                    array_pop($segments);
                }
            } elseif ($segment !== '.') {
                $segments[] = $segment;
            }
        }
        $path = implode('/', $segments);
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return e($origin.$path.$split[2]);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $sub = $this->subject ?: __('(no subject)');

        return $this->subject($sub)
            ->view('emails.message_received')
            ->with([
                'sender' => $this->sender,
                'subject' => $this->subject,
                'messageHtml' => $this->messageHtml,
                'recipientName' => $this->recipientName,
                'url' => $this->url ?? url('/messages'),
            ]);
    }
}
