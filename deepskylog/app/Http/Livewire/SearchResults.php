<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\ActiveObservingListService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SearchResults extends Component
{
    public $q;
    public $results = [];
    public array $objectRefs = [];
    // An observation search (['q' => ..., 'filters' => [...]]) whose observed
    // objects are listed; matched in the database by the results table.
    public ?array $observationSearch = null;
    public ?string $title = null;
    public bool $canModifyActiveList = false;
    public bool $fixedResultsMode = false;

    public function mount($q = '', $objectRefs = [], $title = null, $observationSearch = null)
    {
        $this->q = $q;
        $this->objectRefs = is_array($objectRefs) ? $objectRefs : [];
        $this->observationSearch = is_array($observationSearch) ? $observationSearch : null;
        $this->title = is_string($title) && $title !== '' ? $title : null;
        $this->fixedResultsMode = $this->objectRefs !== [] || $this->observationSearch !== null;

        if (!$this->fixedResultsMode) {
            $this->performSearch();
        }

        $this->resolveCanModifyActiveList();
    }

    private function resolveCanModifyActiveList(): void
    {
        try {
            $user = auth()->user();
            if (!$user) {
                $this->canModifyActiveList = false;
                return;
            }

            /** @var ActiveObservingListService $svc */
            $svc = app(ActiveObservingListService::class);
            $activeList = $svc->getActiveList($user);
            $this->canModifyActiveList = !$activeList || $user->can('addItem', $activeList);
        } catch (\Throwable $_) {
            $this->canModifyActiveList = false;
        }
    }

    protected function performSearch()
    {
        $q = trim($this->q);
        if ($q === '') {
            $this->results = [];
            return;
        }

        // Build LIKE pattern from wildcard query. Treat a trailing wildcard
        // (e.g. "M *") as a prefix search so it becomes "M %" instead of
        // a contains search that would match substrings like "bochuM 1".
        $like = null;
        if (strpos($q, '*') !== false) {
            $trimQ = trim($q);
            // leading wildcard => ends-with (suffix match)
            if (preg_match('/^\*/', $trimQ)) {
                $like = '%' . ltrim(str_replace('*', '%', $trimQ), '%');
            } elseif (preg_match('/\*\s*$/', $trimQ)) {
                // trailing wildcard => prefix; preserve whitespace in base so
                // `M *` becomes `M %` (matches names starting with `M `)
                $base = preg_replace('/\*+\s*$/', '', $trimQ);
                $like = $base . '%';
            } else {
                // internal wildcard => contains
                $like = '%' . str_replace('*', '%', $trimQ) . '%';
            }
        } else {
            // no wildcard: default to contains search
            $like = '%' . $q . '%';
        }

        // Prefer search_index if exists
        try {
            if (DB::getSchemaBuilder()->hasTable('search_index')) {
                $this->results = DB::table('search_index')
                    ->where('name', 'like', $like)
                    ->orderBy('name')
                    ->limit(200)
                    ->get()
                    ->toArray();
                return;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Fallback: search objects and aliases
        $objs = DB::table('objects')
            ->select(['name as title', 'slug', DB::raw("'object' as source_type")])
            ->where('name', 'like', $like)
            ->limit(200)
            ->get()
            ->toArray();

        $this->results = $objs;
    }

    public function render()
    {
        return view('livewire.search-results');
    }
}
