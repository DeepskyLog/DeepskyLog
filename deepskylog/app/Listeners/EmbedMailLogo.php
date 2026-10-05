<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

/**
 * Embeds the DeepskyLog logo of the mail header in the mail itself (as a CID
 * attachment) instead of linking to it: a mail client then shows it whatever
 * server sent the mail, also when that server is not reachable from outside
 * (a development machine on http://localhost).
 */
class EmbedMailLogo
{
    public const CID = 'deepskylog-logo';

    public function handle(MessageSending $event): void
    {
        $email = $event->message;
        $html = $email->getHtmlBody();

        if (! is_string($html) || ! str_contains($html, '/images/logo-mail.png')) {
            return;
        }

        $html = preg_replace('#src="[^"]*/images/logo-mail\.png"#', 'src="cid:'.self::CID.'"', $html);

        $email->html($html)->embedFromPath(public_path('images/logo-mail.png'), self::CID, 'image/png');
    }
}
