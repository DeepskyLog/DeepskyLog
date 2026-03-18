<?php

namespace App\Livewire;

use App\Models\DeepskyObject;
use App\Models\ObservationsOld;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DeleteObjectModal extends Component
{
    public string $objectSlug = '';
    public string $objectName = '';
    public bool $showModal = false;
    public int $observationsCount = 0;
    public int $observingListCount = 0;
    public string $moveToSearch = '';
    public string $moveToSlug = '';
    public string $moveToLabel = '';
    public array $searchResults = [];

    public function mount(string $objectSlug, string $objectName): void
    {
        $this->objectSlug = $objectSlug;
        $this->objectName = $objectName;
    }

    public function openModal(): void
    {
        $this->observationsCount = ObservationsOld::getObservationsCountForObject($this->objectName);
        $this->observingListCount = DB::connection('mysqlOld')
            ->table('observerobjectlist')
            ->where('objectname', $this->objectName)
            ->count();
        $this->moveToSearch = '';
        $this->moveToSlug = '';
        $this->moveToLabel = '';
        $this->searchResults = [];
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function updatedMoveToSearch(string $value): void
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }

        $results = DB::table('objects')
            ->where(function ($q) use ($value) {
                $q->where('name', 'LIKE', "%{$value}%")
                    ->orWhere('slug', 'LIKE', "%{$value}%");
            })
            ->whereNot('name', $this->objectName)
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$value}%"])
            ->orderBy('name')
            ->limit(15)
            ->get(['name', 'slug']);

        // Also search objectnames/aliases
        $aliasResults = DB::table('objectnames')
            ->where('altname', 'LIKE', "%{$value}%")
            ->whereNot('objectname', $this->objectName)
            ->orderByRaw("CASE WHEN altname LIKE ? THEN 0 ELSE 1 END", ["{$value}%"])
            ->orderBy('altname')
            ->limit(15)
            ->get(['objectname', 'slug', 'altname']);

        $combined = collect();
        foreach ($results as $row) {
            $combined->push([
                'label' => $row->name,
                'slug' => $row->slug ?? $row->name,
                'starts_with' => (stripos($row->name, $value) === 0) ? 0 : 1,
            ]);
        }
        foreach ($aliasResults as $row) {
            $combined->push([
                'label' => $row->altname . ' (' . $row->objectname . ')',
                'slug' => $row->slug ?? $row->objectname,
                'starts_with' => (stripos($row->altname, $value) === 0) ? 0 : 1,
            ]);
        }

        $this->searchResults = $combined
            ->unique('slug')
            ->sortBy([['starts_with', 'asc'], ['label', 'asc']])
            ->take(10)
            ->map(fn($r) => ['label' => $r['label'], 'slug' => $r['slug']])
            ->values()
            ->toArray();
    }

    public function selectTarget(string $slug, string $label): void
    {
        $this->moveToSlug = $slug;
        $this->moveToLabel = $label;
        $this->moveToSearch = $label;
        $this->searchResults = [];
    }

    public function clearTarget(): void
    {
        $this->moveToSlug = '';
        $this->moveToLabel = '';
        $this->moveToSearch = '';
        $this->searchResults = [];
    }

    public function render()
    {
        return view('livewire.delete-object-modal');
    }
}
