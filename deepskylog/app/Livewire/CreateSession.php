<?php

namespace App\Livewire;

use App\Jobs\BuildSessionCopies;
use App\Jobs\SendSessionInvitations;
use App\Models\ObservationSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateSession extends Component
{
    use WithFileUploads;

    public $session;

    public $name;

    public $observer;

    // Array of additional (legacy) observers for this session (usernames)
    public $otherObservers = [];

    public $locationid;

    public $begindate;

    public $enddate;

    public $weather;

    public $equipment;

    public $comments;

    public $active = 1;

    public $photo;

    public function mount($session = null)
    {
        if ($session) {
            $this->session = $session;
            // Decode any stored HTML entities so the edit form shows human-readable characters
            $this->name = ! empty($session->name) ? html_entity_decode($session->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $session->name;
            $this->observer = $session->observerid;
            // Load other observers from legacy pivot table (returns array of usernames)
            $this->otherObservers = method_exists($session, 'otherObservers') ? $session->otherObservers() : [];
            $this->locationid = $session->locationid;
            // Ensure we expose only the date portion (YYYY-MM-DD) to the datepickers
            try {
                if (! empty($session->begindate)) {
                    // Stored values are saved as 'Y-m-d H:i:s'. Parse with explicit format to avoid timezone inference
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $session->begindate);
                    $this->begindate = $dt ? $dt->toDateString() : Carbon::parse($session->begindate)->toDateString();
                } else {
                    $this->begindate = null;
                }
            } catch (\Throwable $e) {
                $this->begindate = $session->begindate;
            }

            try {
                if (! empty($session->enddate)) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $session->enddate);
                    $this->enddate = $dt ? $dt->toDateString() : Carbon::parse($session->enddate)->toDateString();
                } else {
                    $this->enddate = null;
                }
            } catch (\Throwable $e) {
                $this->enddate = $session->enddate;
            }
            $this->weather = ! empty($session->weather) ? html_entity_decode($session->weather, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $session->weather;
            $this->equipment = ! empty($session->equipment) ? html_entity_decode($session->equipment, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $session->equipment;
            $this->comments = ! empty($session->comments) ? html_entity_decode($session->comments, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $session->comments;
            $this->active = $session->active;
        } else {
            $this->observer = Auth::user()->username ?? null;
        }
    }

    public function render()
    {
        $update = (bool) $this->session;

        // When showing the create form (not updating an existing session),
        // surface any inactive sessions (copies created for this observer)
        // so they can be adapted or deleted.
        $inactiveSessions = collect();
        try {
            if (! $update && Auth::check()) {
                $username = Auth::user()->username;
                $inactiveSessions = ObservationSession::where('observerid', $username)
                    ->where('active', 0)
                    ->orderByDesc('id')
                    ->get();
            }
        } catch (\Throwable $e) {
            // non-fatal: log and continue with empty collection
            report($e);
            $inactiveSessions = collect();
        }

        return view('livewire.create-session', [
            'update' => $update,
            'inactiveSessions' => $inactiveSessions,
        ]);
    }

    public function save()
    {
        // Debug: log raw incoming date values to diagnose timezone/format issues
        try {
            Log::info('CreateSession.save raw dates', ['begindate_raw' => $this->begindate, 'enddate_raw' => $this->enddate]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        $data = $this->validate([
            'name' => 'required|min:3',
            'observer' => 'nullable|exists:users,username',
            'locationid' => 'nullable|exists:locations,id',
            'begindate' => 'nullable|date',
            'enddate' => 'nullable|date',
            'weather' => 'nullable|string',
            'equipment' => 'nullable|string',
            'comments' => 'nullable|string',
            'active' => 'nullable|boolean',
            'otherObservers' => 'nullable|array',
            'photo' => 'nullable|image|max:4096',
            'otherObservers.*' => 'string',
        ]);

        // Normalize date-only inputs into full datetime strings (midnight) so datetime DB columns are populated correctly
        if (! empty($this->begindate)) {
            try {
                // Some datetime pickers emit ISO strings with timezone. Extract the YYYY-MM-DD prefix
                $raw = (string) $this->begindate;
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
                    $datePart = $m[1];
                    $this->begindate = $datePart.' 00:00:00';
                }
            } catch (\Throwable $e) {
                // leave as-is
            }
        }

        if (! empty($this->enddate)) {
            try {
                $raw = (string) $this->enddate;
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
                    $datePart = $m[1];
                    $this->enddate = $datePart.' 23:59:59';
                }
            } catch (\Throwable $e) {
                // leave as-is
            }
        }

        if ($this->session) {
            // If we're updating an existing inactive session (draft), activate it
            $activeToSave = $this->session->active == 0 ? 1 : ($this->active ?? $this->session->active);

            $this->session->update([
                'name' => $this->name,
                'observerid' => $this->observer,
                'locationid' => $this->locationid,
                'begindate' => $this->begindate,
                'enddate' => $this->enddate,
                'weather' => $this->weather,
                'equipment' => $this->equipment,
                'comments' => $this->comments,
                'active' => $activeToSave,
            ]);

            // Keep component state in sync
            $this->session->active = $activeToSave;
            $this->active = $activeToSave;

            // Sync legacy sessionObservers pivot table
            try {
                DB::table('sessionObservers')->where('sessionid', $this->session->id)->delete();
                $rows = [];
                if (! empty($this->otherObservers) && is_array($this->otherObservers)) {
                    foreach (array_values(array_unique($this->otherObservers)) as $uname) {
                        // skip primary observer
                        if ($uname === $this->observer) {
                            continue;
                        }
                        $rows[] = ['sessionid' => $this->session->id, 'observer' => $uname];
                    }
                }
                if (! empty($rows)) {
                    DB::table('sessionObservers')->insert($rows);
                }
            } catch (\Throwable $e) {
                // non-fatal: log and continue
                report($e);
            }

            // Handle photo upload for update: store on the public disk and persist path to DB
            try {
                if ($this->photo) {
                    $ext = $this->photo->getClientOriginalExtension() ?: 'jpg';
                    $filename = $this->session->id.'.'.$ext;

                    // Remove any previous storage-based image path on this session
                    try {
                        if (! empty($this->session->picture)) {
                            $old = 'public/'.ltrim($this->session->picture, '/');
                            if (\Storage::exists($old)) {
                                \Storage::delete($old);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }

                    // Store file to storage/app/public/photos/sessions/{filename}
                    $path = $this->photo->storePubliclyAs('photos/sessions', $filename, 'public');

                    // Persist path on model (so SessionController can use asset('storage/...'))
                    $this->session->picture = $path;
                    $this->session->save();
                }
            } catch (\Throwable $e) {
                report($e);
            }

            session()->flash('message', __('Session updated successfully.'));

            $user = User::where('username', $this->session->observerid)->first();
            $userSlug = $user ? $user->slug : $this->session->observerid;
            $sessionParam = $this->session->slug ?? $this->session->id;

            return redirect()->route('session.show', [$userSlug, $sessionParam]);
        }

        $session = ObservationSession::create([
            'name' => $this->name,
            'observerid' => $this->observer,
            'locationid' => $this->locationid,
            'begindate' => $this->begindate,
            'enddate' => $this->enddate,
            'weather' => $this->weather,
            'equipment' => $this->equipment,
            'comments' => $this->comments,
            'language' => 'en',
            'active' => $this->active,
        ]);

        // Persist uploaded photo for the new session (only creator's session gets the image)
        try {
            if ($this->photo) {
                $ext = $this->photo->getClientOriginalExtension() ?: 'jpg';
                $filename = $session->id.'.'.$ext;
                $path = $this->photo->storePubliclyAs('photos/sessions', $filename, 'public');
                $session->picture = $path;
                $session->save();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // Persist other observers to legacy pivot table
        try {
            DB::table('sessionObservers')->where('sessionid', $session->id)->delete();
            $rows = [];
            if (! empty($this->otherObservers) && is_array($this->otherObservers)) {
                foreach (array_values(array_unique($this->otherObservers)) as $uname) {
                    if ($uname === $this->observer) {
                        continue;
                    }
                    $rows[] = ['sessionid' => $session->id, 'observer' => $uname];
                }
            }
            if (! empty($rows)) {
                DB::table('sessionObservers')->insert($rows);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // Dispatch job to build per-user copies and populate sessionObservations asynchronously
        try {
            $participants = array_values(array_unique(array_filter(array_merge([$this->observer], is_array($this->otherObservers) ? $this->otherObservers : []))));
            BuildSessionCopies::dispatch($session->id, $participants);
        } catch (\Throwable $e) {
            report($e);
        }

        // Dispatch invitation messages to participants (asynchronous)
        // Do not send an invitation to the creator of the session — only to the other observers.
        try {
            $sender = auth()->user()->username ?? ($this->observer ?? 'admin');
            $creator = auth()->user() ? auth()->user()->username : null;
            // Exclude the creator from invitees (if we can determine the creator)
            $invitees = $participants;
            if (! empty($creator)) {
                $invitees = array_values(array_filter($participants, function ($u) use ($creator) {
                    return $u !== $creator;
                }));
            }

            if (! empty($invitees)) {
                SendSessionInvitations::dispatch($session->id, $invitees, $sender);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        session()->flash('message', __('Session created successfully.'));

        $user = User::where('username', $session->observerid)->first();
        $userSlug = $user ? $user->slug : $session->observerid;
        $sessionParam = $session->slug ?? $session->id;

        return redirect()->route('session.show', [$userSlug, $sessionParam]);
    }
}
