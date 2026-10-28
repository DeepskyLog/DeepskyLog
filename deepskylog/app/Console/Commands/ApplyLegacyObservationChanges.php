<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\ObservationLike;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Observers\ObservationObserver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Do for the observations the old site (DeepskyLog.old) created, changed or
 * deleted what this site does when it saves one.
 *
 * The old site writes the observations tables with SQL and records each
 * change in legacy_observation_changes (the observation and its values
 * before the change). ObservationObserver then keeps the counters of the
 * equipment and locations, the session links, the object statistics, the
 * cached home page lists and the translations up to date, as if the change
 * had been made here. A deleted observation also loses its likes, comments,
 * sketch of the week/month and stored drawing, as ObservationsController
 * deletes them.
 *
 * Scheduled every minute with --apply.
 */
class ApplyLegacyObservationChanges extends Command
{
    /** Changes handled per run. */
    public const BATCH = 500;

    protected $signature = 'observations:apply-legacy-changes
        {--apply : Process the changes}';

    protected $description = 'Run the observation hooks for the observations the old site created, changed or deleted';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        // All waiting changes of the first observations, so the changes of
        // one observation are handled together.
        $ids = DB::table('legacy_observation_changes')
            ->select('observation_id')
            ->groupBy('observation_id')
            ->orderByRaw('MIN(id)')
            ->limit(self::BATCH)
            ->pluck('observation_id');
        $changes = DB::table('legacy_observation_changes')->whereIn('observation_id', $ids)->orderBy('id')->get();

        if ($this->getOutput()->isVerbose()) {
            $this->table(['Id', 'Observation', 'Action', 'At'], $changes->map(fn ($c) => [$c->id, $c->observation_id, $c->action, $c->created_at])->all());
        }

        if (! $apply) {
            $this->info('Changes waiting: '.DB::table('legacy_observation_changes')->count());
            $this->warn('Dry-run only: nothing was processed. Re-run with --apply.');

            return self::SUCCESS;
        }

        $observer = new ObservationObserver;

        foreach ($changes->groupBy('observation_id') as $id => $group) {
            try {
                $this->applyChanges($observer, (int) $id, $group->values()->all());
            } catch (\Throwable $e) {
                report($e);
            }
            DB::table('legacy_observation_changes')->whereIn('id', $group->pluck('id'))->delete();
        }

        $this->info('Observations processed: '.$ids->count());

        return self::SUCCESS;
    }

    /**
     * Handle the changes of one observation as one change: from the values
     * before the first to the values now. A new form saves an observation
     * and then each of its fields, which would otherwise count it many times.
     *
     * @param  list<object>  $changes  oldest first
     */
    private function applyChanges(ObservationObserver $observer, int $id, array $changes): void
    {
        $first = $changes[0];
        $last = $changes[count($changes) - 1];
        $before = $first->original ? json_decode($first->original, true) : null;

        if ($last->action === 'deleted') {
            $this->forgetDeleted($id, (array) json_decode((string) $last->original, true));
            // Created and deleted again: nothing was counted.
            if ($first->action !== 'created' && $before) {
                $observer->deleted((new Observation)->newFromBuilder($before));
            }

            return;
        }

        $observation = Observation::find($id);
        if (! $observation) {
            return;
        }

        if ($first->action === 'created') {
            $observer->created($observation);

            return;
        }

        // The model holds the values before the changes as its original
        // ones, so wasChanged() tells the observer what the old site changed.
        $current = $observation->getAttributes();
        $observation->setRawAttributes(array_intersect_key((array) $before, $current) + $current, true);
        $observation->setRawAttributes($current);
        $observation->syncChanges();
        $observer->updated($observation);
    }

    /**
     * Remove what belongs to a deleted observation.
     */
    private function forgetDeleted(int $id, array $original): void
    {
        ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)->where('observation_id', $id)->delete();
        ObservationComment::where('observation_type', ObservationComment::TYPE_OBSERVATION)->where('observation_id', $id)->delete();
        SketchOfTheWeek::where('observation_id', $id)->delete();
        SketchOfTheMonth::where('observation_id', $id)->delete();

        $filename = (string) ($original['drawing_filename'] ?? '');
        // The drawings of the old site (drawings/{legacy id}.jpg) stay.
        if ($filename !== '' && empty($original['legacy_id'])) {
            try {
                if (Storage::disk('public')->exists($filename)) {
                    Storage::disk('public')->delete($filename);
                }
            } catch (\Throwable $_) {
            }
        }
    }
}
