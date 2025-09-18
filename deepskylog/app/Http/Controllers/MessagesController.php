<?php

namespace App\Http\Controllers;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageDeleted;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MessagesController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
    /** @var \App\Models\User $user */
    $user = Auth::user();
        // Exclude deleted messages
        $deleted = MessageDeleted::where('receiver', $user->username)->pluck('id')->toArray();

        // Read message ids for marking
        $read = MessageRead::where('receiver', $user->username)->pluck('id');

        // Allow sorting only on specific logical columns mapped to real DB columns
        $allowed = [
            'from' => 'sender',
            'to' => 'receiver',
            'subject' => 'subject',
            'date' => 'date',
        ];

        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $column = $allowed[$sort] ?? 'date';

    // If sorting by 'from', order by the users.name (full name) using a left join.
        // messages lives on the 'mysqlOld' connection; the users table is on the default connection.
        // Build two sets: received/broadcast messages, and grouped sent messages.
        // We'll merge them in PHP and paginate manually so sent messages that were
        // duplicated for multiple recipients show only once (as a 'group').

        // Received and broadcast messages (exclude messages where the user is the sender to avoid duplicates)
        $receivedQuery = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->where('sender', '!=', $user->username);

        // Load these into a collection
        $received = $receivedQuery->get()->map(function ($m) {
            // mark as not a grouped sent row
            $m->_is_group = false;
            $m->_group_count = 1;
            return $m;
        });

        // Grouped sent messages: group by sender, subject, message, date and pick a representative id
        $sentGroups = Message::where('sender', $user->username)->whereNotIn('id', $deleted)
            ->selectRaw('MIN(id) as id, sender, subject, message, date, COUNT(*) as recipients')
            ->groupBy('sender', 'subject', 'message', 'date')
            ->get()
            ->map(function ($g) {
                // Load the representative Message model so view accessors (safe_subject, safe_preview, formatted_date)
                // are available. Then mark it as a grouped item and override receiver for display.
                $rep = Message::find($g->id);
                if ($rep) {
                    $rep->_is_group = true;
                    $rep->_group_count = (int) ($g->recipients ?? 1);
                    // If this grouped sent message actually targets multiple recipients
                    // then display it as broadcast-like (To: All). For single-recipient
                    // groups, keep the real receiver so the "To" column remains accurate.
                    if (($g->recipients ?? 1) > 1 || ($rep->receiver ?? '') === 'all') {
                        $rep->receiver = 'all';
                    }
                    return $rep;
                }

                // Fallback: create a lightweight object with necessary properties
                $obj = new \stdClass();
                $obj->id = $g->id;
                $obj->sender = $g->sender;
                $obj->receiver = 'all';
                $obj->subject = $g->subject;
                $obj->message = $g->message;
                $obj->date = $g->date;
                $obj->_is_group = true;
                $obj->_group_count = (int) ($g->recipients ?? 1);
                // Provide minimal safe_*/formatted_date fallbacks
                $obj->safe_subject = $g->subject;
                $obj->safe_preview = strip_tags($g->message);
                $obj->formatted_date = $g->date;
                $obj->safe_message = $g->message;
                return $obj;
            });

        // Merge collections
        $all = $received->concat($sentGroups);

        // Eager-fetch user models (senders and receivers) so we can sort by full name
        $usernames = $all->pluck('sender')->merge($all->pluck('receiver'))->unique()->values()->filter(function ($u) {
            return $u !== null && $u !== '' && strtolower($u) !== 'all';
        })->all();

        $users = User::whereIn('username', $usernames)->get()->keyBy('username');

        $sortKey = $column; // 'sender'|'receiver'|'subject'|'date'
        $dir = $direction === 'asc' ? 1 : -1;

        $all = $all->sortBy(function ($m) use ($sortKey, $users) {
            if ($sortKey === 'sender') {
                $uname = $m->sender ?? '';
                if ($uname && isset($users[$uname])) {
                    return mb_strtolower($users[$uname]->name ?? $uname);
                }
                return mb_strtolower($m->sender ?? '');
            }
            if ($sortKey === 'receiver') {
                $r = $m->receiver ?? '';
                if (strtolower($r) === 'all') {
                    return mb_strtolower(__('All'));
                }
                if ($r && isset($users[$r])) {
                    return mb_strtolower($users[$r]->name ?? $r);
                }
                return mb_strtolower($r ?: '');
            }
            if ($sortKey === 'subject') {
                return mb_strtolower(strip_tags($m->subject ?? ''));
            }
            if ($sortKey === 'date') {
                return strtotime((string) ($m->date ?? 0)) ?: 0;
            }
            return $m->$sortKey ?? '';
        }, SORT_REGULAR, $dir === -1);

        $all = $all->values();
        // Determine per-page (allowed values) from query or session. Persist selection in session.
        $allowedPerPage = [10, 20, 50, 100];
        $perPageParam = $request->query('per_page');
        if ($perPageParam !== null) {
            $perPage = in_array((int) $perPageParam, $allowedPerPage, true) ? (int) $perPageParam : 10;
            $request->session()->put('messages_per_page', $perPage);
        } else {
            $perPage = $request->session()->get('messages_per_page', 10);
            if (! in_array((int) $perPage, $allowedPerPage, true)) {
                $perPage = 10;
            }
        }

    // We'll build the paginated collection below from merged results.

        // Total messages for this user (including broadcasts) excluding deleted
        $totalMessages = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->count();

        // Unread messages (uses helper that excludes deleted/read)
        $unreadMessages = Message::getNumberOfUnreadMails($user->username);

        // Paginate the merged collection
        $currentPage = max(1, (int) $request->query('page', 1));
        $perPage = (int) $perPage;
        $total = $all->count();
        $slice = $all->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Build a LengthAwarePaginator for compatibility with the view links
        $messages = new \Illuminate\Pagination\LengthAwarePaginator($slice, $total, $perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);

        // Eager-fetch involved users (senders and receivers) for the current page to show full name and profile link
        $usernames = $all->pluck('sender')->merge($all->pluck('receiver'))->unique()->values()->filter(function ($u) {
            return $u !== null && $u !== '' && strtolower($u) !== 'all';
        })->all();

        $senders = User::whereIn('username', $usernames)->get()->keyBy('username');

        // Do not overwrite administrator user names here. We want admin user accounts
        // to keep their full name for regular messages. The special branded name
        // 'DeepskyLog' should only apply to literal legacy senders like 'admin' or
        // explicit broadcast markers; that is handled later when building view rows.

        // Some legacy messages may have 'admin' as a literal sender username without a
        // corresponding User row. Ensure those still display as 'DeepskyLog' by adding
        // a lightweight placeholder into the $senders map so views pick it up.
    $pageSenders = $messages->pluck('sender')->unique()->values()->all();
        foreach ($pageSenders as $s) {
            if (strtolower($s) === 'admin' && ! isset($senders['admin'])) {
                $placeholder = new \stdClass;
                $placeholder->username = 'admin';
                $placeholder->name = 'admin';
                $placeholder->slug = 'admin';
                $placeholder->profile_photo_url = null;
                $senders['admin'] = $placeholder;
                break;
            }
        }

        return view('messages.index', compact('messages', 'read', 'senders', 'totalMessages', 'unreadMessages', 'perPage'));
    }

    public function show($id)
    {
    /** @var \App\Models\User $user */
    $user = Auth::user();
        $message = Message::findOrFail($id);

        // ensure receiver is user or admin
        if ($message->receiver !== $user->username && ! $this->userIsAdmin($user)) {
            abort(403);
        }

        // mark as read
        if (! MessageRead::where('id', $id)->where('receiver', $user->username)->exists()) {
            MessageRead::create(['id' => $id, 'receiver' => $user->username, 'read_at' => now()]);
        }

        // Fetch sender user if available. Do NOT overwrite an admin user's real name here.
        // The branded name 'DeepskyLog' should only apply to literal legacy senders
        // like the string 'admin' or 'deepskylog' (handled in the view), or to
        // explicit broadcast markers if/when they exist.
        $senderUser = User::where('username', $message->sender)->first();

        return view('messages.show', compact('message', 'senderUser'));
    }

    public function create()
    {
        return view('messages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $senderUsername = Auth::user()->username;
        // For normal (non-broadcast) messages, show the sender's full name even for admins.
        $senderName = Auth::user()->name;

        // If receiver is 'all' and user is admin, broadcast
        if ($request->receiver === 'all' && $this->userIsAdmin(Auth::user())) {
            return $this->broadcast($request);
        }

        $safeSubject = Message::sanitizeHtml($request->subject);
        $safeMessage = Message::sanitizeHtml($request->message);

        Message::create([
            'sender' => $senderUsername,
            'receiver' => $request->receiver,
            'subject' => $safeSubject,
            'message' => $safeMessage,
            'date' => now(),
        ]);

        // Send email notification to recipient if they have enabled mail notifications
        try {
            $recipientUser = User::where('username', $request->receiver)->first();
            if ($recipientUser && $recipientUser->sendMail) {
                Mail::to($recipientUser->email)->send(new MessageReceived($senderName, $request->subject, $safeMessage, $recipientUser->name));
            }
        } catch (\Throwable $e) {
            // Don't break main flow on mail failures; just log to the error log.
            logger()->error('Failed to send message email notification: '.$e->getMessage());
        }

        return redirect()->route('messages.index')->with('status', __('Message sent'));
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $sender = Auth::user()->username;
        // For broadcasts, always display 'DeepskyLog' as sender name regardless of which admin sent it.
        $senderName = 'DeepskyLog';

        $now = now();

        $safeSubject = Message::sanitizeHtml($request->subject);
        $safeMessage = Message::sanitizeHtml($request->message);

        // Store a single legacy broadcast row with receiver = 'all' so
        // older codepaths and external tools that expect a single broadcast
        // entry continue to work. We do NOT create per-user DB rows here.
        try {
            $allRow = [
                'sender' => $sender,
                'receiver' => 'all',
                'subject' => $safeSubject,
                'message' => $safeMessage,
                'date' => $now,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            DB::table('messages')->insert($allRow);
        } catch (\Exception $e) {
            // Log and continue: failure to add the legacy 'all' row shouldn't stop emailing
            logger()->error('Failed to insert legacy broadcast row: '.$e->getMessage());
        }

        // Send email notifications to users who have enabled mail notifications.
        try {
            $usersWithMail = User::where('sendMail', true)->pluck('email', 'username');
            foreach ($usersWithMail as $username => $email) {
                // avoid sending to empty emails
                if (empty($email)) {
                    continue;
                }
                Mail::to($email)->send(new MessageReceived($senderName, $request->subject, $safeMessage, User::where('username', $username)->value('name')));
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to send broadcast email notifications: '.$e->getMessage());
        }

        return redirect()->route('messages.index')->with('status', __('Broadcast sent'));
    }

    /**
     * Mark all messages for the current user as read (creates rows in messagesRead if missing)
     */
    public function markAllRead(Request $request)
    {
    /** @var \App\Models\User $user */
    $user = Auth::user();

        // Exclude deleted messages
        $deleted = MessageDeleted::where('receiver', $user->username)->pluck('id')->toArray();

        // IDs already read
        $readIds = MessageRead::where('receiver', $user->username)->pluck('id')->all();

        // All message ids for user including broadcasts
        // Use the new Message model which targets the new `messages` table
        $allIds = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->pluck('id')->all();

        $toInsert = array_diff($allIds, $readIds);

        $rows = [];
        foreach ($toInsert as $id) {
            $rows[] = ['id' => $id, 'receiver' => $user->username];
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                $insertRows = array_map(function ($r) {
                    return ['id' => $r['id'], 'receiver' => $r['receiver'], 'read_at' => now()];
                }, $chunk);
                foreach ($insertRows as $ir) {
                    try {
                        DB::table('messages_read')->insert($ir);
                    } catch (\Exception $e) {
                        // ignore duplicates
                    }
                }
            }
        }

        // Redirect back to the inbox page (preserves query params) so the refreshed
        // list shows updated read/unread state.
        return redirect()->back()->with('status', __('All messages marked as read'));
    }

    /**
     * Mark a single message as deleted for the current user (legacy messagesDeleted table).
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $message = Message::findOrFail($id);

        // ensure the current user is allowed to delete this message: either the receiver,
        // the sender, or an administrator.
        if ($message->receiver !== $user->username && $message->sender !== $user->username && ! $this->userIsAdmin($user)) {
            abort(403);
        }

        // avoid duplicate marks: mark this message as deleted for the current user (so it
        // disappears from their inbox/sent lists)
        $exists = MessageDeleted::where('id', $id)->where('receiver', $user->username)->exists();
        if (! $exists) {
            try {
                DB::table('messages_deleted')->insert(['id' => $id, 'receiver' => $user->username, 'deleted_at' => now()]);
            } catch (\Exception $e) {
                // ignore duplicate key or other insert error
            }
        }

        $page = request()->input('page', 1);
        // Clamp page to available pages so deleting the last item on a page moves the user to a valid page
        $allowedPerPage = [10,20,50,100];
        $perPage = (int) session('messages_per_page', 10);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        if (! ($user instanceof User)) {
            $total = 0;
        } else {
            $total = $this->visibleMessagesCount($user);
        }
        $maxPage = max(1, (int) ceil($total / $perPage));
        if ($page > $maxPage) {
            $page = $maxPage;
        }

        return redirect()->route('messages.index', ['page' => $page])->with('status', __('Message deleted'));
    }

    /**
     * Bulk delete grouped sent message rows: mark all message ids that share the
     * same sender/subject/message/date as deleted for the current user.
     */
    public function bulkDestroy($id)
    {
        $user = Auth::user();

        $message = Message::findOrFail($id);

        // only allow sender or administrator to bulk-delete
        if ($message->sender !== $user->username && ! $this->userIsAdmin($user)) {
            abort(403);
        }

        // Find all messages that match the same content signature
        $matches = Message::where('sender', $message->sender)
            ->where('subject', $message->subject)
            ->where('message', $message->message)
            ->where('date', $message->date)
            ->pluck('id')
            ->all();

        foreach ($matches as $mid) {
            $exists = MessageDeleted::where('id', $mid)->where('receiver', $user->username)->exists();
            if (! $exists) {
                try {
                    DB::table('messages_deleted')->insert(['id' => $mid, 'receiver' => $user->username, 'deleted_at' => now()]);
                } catch (\Exception $e) {
                    // ignore duplicates
                }
            }
        }

        $page = request()->input('page', 1);
        // Clamp page similar to single delete
        $allowedPerPage = [10,20,50,100];
        $perPage = (int) session('messages_per_page', 10);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        if (! ($user instanceof User)) {
            $total = 0;
        } else {
            $total = $this->visibleMessagesCount($user);
        }
        $maxPage = max(1, (int) ceil($total / $perPage));
        if ($page > $maxPage) {
            $page = $maxPage;
        }

        return redirect()->route('messages.index', ['page' => $page])->with('status', __('Messages deleted'));
    }

    /**
     * Compute the number of visible message rows for the given user (received/broadcast + grouped sent)
     * This mirrors the logic in index() used to build the paginated list.
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    private function visibleMessagesCount(User $user): int
    {
        // Exclude deleted messages for this user
        $deleted = MessageDeleted::where('receiver', $user->username)->pluck('id')->toArray();

        // Received and broadcast messages (exclude messages where the user is the sender to avoid duplicates)
        $receivedCount = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->where('sender', '!=', $user->username)->count();

        // Grouped sent messages: group by sender, subject, message, date and count groups
        $sentGroupsCount = Message::where('sender', $user->username)->whereNotIn('id', $deleted)
            ->selectRaw('MIN(id) as id, sender, subject, message, date')
            ->groupBy('sender', 'subject', 'message', 'date')
            ->get()
            ->count();

        return $receivedCount + $sentGroupsCount;
    }

    /**
     * Return reply data (plain-text quoted message) for AJAX prefill.
     */
    public function replyData($id)
    {
    /** @var \App\Models\User $auth */
    $auth = Auth::user();
        $message = Message::findOrFail($id);

        // ensure receiver is user or admin
        if ($message->receiver !== $auth->username && ! $this->userIsAdmin($auth)) {
            abort(403);
        }

        // build plain text and quote it with > like mail
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($message->message)));
        $lines = preg_split('/\r?\n/', $plain);
        $quoted = implode("\n", array_map(function ($l) {
            return '> '.$l;
        }, $lines));

        // Decode any HTML entities stored in the subject so reply subjects
        // show human-readable characters and prefix detection works correctly.
        $originalSubject = $message->subject ? html_entity_decode($message->subject, ENT_QUOTES | ENT_HTML5, 'UTF-8') : __('(no subject)');
        $rePrefix = __('Re:');
        // If the original subject already starts with a Re: prefix (localized or plain), don't add another
        if (mb_stripos(ltrim($originalSubject), $rePrefix) === 0 || mb_stripos(ltrim($originalSubject), 're:') === 0) {
            $subject = $originalSubject;
        } else {
            $subject = $rePrefix.' '.$originalSubject;
        }

        // Resolve sender display name. Preserve real user names (including admins).
        // Only map literal legacy senders 'admin' or 'deepskylog' to the branded
        // display name 'DeepskyLog'. This ensures administrator user accounts
        // still show their personal name for normal messages and replies.
        $senderModel = User::where('username', $message->sender)->first();
        if ($senderModel) {
            $senderName = $senderModel->name;
        } else {
            $senderName = in_array(strtolower($message->sender), ['admin', 'deepskylog']) ? 'DeepskyLog' : $message->sender;
        }

        // Localized header: "On DATE, USER wrote:"
        $displayDate = $message->formatted_date ?? $message->date;
        $header = __('On :date, :user wrote:', ['date' => $displayDate, 'user' => $senderName]);

        return response()->json([
            'subject' => $subject,
            'message' => $quoted,
            // Also return sanitized HTML so rich editors can preserve markup
            'message_html' => Message::sanitizeHtml($message->message),
            'sender' => $message->sender,
            'date' => $message->date,
            'header' => $header,
        ]);
    }

    /**
     * Determine if a given user is in the Administrators team.
     * Using a static query avoids calling instance methods that some static analyzers
     * may not resolve when the model uses traits.
     *
     * @param  \App\Models\User|null  $user
     * @return bool
     */
    private function userIsAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return User::where('username', $user->username)->whereHas('teams', function ($q) {
            $q->where('name', 'Administrators');
        })->exists();
    }
}
