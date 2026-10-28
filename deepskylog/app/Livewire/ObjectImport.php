<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\Import\ObjectCsvImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * /admin/objects/import: administrators and database experts check a CSV
 * file of objects against DeepskyLog (#131, #136) and then add the new
 * objects, the new names and the differing fields, as they choose. The file
 * is checked by ObjectImportController, which keeps the checked rows in the
 * cache (CACHE_PREFIX . token) for this page, the report and the import.
 */
class ObjectImport extends Component
{
    public const CACHE_PREFIX = 'object_import:';

    public const KEEP_HOURS = 24;

    /** The rows shown on the page per status; the report has all of them. */
    public const MAX_SHOWN = 250;

    #[Url]
    public ?string $token = null;

    public string $show = '';

    public bool $create = true;

    public string $duplicates = 'skip';

    public bool $names = true;

    public bool $update = false;

    public string $datasource = '';

    public ?array $applied = null;

    public bool $expired = false;

    public function mount(): void
    {
        $checked = self::checkedFile($this->token);
        if ($checked === null) {
            $this->expired = $this->token !== null;
            $this->token = null;

            return;
        }

        $this->datasource = mb_substr(pathinfo((string) $checked['file'], PATHINFO_FILENAME), 0, 50);
        $this->show = collect(['invalid', 'duplicate', 'new', 'differences'])
            ->first(fn ($status) => self::rowsWithStatus($checked['rows'], $status) !== []) ?? 'recognised';
    }

    public function import(): void
    {
        Gate::authorize('import_objects', User::class);

        $this->validate([
            'duplicates' => ['required', 'in:skip,alias,create'],
            'datasource' => ['nullable', 'string', 'max:50'],
        ]);

        $checked = self::checkedFile($this->token);
        if ($checked === null) {
            $this->expired = true;
            $this->token = null;

            return;
        }

        $this->applied = (new ObjectCsvImport())->apply($checked['rows'], [
            'create' => $this->create,
            'duplicates' => $this->duplicates,
            'names' => $this->names,
            'update' => $this->update,
            'datasource' => $this->datasource,
        ]);

        // A file is imported once: check it again to import it again
        Cache::forget(self::CACHE_PREFIX . $this->token);
        $this->token = null;
    }

    public function startOver(): void
    {
        if ($this->token !== null) {
            Cache::forget(self::CACHE_PREFIX . $this->token);
        }
        $this->reset('token', 'applied', 'show', 'expired');
    }

    /**
     * The checked rows of a file, if they are still there and the current
     * observer checked them.
     */
    public static function checkedFile(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }
        $checked = Cache::get(self::CACHE_PREFIX . $token);

        return is_array($checked) && ($checked['user_id'] ?? null) === Auth::id() ? $checked : null;
    }

    /**
     * The rows of a status; 'differences' are the recognised rows with
     * differences or names to add.
     */
    private static function rowsWithStatus(array $rows, string $status): array
    {
        return array_values(array_filter($rows, fn (array $row) => $status === 'differences'
            ? $row['status'] === 'recognised' && ($row['differences'] !== [] || $row['names'] !== [])
            : $row['status'] === $status));
    }

    public function render()
    {
        Gate::authorize('import_objects', User::class);

        $checked = self::checkedFile($this->token);
        $rows = $checked['rows'] ?? [];
        $counts = [];
        foreach (['differences', 'recognised', 'duplicate', 'new', 'invalid'] as $status) {
            $counts[$status] = count(self::rowsWithStatus($rows, $status));
        }
        $shown = self::rowsWithStatus($rows, $this->show);

        return view('livewire.object-import', [
            'checked' => $checked,
            'settings' => array_merge(ObjectCsvImport::DEFAULT_SETTINGS, $checked['settings'] ?? []),
            'counts' => $counts,
            'shown' => array_slice($shown, 0, self::MAX_SHOWN),
            'more' => max(0, count($shown) - self::MAX_SHOWN),
            'types' => (new ObjectCsvImport())->types(),
        ]);
    }
}
