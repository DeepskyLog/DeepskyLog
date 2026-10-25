<?php

namespace App\Observers;

use App\Models\Observation;
use App\Models\Instrument;
use App\Models\Eyepiece;
use App\Models\Lens;
use App\Models\Filter;
use App\Models\Location;
use App\Jobs\TranslateObservationDescriptions;
use App\Services\ObjectObservationStatistics;
use App\Services\Observations\DescriptionTranslations;
use App\Services\SessionObservationLinker;
use Illuminate\Support\Facades\Cache;

class ObservationObserver
{
    public function created(Observation $obs): void
    {
        if ($obs->instrument_id)
            Instrument::where('id', $obs->instrument_id)->increment('observations');
        if ($obs->eyepiece_id)
            Eyepiece::where('id', $obs->eyepiece_id)->increment('observations');
        if ($obs->lens_id)
            Lens::where('id', $obs->lens_id)->increment('observations');
        if ($obs->filter_id)
            Filter::where('id', $obs->filter_id)->increment('observations');
        if ($obs->location_id)
            Location::where('id', $obs->location_id)->increment('observations');

        $this->syncSessions($obs);
        $this->refreshObjectStatistics($obs);

        // Invalidate homepage sketch/observation caches so lists refresh quickly
        // (include non-drawing observations so the "20 newest observations" list updates)
        $this->forgetHomepageCaches();
    }

    public function deleted(Observation $obs): void
    {
        if ($obs->instrument_id)
            Instrument::where('id', $obs->instrument_id)->where('observations', '>', 0)->decrement('observations');
        if ($obs->eyepiece_id)
            Eyepiece::where('id', $obs->eyepiece_id)->where('observations', '>', 0)->decrement('observations');
        if ($obs->lens_id)
            Lens::where('id', $obs->lens_id)->where('observations', '>', 0)->decrement('observations');
        if ($obs->filter_id)
            Filter::where('id', $obs->filter_id)->where('observations', '>', 0)->decrement('observations');
        if ($obs->location_id)
            Location::where('id', $obs->location_id)->where('observations', '>', 0)->decrement('observations');

        try {
            SessionObservationLinker::forgetObservation((int) $obs->id);
        } catch (\Throwable $e) {
            report($e);
        }

        $this->refreshObjectStatistics($obs);

        // Deleted observation -> invalidate homepage caches so lists refresh
        $this->forgetHomepageCaches();
    }

    public function restored(Observation $obs): void
    {
        $this->created($obs);
    }

    public function updated(Observation $obs): void
    {
        if ($obs->wasChanged(['observed_on', 'observer_username'])) {
            $this->syncSessions($obs);
        }

        if ($obs->wasChanged(['target_type', 'target_name', 'observer_username', 'observed_on', 'has_drawing'])) {
            $this->refreshObjectStatistics($obs);
        }

        if ($obs->wasChanged(['description', 'language'])) {
            $this->retranslate($obs);
        }

        if ($obs->wasChanged('instrument_id')) {
            $old = $obs->getOriginal('instrument_id');
            $new = $obs->instrument_id;
            if ($old)
                Instrument::where('id', $old)->where('observations', '>', 0)->decrement('observations');
            if ($new)
                Instrument::where('id', $new)->increment('observations');
        }

        if ($obs->wasChanged('eyepiece_id')) {
            $old = $obs->getOriginal('eyepiece_id');
            $new = $obs->eyepiece_id;
            if ($old)
                Eyepiece::where('id', $old)->where('observations', '>', 0)->decrement('observations');
            if ($new)
                Eyepiece::where('id', $new)->increment('observations');
        }

        if ($obs->wasChanged('lens_id')) {
            $old = $obs->getOriginal('lens_id');
            $new = $obs->lens_id;
            if ($old)
                Lens::where('id', $old)->where('observations', '>', 0)->decrement('observations');
            if ($new)
                Lens::where('id', $new)->increment('observations');
        }

        if ($obs->wasChanged('filter_id')) {
            $old = $obs->getOriginal('filter_id');
            $new = $obs->filter_id;
            if ($old)
                Filter::where('id', $old)->where('observations', '>', 0)->decrement('observations');
            if ($new)
                Filter::where('id', $new)->increment('observations');
        }

        if ($obs->wasChanged('location_id')) {
            $old = $obs->getOriginal('location_id');
            $new = $obs->location_id;
            if ($old)
                Location::where('id', $old)->where('observations', '>', 0)->decrement('observations');
            if ($new)
                Location::where('id', $new)->increment('observations');
        }

        // The homepage caches the observations themselves, so any edit (the
        // class, the description, the conditions, the drawing, ...) has to
        // refresh its lists of observations and sketches.
        if (array_diff(array_keys($obs->getChanges()), ['updated_at']) !== []) {
            $this->forgetHomepageCaches();
        }
    }

    /**
     * Replace the translations of an edited description (#310). New
     * observations are translated by the scheduled
     * observations:translate-descriptions, newest first; an edited old one
     * would wait there behind all untranslated newer ones, so it is queued
     * here.
     */
    private function retranslate(Observation $obs): void
    {
        try {
            DescriptionTranslations::forget($obs);
            if (!config('app.translate_observations') || trim(strip_tags((string) $obs->description)) === '') {
                return;
            }
            foreach (DescriptionTranslations::siteLanguages() as $language) {
                if ($language !== $obs->language) {
                    TranslateObservationDescriptions::dispatch([$obs->id], $language)->afterCommit();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Link the observation to the sessions of its observer that cover its date.
     */
    private function syncSessions(Observation $obs): void
    {
        try {
            SessionObservationLinker::syncObservation($obs);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Recalculate the statistics of the object tables (seen, last seen, ...)
     * the observation counts for.
     */
    private function refreshObjectStatistics(Observation $obs): void
    {
        try {
            ObjectObservationStatistics::refreshObservation($obs);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Forget a small set of cached homepage sketch/observation pages so the UI refreshes
     * after drawings are created/updated/deleted. We target the common sketch/obs types
     * and the first few pages to avoid brute-forcing the entire cache.
     */
    public static function forgetHomepageCaches(): void
    {
        $types = ['all', 'deepsky', 'comet', 'solarsystem'];
        for ($page = 1; $page <= 3; $page++) {
            foreach ($types as $type) {
                try {
                    Cache::forget("welcome_sketches_page_{$type}_{$page}");
                    Cache::forget("welcome_observations_page_{$type}_{$page}");
                } catch (\Throwable $_) {
                    // ignore cache failures
                }
            }
        }
    }
}
