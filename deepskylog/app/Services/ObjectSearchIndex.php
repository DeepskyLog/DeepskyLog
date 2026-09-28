<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The rows of deep-sky objects and their aliases in search_index. Used by
 * search:reindex and by the object create, edit and delete actions, so both
 * write the same rows.
 *
 * An object has one row with source_type = its type code and one row per
 * alias with source_type 'alias'; all of them have the slug of the object
 * (or its name when it has no slug) as source_pk.
 */
class ObjectSearchIndex
{
    /**
     * The search_index row of an objects row (name, slug, ra, decl, type, description).
     */
    public static function objectRow(object $o, string $now): array
    {
        return [
            'name' => $o->name,
            'name_normalized' => mb_strtolower($o->name),
            'source_table' => 'objects',
            'source_pk' => $o->slug ?? $o->name,
            'display_name' => $o->name,
            'source_type' => $o->type,
            'ra' => $o->ra,
            'decl' => $o->decl,
            'metadata' => json_encode(['description' => $o->description]),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * The aliases of objectnames joined with their object.
     */
    public static function aliasQuery()
    {
        return DB::table('objectnames as n')
            ->leftJoin('objects as o', 'n.objectname', '=', 'o.name')
            ->select(['n.id', 'n.objectname', 'n.slug as alias_slug', 'n.altname', 'n.catalog', 'n.catindex', 'o.slug as canonical_slug', 'n.timestamp as ntimestamp']);
    }

    /**
     * The search_index rows of alias rows from aliasQuery().
     */
    public static function aliasRows(iterable $aliases, string $now): array
    {
        $rows = [];
        foreach ($aliases as $a) {
            $display = $a->altname ?? $a->objectname;
            $pk = $a->canonical_slug ?? $a->alias_slug ?? $a->objectname;

            // Skip alias entries that would be identical to the canonical object
            // (i.e., no altname provided, or altname equals the canonical object
            // name). These rows duplicate the canonical object row and cause
            // duplicate results in the search UI.
            $alt = trim((string) ($a->altname ?? ''));
            $canonicalName = trim((string) ($a->objectname ?? ''));
            $altEmpty = ($alt === '');
            $altMatchesCanonical = (mb_strtolower($alt) === mb_strtolower($canonicalName));
            if (!empty($a->canonical_slug) && ($altEmpty || $altMatchesCanonical)) {
                continue;
            }

            $rows[] = [
                'name' => $display,
                'name_normalized' => mb_strtolower($display),
                'source_table' => 'objects',
                'source_pk' => $pk,
                'display_name' => $display,
                'source_type' => 'alias',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    /**
     * Write the rows of one object and its aliases again. $oldKeys are the
     * slug and name the object had before (after a rename), whose rows are
     * removed as well.
     */
    public static function syncObject(string $name, array $oldKeys = []): void
    {
        $object = DB::table('objects')->where('name', $name)->first(['name', 'slug', 'ra', 'decl', 'type', 'description']);
        $now = now()->toDateTimeString();

        DB::transaction(function () use ($object, $name, $oldKeys, $now) {
            self::deleteRows(array_merge($oldKeys, [$name, $object->slug ?? null]));
            if (!$object) {
                return;
            }

            // Two inserts: the alias rows have fewer columns than the object row
            DB::table('search_index')->insert(self::objectRow($object, $now));
            $rows = self::aliasRows(self::aliasQuery()->where('n.objectname', $name)->get(), $now);
            foreach (array_chunk($rows, 1000) as $batch) {
                DB::table('search_index')->insert($batch);
            }
        });

        self::forgetResolutions(array_merge($oldKeys, [$name, $object->slug ?? null]));
    }

    /**
     * Remove the rows of a deleted object and its aliases. Call before the
     * objectnames rows are deleted, so the cached lookups of the aliases can
     * be forgotten too.
     */
    public static function removeObject(string $name, ?string $slug): void
    {
        $aliases = DB::table('objectnames')->where('objectname', $name)->get(['altname', 'slug']);
        $keys = array_merge([$name, $slug], $aliases->pluck('altname')->all(), $aliases->pluck('slug')->all());

        self::deleteRows([$name, $slug]);
        self::forgetResolutions($keys);
    }

    /**
     * Delete the object and alias rows whose source_pk is one of the keys.
     */
    private static function deleteRows(array $keys): void
    {
        $keys = array_values(array_unique(array_filter($keys, fn ($k) => $k !== null && $k !== '')));
        if (!empty($keys)) {
            DB::table('search_index')->where('source_table', 'objects')->whereIn('source_pk', $keys)->delete();
        }
    }

    /**
     * Forget the cached slug resolutions of ObjectController::show() for the
     * given names and slugs, so they do not point to an old name.
     */
    private static function forgetResolutions(array $keys): void
    {
        foreach (array_unique(array_filter($keys, fn ($k) => $k !== null && $k !== '')) as $key) {
            foreach (array_unique([$key, Str::slug($key)]) as $variant) {
                Cache::forget('object:resolve:v1:' . sha1(mb_strtolower((string) $variant)));
            }
        }
    }
}
