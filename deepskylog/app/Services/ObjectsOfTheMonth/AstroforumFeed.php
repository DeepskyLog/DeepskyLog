<?php

namespace App\Services\ObjectsOfTheMonth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * The threads of the objects of the month of Astroforum
 * (https://www.astroforum.nl/forums/objecten-van-de-maand.49/), read with
 * their permission: the RSS feed of the forum lists the threads, and the
 * first post of a thread names the objects (the feed holds only its start).
 * The forum is turned off with ASTROFORUM=false (services.astroforum).
 */
class AstroforumFeed
{
    /** The months in Dutch, and in English for a title written in English. */
    private const MONTHS = [
        'januari' => 1, 'februari' => 2, 'maart' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7,
        'augustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'december' => 12,
        'january' => 1, 'february' => 2, 'march' => 3, 'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8, 'october' => 10,
    ];

    public static function enabled(): bool
    {
        return (bool) config('services.astroforum.enabled', true);
    }

    /**
     * The threads in the feed, newest first: the id of the thread, its link,
     * title and the moment it was posted.
     *
     * @return list<array{id: int, url: string, title: string, posted_at: Carbon}>|null null when the forum did not answer
     */
    public function threads(): ?array
    {
        $body = $this->get((string) config('services.astroforum.feed'));
        if ($body === null) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_use_internal_errors($previous);
        if ($xml === false || ! isset($xml->channel)) {
            return null;
        }

        $threads = [];
        foreach ($xml->channel->item as $item) {
            $url = trim((string) $item->link);
            if (! preg_match('#/threads/[^/]*?\.?(\d+)/?$#', $url, $match)) {
                continue;
            }
            $threads[] = [
                'id' => (int) $match[1],
                'url' => $url,
                'title' => html_entity_decode(trim((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'posted_at' => Carbon::parse((string) $item->pubDate),
            ];
        }

        return $threads;
    }

    /** The text of the first post of a thread, or null when the forum did not answer. */
    public function firstPost(string $url): ?string
    {
        $html = $this->get($url);
        if ($html === null) {
            return null;
        }

        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_use_internal_errors($previous);

        $post = (new \DOMXPath($document))->query("(//article[contains(concat(' ', normalize-space(@class), ' '), ' message ')])[1]//div[contains(concat(' ', normalize-space(@class), ' '), ' bbWrapper ')]")->item(0);
        if ($post === null) {
            return null;
        }
        // Keep the words of neighbouring blocks apart
        foreach (iterator_to_array((new \DOMXPath($document))->query('.//br|.//p|.//div|.//li', $post)) as $node) {
            $node->parentNode?->insertBefore($document->createTextNode(' '), $node);
        }

        return trim((string) preg_replace('/\s+/u', ' ', $post->textContent));
    }

    /**
     * The months a thread is about: from the first of the first month to the
     * last of the last month its title names ("juli/augustus 2026"), in the
     * year it names or else the year it was posted; the month it was posted
     * when the title names none. A thread is often posted after its month
     * started, so the date of the post is no guide.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function period(string $title, Carbon $postedAt): array
    {
        preg_match_all('/(?<![\p{L}])('.implode('|', array_keys(self::MONTHS)).')(?![\p{L}])/iu', $title, $matches);
        $months = array_map(fn (string $month) => self::MONTHS[mb_strtolower($month)], $matches[1]);
        $year = preg_match('/(?<!\d)(20\d{2})(?!\d)/', $title, $match) ? (int) $match[1] : (int) $postedAt->year;

        if ($months === []) {
            $start = $postedAt->copy()->startOfMonth();

            return [$start, $start->copy()->endOfMonth()->startOfDay()];
        }

        $first = Carbon::create($year, $months[0], 1)->startOfDay();
        $last = Carbon::create($year, end($months), 1)->startOfDay();
        // "december/januari 2027": the first month in the year before
        if ($last->lt($first)) {
            $first->subYear();
        }

        return [$first, $last->endOfMonth()->startOfDay()];
    }

    private function get(string $url): ?string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'DeepskyLog ('.config('app.url').')'])
                ->timeout(20)
                ->get($url);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return $response->successful() ? $response->body() : null;
    }
}
