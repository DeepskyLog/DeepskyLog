<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectName extends Model
{
    protected $table = 'objectnames';
    protected $guarded = [];
    public $timestamps = false;

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
