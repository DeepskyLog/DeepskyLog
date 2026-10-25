<?php

namespace App\Mail;

use App\Models\StarPartyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The invitation to a starparty (#315) mailed to an address, with the link
 * that holds the token of the invitation. Queued; send it with the locale of
 * the observer who invited (->locale()), as the language of the receiver is
 * not known.
 */
class StarPartyInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public StarPartyInvitation $invitation)
    {
    }

    public function build()
    {
        $party   = $this->invitation->starParty;
        $inviter = $this->invitation->invitedBy;

        return $this->subject(__(':name invites you to the starparty :party', ['name' => $inviter?->name ?? 'DeepskyLog', 'party' => $party->name]))
            ->view('emails.star_party_invitation')
            ->with([
                'party'   => $party,
                'inviter' => $inviter,
                'url'     => route('star-parties.invitation', $this->invitation->token),
            ]);
    }
}
