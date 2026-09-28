<?php

namespace App\Jobs;

use App\Models\ObservationSession;
use App\Services\SessionObservationLinker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BuildSessionCopies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sessionId;

    public array $participants;

    /**
     * Participants who get a copy of the session; null means all of them.
     */
    public ?array $copiesFor;

    /**
     * Create a new job instance.
     */
    public function __construct(int $sessionId, array $participants, ?array $copiesFor = null)
    {
        $this->sessionId = $sessionId;
        $this->participants = array_values(array_unique(array_filter($participants)));
        $this->copiesFor = $copiesFor === null ? null : array_values(array_unique(array_filter($copiesFor)));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $session = ObservationSession::find($this->sessionId);
            if (!$session) {
                return;
            }

            // Persist sessionObservers for primary session
            DB::table('sessionObservers')->where('sessionid', $session->id)->delete();
            $rows = [];
            foreach ($this->participants as $uname) {
                if ($uname === $session->observerid) {
                    continue;
                }
                $rows[] = ['sessionid' => $session->id, 'observer' => $uname];
            }
            if (!empty($rows)) {
                DB::table('sessionObservers')->insert($rows);
            }

            SessionObservationLinker::syncSession($session);

            // Create per-user copies
            foreach ($this->copiesFor ?? $this->participants as $p) {
                if ($p === $session->observerid || !in_array($p, $this->participants, true)) {
                    continue;
                }

                $exists = ObservationSession::where('observerid', $p)
                    ->where('name', $session->name)
                    ->where('begindate', $session->begindate)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $copy = ObservationSession::create([
                    'name' => $session->name,
                    'observerid' => $p,
                    'slug' => $session->slug,
                    'locationid' => $session->locationid,
                    'begindate' => $session->begindate,
                    'enddate' => $session->enddate,
                    'weather' => $session->weather,
                    'equipment' => $session->equipment,
                    'comments' => $session->comments,
                    'language' => 'en',
                    'active' => 0,
                ]);

                // sessionObservers for copy
                $copyRows = [];
                foreach ($this->participants as $uname) {
                    if ($uname === $p) {
                        continue;
                    }
                    $copyRows[] = ['sessionid' => $copy->id, 'observer' => $uname];
                }
                if (!empty($copyRows)) {
                    DB::table('sessionObservers')->where('sessionid', $copy->id)->delete();
                    DB::table('sessionObservers')->insert($copyRows);
                }

                SessionObservationLinker::syncSession($copy);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
