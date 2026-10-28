<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ObjectName extends Model
{
    protected $table   = 'objectnames';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * The slug of an alternative name. Σ is spelled out ('Σ 1' gives
     * 'sigma-1', 'OΣΣ 1' 'o-sigma-sigma-1'), as Str::slug() would make it an
     * 's' and 'Σ 1' would take the slug of 'S 1'.
     */
    public static function slugFor(string $name): string
    {
        return Str::slug(str_replace(['Σ', 'σ'], ' sigma ', $name));
    }

    /**
     * Split a name into catalog and catindex at the first space: 'H400 12' gives
     * ['H400', '12'], 'vdB-Ha 5' gives ['vdB-Ha', '5'], 'H N 122' gives ['H', 'N 122'].
     * A name without a space, or whose first word has no letter ('Merope', '1 Ari'),
     * has no catalog; the whole name is then the catindex, as in the old database.
     *
     * @return array{0: string, 1: string} [catalog, catindex]
     */
    public static function splitCatalogDesignation(string $name): array
    {
        $name = trim($name);

        if (preg_match('/^(\S*[A-Za-z]\S*)\s+(\S.*)$/u', $name, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return ['', $name];
    }
}
