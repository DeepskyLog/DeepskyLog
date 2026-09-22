<?php

namespace App\Services;

/**
 * The page layout of the printed DeepskyLog atlases.
 *
 * Each atlas variant divides the sky into declination bands of equal height,
 * and each band into a whole number of pages of equal width in right
 * ascension. Page 1 is the first page of the northernmost band; numbering runs
 * west to east within a band, then on to the next band southwards.
 *
 * These page numbers are printed in the atlases and shown on object pages, so
 * the tables below are fixed: changing one renumbers every page after it.
 */
class AtlasLayout
{
    /**
     * Pages per declination band, for each atlas variant.
     *
     * @var array<int, array<int, int>>
     */
    private const PAGES = [
        [12, 20, 26, 30, 32, 32, 30, 26, 20, 12],
        [12, 20, 30, 36, 42, 48, 52, 54, 54, 54, 52, 48, 42, 36, 30, 20, 12],
        [12, 21, 31, 40, 48, 55, 63, 70, 77, 83, 89, 94, 98, 101, 104, 106, 107, 107, 107, 107, 106, 104, 101, 98, 94, 89, 83, 77, 70, 63, 55, 48, 40, 31, 21, 12],
        [6, 10, 13, 16, 19, 20, 21, 21, 21, 20, 19, 16, 13, 10, 6],
        [6, 10, 13, 17, 21, 24, 27, 30, 32, 34, 35, 36, 36, 36, 36, 36, 35, 34, 32, 30, 27, 24, 21, 17, 13, 10, 6],
        [6, 10, 14, 18, 22, 26, 30, 33, 37, 41, 44, 47, 50, 53, 56, 58, 61, 63, 65, 66, 68, 70, 70, 71, 71, 72, 72, 72, 71, 71, 70, 70, 68, 66, 65, 63, 61, 58, 56, 53, 50, 47, 44, 41, 37, 33, 30, 26, 22, 18, 14, 10, 6],
    ];

    public static function isVariant(int $variant): bool
    {
        return $variant >= 0 && $variant < count(self::PAGES);
    }

    /**
     * Atlas variant index (0..5) for an atlas type and paper orientation.
     */
    public static function variant(string $type, string $orientation): int
    {
        $typeIndex = match (strtolower(trim($type))) {
            'overview' => 0,
            'lookup' => 1,
            default => 2,
        };

        return $typeIndex + (strtolower(trim($orientation)) === 'landscape' ? 3 : 0);
    }

    public static function bandCount(int $variant): int
    {
        return self::isVariant($variant) ? count(self::PAGES[$variant]) : 0;
    }

    public static function columnCount(int $variant, int $band): int
    {
        return self::PAGES[$variant][$band] ?? 0;
    }

    public static function totalPages(int $variant): int
    {
        return self::isVariant($variant) ? array_sum(self::PAGES[$variant]) : 0;
    }

    /**
     * Declination band edges in degrees, northern edge first.
     *
     * @return array{0: float, 1: float}
     */
    public static function bandBounds(int $variant, int $band): array
    {
        $height = 180.0 / self::bandCount($variant);

        return [90.0 - ($band * $height), 90.0 - (($band + 1) * $height)];
    }

    /**
     * Centre of a page, as [right ascension in hours, declination in degrees].
     *
     * @return array{0: float, 1: float}
     */
    public static function pageCenter(int $variant, int $band, int $column): array
    {
        [$upper, $lower] = self::bandBounds($variant, $band);
        $columns = self::columnCount($variant, $band);

        $raHours = 24.0 - (($column + 0.5) * (24.0 / max(1, $columns)));
        if ($raHours < 0.0) {
            $raHours += 24.0;
        }

        return [$raHours, ($upper + $lower) / 2.0];
    }

    /**
     * Page number of a band/column position, counting from 1.
     */
    public static function pageNumber(int $variant, int $band, int $column): int
    {
        $page = 1;
        for ($i = 0; $i < $band; $i++) {
            $page += self::columnCount($variant, $i);
        }

        return $page + $column;
    }

    /**
     * Page number covering a position on the sky, or 0 when there is none.
     */
    public static function pageAt(int $variant, float $raHours, float $decDeg): int
    {
        if (! self::isVariant($variant)) {
            return 0;
        }

        $bands = self::bandCount($variant);
        for ($band = 0; $band < $bands; $band++) {
            [$upper, $lower] = self::bandBounds($variant, $band);

            // Bands are closed at their southern edge, so the south pole lands
            // on the last band. The north pole would otherwise fall through
            // every band, so the first one is closed at its northern edge too.
            $belowUpper = $band === 0 ? $decDeg <= $upper : $decDeg < $upper;

            if ($belowUpper && $decDeg >= $lower) {
                return self::pageNumber($variant, $band, self::columnAt($variant, $band, $raHours));
            }
        }

        return 0;
    }

    /**
     * Column of a band that covers a right ascension.
     */
    public static function columnAt(int $variant, int $band, float $raHours): int
    {
        $columns = self::columnCount($variant, $band);
        if ($columns < 1) {
            return 0;
        }

        $column = (int) floor((24.0 - $raHours) / (24.0 / $columns));

        return max(0, min($columns - 1, $column));
    }

    /**
     * Page numbers of the charts adjoining a page on each side.
     *
     * Right ascension increases towards the left of a chart, so the page to
     * the left is the previous column. Bands wrap around the sky, so every
     * page has a left and a right neighbour; the northernmost and
     * southernmost bands have no neighbour beyond them, because beyond them
     * lies a pole.
     *
     * @return array{left: ?int, right: ?int, top: ?int, bottom: ?int}
     */
    public static function neighbours(int $variant, int $band, int $column): array
    {
        $columns = self::columnCount($variant, $band);
        $neighbours = ['left' => null, 'right' => null, 'top' => null, 'bottom' => null];

        if ($columns > 1) {
            $neighbours['left'] = self::pageNumber($variant, $band, ($column - 1 + $columns) % $columns);
            $neighbours['right'] = self::pageNumber($variant, $band, ($column + 1) % $columns);
        }

        [$raHours] = self::pageCenter($variant, $band, $column);

        if ($band > 0) {
            $neighbours['top'] = self::pageNumber($variant, $band - 1, self::columnAt($variant, $band - 1, $raHours));
        }

        if ($band < self::bandCount($variant) - 1) {
            $neighbours['bottom'] = self::pageNumber($variant, $band + 1, self::columnAt($variant, $band + 1, $raHours));
        }

        return $neighbours;
    }
}
