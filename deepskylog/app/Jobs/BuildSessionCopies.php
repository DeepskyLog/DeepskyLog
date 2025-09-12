<?php

namespace App\Jobs;

use App\Models\CometObservationsOld;
use App\Models\ObservationSession;
use App\Models\ObservationsOld;
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
            if (! $session) {
                return;
            }

            // Determine date range in legacy YYYYMMDD format (observations use yyyymmdd integers)
            $startYmd = null;
            $endYmd = null;
            try {
                if (! empty($session->begindate)) {
                    $startYmd = Carbon::parse($session->begindate)->format('Ymd');
                }
            } catch (\Throwable $e) {
                $startYmd = null;
            }
            try {
                if (! empty($session->enddate)) {
                    $endYmd = Carbon::parse($session->enddate)->format('Ymd');
                }
            } catch (\Throwable $e) {
                $endYmd = null;
            }

            // collect observation ids for participants, filtered by session date range when provided
            $obsDeepQuery = ObservationsOld::whereIn('observerid', $this->participants);
            $obsCometQuery = CometObservationsOld::whereIn('observerid', $this->participants);
            if ($startYmd) {
                $obsDeepQuery->where('date', '>=', $startYmd);
                $obsCometQuery->where('date', '>=', $startYmd);
            }
            if ($endYmd) {
                $obsDeepQuery->where('date', '<=', $endYmd);
                $obsCometQuery->where('date', '<=', $endYmd);
            }

            $obsDeep = $obsDeepQuery->pluck('id')->toArray();
            $obsComet = $obsCometQuery->pluck('id')->toArray();
            $allObsIds = array_values(array_unique(array_merge($obsDeep, $obsComet)));

            // Helper: write sessionObservations
            $writeSessionObservations = function ($sid, $ids) {
                DB::table('sessionObservations')->where('sessionid', $sid)->delete();
                $rows = [];
                foreach ($ids as $oid) {
                    $rows[] = ['sessionid' => $sid, 'observationid' => $oid];
                }
                if (! empty($rows)) {
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
            if (! empty($rows)) {
                DB::table('sessionObservers')->insert($rows);
            }

            // Write observations for primary
            if (! empty($allObsIds)) {
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
                if (! empty($copyRows)) {
                    DB::table('sessionObservers')->where('sessionid', $copy->id)->delete();
                    DB::table('sessionObservers')->insert($copyRows);
                }

                // observations for copy: only this participant's observations within the session date range
                $pObsDeepQuery = ObservationsOld::where('observerid', $p);
                $pObsCometQuery = CometObservationsOld::where('observerid', $p);
                if ($startYmd) {
                    $pObsDeepQuery->where('date', '>=', $startYmd);
                    $pObsCometQuery->where('date', '>=', $startYmd);
                }
                if ($endYmd) {
                    $pObsDeepQuery->where('date', '<=', $endYmd);
                    $pObsCometQuery->where('date', '<=', $endYmd);
                }
                $pObsDeep = $pObsDeepQuery->pluck('id')->toArray();
                $pObsComet = $pObsCometQuery->pluck('id')->toArray();
                $pObsIds = array_values(array_unique(array_merge($pObsDeep, $pObsComet)));

                if (! empty($pObsIds)) {
                    $writeSessionObservations($copy->id, $pObsIds);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
