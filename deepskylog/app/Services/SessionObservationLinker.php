<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\ObservationSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the sessionObservations pivot up to date.
 *
 * A session contains the observations of its owner and of its other observers
 * (sessionObservers) made between the begin and the end date of the session,
 * as in the old DeepskyLog. The per-user copies of a shared session list the
 * other participants as their observers, so they follow the same rule.
 */
class SessionObservationLinker
{
    /**
     * Usernames of the owner and the other observers of a session.
     *
     * @return array<int, string>
     */
    public static function participants(ObservationSession $session): array
    {
        $participants = $session->otherObservers();
        $participants[] = $session->observerid;

        return array_values(array_unique(array_filter($participants)));
    }

    /**
     * Rewrite the observations of one session.
     */
    public static function syncSession(ObservationSession $session): void
    {
        [$start, $end] = self::dateRange($session);

        $ids = [];
        if ($start !== null || $end !== null) {
            $query = DB::table('observations')->whereIn('observer_username', self::participants($session));
            if ($start !== null) {
                $query->whereDate('observed_on', '>=', $start);
            }
            if ($end !== null) {
                $query->whereDate('observed_on', '<=', $end);
            }
            $ids = $query->pluck('id')->all();
        }

        DB::transaction(function () use ($session, $ids) {
            DB::table('sessionObservations')->where('sessionid', $session->id)->delete();
            foreach (array_chunk($ids, 500) as $chunk) {
                DB::table('sessionObservations')->insert(array_map(
                    fn ($id) => ['sessionid' => $session->id, 'observationid' => $id],
                    $chunk
                ));
            }
        });
    }

    /**
     * Link one observation to every session it belongs to, and unlink it from
     * the others (after it was created, or its date or observer changed).
     */
    public static function syncObservation(Observation $observation): void
    {
        $sessionIds = [];
        $date = $observation->observed_on ? Carbon::parse($observation->observed_on)->format('Y-m-d') : null;
        $observer = $observation->observer_username;

        if ($date !== null && !empty($observer)) {
            $sessionIds = ObservationSession::query()
                ->where(function ($q) use ($observer) {
                    $q->where('observerid', $observer)
                        ->orWhereIn('id', DB::table('sessionObservers')->where('observer', $observer)->select('sessionid'));
                })
                ->where(function ($q) {
                    $q->whereNotNull('begindate')->orWhereNotNull('enddate');
                })
                ->where(function ($q) use ($date) {
                    $q->whereNull('begindate')->orWhereDate('begindate', '<=', $date);
                })
                ->where(function ($q) use ($date) {
                    $q->whereNull('enddate')->orWhereDate('enddate', '>=', $date);
                })
                ->pluck('id')
                ->all();
        }

        DB::transaction(function () use ($observation, $sessionIds) {
            self::forgetObservation((int) $observation->id);
            if (!empty($sessionIds)) {
                DB::table('sessionObservations')->insert(array_map(
                    fn ($sid) => ['sessionid' => $sid, 'observationid' => $observation->id],
                    $sessionIds
                ));
            }
        });
    }

    /**
     * Remove an observation from all sessions.
     */
    public static function forgetObservation(int $observationId): void
    {
        DB::table('sessionObservations')->where('observationid', $observationId)->delete();
    }

    /**
     * @return array{0: ?string, 1: ?string} begin and end date as Y-m-d
     */
    private static function dateRange(ObservationSession $session): array
    {
        $format = function ($value): ?string {
            if (empty($value)) {
                return null;
            }
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $_) {
                return null;
            }
        };

        return [$format($session->begindate), $format($session->enddate)];
    }
}
