<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Pagination of the observation and drawing listings (/observations,
 * /cometobservations, /drawings and the per-observer and per-object lists).
 *
 * The paginator links and the filter/sort links only keep the parameters the
 * listings read (QUERY_PARAMS): copying every query parameter lets crawlers
 * (and junk parameters) multiply the URLs of every page. The global lists
 * hold every observation of every observer, so they stop at MAX_PAGES; the
 * observation search covers older observations.
 */
class ListingPagination
{
    public const MAX_PAGES = 100;

    public const QUERY_PARAMS = ['obs_type', 'sort', 'images', 'following'];

    /**
     * The listing parameters of the current request, to append to links.
     *
     * @return array<string, string>
     */
    public static function query(array $except = []): array
    {
        $params = array_diff(self::QUERY_PARAMS, $except);

        return array_filter(request()->only($params), fn ($value) => is_scalar($value) && $value !== '');
    }

    /**
     * Whether the page parameter is absent or a page number between 1 and
     * MAX_PAGES.
     */
    public static function isValidPage(Request $request, string $pageName): bool
    {
        if (!$request->query->has($pageName)) {
            return true;
        }

        $page = filter_var($request->query($pageName), FILTER_VALIDATE_INT);

        return $page !== false && $page >= 1 && $page <= self::MAX_PAGES;
    }

    /**
     * Limit a paginator of a global list to MAX_PAGES so its links never point
     * past the cap, keeping only the listing parameters in its links.
     */
    public static function cap(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $capped = new LengthAwarePaginator(
            $paginator->getCollection(),
            min($paginator->total(), self::MAX_PAGES * $paginator->perPage()),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $paginator->path(), 'pageName' => $paginator->getPageName()]
        );

        return $capped->appends(self::query());
    }
}
