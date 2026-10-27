<?php

namespace App\Console\Commands;

use App\Models\DeepskyObject;
use App\Models\NewsItem;
use App\Services\AavsoVariableStars;
use App\Services\News\BrightTransients;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Fetches the latest AAVSO estimates (App\Services\AavsoVariableStars) of the
 * supernovae, the novae and the stars and double stars in observing lists,
 * so their object pages show them at once, and keeps a news item for each
 * supernova or nova that is bright
 * (App\Services\News\BrightTransients). The item is published when the
 * object is first found bright and expires EXPIRES_DAYS after the last run
 * that found it bright; an administrator who hid it keeps it hidden.
 *
 * The other single stars are only identified (cached for a month), which
 * finds the novae among them; at most MAX_IDENTIFY objects not identified
 * yet per run, supernovae and listed stars first, so the AAVSO's firewall is
 * not provoked. The run stops when the AAVSO refuses a request.
 */
class RefreshAavsoEstimates extends Command
{
    public const EXPIRES_DAYS = 3;

    /** The objects not identified yet that one run looks up in VSX. */
    public const MAX_IDENTIFY = 100;

    protected $signature = 'aavso:refresh
        {--apply : Write the news items}';

    protected $description = 'Fetch the latest AAVSO estimates of supernovae, novae and the variable stars in observing lists, and announce the bright ones';

    public function handle(): int
    {
        if (! AavsoVariableStars::enabled()) {
            $this->warn('The AAVSO is turned off (AAVSO=false).');

            return self::SUCCESS;
        }
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the news items.');
        }

        AavsoVariableStars::throttle((int) config('services.aavso.delay', 1000));
        $listed    = DB::table('observing_list_items')->distinct()->pluck('object_name')->flip();
        $objects   = $this->objects($listed->keys());
        $variables = 0;
        $refreshed = 0;
        $rows      = [];
        $added     = 0;
        $updated   = 0;
        $identify  = self::MAX_IDENTIFY;
        foreach ($objects as $object) {
            if (AavsoVariableStars::paused()) {
                $this->warn('The AAVSO refused a request: stopped, the next run continues.');

                break;
            }
            if (! AavsoVariableStars::isIdentified($object) && $identify-- <= 0) {
                continue;
            }
            $variable = AavsoVariableStars::forObject($object);
            if ($variable === null) {
                continue;
            }
            $variables++;
            if (! $listed->has($object->name) && AavsoVariableStars::kind($variable['star']) === 'variable') {
                continue;
            }
            $variable = AavsoVariableStars::refresh($object) ?? $variable;
            $refreshed++;
            $latest = $variable['latest'];
            $rows[] = [$object->name, $variable['star']['name'], $variable['star']['type'], $latest ? $latest['magnitude'] . ' ' . $latest['band'] : '-'];

            $event = BrightTransients::event($object, $variable);
            if ($event === null) {
                continue;
            }
            $item = $this->current($event['kind'], $object);
            if ($item === null) {
                $added++;
                if ($apply) {
                    NewsItem::create($this->values($event, null) + [
                        'event_key'    => $event['kind'] . ':' . $object->slug . ':' . now('UTC')->format('Y-m-d'),
                        'published_at' => now(),
                    ]);
                }

                continue;
            }
            $item->fill($this->values($event, $item));
            if ($item->isDirty()) {
                $updated++;
                if ($apply) {
                    $item->save();
                }
            }
        }

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Object', 'AAVSO', 'Type', 'Latest'], $rows);
        }
        $this->info('Objects: ' . $objects->count() . ', variable: ' . $variables . ', refreshed: ' . $refreshed);
        $this->info(($apply ? 'News items added: ' : 'News items to add: ') . $added);
        $this->info(($apply ? 'News items updated: ' : 'News items to update: ') . $updated);

        return self::SUCCESS;
    }

    /**
     * The supernovae and the stars in observing lists, then the other single stars.
     *
     * @param  Collection<int, string>  $listed
     * @return Collection<int, DeepskyObject>
     */
    private function objects(Collection $listed): Collection
    {
        return DeepskyObject::whereIn('type', ['SNOVA', 'AA1STAR'])
            ->orWhere(fn ($query) => $query->whereIn('type', AavsoVariableStars::TYPES)->whereIn('name', $listed))
            ->orderBy('name')
            ->get()
            ->sortBy(fn (DeepskyObject $object) => $object->type === 'SNOVA' || $listed->contains($object->name) ? 0 : 1)
            ->values();
    }

    /** The news item of the object's outburst that has not expired yet. */
    private function current(string $kind, DeepskyObject $object): ?NewsItem
    {
        return NewsItem::where('kind', $kind)
            ->where('event_key', 'like', $kind . ':' . $object->slug . ':%')
            ->where('expires_at', '>', now())
            ->latest('published_at')
            ->first();
    }

    /**
     * @param  array{kind: string, at: int, data: array<string, mixed>}  $event
     * @return array<string, mixed>
     */
    private function values(array $event, ?NewsItem $item): array
    {
        $data = $event['data'];
        // The brightest the object was while announced
        if ($item !== null && isset($item->data['peak'])) {
            $data['peak'] = min((float) $item->data['peak'], $data['brightest']);
        } else {
            $data['peak'] = $data['brightest'];
        }

        return [
            'kind'       => $event['kind'],
            'event_at'   => now('UTC')->setTimestamp($event['at']),
            'data'       => $data,
            'expires_at' => now()->max(now()->setTimestamp($event['at']))->addDays(self::EXPIRES_DAYS)->startOfHour(),
        ];
    }
}
