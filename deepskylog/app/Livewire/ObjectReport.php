<?php

namespace App\Livewire;

use App\Jobs\GenerateObjectReportPdf;
use App\Models\DeepskyObject;
use App\Models\ReportLayout;
use App\Services\ReportLayoutCatalog;
use App\Support\EphemerisDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The object report page: pick a layout -- a DeepskyLog default, one of your
 * own or, if you like, one of another observer -- change it as you see fit,
 * save it under your name and have a queue worker turn the objects into a
 * PDF with it.
 */
class ObjectReport extends Component
{
    /**
     * A report has a few lines per object, so it can hold many more objects
     * than an object set; the ephemerides through the year are what limits it.
     */
    public const MAX_OBJECTS = 2000;

    /** How many of a user's recent reports the page keeps track of. */
    private const RECENT_REPORTS = 20;

    /** The layout id of the built-in default, when none have been imported. */
    public const BUILT_IN = 0;

    #[Locked]
    public string $title = '';

    #[Locked]
    public int $total = 0;

    #[Locked]
    public ?int $listId = null;

    #[Locked]
    public string $filename = 'report';

    /** @var array<int, string> */
    #[Locked]
    public array $objectNames = [];

    public ?int $layoutId = null;

    public bool $showAll = false;

    /** @var array<string, string> */
    public array $settings = [];

    /** @var array<string, array<string, mixed>> */
    public array $fields = [];

    public string $reportTitle = '';

    public bool $sort = false;

    public string $newName = '';

    /** @var array<int, string> */
    #[Locked]
    public array $tokens = [];

    /**
     * @param  Collection<int, DeepskyObject>|array<int, DeepskyObject>  $objects
     */
    public function mount($objects = [], string $title = '', int $total = 0, ?int $listId = null, string $filename = 'report'): void
    {
        $user = auth()->user();

        $this->title = $title;
        $this->total = $total;
        $this->listId = $listId;
        $this->filename = $filename;
        $this->reportTitle = $title;
        $this->objectNames = collect($objects)->map(fn (DeepskyObject $o) => (string) $o->name)->values()->all();

        $this->loadLayout($this->fallbackLayoutId());

        $this->tokens = collect(Cache::get(GenerateObjectReportPdf::recentKey((int) $user->id), []))
            ->filter(fn ($token) => is_string($token) && Cache::has(GenerateObjectReportPdf::statusKey($token)))
            ->values()
            ->all();
    }

    public function updatedLayoutId($value): void
    {
        $this->loadLayout(is_numeric($value) ? (int) $value : self::BUILT_IN);
    }

    public function updatedShowAll(): void
    {
        // Leaving "show all" while another observer's layout is chosen keeps
        // it in the editor, but the list no longer offers it; fall back.
        if (! $this->showAll && $this->layoutId !== null && $this->layoutId !== self::BUILT_IN && ! $this->ownsOrDefault($this->layoutId)) {
            $this->loadLayout($this->fallbackLayoutId());
        }
    }

    /**
     * Show or leave out a field: a field is shown when it has a width.
     */
    public function toggleField(string $name): void
    {
        if (! isset($this->fields[$name])) {
            return;
        }

        $this->fields[$name]['width'] = (float) ($this->fields[$name]['width'] ?? 0) > 0 ? 0 : 20;
    }

    /**
     * Save the layout under your name: over your own layout, or as your copy
     * of a default or of another observer's layout.
     */
    public function save(): void
    {
        $this->store($this->currentLayout()?->name ?? __('My layout'));
    }

    public function saveAs(): void
    {
        $this->validate(['newName' => 'required|string|max:100'], [], ['newName' => __('Layout name')]);

        $this->store(trim($this->newName));
        $this->newName = '';
    }

    public function delete(): void
    {
        $layout = $this->currentLayout();
        if ($layout === null || (int) $layout->user_id !== (int) auth()->id()) {
            return;
        }

        $layout->delete();
        session()->flash('report-layout', __('Layout ":name" deleted.', ['name' => $layout->name]));

        $this->loadLayout($this->fallbackLayoutId());
    }

