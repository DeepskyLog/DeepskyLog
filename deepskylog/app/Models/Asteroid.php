<?php

namespace App\Models;

use App\Services\AsteroidCoordinates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Asteroid extends Model
{
    protected $table = 'asteroids';
    protected $guarded = [];

    /**
     * The asteroid of these orbital elements (a row of
     * `asteroids_orbital_elements`), added when it is not in DeepskyLog yet:
     * named "(4) Vesta", with the name of the elements ("Vesta", or the
     * designation of an asteroid without a name) as designation, and in the
     * search index under both.
     */
    public static function addFromElements(object $elements): self
    {
        $number = (int) $elements->number;
        if ($existing = self::where('number', $number)->first()) {
            return $existing;
        }

        $name = AsteroidCoordinates::displayName($elements);
        $base = Str::slug($name) ?: 'asteroid-'.$number;
        $slug = $base;
        for ($i = 1; self::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        $asteroid = self::create([
            'name' => $name,
            'slug' => $slug,
            'designation' => trim((string) $elements->name),
            'number' => $number,
            'body_type' => 'asteroid',
        ]);
        $asteroid->syncSearchIndex();

        return $asteroid;
    }

    /**
     * The rows of the asteroid in the search index: its name, and its
     * designation as alias, so quick search finds "Vesta" as well.
     *
     * @return list<array<string, mixed>>
     */
    public static function searchIndexRows(object $asteroid, string $now): array
    {
        $row = fn (string $name, string $type) => [
            'name' => $name,
            'name_normalized' => mb_strtolower($name),
            'source_table' => 'asteroids',
            'source_pk' => (string) $asteroid->id,
            'display_name' => $type === 'alias' ? $asteroid->name : $name,
            'source_type' => $type,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $rows = [$row((string) $asteroid->name, $asteroid->body_type ?? 'asteroid')];
        $designation = trim((string) ($asteroid->designation ?? ''));
        if ($designation !== '' && $designation !== $asteroid->name) {
            $rows[] = $row($designation, 'alias');
        }

        return $rows;
    }

    public function syncSearchIndex(): void
    {
        DB::transaction(function () {
            DB::table('search_index')->where('source_table', 'asteroids')->where('source_pk', (string) $this->id)->delete();
            DB::table('search_index')->insert(self::searchIndexRows($this, now()->toDateTimeString()));
        });
        SearchIndex::markUniqueNames([$this->name, $this->designation]);
    }
}
