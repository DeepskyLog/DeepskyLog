<?php

namespace App\Console\Commands;

use App\Services\ObjectObservationStatistics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Put the characters back into catalogue names that hold an HTML entity.
 *
 * A handful of names were imported with their non-ASCII characters still
 * encoded -- "T&ouml;pler 2" for "Töpler 2". Nothing decodes them again on the
 * way out: a chart label is escaped rather than interpreted, so the entity is
 * what gets printed on an atlas page, in the atlas index and anywhere else the
 * name is shown.
 *
 * An object's name is its primary key and half a dozen tables refer to it by
 * that string, so renaming one means rewriting every reference in the same
 * transaction. Aliases carry no such weight and are simply corrected.
 *
 * Safe to run more than once: only rows that still hold an entity are touched.
 */
class FixObjectNameEntities extends Command
{
    protected $signature = 'objects:fix-html-entities {--apply : Apply fixes to the database}';

    protected $description = 'Decode HTML entities stored in deep sky object names and aliases';

    /**
     * Every column that refers to an object by its name.
     *
     * Renaming an object without rewriting these would orphan the aliases,
     * the observations and everything else that points at it. The generated
     * columns -- objects.name_normalized, observations.target_name_normalized
     * -- follow their source and are deliberately absent.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const OBJECT_NAME_REFERENCES = [
        ['objectnames', 'objectname'],
        ['objectpartof', 'objectname'],
        ['objectpartof', 'partofname'],
        ['object_contours', 'object_name'],
        ['object_name_translations', 'objectname'],
        ['observerobjectlist', 'objectname'],
        ['observing_list_items', 'object_name'],
        ['user_object_metrics', 'object_name'],
        ['observations', 'target_name'],
        ['observations', 'target_key'],
    ];

    /** The alias columns, which no other table refers to. */
    private const ALIAS_COLUMNS = ['altname', 'catindex'];

    /** A named, decimal or hexadecimal HTML entity. */
    private const ENTITY = '/&(?:[A-Za-z][A-Za-z0-9]{1,30}|#[0-9]{1,7}|#[xX][0-9A-Fa-f]{1,6});/';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $renamed = $this->fixObjectNames($apply);
        $aliases = $this->fixAliases($apply);

        $this->newLine();
        $this->info('Objects renamed: ' . $renamed);
        $this->info('Alias names corrected: ' . $aliases);

        if (! $apply && ($renamed + $aliases) > 0) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');

            return self::SUCCESS;
        }

        if ($apply && ($renamed + $aliases) > 0) {
            // The search index stores its own copy of every name, and the
            // atlas caches whole pages; neither notices a rename on its own.
            $this->warn('Now run: php artisan search:reindex');
            $this->warn('Names already printed into a generated atlas PDF keep the old spelling until the atlas is generated again.');
        }

        $this->reportLeftovers();

        return self::SUCCESS;
    }

    /**
     * Rename the objects whose own name holds an entity, references and all.
     */
    private function fixObjectNames(bool $apply): int
    {
        $fixed = 0;

        foreach ($this->rowsWithEntities('objects', 'name') as $row) {
            $old = (string) $row->name;
            $new = $this->decode($old);

            if ($new === $old) {
                continue;
            }

            // Never merge two catalogue entries. If the corrected name is
            // already taken, the two rows are either duplicates or a genuine
            // clash, and either way that is not a decision for this command.
            //
            // The comparison is settled in PHP: the database matches names
            // under a collation that reads "Topler 2" and "Töpler 2" as the
            // same string, which would report a clash that is not one.
            if ($this->nameIsTaken($new)) {
                $this->error(sprintf('Skipped "%s": "%s" is already in the catalogue.', $old, $new));

                continue;
            }

            $this->line(sprintf('%s "%s" -> "%s"', $apply ? 'Renaming' : 'Would rename', $old, $new));

            if (! $apply) {
                $fixed++;

                continue;
            }

            DB::transaction(function () use ($old, $new) {
                // The object first: it is the row the others point at. One
                // row and no more -- if the loose collation has matched
                // something else as well, the whole rename comes back off.
                $touched = DB::table('objects')->where('name', $old)->update(['name' => $new]);

                if ($touched !== 1) {
                    throw new \RuntimeException(sprintf('Renaming "%s" touched %d rows, expected 1.', $old, $touched));
                }

                foreach (self::OBJECT_NAME_REFERENCES as [$table, $column]) {
                    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                        continue;
                    }

                    // Any row holding this exact string was referring to this
                    // object: no other kind of target is spelled like this.
                    $touched = DB::table($table)->where($column, $old)->update([$column => $new]);

                    if ($touched > 0) {
                        $this->line(sprintf('  %s.%s: %d row(s)', $table, $column, $touched));
                    }
                }
            });

            // The observations were renamed without their model events.
            ObjectObservationStatistics::refreshObjects([$old, $new]);

            $fixed++;
        }

        return $fixed;
    }

    /**
     * Correct the alias names, which nothing else refers to.
     */
    private function fixAliases(bool $apply): int
    {
        if (! Schema::hasTable('objectnames')) {
            return 0;
        }

        $fixed = 0;

        foreach (self::ALIAS_COLUMNS as $column) {
            if (! Schema::hasColumn('objectnames', $column)) {
                continue;
            }

            foreach ($this->rowsWithEntities('objectnames', $column, ['id']) as $row) {
                $old = (string) $row->{$column};
                $new = $this->decode($old);

                if ($new === $old) {
                    continue;
                }

                $this->line(sprintf('%s objectnames.%s #%d "%s" -> "%s"', $apply ? 'Fixing' : 'Would fix', $column, $row->id, $old, $new));

                if ($apply) {
                    DB::table('objectnames')->where('id', $row->id)->update([$column => $new]);
                }

                $fixed++;
            }
        }

        return $fixed;
    }

    /**
     * The rows of a column that hold something looking like an HTML entity.
     *
     * An ampersand is the cheap, portable test the database can do; whether
     * what follows it is really an entity is settled in PHP.
     *
     * @param  array<int, string>  $extraColumns
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function rowsWithEntities(string $table, string $column, array $extraColumns = [])
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return collect();
        }

        return DB::table($table)
            ->select(array_merge($extraColumns, [$column]))
            ->where($column, 'like', '%&%')
            ->orderBy($column)
            ->get()
            ->filter(fn (object $row) => preg_match(self::ENTITY, (string) $row->{$column}) === 1)
            ->values();
    }

    /**
     * Whether the catalogue already holds this exact name.
     */
    private function nameIsTaken(string $name): bool
    {
        return DB::table('objects')
            ->where('name', $name)
            ->pluck('name')
            ->contains(fn ($existing) => (string) $existing === $name);
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Name an entity this command could not decode, rather than leave it to
     * be discovered on a printed page.
     */
    private function reportLeftovers(): void
    {
        $leftovers = 0;

        foreach ([['objects', 'name'], ['objectnames', 'objectname'], ['objectnames', 'altname'], ['objectnames', 'catindex']] as [$table, $column]) {
            foreach ($this->rowsWithEntities($table, $column) as $row) {
                $this->warn(sprintf('Still encoded: %s.%s "%s"', $table, $column, (string) $row->{$column}));
                $leftovers++;
            }
        }

        if ($leftovers === 0) {
            $this->info('No encoded names left in the catalogue.');
        }
    }
}
