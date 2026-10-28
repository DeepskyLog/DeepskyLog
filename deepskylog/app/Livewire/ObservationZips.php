<?php

namespace App\Livewire;

use App\Jobs\GenerateObservationsZip;
use App\Services\Observations\ObservationZipBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The page that queues a zip of observations with their drawings
 * (GenerateObservationsZip) and lists the observer's recent zips.
 */
class ObservationZips extends Component
{
    /** How many of a user's recent zips the page keeps track of. */
    private const RECENT_ZIPS = 20;

    /**
     * Which observations: see ObservationZipBuilder::query(). Set by the
     * controller, which checks that the observer may have them.
     *
     * @var array<string, mixed>
     */
    #[Locked]
    public array $source = [];

    #[Locked]
    public string $title = '';

    /** @var array{observations: int, drawings: int} */
    #[Locked]
    public array $counts = ['observations' => 0, 'drawings' => 0];

    public bool $drawingsOnly = false;

    /** @var array<int, string> */
    #[Locked]
    public array $tokens = [];

    public function mount(array $source, string $title, bool $drawingsOnly = false): void
    {
        $this->source = $source;
        $this->title = $title;
        $this->drawingsOnly = $drawingsOnly;
        $this->counts = ObservationZipBuilder::counts(ObservationZipBuilder::query($source));

        $this->tokens = collect(Cache::get(GenerateObservationsZip::recentKey((int) auth()->id()), []))
            ->filter(fn ($token) => is_string($token) && Cache::has(GenerateObservationsZip::statusKey($token)))
            ->values()
            ->all();
    }

    public function generate(): void
    {
        $counts = $this->selectedCounts();
        if (($refusal = ObservationZipBuilder::refusal($counts)) !== null) {
            $this->addError('generate', $refusal);

            return;
        }

        $userId = (int) auth()->id();
        if (($refusal = GenerateObservationsZip::refusal($userId)) !== null) {
            $this->addError('generate', $refusal);

            return;
        }

        $source = $this->source + ['drawings_only' => $this->drawingsOnly];
        $token = (string) Str::uuid();
        $filename = ObservationZipBuilder::filenameFor($source);

        Cache::put(GenerateObservationsZip::statusKey($token), [
            'state' => 'queued',
            'user_id' => $userId,
            'label' => $this->title . ': ' . $this->countsLabel($counts),
            'filename' => $filename,
            'created_at' => now()->toIso8601String(),
        ], now()->addHours(GenerateObservationsZip::KEEP_HOURS));

        GenerateObservationsZip::dispatch($token, $userId, $source);
        GenerateObservationsZip::recordQueued($userId);

        $this->tokens = GenerateObservationsZip::forgetUnfinished($userId, $filename, $this->tokens);
        array_unshift($this->tokens, $token);
        $this->tokens = array_slice(array_values(array_unique($this->tokens)), 0, self::RECENT_ZIPS);
        Cache::put(GenerateObservationsZip::recentKey($userId), $this->tokens, now()->addHours(GenerateObservationsZip::KEEP_HOURS));

        $this->resetErrorBag('generate');
    }

    public function render()
    {
        $userId = (int) auth()->id();

        $jobs = collect($this->tokens)
            ->map(function (string $token) use ($userId) {
                $status = Cache::get(GenerateObservationsZip::statusKey($token));

                return is_array($status) && (int) ($status['user_id'] ?? 0) === $userId
                    ? ['token' => $token] + $status
                    : null;
            })
            ->filter()
            ->values();

        // The observer sees the result here and needs no message about it.
        GenerateObservationsZip::markSeen($jobs);

        $counts = $this->selectedCounts();

        return view('livewire.observation-zips', [
            'jobs' => $jobs,
            'pending' => $jobs->contains(fn (array $job) => in_array($job['state'] ?? '', ['queued', 'processing'], true)),
            'countsLabel' => $this->countsLabel($counts),
            'refusal' => ObservationZipBuilder::refusal($counts),
        ]);
    }

    /**
     * @return array{observations: int, drawings: int}
     */
    private function selectedCounts(): array
    {
        return $this->drawingsOnly
            ? ['observations' => $this->counts['drawings'], 'drawings' => $this->counts['drawings']]
            : $this->counts;
    }

    /**
     * @param  array{observations: int, drawings: int}  $counts
     */
    private function countsLabel(array $counts): string
    {
        return trans_choice(':count observation|:count observations', $counts['observations'], ['count' => number_format($counts['observations'])])
            . ', ' . trans_choice(':count drawing|:count drawings', $counts['drawings'], ['count' => number_format($counts['drawings'])]);
    }
}
