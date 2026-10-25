<?php

namespace App\Livewire;

use App\Models\CompleteCatalog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * /admin/complete-catalogs: the catalogs observers can't add objects to,
 * managed by administrators and database experts, with the number of
 * designations each has in DeepskyLog.
 */
class CompleteCatalogs extends Component
{
    public string $catalog = '';

    public function add(): void
    {
        Gate::authorize('manage_catalogs', User::class);

        $this->validate([
            'catalog' => ['required', 'string', 'max:50', 'exists:objectnames,catalog', 'unique:complete_catalogs,catalog'],
        ]);

        CompleteCatalog::create(['catalog' => $this->catalog]);
        $this->catalog = '';
    }

    public function remove(int $id): void
    {
        Gate::authorize('manage_catalogs', User::class);

        CompleteCatalog::whereKey($id)->first()?->delete();
    }

    public function render()
    {
        Gate::authorize('manage_catalogs', User::class);

        $complete = CompleteCatalog::query()->orderBy('catalog')->get();
        $codes = $complete->pluck('catalog')->map(fn ($code) => mb_strtolower($code))->all();

        $catalogs = DB::table('objectnames')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog')
            ->reject(fn ($code) => in_array(mb_strtolower($code), $codes, true))
            ->values();

        $designations = DB::table('objectnames')
            ->whereIn('catalog', $complete->pluck('catalog'))
            ->groupBy('catalog')
            ->selectRaw('catalog, COUNT(DISTINCT catindex) AS n')
            ->pluck('n', 'catalog')
            ->mapWithKeys(fn ($n, $code) => [mb_strtolower($code) => (int) $n]);

        return view('livewire.complete-catalogs', [
            'complete' => $complete,
            'catalogs' => $catalogs,
            'designations' => $designations,
        ]);
    }
}
