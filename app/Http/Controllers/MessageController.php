<?php
/**
 * Messages Controller.
 *
 * PHP Version 7
 *
 * @category Messages
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Mail\MessageReceived;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

/**
 * Messages Controller.
 *
 * @category Messages
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class MessageController extends Controller
{
    /**
     * Make sure the message pages can only be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show all of the message threads to the user.
     *
     * @return mixed
     */
    public function index()
    {
        // All threads that user is participating in
        $allThreads = Thread::forUser(Auth::id())
            ->with(['participants', 'messages'])
            ->latest('updated_at')->get();

        // All threads that user is participating in, with new messages
        $newThreads = Thread::forUserWithNewMessages(
            Auth::id()
        )->with(['participants', 'messages'])->latest('updated_at')->get();

        $oldThreads = $allThreads->diff($newThreads);

        return view('messenger.index', compact('newThreads'), compact('oldThreads'));
    }

    /**
     * Shows a message thread.
     *
     * @param int $id the id of the thread to show
     *
     * @return mixed
     */
    public function show($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            laraflash(
                _i('The requested thread was not found.')
            )->danger();

            return redirect()->route('messages');
        }

        // show current user in list if not a current participant
        $allowedUsers = $thread->participantsUserIds();

        if (! in_array(Auth::id(), $allowedUsers)) {
            abort(403, _i('Not authorized to see this message.'));
        }

        // don't show the current user in list
        $userId = Auth::id();
        $users = User::whereNotIn(
            'id',
            $thread->participantsUserIds($userId)
        )->get();

        $thread->markAsRead($userId);

        return view('messenger.show', compact('thread', 'users'));
    }

    /**
     * Creates a new message thread.
     *
     * @return mixed
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();

        return view('messenger.create', compact('users'));
    }

    /**
     * Creates a new message thread to all observers.
     *
     * @return mixed
     */
    public function createAll()
    {
        if (auth()->user()->isAdmin()) {
            $users = 'All';

            return view('messenger.create', compact('users'));
        } else {
            abort(401);
        }
    }

    /**
     * Creates a new message thread.
     *
     * @param int $id The id to send the mail to
     *
     * @return mixed
     */
    public function createId($id)
    {
        $users = User::where('id', '!=', Auth::id())->get();

        return view('messenger.create', compact('users'), compact('id'));
    }

    /**
     * Stores a new message thread.
     *
     * @return mixed
     */
    public function store()
    {
        $input = Input::all();

        if (count($input['recipients']) == 1) {
            if ($input['recipients'][0] == 'All') {
                $users = User::where('id', '!=', Auth::id())->get();
                foreach ($users as $user) {
                    $thread = Thread::create(
                        [
                            'subject' => $input['subject'],
                        ]
                    );

                    // Message
                    Message::create(
                        [
                            'thread_id' => $thread->id,
                            'user_id' => Auth::id(),
                            'body' => $input['message'],
                        ]
                    );

                    // Sender
                    Participant::create(
                        [
                            'thread_id' => $thread->id,
                            'user_id' => Auth::id(),
                            'last_read' => new Carbon,
                        ]
                    );

                    // Recipients
                    $thread->addParticipant($user->id);

                    if ($user->sendMail) {
                        Mail::to($user->email)->send(
                            new MessageReceived(
                                $input['subject'],
                                $input['message'],
                                Auth::user()->name,
                                $thread->id,
                                $thread->participantsString()
                            )
                        );
                    }
                }

                return redirect()->route('messages');
            }
        }
        $thread = Thread::create(
            [
                'subject' => $input['subject'],
            ]
        );

        // Message
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id' => Auth::id(),
                'body' => $input['message'],
            ]
        );

        // Sender
        Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id' => Auth::id(),
                'last_read' => new Carbon,
            ]
        );

        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipant($input['recipients']);
        }

        // Only send mail if the user wants to receive mail
        foreach ($input['recipients'] as $userid) {
            $user = User::findOrFail($userid);
            if ($user->sendMail) {
                Mail::to($user->email)->send(
                    new MessageReceived(
                        $input['subject'],
                        $input['message'],
                        Auth::user()->name,
                        $thread->id,
                        $thread->participantsString()
                    )
                );
            }
        }

        return redirect()->route('messages');
    }

    /**
     * Adds a new message to a current thread.
     *
     * @param int $id the id of the thread to update
     *
     * @return mixed
     */
    public function update($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            laraflash(
                _i('The requested thread was not found.')
            )->danger();

            return redirect()->route('messages');
        }

        $thread->activateAllParticipants();

        // Message
        $message = Message::create(
            [
                'thread_id' => $thread->id,
                'user_id' => Auth::id(),
                'body' => Input::get('message'),
            ]
        );

        // Add replier as a participant
        $participant = Participant::firstOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id' => Auth::id(),
            ]
        );
        $participant->last_read = new Carbon;
        $participant->save();

        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipant(Input::get('recipients'));
        }

        // Only send mail if the user wants to receive mail
        foreach ($thread->participantsUserIds() as $userid) {
            if ($userid !== Auth::id()) {
                $user = User::findOrFail($userid);
                if ($user->sendMail) {
                    Mail::to($user->email)->send(
                        new MessageReceived(
                            $thread->subject,
                            $message->body,
                            Auth::user()->name,
                            $thread->id,
                            $thread->participantsString()
                        )
                    );
                }
            }
        }

        return redirect()->route('messages.show', $id);
    }
}
