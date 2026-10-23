<?php

namespace App\Http\Controllers;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\MessageDeleted;
use App\Models\MessageRead;
use App\Models\Team;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller
{
    /** The most receivers of one message. */
    public const MAX_RECEIVERS = 20;

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
        // If the user wants to view only unread messages, query param 'unread'=1
        $onlyUnread = (string) $request->query('unread') === '1';
        // Search in the subject, the text and the name of the other observer
        // (?q=), and show only the received or only the sent messages (?box=).
        $search = trim((string) $request->query('q', ''));
        $box = in_array($request->query('box'), ['received', 'sent'], true) ? $request->query('box') : null;

    // If sorting by 'from', order by the users.name (full name) using a left join.
        // Build two sets: received/broadcast messages, and grouped sent messages.
        // We'll merge them in PHP and paginate manually so sent messages that were
        // duplicated for multiple recipients show only once (as a 'group').

        // Received and broadcast messages (exclude messages where the user is the sender to avoid duplicates)
        $receivedQuery = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->where('sender', '!=', $user->username);

        // If filtering to unread-only, exclude messages that already have a read row
        if ($onlyUnread) {
            $receivedQuery = $receivedQuery->whereNotIn('id', $read->toArray());
        }

        $this->applySearch($receivedQuery, 'sender', $search);

        // Load these into a collection
        $received = ($box === 'sent' ? collect() : $receivedQuery->get())->map(function ($m) {
            // mark as not a grouped sent row
            $m->_is_group = false;
            $m->_group_count = 1;
            return $m;
        });

        // Grouped sent messages: the copies of a message sent to several
        // receivers (same sender, subject, text and date) are one row, shown
        // as the copy with the lowest id, with the list of its receivers.
        // With a search, a group is kept when one of its copies matches.
        $sentGroups = collect();
        if ($box !== 'received' && ! $onlyUnread) {
            $groups = Message::where('sender', $user->username)->whereNotIn('id', $deleted)
                ->orderBy('id')
                ->get()
                ->groupBy(fn ($m) => $m->subject."\0".$m->message."\0".$m->date);
            if ($search !== '') {
                $matching = Message::where('sender', $user->username)->whereNotIn('id', $deleted);
                $this->applySearch($matching, 'receiver', $search);
                $matching = array_flip($matching->pluck('id')->all());
                $groups = $groups->filter(fn ($copies) => $copies->contains(fn ($m) => isset($matching[$m->id])));
            }
            $sentGroups = $groups->map(function ($copies) {
                $rep = $copies->first();
                $rep->_is_group = true;
                $rep->_group_count = $copies->count();
                $rep->_receivers = $copies->pluck('receiver')->unique()->values()->all();

                return $rep;
            })->values();
        }

        // If onlyUnread is active we only show received (unread) messages; hide grouped sent messages
        $all = $received->concat($onlyUnread ? collect() : $sentGroups);

        // Eager-fetch user models (senders and receivers) so we can sort by full name
        $usernames = $all->pluck('sender')->merge($all->pluck('receiver'))->merge($all->pluck('_receivers')->flatten())->unique()->values()->filter(function ($u) {
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

        // Received messages count (exclude messages where the user is the sender to avoid duplicates)
        $receivedCount = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->where('sender', '!=', $user->username)->count();

        // Grouped sent messages count (groups of identical sends)
        $sentGroupsCount = Message::where('sender', $user->username)->whereNotIn('id', $deleted)
            ->selectRaw('MIN(id) as id, sender, subject, message, date')
            ->groupBy('sender', 'subject', 'message', 'date')
            ->get()
            ->count();

        // Paginate the merged collection
        $currentPage = max(1, (int) $request->query('page', 1));
        $perPage = (int) $perPage;
        $total = $all->count();
    // merged total: number of rows shown in the merged inbox (received + grouped sent)
    $mergedTotal = $total;
        $slice = $all->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // A received message sent to several observers: list all its receivers,
        // as for a sent one. Its copies have the same sender, subject, text and
        // date; looked up for the rows of this page only, in one query.
        $personal = $slice->filter(fn ($m) => empty($m->_is_group) && strtolower((string) $m->receiver) !== 'all');
        if ($personal->isNotEmpty()) {
            $key = fn ($m) => $m->sender."\0".$m->subject."\0".$m->message."\0".$m->date;
            $copies = Message::whereIn('sender', $personal->pluck('sender')->unique()->values())
                ->whereIn('date', $personal->pluck('date')->unique()->values())
                ->where('receiver', '<>', 'all')
                ->orderBy('id')
                ->get(['id', 'sender', 'receiver', 'subject', 'message', 'date'])
                ->groupBy($key);
            foreach ($personal as $m) {
                $receivers = ($copies[$key($m)] ?? collect())->pluck('receiver')->unique()->values();
                if ($receivers->count() > 1) {
                    $m->_receivers = $receivers->all();
                }
            }
        }

        // Build a LengthAwarePaginator for compatibility with the view links
        $messages = new \Illuminate\Pagination\LengthAwarePaginator($slice, $total, $perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);

        // Eager-fetch involved users (senders and receivers) for the current page to show full name and profile link
        $usernames = $all->pluck('sender')->merge($all->pluck('receiver'))->merge($all->pluck('_receivers')->flatten())->unique()->values()->filter(function ($u) {
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

        $blocked = MessageBlock::with('blockedUser:id,name,username,slug')->where('user_id', $user->id)->get();

        return view('messages.index', compact('messages', 'read', 'senders', 'totalMessages', 'unreadMessages', 'perPage', 'receivedCount', 'sentGroupsCount', 'mergedTotal', 'search', 'box', 'blocked'));
    }

    /**
     * Limit a messages query to the search term: in the subject, the text, or
     * the username or name in the given column (the sender of received
     * messages, the receiver of sent ones).
     */
    private function applySearch($query, string $column, string $search): void
    {
        if ($search === '') {
            return;
        }

        $like = '%'.addcslashes($search, '%_\\').'%';
        $query->where(fn ($q) => $q->where('subject', 'like', $like)
            ->orWhere('message', 'like', $like)
            ->orWhere($column, 'like', $like)
            ->orWhereIn($column, User::where('name', 'like', $like)->select('username')));
    }

    /**
     * Readable by its receiver, by everyone for a broadcast (receiver 'all',
     * which every inbox lists), by its sender and by administrators.
     */
    private function canRead(Message $message, User $user): bool
    {
        return $this->isReceiver($message, $user)
            || $message->sender === $user->username
            || $this->userIsAdmin($user);
    }

    private function isReceiver(Message $message, User $user): bool
    {
        return $message->receiver === $user->username || strtolower((string) $message->receiver) === 'all';
    }

    public function show($id)
    {
    /** @var \App\Models\User $user */
    $user = Auth::user();
        $message = Message::findOrFail($id);

        if (! $this->canRead($message, $user)) {
            abort(403);
        }
        $isReceiver = $this->isReceiver($message, $user);

        // A message the user deleted is gone for them, also by its URL.
        if (MessageDeleted::where('id', $id)->where('receiver', $user->username)->exists()) {
            abort(404);
        }

        // mark as read
        if ($isReceiver && ! MessageRead::where('id', $id)->where('receiver', $user->username)->exists()) {
            MessageRead::create(['id' => $id, 'receiver' => $user->username, 'read_at' => now()]);
            Message::forgetUnreadCount($user->username);
        }

        // Fetch sender user if available. Do NOT overwrite an admin user's real name here.
        // The branded name 'DeepskyLog' should only apply to literal legacy senders
        // like the string 'admin' or 'deepskylog' (handled in the view), or to
        // explicit broadcast markers if/when they exist.
        $senderUser = User::where('username', $message->sender)->first();
        $isBroadcast = strtolower((string) $message->receiver) === 'all';

        // All receivers of the message: a message to several observers is a
        // copy per receiver, with the same sender, subject, text and date.
        $receiverNames = $isBroadcast ? collect() : Message::whereIn('id', $this->groupIds($message))->orderBy('id')->pluck('receiver')->unique()->values();
        $receiverUsers = User::whereIn('username', $receiverNames)->get()->keyBy(fn ($u) => mb_strtolower($u->username));
        $recipients = $receiverNames->map(fn ($name) => ['name' => $name, 'user' => $receiverUsers[mb_strtolower($name)] ?? null]);

        // A message the user sent to somebody else: replying to it continues
        // the conversation with the receivers, as in a mail client.
        $isSent = $message->sender === $user->username && $message->receiver !== $user->username;

        // From DeepskyLog: a broadcast, or a message of the old site's 'admin'.
        $isDsl = $isBroadcast || ($senderUser
            ? ($senderUser->name === 'DeepskyLog' || strtolower($senderUser->username) === 'admin')
            : in_array(strtolower($message->sender), ['admin', 'deepskylog']));
        // The other receivers with an account, for "Reply to all".
        $others = $recipients->pluck('user')->filter()->pluck('username')->reject(fn ($u) => $u === $user->username)->values();
        if ($isSent) {
            $replyTo = $others->isNotEmpty() ? $others->join(',') : null;
            $replyAllTo = null;
        } else {
            $replyTo = ! $isDsl && $senderUser ? $senderUser->username : null;
            $replyAllTo = $replyTo && $others->isNotEmpty() ? $others->prepend($replyTo)->unique()->join(',') : null;
        }

        // The conversation: the message this one answers and the replies to
        // it, as far as the user can read them and didn't delete them.
        $deleted = MessageDeleted::where('receiver', $user->username)->pluck('id')->all();
        $visible = fn (?Message $m) => $m && $this->canRead($m, $user) && ! in_array($m->id, $deleted);
        $repliedTo = $message->reply_to_id ? Message::find($message->reply_to_id) : null;
        $repliedTo = $visible($repliedTo) ? $repliedTo : null;
        $replies = Message::where('reply_to_id', $message->id)->orderBy('date')->get()->filter($visible)->values();
        $names = User::whereIn('username', $replies->pluck('sender'))->pluck('name', 'username');

        // A personal message of another observer, who isn't an administrator:
        // the user can block them.
        $canBlock = $senderUser && ! $isSent && $senderUser->id !== $user->id
            && ! $isBroadcast && ! $this->userIsAdmin($senderUser);
        $isBlocked = $canBlock && MessageBlock::blocks($user, $senderUser);
        // Only what the user received has a read state.
        $canMarkUnread = $isReceiver && $message->sender !== $user->username;

        return view('messages.show', compact('message', 'senderUser', 'recipients', 'isBroadcast', 'repliedTo', 'replies', 'names', 'canBlock', 'isBlocked', 'canMarkUnread', 'replyTo', 'replyAllTo'));
    }

    public function create(Request $request)
    {
        return view('messages.create', [
            'aboutUrl' => $this->aboutUrl($request->query('about')),
        ]);
    }

    /**
     * The page a message is about (an observation, sketch, session…), passed
     * as ?about= by the message buttons so the body can start with a link to
     * it. Only links to this site are taken, so a crafted link cannot put
     * someone else's URL in the message.
     */
    private function aboutUrl(mixed $url): ?string
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $site = parse_url(url('/'));

        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || strcasecmp($parts['host'] ?? '', $site['host'] ?? '') !== 0
            || ($parts['port'] ?? null) !== ($site['port'] ?? null)) {
            return null;
        }

        return $url;
    }

    public function store(Request $request)
    {
        // A missing receiver is reported together with the other errors.
        Validator::make($request->all(), [
            'receiver' => 'nullable|string',
            'receivers' => 'nullable',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'reply_to_id' => 'nullable|integer',
        ])->after(function ($validator) use ($request) {
            if ($this->receiversFrom($request) === []) {
                $validator->errors()->add('receiver', __('validation.required', ['attribute' => 'receiver']));
            }
        })->validate();

        /** @var User $sender */
        $sender = Auth::user();
        $isAdmin = $this->userIsAdmin($sender);

        // If receiver is 'all' and user is admin, broadcast
        if ($request->receiver === 'all' && $isAdmin) {
            return $this->broadcast($request);
        }

        // Every receiver must be an existing user: only administrators may
        // send to 'all', and a non-existing username would be stored as a
        // message nobody can read.
        $usernames = $this->receiversFrom($request);
        $error = fn (string $text) => back()->withInput()->withErrors(['receiver' => $text]);
        if (count($usernames) > self::MAX_RECEIVERS) {
            return $error(__('A message can have at most :max receivers.', ['max' => self::MAX_RECEIVERS]));
        }
        // In the order they were chosen: the copies are stored in that order,
        // which is the order the message page lists them in.
        $order = array_flip(array_map('mb_strtolower', $usernames));
        $recipients = User::whereIn('username', $usernames)->get()->unique('id')
            ->sortBy(fn ($u) => $order[mb_strtolower($u->username)] ?? PHP_INT_MAX)->values();
        $found = $recipients->map(fn ($u) => mb_strtolower($u->username))->all();
        if (collect($usernames)->contains(fn ($u) => strtolower($u) === 'all' || ! in_array(mb_strtolower($u), $found, true))) {
            return $error(__('validation.exists', ['attribute' => 'receiver']));
        }

        // An observer who blocked the sender doesn't get their messages.
        // Administrators can't be blocked.
        $blockedBy = $isAdmin ? collect() : $recipients->filter(fn ($u) => MessageBlock::blocks($u, $sender));
        if ($blockedBy->count() === 1 && $recipients->count() === 1) {
            return $error(__('This observer does not accept messages from you.'));
        }
        if ($blockedBy->isNotEmpty()) {
            return $error(__('These observers do not accept messages from you: :names', ['names' => $blockedBy->pluck('name')->join(', ')]));
        }

        // The message this one answers, only when the sender can read it.
        $repliedTo = $request->filled('reply_to_id') ? Message::find((int) $request->reply_to_id) : null;

        $safeSubject = Message::sanitizeHtml($request->subject);
        $safeMessage = Message::sanitizeHtml($request->message);

        // A row per receiver, with the same date: the inbox of the sender
        // shows them as one sent message.
        $now = now();
        foreach ($recipients as $recipientUser) {
            $message = Message::create([
                'sender' => $sender->username,
                'receiver' => $recipientUser->username,
                'subject' => $safeSubject,
                'message' => $safeMessage,
                'date' => $now,
                'reply_to_id' => $repliedTo && $this->canRead($repliedTo, $sender) ? $repliedTo->id : null,
            ]);

            // Send email notification to recipient if they have enabled mail notifications
            try {
                if ($recipientUser->sendMail) {
                    // For normal (non-broadcast) messages, show the sender's full name even for admins.
                    Mail::to($recipientUser->email)
                        ->locale($recipientUser->language ?: app()->getLocale())
                        ->send(new MessageReceived($sender->name, $request->subject, $safeMessage, $recipientUser->name, route('messages.show', $message->id)));
                }
            } catch (\Throwable $e) {
                // Don't break main flow on mail failures; just log to the error log.
                logger()->error('Failed to send message email notification: '.$e->getMessage());
            }
        }

        return redirect()->route('messages.index')->with('success', __('Message sent'));
    }

    /**
     * The usernames a message is sent to: 'receivers', a JSON list (the
     * multiselect of the form) or an array, and 'receiver', one username.
     *
     * @return array<int, string>
     */
    private function receiversFrom(Request $request): array
    {
        $receivers = $request->input('receivers');
        if (is_string($receivers)) {
            $decoded = json_decode($receivers, true);
            $receivers = is_array($decoded) ? $decoded : [$receivers];
        }

        return collect((array) $receivers)
            ->push($request->input('receiver'))
            ->filter(fn ($u) => is_string($u) && trim($u) !== '')
            ->map(fn ($u) => trim($u))
            ->unique(fn ($u) => mb_strtolower($u))
            ->values()
            ->all();
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
        // Through the model, so the cached unread counts of everybody are
        // dropped (Message::booted()).
        $url = route('messages.index');
        try {
            $url = route('messages.show', Message::create([
                'sender' => $sender,
                'receiver' => 'all',
                'subject' => $safeSubject,
                'message' => $safeMessage,
                'date' => $now,
            ])->id);
        } catch (\Exception $e) {
            // Log and continue: failure to add the legacy 'all' row shouldn't stop emailing
            logger()->error('Failed to insert legacy broadcast row: '.$e->getMessage());
        }

        // Send email notifications to users who have enabled mail notifications.
        // The mails are queued; one failing address must not keep the others
        // from getting the mail.
        $usersWithMail = User::where('sendMail', true)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get(['username', 'email', 'name', 'language']);
        foreach ($usersWithMail as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->locale($recipient->language ?: app()->getLocale())
                    ->send(new MessageReceived($senderName, $request->subject, $safeMessage, $recipient->name, $url));
            } catch (\Throwable $e) {
                logger()->error('Failed to send broadcast email notification', [
                    'username' => $recipient->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('messages.index')->with('success', __('Broadcast sent'));
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

        Message::forgetUnreadCount($user->username);

        // Redirect back to the inbox page (preserves query params) so the refreshed
        // list shows updated read/unread state.
        return redirect()->back()->with('success', __('All messages marked as read'));
    }

    /**
     * Mark all messages as deleted for the current user (legacy messages_deleted table).
     */
    public function deleteAll(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Exclude messages that are already marked deleted for this user
        $alreadyDeleted = MessageDeleted::where('receiver', $user->username)->pluck('id')->toArray();

        // Find all message ids relevant to this user: received/broadcast and any sent rows
        $allIds = Message::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all')
                ->orWhere('sender', $user->username);
        })->whereNotIn('id', $alreadyDeleted)->pluck('id')->all();

        $rows = [];
        foreach ($allIds as $id) {
            $rows[] = ['id' => $id, 'receiver' => $user->username, 'deleted_at' => now()];
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                try {
                    foreach ($chunk as $r) {
                        DB::table('messages_deleted')->insert($r);
                    }
                } catch (\Exception $e) {
                    // ignore duplicates or DB errors per-row
                }
            }
        }

        Message::forgetUnreadCount($user->username);

        return redirect()->route('messages.index')->with('success', __('All messages deleted'));
    }

    /**
     * Mark a single message as deleted for the current user (legacy messagesDeleted table).
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $message = Message::findOrFail($id);

        // ensure the current user is allowed to delete this message: either the receiver
        // (everyone for a broadcast, receiver 'all'), the sender, or an administrator.
        if (! $this->canRead($message, $user)) {
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
        Message::forgetUnreadCount($user->username);

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

        return redirect()->route('messages.index', ['page' => $page])->with('success', __('Message deleted'));
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

        foreach ($this->groupIds($message) as $mid) {
            $exists = MessageDeleted::where('id', $mid)->where('receiver', $user->username)->exists();
            if (! $exists) {
                try {
                    DB::table('messages_deleted')->insert(['id' => $mid, 'receiver' => $user->username, 'deleted_at' => now()]);
                } catch (\Exception $e) {
                    // ignore duplicates
                }
            }
        }
        Message::forgetUnreadCount($user->username);

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

        return redirect()->route('messages.index', ['page' => $page])->with('success', __('Messages deleted'));
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

        // ensure receiver is user (everyone for a broadcast, receiver 'all'),
        // the sender (replying to a sent message) or admin
        if (! $this->canRead($message, $auth)) {
            abort(403);
        }

        // build plain text and quote it with > like mail: a line per paragraph,
        // line break or list item of the message
        $text = preg_replace('#<br\s*/?>|</(p|div|li|h[1-6]|blockquote)>#i', "\n", (string) $message->message);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_values(array_filter(
            array_map(fn ($l) => trim(preg_replace('/[^\S\n]+/u', ' ', $l)), explode("\n", $text)),
            fn ($l) => $l !== ''
        ));
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
            $senderName = in_array(strtolower($message->sender), ['admin', 'deepskylog']) ? 'DeepskyLog' : html_entity_decode($message->sender, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Localized header: "On DATE, USER wrote:"
        $displayDate = $message->formatted_date ?? $message->date;
        $header = __('On :date, :user wrote:', ['date' => $displayDate, 'user' => $senderName]);

        return response()->json([
            'subject' => $subject,
            'message' => $quoted,
            // Also return sanitized HTML so rich editors can preserve markup
            'message_html' => '<blockquote>'.Message::sanitizeHtml($message->message).'</blockquote>',
            'sender' => $message->sender,
            'date' => $message->date,
            'header' => $header,
        ]);
    }

    /**
     * The ids of the copies of a sent message: the messages with the same
     * sender, subject, text and date, which the inbox shows as one row.
     *
     * @return array<int, int>
     */
    private function groupIds(Message $message): array
    {
        return Message::where('sender', $message->sender)
            ->where('subject', $message->subject)
            ->where('message', $message->message)
            ->where('date', $message->date)
            ->pluck('id')
            ->all();
    }

    /**
     * Delete, mark read or mark unread the messages selected in the inbox.
     */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|max:100',
            'ids.*' => 'integer',
            'action' => 'required|in:delete,read,unread',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $messages = Message::whereIn('id', $data['ids'])->get()->filter(fn ($m) => $this->canRead($m, $user));

        foreach ($messages as $message) {
            if ($data['action'] === 'delete') {
                // A sent message stands for all its copies, as in the inbox.
                $ids = $message->sender === $user->username ? $this->groupIds($message) : [$message->id];
                DB::table('messages_deleted')->insertOrIgnore(
                    array_map(fn ($id) => ['id' => $id, 'receiver' => $user->username, 'deleted_at' => now()], $ids)
                );
            } elseif ($this->isReceiver($message, $user) && $message->sender !== $user->username) {
                // Only what the user received has a read state.
                if ($data['action'] === 'read') {
                    DB::table('messages_read')->insertOrIgnore(['id' => $message->id, 'receiver' => $user->username, 'read_at' => now()]);
                } else {
                    MessageRead::where('id', $message->id)->where('receiver', $user->username)->delete();
                }
            }
        }
        Message::forgetUnreadCount($user->username);

        return redirect()->back()->with('success', match ($data['action']) {
            'delete' => __('Selected messages deleted'),
            'read' => __('Selected messages marked as read'),
            'unread' => __('Selected messages marked as unread'),
        });
    }

    /**
     * Mark a received message as unread again.
     */
    public function markUnread($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $message = Message::findOrFail($id);

        if (! $this->isReceiver($message, $user)) {
            abort(403);
        }

        MessageRead::where('id', $message->id)->where('receiver', $user->username)->delete();
        Message::forgetUnreadCount($user->username);

        return redirect()->route('messages.index')->with('success', __('Message marked as unread'));
    }

    /**
     * Block an observer: they can't send the user messages any more.
     * Administrators can't be blocked, so their messages always arrive.
     */
    public function block(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        /** @var User $user */
        $user = Auth::user();
        $blocked = User::findOrFail($data['user_id']);

        if ($blocked->id === $user->id || $this->userIsAdmin($blocked)) {
            abort(422);
        }

        MessageBlock::firstOrCreate(['user_id' => $user->id, 'blocked_user_id' => $blocked->id]);
        // A blocked observer no longer follows the user either
        UserFollow::where('follower_id', $blocked->id)->where('followed_id', $user->id)->get()->each->delete();

        return redirect()->back()->with('success', __(':name can no longer send you messages', ['name' => $blocked->name]));
    }

    public function unblock($userId)
    {
        MessageBlock::where('user_id', Auth::id())->where('blocked_user_id', (int) $userId)->delete();

        return redirect()->back()->with('success', __('Observer unblocked'));
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
            $q->where('teams.id', Team::ADMINISTRATORS);
        })->exists();
    }
}
