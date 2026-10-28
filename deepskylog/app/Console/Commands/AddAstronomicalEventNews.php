<?php

namespace App\Console\Commands;

use App\Models\NewsItem;
use App\Services\News\AstronomicalEvents;
use Illuminate\Console\Command;

/**
 * Adds a news item (#960) for each astronomical event of the coming days
 * (App\Services\News\AstronomicalEvents: eclipses, close conjunctions,
 * oppositions, greatest elongations, meteor showers, shadows on Jupiter and
 * Saturn), published PUBLISH_DAYS_BEFORE days before the event (or those of
 * PUBLISH_DAYS_BEFORE_KIND) and expiring a day after it. An
 * item is found again by the event's key, so a later run only updates it and
 * keeps it hidden when an administrator hid it; a scheduled item whose event
 * is no longer found is removed.
 */
class AddAstronomicalEventNews extends Command
{
    public const PUBLISH_DAYS_BEFORE = 7;

    /**
     * Kinds published closer to the event: in the months Io and Europa are in
     * step there are two shadows on Jupiter every three and a half days.
     */
    public const PUBLISH_DAYS_BEFORE_KIND = ['double_shadow_transit' => 2];

    protected $signature = 'news:astronomical-events
        {--apply : Write the news items}
        {--days=45 : How many days ahead to look}';

    protected $description = 'Add news items for the coming eclipses, conjunctions, oppositions, elongations and meteor showers';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the news items.');
        }

        // Whole days, so every run samples the same hours and finds the same moments.
        $from   = now('UTC')->startOfDay();
        $to     = $from->copy()->addDays(max(1, (int) $this->option('days')));
        $events = (new AstronomicalEvents())->between($from->timestamp, $to->timestamp);

        $existing = NewsItem::whereIn('event_key', array_column($events, 'key'))->get()->keyBy('event_key');
        $added    = 0;
        $updated  = 0;
        $rows     = [];
        foreach ($events as $event) {
            $at     = now('UTC')->setTimestamp($event['at']);
            $values = [
                'kind'         => $event['kind'],
                'event_at'     => $at,
                'data'         => $event['data'],
                'published_at' => $at->copy()->subDays(self::PUBLISH_DAYS_BEFORE_KIND[$event['kind']] ?? self::PUBLISH_DAYS_BEFORE),
                'expires_at'   => $at->copy()->addDay(),
            ];
            $rows[] = [$at->format('Y-m-d H:i'), $event['key']];

            $item = $existing[$event['key']] ?? null;
            if ($item === null) {
                $added++;
                if ($apply) {
                    NewsItem::create($values + ['event_key' => $event['key']]);
                }

                continue;
            }

            $item->fill($values);
            if ($item->isDirty()) {
                $updated++;
                if ($apply) {
                    $item->save();
                }
            }
        }

        // Scheduled items of this period whose event was not found again.
        $stale = NewsItem::where('kind', '!=', NewsItem::MANUAL)
            ->whereBetween('event_at', [$from, $to])
            ->where('published_at', '>', now())
            ->whereNotIn('event_key', array_column($events, 'key'));
        $removed = $stale->count();
        if ($apply) {
            $stale->delete();
        }

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Moment (UT)', 'Event'], $rows);
        }

        $this->info(($apply ? 'Added: ' : 'To add: ') . $added);
        $this->info(($apply ? 'Updated: ' : 'To update: ') . $updated);
        $this->info(($apply ? 'Removed: ' : 'To remove: ') . $removed);

        return self::SUCCESS;
    }
}
