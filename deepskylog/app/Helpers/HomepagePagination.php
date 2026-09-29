<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * The homepage shows several "newest ..." boxes, each with its own paginator.
 * Only the first few pages are reachable from the homepage (the full lists
 * live on /observations, /drawings, ...), and every paginator links with only
 * its own page parameter. Without these limits the combinations of page
 * parameters form an effectively infinite URL space that crawlers walk.
 */
class HomepagePagination
{
    public const MAX_PAGES = 10;

    public const PAGE_PARAMS = ['sessions', 'sketches', 'cometsketches', 'deepsky', 'comets'];

    /**
     * Whether the request uses at most one homepage page parameter, with a
     * page number between 1 and MAX_PAGES.
     */
    public static function isValidRequest(Request $request): bool
    {
        $present = array_values(array_intersect(self::PAGE_PARAMS, array_keys($request->query())));

        if (count($present) > 1) {
            return false;
        }

        if (count($present) === 1) {
            $page = filter_var($request->query($present[0]), FILTER_VALIDATE_INT);

            return $page !== false && $page >= 1 && $page <= self::MAX_PAGES;
        }

        return true;
    }

    /**
     * Limit a paginator to MAX_PAGES so its links never point past the cap.
     * The links always use the current path: the paginators are cached and
     * shared between / and /dashboard.
     */
    public static function cap(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $paginator->getCollection(),
            min($paginator->total(), self::MAX_PAGES * $paginator->perPage()),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => $paginator->getPageName()]
        );
    }
}
