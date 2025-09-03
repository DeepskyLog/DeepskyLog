<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MessageReceived extends Mailable
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

    /**
     * Create a new message instance.
     */
    public function __construct(string $sender, ?string $subject, string $messageHtml, ?string $recipientName = null)
    {
        $this->sender = $sender;
        $this->subject = $subject;
        $this->messageHtml = $messageHtml;
        $this->recipientName = $recipientName;
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
            ]);
    }
}
