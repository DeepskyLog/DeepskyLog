<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;
use Symfony\Component\HttpFoundation\Response;

/**
 * StartSession that does not store sessions for crawlers.
 *
 * Crawlers and monitoring tools send no cookies, so every request they make
 * would create a new session: a row in the sessions table (SESSION_DRIVER=
 * database) that is kept for the whole SESSION_LIFETIME, also for pages
 * served from the response cache. A request without a session cookie whose
 * User-Agent looks like a crawler gets an in-memory session instead, and no
 * session cookie. Browsers are not affected: they need the stored session
 * for the CSRF token of forms and Livewire components on the page.
 */
class StartSession extends BaseStartSession
{
    /**
     * User agents of crawlers, feed readers, link previews, monitoring tools
     * and HTTP libraries.
     */
    private const CRAWLER_PATTERN = '/bot\b|bot\/|crawl|spider|slurp|scrap|archiver|facebookexternalhit|embedly|'
        . 'feedfetcher|feedly|feedburner|rss|lighthouse|pingdom|uptime|monitor|statuscake|headless|'
        . 'curl\/|wget\/|python-|httpclient|http_client|go-http-client|java\/|okhttp|libwww|axios\/|node-fetch|guzzlehttp/i';

    private bool $transient = false;

    public function handle($request, Closure $next)
    {
        $this->transient = $this->sessionConfigured() && $this->isCookielessCrawler($request);
        if ($this->transient) {
            $this->manager->setDefaultDriver('array');
        }

        return parent::handle($request, $next);
    }

    public static function isCrawler(Request $request): bool
    {
        $userAgent = trim((string) $request->userAgent());

        return $userAgent === '' || preg_match(self::CRAWLER_PATTERN, $userAgent) === 1;
    }

    protected function isCookielessCrawler(Request $request): bool
    {
        return !$request->cookies->has($this->manager->getSessionConfig()['cookie'])
            && static::isCrawler($request);
    }

    protected function addCookieToResponse(Response $response, Session $session)
    {
        if ($this->transient) {
            return;
        }

        parent::addCookieToResponse($response, $session);
    }
}
