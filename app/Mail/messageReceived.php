<?php
/**
 * Message Received mailable.
 *
 * PHP Version 7
 *
 * @category Messages
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Message Received mailable.
 *
 * @category Messages
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class messageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $author;
    public $id;
    public $participants;

    /**
     * Create a new message instance.
     *
     * @param string  $subject      The subject of the message.
     * @param string  $message      The content of the message.
     * @param string  $author       The author of the message.
     * @param int $id           The id of the thread.
     * @param string  $participants The participants of the thread.
     *
     * @return void
     */
    public function __construct($subject, $message, $author, $id, $participants)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->author = $author;
        $this->id = $id;
        $this->participants = $participants;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('message-received')->with(
            [
                'subject'=>$this->subject,
                'message'=>$this->message,
                'author'=>$this->author,
                'id'=>$this->id,
                'participants'=>$this->participants,
            ]
        );
    }
}
