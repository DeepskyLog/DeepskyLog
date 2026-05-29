<?php

namespace App\Support;

use App\Models\DeepskyObject;
use Illuminate\Database\ConnectionInterface;

class LegacyStarCatalog
{
    /**
     * @return list<string>
     */
    public static function discoverTables(ConnectionInterface $legacy): array
    {
        $rows = $legacy->select("SHOW TABLES LIKE 'stars%'");

        $tables = array_map(
            static fn($row) => (string) array_values((array) $row)[0],
            $rows
        );

        usort($tables, 'strnatcmp');

        return $tables;
    }

    /**
     * @param  array<string, mixed>|object  $row
     * @return array<string, mixed>
     */
    public static function normalizeRow(string $sourceTable, array|object $row): array
    {
        $data = (array) $row;

        $raHours = self::nullableFloat($data['RA2000'] ?? null);
        $declDegrees = self::nullableFloat($data['DE2000'] ?? null);
        $raDegrees = $raHours === null ? null : DeepskyObject::raToDecimal((string) $raHours);
        $vMagInt = self::nullableInt($data['vMag'] ?? null);

        $sourceParts = [
            $sourceTable,
            (string) ($data['name'] ?? ''),
            (string) ($data['RA2000mas'] ?? $raHours ?? ''),
            (string) ($data['DE2000mas'] ?? $declDegrees ?? ''),
            (string) ($data['vMag'] ?? ''),
        ];

        return [
            'source_key' => sha1(implode('|', $sourceParts)),
            'source_table' => $sourceTable,
            'type' => self::nullableString($data['type'] ?? null, 7),
            'constellation_code' => self::nullableString($data['nameCon'] ?? null, 3),
            'bayer' => self::nullableString($data['nameBayer'] ?? null, 3),
            'bayer_suffix' => self::nullableString($data['nameBayer2'] ?? null, 2),
            'flamsteed' => self::nullableString($data['nameFlamSteed'] ?? null, 3),
            'name' => self::nullableString($data['name'] ?? null, 25),
            'ra_mas' => self::nullableInt($data['RA2000mas'] ?? null),
            'dec_mas' => self::nullableInt($data['DE2000mas'] ?? null),
            'ra_hours' => $raHours,
            'ra_degrees' => $raDegrees,
            'decl_degrees' => $declDegrees,
            'vmag_int' => $vMagInt,
            'vmag' => $vMagInt === null ? null : round($vMagInt / 100, 2),
            'spectral_type' => self::nullableString($data['spType'] ?? null, 25),
        ];
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function nullableString(mixed $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        return mb_substr($string, 0, $maxLength);
    }
}