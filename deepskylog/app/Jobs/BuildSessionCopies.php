<?php

namespace App\Jobs;

use App\Models\ObservationSession;
use Carbon\Carbon;
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
     * Create a new job instance.
     */
    public function __construct(int $sessionId, array $participants)
    {
        $this->sessionId = $sessionId;
        $this->participants = array_values(array_unique(array_filter($participants)));
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

            // Determine date range as SQL dates for the new observations table.
            $startDate = null;
            $endDate = null;
            try {
                if (!empty($session->begindate)) {
                    $startDate = Carbon::parse($session->begindate)->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                $startDate = null;
            }
            try {
                if (!empty($session->enddate)) {
                    $endDate = Carbon::parse($session->enddate)->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                $endDate = null;
            }

            // Collect new observations.id values for all participants filtered by date range.
            $obsQuery = DB::table('observations')
                ->whereIn('observer_username', $this->participants);
            if ($startDate) {
                $obsQuery->where('observed_on', '>=', $startDate);
            }
            if ($endDate) {
                $obsQuery->where('observed_on', '<=', $endDate);
            }
            $allObsIds = $obsQuery->pluck('id')->map(fn($v) => (string) $v)->toArray();

            // Helper: write sessionObservations
            $writeSessionObservations = function ($sid, $ids) {
                DB::table('sessionObservations')->where('sessionid', $sid)->delete();
                $rows = [];
                foreach ($ids as $oid) {
                    $rows[] = ['sessionid' => $sid, 'observationid' => $oid];
                }
                if (!empty($rows)) {
                    DB::table('sessionObservations')->insert($rows);
                }
            };

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

            // Write observations for primary
            if (!empty($allObsIds)) {
                $writeSessionObservations($session->id, $allObsIds);
            }

            // Create per-user copies
            foreach ($this->participants as $p) {
                if ($p === $session->observerid) {
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

                // observations for copy: only this participant's observations within the session date range
                // Collect new observations.id values for this specific participant.
                $pObsQuery = DB::table('observations')
                    ->where('observer_username', $p);
                if ($startDate) {
                    $pObsQuery->where('observed_on', '>=', $startDate);
                }
                if ($endDate) {
                    $pObsQuery->where('observed_on', '<=', $endDate);
                }
                $pObsIds = $pObsQuery->pluck('id')->map(fn($v) => (string) $v)->toArray();

                if (!empty($pObsIds)) {
                    $writeSessionObservations($copy->id, $pObsIds);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