    /**
     * Queue the report with the layout as it is in the editor.
     */
    public function generate(): void
    {
        if ($this->objectNames === []) {
            $this->addError('generate', __('There are no objects to report on.'));

            return;
        }

        $layout = ReportLayoutCatalog::normalize(['settings' => $this->settings, 'fields' => $this->fields]);
        if (! collect($layout['fields'])->contains(fn (array $f) => $f['width'] > 0)) {
            $this->addError('generate', __('The layout shows no fields: give at least one field a width.'));

            return;
        }

        $user = auth()->user();
        if (($refusal = GenerateObjectReportPdf::refusal((int) $user->id)) !== null) {
            $this->addError('generate', $refusal);

            return;
        }

        $token = (string) Str::uuid();
        $title = trim($this->reportTitle);
        $filename = Str::slug($this->filename) . '.pdf';

        Cache::put(GenerateObjectReportPdf::statusKey($token), [
            'state' => 'queued',
            'user_id' => $user->id,
            'label' => ($title !== '' ? $title . ' - ' : '') . trans_choice(':count object|:count objects', count($this->objectNames), ['count' => count($this->objectNames)]),
            'filename' => $filename,
            'created_at' => now()->toIso8601String(),
            'total' => count($this->objectNames),
        ], now()->addHours(GenerateObjectReportPdf::KEEP_HOURS));

        GenerateObjectReportPdf::dispatch($token, (int) $user->id, [
            'layout' => $layout,
            'objects' => $this->objectNames,
            'title' => $title,
            'sort' => $this->sort,
            'date' => $this->ephemerisDate(),
            'list_id' => $this->listId,
        ]);
        GenerateObjectReportPdf::recordQueued((int) $user->id);

        $this->tokens = GenerateObjectReportPdf::forgetUnfinished((int) $user->id, $filename, $this->tokens);
        array_unshift($this->tokens, $token);
        $this->tokens = array_slice(array_values(array_unique($this->tokens)), 0, self::RECENT_REPORTS);
        Cache::put(GenerateObjectReportPdf::recentKey((int) $user->id), $this->tokens, now()->addHours(GenerateObjectReportPdf::KEEP_HOURS));

        $this->resetErrorBag('generate');
    }

    public function render()
    {
        $userId = (int) auth()->id();

        $jobs = collect($this->tokens)
            ->map(function (string $token) use ($userId) {
                $status = Cache::get(GenerateObjectReportPdf::statusKey($token));

                return is_array($status) && (int) ($status['user_id'] ?? 0) === $userId
                    ? ['token' => $token] + $status
                    : null;
            })
            ->filter()
            ->values();

        // The observer sees them finish here and needs no message.
        GenerateObjectReportPdf::watch($jobs);

        $defaults = ReportLayout::defaults()->orderBy('name')->get(['id', 'name']);
        $current = $this->currentLayout();

        return view('livewire.object-report', [
            'jobs' => $jobs,
            'pending' => $jobs->contains(fn (array $job) => in_array($job['state'] ?? '', ['queued', 'processing'], true)),
            'defaults' => $defaults,
            'mine' => ReportLayout::where('user_id', $userId)->orderBy('name')->get(['id', 'name']),
            'others' => $this->showAll
                ? ReportLayout::whereNotNull('user_id')->where('user_id', '!=', $userId)
                    ->with('user:id,name')->get(['id', 'name', 'user_id'])
                    ->sortBy(fn (ReportLayout $l) => mb_strtolower(($l->user?->name ?? '') . "\0" . $l->name))
                    ->values()
                : collect(),
            'ownsCurrent' => $current !== null && (int) $current->user_id === $userId,
            'settingLabels' => ReportLayoutCatalog::settingLabels(),
            'fieldLabels' => ReportLayoutCatalog::fieldLabels(),
        ]);
    }

    private function loadLayout(int $id): void
    {
        $layout = $id !== self::BUILT_IN ? ReportLayout::find($id) : null;

        if ($layout !== null && ! $this->ownsOrDefault($layout->id) && ! $this->showAll) {
            // Another observer's layout is only offered with "show all".
            $this->showAll = true;
        }

        $definition = $layout?->definition() ?? ReportLayoutCatalog::builtInDefault();

        $this->layoutId = $layout?->id ?? self::BUILT_IN;
        $this->settings = $definition['settings'];
        $this->fields = $definition['fields'];
        $this->resetErrorBag();
    }

    private function store(string $name): void
    {
        $definition = ReportLayoutCatalog::normalize(['settings' => $this->settings, 'fields' => $this->fields]);

        $layout = ReportLayout::updateOrCreate(
            ['user_id' => auth()->id(), 'name' => $name],
            $definition
        );
        $layout->touch();

        $this->layoutId = $layout->id;
        $this->settings = $definition['settings'];
        $this->fields = $definition['fields'];
        session()->flash('report-layout', __('Layout ":name" saved.', ['name' => $name]));
    }

    /**
     * Your most recently saved layout, or else the first DeepskyLog default.
     */
    private function fallbackLayoutId(): int
    {
        return ReportLayout::where('user_id', auth()->id())->orderByDesc('updated_at')->value('id')
            ?? ReportLayout::defaults()->orderBy('name')->value('id')
            ?? self::BUILT_IN;
    }

    private function currentLayout(): ?ReportLayout
    {
        return $this->layoutId !== null && $this->layoutId !== self::BUILT_IN
            ? ReportLayout::find($this->layoutId)
            : null;
    }

    private function ownsOrDefault(int $id): bool
    {
        return ReportLayout::whereKey($id)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->exists();
    }

    private function ephemerisDate(): string
    {
        return EphemerisDate::current();
    }
}
