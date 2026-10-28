<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SearchIndex extends Model
{
    protected $table = 'search_index';
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The key AdvancedObjectSearchTable merges rows on.
     */
    public const NAME_KEY = 'LOWER(COALESCE(name, display_name))';

    /**
     * Set name_unique: true unless another row that is not an alias has the
     * same NAME_KEY (alias rows are never merged, so they are true as well).
     * Without $names every row is updated, otherwise only the rows with one of
     * these names; call it after writing or deleting rows.
     */
    public static function markUniqueNames(?array $names = null): void
    {
        $key = self::NAME_KEY;
        $update = function (string $where = '', array $bindings = []) use ($key) {
            // One statement, so a name is never listed twice in between.
            DB::update(
                "UPDATE search_index SET name_unique = CASE WHEN source_type <> 'alias' AND {$key} IN ("
                . "SELECT name_key FROM (SELECT {$key} AS name_key FROM search_index WHERE source_type <> 'alias' {$where}"
                . " GROUP BY {$key} HAVING COUNT(*) > 1) shared"
                . ") THEN 0 ELSE 1 END WHERE 1 = 1 {$where}",
                array_merge($bindings, $bindings)
            );
        };

        if ($names === null) {
            $update();

            return;
        }

        // The name column compares case-insensitively, like the key.
        $names = array_values(array_unique(array_filter($names, fn ($n) => $n !== null && $n !== '')));
        foreach (array_chunk($names, 500) as $chunk) {
            $update('AND name IN (' . implode(', ', array_fill(0, count($chunk), '?')) . ')', $chunk);
        }
    }

    /**
     * Return a flat array suitable for indexing by TNTSearch.
     */
    public function toTntArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'source_type' => $this->source_type,
            'metadata' => $this->metadata,
        ];
    }
}
