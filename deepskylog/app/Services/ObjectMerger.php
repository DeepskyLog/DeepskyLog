<?php

namespace App\Services;

use App\Models\Observation;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Merge a deep-sky object that is a duplicate into the object it is, or
 * delete an object that does not exist.
 *
 * A merge moves the observations, observing list items (an item of a list
 * that already holds the other object is dropped), part-of links and name
 * translations to the other object, and makes the names of the duplicate alternative names of
 * it: the objectnames rows move, keeping their slug, so old links to the
 * duplicate open the other object. The duplicate's outlines and stored
 * metrics are deleted with it.
 */
class ObjectMerger
{
    /**
     * Merge $duplicate into $into (objects.name). False when one of them is
     * not there.
     */
    public static function merge(string $duplicate, string $into): bool
    {
        $from   = DB::table('objects')->where('name', $duplicate)->first(['name', 'slug']);
        $target = DB::table('objects')->where('name', $into)->first(['name', 'slug']);
        if (! $from || ! $target || $from->name === $target->name) {
            return false;
        }

        // before the names move, so the cached lookups of the aliases are forgotten
        ObjectSearchIndex::removeObject($from->name, $from->slug);

        DB::transaction(function () use ($from, $target) {
            Observation::ofDeepskyObject($from->name)
                ->update(['target_table' => 'objects', 'target_key' => $target->name, 'target_name' => $target->name]);

            $lists = DB::table('observing_list_items')->where('object_name', $target->name)->pluck('observing_list_id');
            DB::table('observing_list_items')->where('object_name', $from->name)->whereIn('observing_list_id', $lists)->delete();
            DB::table('observing_list_items')->where('object_name', $from->name)->update(['object_name' => $target->name]);

            foreach (['objectname', 'partofname'] as $column) {
                $other = $column === 'objectname' ? 'partofname' : 'objectname';
                foreach (DB::table('objectpartof')->where($column, $from->name)->get() as $link) {
                    $exists = $link->{$other} === $target->name || DB::table('objectpartof')
                        ->where($column, $target->name)->where($other, $link->{$other})->exists();
                    $exists
                        ? DB::table('objectpartof')->where('id', $link->id)->delete()
                        : DB::table('objectpartof')->where('id', $link->id)->update([$column => $target->name]);
                }
            }

            $translated = DB::table('object_name_translations')->where('objectname', $target->name)->pluck('locale');
            DB::table('object_name_translations')->where('objectname', $from->name)->whereIn('locale', $translated)->delete();
            DB::table('object_name_translations')->where('objectname', $from->name)->update(['objectname' => $target->name]);

            // a name both have keeps the duplicate's row, whose slug the old links use
            $names = DB::table('objectnames')->where('objectname', $from->name)->pluck('altname');
            DB::table('objectnames')->where('objectname', $target->name)->whereIn('altname', $names)->delete();
            DB::table('objectnames')->where('objectname', $from->name)->update(['objectname' => $target->name]);

            self::deleteRows($from->name);
        });

        ObjectSearchIndex::syncObject($target->name);
        ObjectObservationStatistics::refreshObjects([$from->name, $target->name]);
        ResponseCache::clear();

        return true;
    }

    /**
     * Delete an object with its names, links and list items. False when it
     * is not there or has observations, which would point at nothing.
     */
    public static function delete(string $name): bool
    {
        $object = DB::table('objects')->where('name', $name)->first(['name', 'slug']);
        if (! $object || Observation::ofDeepskyObject($name)->exists()) {
            return false;
        }

        ObjectSearchIndex::removeObject($object->name, $object->slug);
        DB::transaction(function () use ($object) {
            DB::table('objectnames')->where('objectname', $object->name)->delete();
            DB::table('objectpartof')->where('objectname', $object->name)->orWhere('partofname', $object->name)->delete();
            DB::table('observing_list_items')->where('object_name', $object->name)->delete();
            DB::table('object_name_translations')->where('objectname', $object->name)->delete();
            self::deleteRows($object->name);
        });
        ResponseCache::clear();

        return true;
    }

    /** The rows that go with the object itself, and the object. */
    private static function deleteRows(string $name): void
    {
        DB::table('object_contours')->where('object_name', $name)->delete();
        DB::table('user_object_metrics')->where('object_name', $name)->delete();
        DB::table('objects')->where('name', $name)->delete();
    }
}
