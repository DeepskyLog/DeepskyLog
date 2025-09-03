<?php

namespace App\Http\Controllers;

use App\Models\MessagesDeletedOld;
use App\Models\MessagesOld;
use App\Models\MessagesReadOld;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        // Exclude deleted messages
        $deleted = MessagesDeletedOld::where('receiver', $user->username)->pluck('id')->toArray();

        // Read message ids for marking
        $read = MessagesReadOld::where('receiver', $user->username)->pluck('id');

        // Allow sorting only on specific logical columns mapped to real DB columns
        $allowed = [
            'from' => 'sender',
            'subject' => 'subject',
            'date' => 'date',
        ];

        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $column = $allowed[$sort] ?? 'date';

        // If sorting by 'from', order by the users.name (full name) using a left join.
        // messages lives on the 'mysqlOld' connection; the users table is on the default connection.
        if ($sort === 'from') {
            // Try to order by the users.name via a cross-database join. Build the qualification
            // inside the try so failures determining the default DB name are also caught.
            try {
                $defaultConn = config('database.default');
                $defaultDb = DB::connection($defaultConn)->getDatabaseName();
                // Qualify the users table with the default database name to allow cross-database join
                $qualifiedUsers = ($defaultDb ? $defaultDb.'.users' : 'users');

                $query = MessagesOld::where(function ($q) use ($user) {
                    $q->where('messages.receiver', $user->username)
                        ->orWhere('messages.receiver', 'all');
                })
                    ->whereNotIn('messages.id', $deleted)
                    ->leftJoin($qualifiedUsers.' as u', 'u.username', '=', 'messages.sender')
                    ->select('messages.*')
                    ->orderBy('u.name', $direction);
            } catch (\Exception $e) {
                // fallback: order by sender username
                $query = MessagesOld::where(function ($q) use ($user) {
                    $q->where('messages.receiver', $user->username)
                        ->orWhere('messages.receiver', 'all');
                })
                    ->whereNotIn('messages.id', $deleted)
                    ->select('messages.*')
                    ->orderBy('messages.sender', $direction);
            }
        } else {
            $query = MessagesOld::where(function ($q) use ($user) {
                $q->where('messages.receiver', $user->username)
                    ->orWhere('messages.receiver', 'all');
            })
                ->whereNotIn('messages.id', $deleted)
                ->select('messages.*')
                ->orderBy('messages.'.$column, $direction);
        }

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

        $messages = $query->paginate($perPage)->withQueryString();

        // Total messages for this user (including broadcasts) excluding deleted
        $totalMessages = MessagesOld::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->count();

        // Unread messages (uses helper that excludes deleted/read)
        $unreadMessages = MessagesOld::getNumberOfUnreadMails($user->username);

        // Eager-fetch sender users for the current page to show full name and profile link
        $senders = User::whereIn('username', $messages->pluck('sender')->unique()->values()->all())->get()->keyBy('username');

        // Map administrator user display names to the branded 'DeepskyLog'. We resolve admin
        // usernames in one query to avoid N+1 checks and then overwrite the name on the
        // fetched sender models so the view shows the replacement.
        $adminUsernames = User::whereHas('teams', function ($q) {
            $q->where('name', 'Administrators');
        })->pluck('username')->all();

        foreach ($adminUsernames as $adminUsername) {
            if (isset($senders[$adminUsername])) {
                $senders[$adminUsername]->name = 'DeepskyLog';
            }
        }

        // Some legacy messages may have 'admin' as a literal sender username without a
        // corresponding User row. Ensure those still display as 'DeepskyLog' by adding
        // a lightweight placeholder into the $senders map so views pick it up.
        $pageSenders = $messages->pluck('sender')->unique()->values()->all();
        foreach ($pageSenders as $s) {
            if (strtolower($s) === 'admin' && ! isset($senders['admin'])) {
                $placeholder = new \stdClass;
                $placeholder->username = 'admin';
                $placeholder->name = 'DeepskyLog';
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
        $user = Auth::user();
        $message = MessagesOld::findOrFail($id);

        // ensure receiver is user or admin
        if ($message->receiver !== $user->username && ! $user->hasAdministratorPrivileges()) {
            abort(403);
        }

        // mark as read
        if (! MessagesReadOld::where('id', $id)->where('receiver', $user->username)->exists()) {
            MessagesReadOld::create(['id' => $id, 'receiver' => $user->username]);
        }

        // Fetch sender user if available and map admin display-name
        $senderUser = User::where('username', $message->sender)->first();
        if ($senderUser && $senderUser->hasAdministratorPrivileges()) {
            $senderUser->name = 'DeepskyLog';
        }

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

        $sender = Auth::user()->username;

        // If receiver is 'all' and user is admin, broadcast
        if ($request->receiver === 'all' && Auth::user()->hasAdministratorPrivileges()) {
            return $this->broadcast($request);
        }

        $safeSubject = MessagesOld::sanitizeHtml($request->subject);
        $safeMessage = MessagesOld::sanitizeHtml($request->message);

        MessagesOld::create([
            'sender' => $sender,
            'receiver' => $request->receiver,
            'subject' => $safeSubject,
            'message' => $safeMessage,
            'date' => now(),
        ]);

        return redirect()->route('messages.index')->with('status', __('Message sent'));
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $sender = Auth::user()->username;

        $users = User::pluck('username');

        $now = now();

        $rows = [];

        $safeSubject = MessagesOld::sanitizeHtml($request->subject);
        $safeMessage = MessagesOld::sanitizeHtml($request->message);

        foreach ($users as $username) {
            $rows[] = [
                'sender' => $sender,
                'receiver' => $username,
                'subject' => $safeSubject,
                'message' => $safeMessage,
                'date' => $now,
            ];
        }

        // Use the old connection table
        DB::connection('mysqlOld')->table('messages')->insert($rows);

        return redirect()->route('messages.index')->with('status', __('Broadcast sent'));
    }

    /**
     * Mark all messages for the current user as read (creates rows in messagesRead if missing)
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();

        // Exclude deleted messages
        $deleted = MessagesDeletedOld::where('receiver', $user->username)->pluck('id')->toArray();

        // IDs already read
        $readIds = MessagesReadOld::where('receiver', $user->username)->pluck('id')->all();

        // All message ids for user including broadcasts
        $allIds = MessagesOld::where(function ($q) use ($user) {
            $q->where('receiver', $user->username)
                ->orWhere('receiver', 'all');
        })->whereNotIn('id', $deleted)->pluck('id')->all();

        $toInsert = array_diff($allIds, $readIds);

        $rows = [];
        foreach ($toInsert as $id) {
            $rows[] = ['id' => $id, 'receiver' => $user->username];
        }

        if (! empty($rows)) {
            // Use the legacy connection for writes. Insert only the columns that exist in
            // the legacy table (some installs don't have timestamp columns on messagesRead).
            DB::connection('mysqlOld')->table('messagesRead')->insert($rows);
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

        $message = MessagesOld::findOrFail($id);

        // ensure receiver is user or admin
        if ($message->receiver !== $user->username && ! $user->hasAdministratorPrivileges()) {
            abort(403);
        }

        // avoid duplicate marks
        $exists = MessagesDeletedOld::where('id', $id)->where('receiver', $user->username)->exists();
        if (! $exists) {
            // Use legacy connection to insert a deleted marker. Some installs may not have timestamps.
            DB::connection('mysqlOld')->table('messagesDeleted')->insert([
                ['id' => $id, 'receiver' => $user->username],
            ]);
        }

        return redirect()->route('messages.index')->with('status', __('Message deleted'));
    }

    /**
     * Return reply data (plain-text quoted message) for AJAX prefill.
     */
    public function replyData($id)
    {
        $auth = Auth::user();
        $message = MessagesOld::findOrFail($id);

        // ensure receiver is user or admin
        if ($message->receiver !== $auth->username && ! $auth->hasAdministratorPrivileges()) {
            abort(403);
        }

        // build plain text and quote it with > like mail
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($message->message)));
        $lines = preg_split('/\r?\n/', $plain);
        $quoted = implode("\n", array_map(function ($l) {
            return '> '.$l;
        }, $lines));

        $originalSubject = $message->subject ?: __('(no subject)');
        $rePrefix = __('Re:');
        // If the original subject already starts with a Re: prefix (localized or plain), don't add another
        if (mb_stripos(ltrim($originalSubject), $rePrefix) === 0 || mb_stripos(ltrim($originalSubject), 're:') === 0) {
            $subject = $originalSubject;
        } else {
            $subject = $rePrefix.' '.$originalSubject;
        }

        // Resolve sender display name if user exists and map admin to DeepskyLog
        $senderModel = User::where('username', $message->sender)->first();
        if ($senderModel) {
            $senderName = $senderModel->hasAdministratorPrivileges() ? 'DeepskyLog' : $senderModel->name;
        } else {
            $senderName = (strtolower($message->sender) === 'admin') ? 'DeepskyLog' : $message->sender;
        }

        // Localized header: "On DATE, USER wrote:"
        $displayDate = $message->formatted_date ?? $message->date;
        $header = __('On :date, :user wrote:', ['date' => $displayDate, 'user' => $senderName]);

        return response()->json([
            'subject' => $subject,
            'message' => $quoted,
            // Also return sanitized HTML so rich editors can preserve markup
            'message_html' => MessagesOld::sanitizeHtml($message->message),
            'sender' => $message->sender,
            'date' => $message->date,
            'header' => $header,
        ]);
    }
}
