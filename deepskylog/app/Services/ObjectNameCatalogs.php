<?php

namespace App\Services;

use App\Models\ObjectName;
use Illuminate\Support\Facades\DB;

/**
 * The catalogs of the object names (#526). A catalog is not stored on its
 * own: it exists as the catalog column of its names in objectnames, taken
 * from the name by ObjectName::splitCatalogDesignation(). A name whose
 * catalog DeepskyLog does not have yet would silently start a new catalog
 * (a typo such as NCG 891, or Leo T giving the catalog Leo), so the edit
 * page asks for each such name whether it starts a new catalog or is a name
 * without a catalog, stored with an empty catalog and the name as index.
 */
class ObjectNameCatalogs
{
    public const NEW_CATALOG = 'new';

    public const NO_CATALOG = 'none';

    /** @var array<string, string>|null The catalogs, keyed by their lower-case code */
    private ?array $known = null;

    /**
     * The catalogs DeepskyLog has, keyed by their lower-case code.
     *
     * @return array<string, string>
     */
    public function known(): array
    {
        if ($this->known === null) {
            $this->known = [];
            $codes = DB::table('objectnames')->where('catalog', '!=', '')->distinct()->pluck('catalog');
            foreach ($codes as $code) {
                $this->known[mb_strtolower($code)] ??= $code;
            }
        }

        return $this->known;
    }

    /**
     * The catalog a name would start, or null when its catalog exists or
     * the name has none.
     */
    public function newCatalogOf(string $name): ?string
    {
        [$catalog] = ObjectName::splitCatalogDesignation($name);

        return $catalog !== '' && ! isset($this->known()[mb_strtolower($catalog)]) ? $catalog : null;
    }

    /**
     * The catalog and index of a name, with the spelling of an existing
     * catalog; $choice says what a name of a new catalog is.
     *
     * @return array{0: string, 1: string}
     */
    public function split(string $name, ?string $choice = null): array
    {
        if ($choice === self::NO_CATALOG) {
            return ['', $name];
        }

        [$catalog, $catindex] = ObjectName::splitCatalogDesignation($name);

        return [$this->known()[mb_strtolower($catalog)] ?? $catalog, $catindex];
    }
}
