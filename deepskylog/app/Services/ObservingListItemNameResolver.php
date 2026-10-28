<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Resolves the object names people type or import into the names observing
 * list items are stored under.
 *
 * The list tables and exports match observing_list_items.object_name exactly
 * against objects.name and search_index.name (see AdvancedObjectSearchTable),
 * so an item stored as "m31", "NGC 224" or "Andromeda Galaxy" would not show
 * up. Deep-sky objects are stored under objects.name ("M 31"), other targets
 * (comets, planets, moons, lunar features) under their search_index name.
 */
class ObservingListItemNameResolver
{
    /**
     * Catalogue prefixes other programs write, and the one DeepskyLog stores
     * (Argo Navis and SkySafari lists use "N1275", "Barnard 33", "HCG 92").
     */
    private const CATALOGUE_SYNONYMS = [
        'N' => 'NGC',
        'BARNARD' => 'B',
        'HCG' => 'Hickson',
    ];

    /**
     * The canonical name of one object, or null when it isn't known.
     */
    public function resolve(string $name): ?string
    {
        return $this->resolveMany([$name])[$this->clean($name)] ?? null;
    }

    /**
     * The canonical names of the given objects, keyed by the cleaned input
     * name (trimmed, runs of whitespace collapsed); null for unknown objects.
     *
     * @param  iterable<string>  $names
     * @return array<string, string|null>
     */
    public function resolveMany(iterable $names): array
    {
        $variantsByName = [];
        foreach ($names as $name) {
            $clean = $this->clean((string) $name);
            if ($clean !== '' && ! isset($variantsByName[$clean])) {
                $variantsByName[$clean] = $this->variants($clean);
            }
        }

        if ($variantsByName === []) {
            return [];
        }

        // search_index.name is indexed (and case-insensitive on MySQL), so look
        // all variants up in a few queries instead of one per name.
        $rowsByName = [];
        foreach (array_chunk(array_values(array_unique(array_merge(...array_values($variantsByName)))), 500) as $chunk) {
            $rows = DB::table('search_index')
                ->whereIn('name', $chunk)
                ->get(['name', 'source_table', 'source_type', 'source_pk']);
            foreach ($rows as $row) {
                $rowsByName[mb_strtolower((string) $row->name)][] = $row;
            }
        }

        $best = [];
        $slugs = [];
        foreach ($variantsByName as $clean => $variants) {
            $best[$clean] = null;
            foreach ($variants as $variant) {
                $candidates = $rowsByName[mb_strtolower($variant)] ?? [];
                if ($candidates !== []) {
                    // Deep-sky objects first, then primary names before aliases.
                    usort($candidates, fn ($a, $b) => [$a->source_table !== 'objects', $a->source_type === 'alias']
                        <=> [$b->source_table !== 'objects', $b->source_type === 'alias']);
                    $best[$clean] = $candidates[0];
                    if ($candidates[0]->source_table === 'objects') {
                        $slugs[] = (string) $candidates[0]->source_pk;
                    }
                    break;
                }
            }
        }

        $objectNames = [];
        foreach (array_chunk(array_values(array_unique($slugs)), 500) as $chunk) {
            $objectNames += DB::table('objects')->whereIn('slug', $chunk)->pluck('name', 'slug')->all();
        }

        $resolved = [];
        foreach ($best as $clean => $row) {
            if ($row === null) {
                $resolved[$clean] = null;
            } elseif ($row->source_table === 'objects') {
                // An alias row whose object has gone is as good as unknown.
                $resolved[$clean] = isset($objectNames[$row->source_pk]) ? (string) $objectNames[$row->source_pk] : null;
            } else {
                $resolved[$clean] = (string) $row->name;
            }
        }

        return $resolved;
    }

    /**
     * Trim and collapse whitespace: the key resolveMany() uses for a name.
     */
    public function clean(string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    /**
     * The spellings to try for a name, most literal first: catalogue
     * designations are stored with a space ("M 31", "NGC 7000"), but are
     * often written without one or with leading zeros ("M31", "NGC0224").
     * The observation importers look these up too.
     *
     * @return list<string>
     */
    public function variants(string $name): array
    {
        $variants = [$name];

        // Struve double stars: "Σ1", "Sigma 1", "Struve 1", "O Struve 1" (STF 1, STT 1).
        if (($struve = StruveSigmaNames::designation($name)) !== null) {
            $variants[] = $struve;
        }

        // Sharpless: "Sh2-091", "Sh 2 - 91" are stored as "Sh 2-91".
        if (preg_match('/^sh\s*2\s*-\s*0*(\d+)$/i', $name, $m)) {
            $variants[] = 'Sh 2-' . $m[1];
        }

        // Perek-Kohoutek: stored as "PK lll±bb.nn" ("PK 198-06.01"), written as
        // "PK 198-06.1", "PK 198-6 1", "PK198-006.01". The sign is kept even for
        // latitude 00: "PK 089+00.01" and "PK 089-00.01" are different nebulae.
        if (preg_match('/^PK\s*(\d{1,3})\s*([+-])\s*(\d{1,3})\s*[.\s]\s*(\d{1,2})$/i', $name, $m)
            && (int) $m[1] < 360 && (int) $m[3] <= 90) {
            $variants[] = sprintf('PK %03d%s%02d.%02d', $m[1], $m[2], $m[3], $m[4]);
        }

        if (preg_match('/^([A-Za-z]+)[\s_-]*(\d.*)$/', $name, $m)) {
            $prefixes = [$m[1], strtoupper($m[1])];
            if (isset(self::CATALOGUE_SYNONYMS[strtoupper($m[1])])) {
                $prefixes[] = self::CATALOGUE_SYNONYMS[strtoupper($m[1])];
            }
            foreach ($prefixes as $prefix) {
                $variants[] = $prefix . ' ' . $m[2];
                $trimmed = ltrim($m[2], '0');
                if ($trimmed !== '' && ctype_digit($trimmed[0])) {
                    $variants[] = $prefix . ' ' . $trimmed;
                }
            }
        }

        return array_values(array_unique($variants));
    }
}
