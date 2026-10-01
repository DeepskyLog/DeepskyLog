<?php

namespace App\Mail;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an observer that their account will be deleted on the given date,
 * because they recorded nothing in DeepskyLog (see users:prune-inactive).
 */
class InactiveAccountWarning extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public CarbonInterface $deletionDate) {}

    public function build()
    {
        return $this->subject(__('Your DeepskyLog account will be deleted on :date', ['date' => $this->deletionDate->translatedFormat('j F Y')]))
            ->view('emails.inactive_account_warning');
    }
}
