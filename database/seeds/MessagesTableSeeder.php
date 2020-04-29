<?php
/**
 * Seeder for the Messages table of the database.
 * Fills the database with random lenses.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\MessagesOld;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Database\Seeder;

/**
 * Seeder for the messages table of the database.
 * Fills the database with the messages of the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return None
     */
    public function run()
    {
        $messagesData = MessagesOld::all();

        foreach ($messagesData as $message) {
            if ($message->receiver != 'all'
                && $message->sender != 'DeepskyLog'
                && $message->sender != 'admin'
            ) {
                if (strpos($message->subject, 'sessi') === false) {
                    if (strpos($message->subject, 'Re :') === false) {
                        if (strpos($message->subject, 'Re:') === false) {
                            // Get all the messages from the thread.
                            $thread = DB::connection('mysqlOld')->table('messages')
                                ->where('subject', 'Re : '.$message->subject)
                                ->where('sender', $message->receiver)
                                ->where('receiver', $message->sender)->get();
                            $thread2 = DB::connection('mysqlOld')->table('messages')
                                ->where('subject', 'Re : '.$message->subject)
                                ->where('sender', $message->sender)
                                ->where('receiver', $message->receiver)->get();

                            $ids = [];
                            $ids[] = $message->id;
                            $ids = array_merge(
                                $ids, $thread->pluck('id')->toarray()
                            );
                            $ids = array_merge(
                                $ids, $thread2->pluck('id')->toarray()
                            );
                            asort($ids);

                            // Create the thread
                            $thread = Thread::create(
                                [
                                    'subject' => html_entity_decode($message->subject),
                                    'created_at' => $message->date,
                                ]
                            );

                            foreach ($ids as $id) {
                                $messageToAdd = DB::connection('mysqlOld')->table('messages')
                                    ->where('id', $id)->get()->first();

                                // Get the correct user ids.
                                if ($messageToAdd->sender == 'vvs04478Admin') {
                                    $sender = 'vvs04478';
                                } elseif ($messageToAdd->sender == 'evdjadmin') {
                                    $sender = 'Eric VdJ';
                                } elseif ($messageToAdd->sender == 'TomC_developer') {
                                    $sender = 'vvs03296';
                                } elseif ($messageToAdd->sender == 'wvreeven-admin') {
                                    $sender = 'wvreeven';
                                } elseif ($messageToAdd->sender == 'Jef Admin') {
                                    $sender = 'Jef De Wit';
                                } elseif ($messageToAdd->sender == 'adminbob') {
                                    $sender = 'Bob Hogeveen';
                                } else {
                                    $sender = $messageToAdd->sender;
                                }

                                $senderid = DB::table('users')
                                    ->where('username', $sender)->value('id');

                                if ($messageToAdd->receiver == 'vvs04478Admin') {
                                    $receiver = 'vvs04478';
                                } elseif ($messageToAdd->receiver == 'evdjadmin') {
                                    $receiver = 'Eric VdJ';
                                } elseif ($messageToAdd->receiver == 'TomC_developer') {
                                    $receiver = 'vvs03296';
                                } elseif ($messageToAdd->receiver == 'wvreeven-admin') {
                                    $receiver = 'wvreeven';
                                } elseif ($messageToAdd->receiver == 'Jef Admin') {
                                    $receiver = 'Jef De Wit';
                                } elseif ($messageToAdd->receiver == 'adminbob') {
                                    $receiver = 'Bob Hogeveen';
                                } else {
                                    $receiver = $messageToAdd->receiver;
                                }

                                $receiverid = DB::table('users')
                                    ->where('username', $receiver)->value('id');

                                if ($receiverid == '' || $senderid == '') {
                                    continue;
                                }

                                $breaks = ['<br />', '<br>', '<br/>'];

                                Message::create(
                                    [
                                        'thread_id' => $thread->id,
                                        'user_id' => $senderid,
                                        'body' => str_ireplace(
                                            $breaks, '',
                                            html_entity_decode(
                                                $messageToAdd->message
                                            )
                                        ),
                                        'created_at' => $messageToAdd->date,
                                    ]
                                );

                                // Sender
                                $participant = Participant::firstOrCreate(
                                    [
                                        'thread_id' => $thread->id,
                                        'user_id' => $senderid,
                                    ]
                                );

                                $participant->last_read = new Carbon;
                                $participant->save();

                                // Recipients
                                $thread->addParticipant($receiverid);

                                $thread->markAsRead($receiverid);
                                $thread->markAsRead($senderid);
                            }
                        }
                    }
                }
            }
        }
    }
}
