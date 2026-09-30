<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;

/**
 * Gives the string columns of the SQLite test database the NOCASE collation,
 * so that they compare case-insensitively like the utf8mb4_unicode_ci
 * columns of the MariaDB database. The queries rely on that and do not wrap
 * columns in LOWER(), which would keep MariaDB from using the indexes.
 */
class CaseInsensitiveBlueprint extends Blueprint
{
    private const STRING_TYPES = ['char', 'string', 'tinyText', 'text', 'mediumText', 'longText'];

    public function addColumn($type, $name, array $parameters = [])
    {
        $column = parent::addColumn($type, $name, $parameters);

        if (in_array($type, self::STRING_TYPES, true) && $column->collation === null
            && $this->connection->getDriverName() === 'sqlite') {
            $column->collation('NOCASE');
        }

        return $column;
    }
}
