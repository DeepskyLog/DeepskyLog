<?php

namespace App\Services;

use App\Models\DeepskyObject;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The index of objects at the back of a generated atlas.
 *
 * Every object the atlas plots, against the page it is on, under the name the
 * charts actually print beside it -- which is the object's own name and never
 * one of its aliases, because that is all a chart label ever carries. An
 * index entry for a name that appears nowhere on the pages would send a
 * reader looking for something that is not there.
 *
 * Looking a name up is the other way into the atlas: the index maps at the
 * front find a chart from a patch of sky, this finds one from a name.
 *
 * The lines are set in a monospaced face and padded with dots, so the page
 * numbers line up down the column without measuring any text.
 */
class AtlasObjectIndexService
{
    /** Width of one character as a fraction of the font size, for Courier. */
    private const CHARACTER_WIDTH_RATIO = 0.6;

    private const FONT_SIZE = 7.0;

    private const LINE_HEIGHT = 9.1;

    /**
     * Most columns an index page is set in.
     *
     * Past four the dot leaders are longer than the names they lead from,
     * and following one across becomes the hard part of using the index.
     */
    private const MAX_COLUMNS = 4;

    private const COLUMN_GAP = 20.0;

    /** Space for the running heads above the entries. */
    private const HEADER_HEIGHT = 96.0;

    private const FOOTER_HEIGHT = 56.0;

    private const SIDE_MARGIN = 56.0;

    public function __construct(private readonly StarChartService $starChartService)
    {
    }

    /**
     * The index pages of an atlas, as SVG documents on an atlas-sized canvas.
     *
     * @param  float  $objectMagnitudeLimit  The limit the atlas plots objects to.
     * @param  bool  $showUnknownMagnitudes  Whether it also plots objects of unrecorded magnitude.
     * @param  int|null  $lastPage  Highest atlas page actually generated, for a run cut short.
     * @return array<int, string>
     */
    public function build(string $atlasType, string $orientation, float $objectMagnitudeLimit, bool $showUnknownMagnitudes, ?int $lastPage = null): array
    {
        $variant = AtlasLayout::variant($atlasType, $orientation);
        if (! AtlasLayout::isVariant($variant)) {
            return [];
        }

        $entries = $this->entries($variant, $objectMagnitudeLimit, $showUnknownMagnitudes, $lastPage);
        if ($entries === []) {
            return [];
        }

        $layout = $this->layout($orientation, $entries);
        $perPage = ((int) $layout['rows']) * ((int) $layout['columns']);

        $pages = [];
        foreach (array_chunk($entries, $perPage) as $chunk) {
            $pages[] = $this->renderPage($chunk, $layout);
        }

        return $pages;
    }

    /**
     * Every name the atlas prints, against its page number, in the order the
     * index sets them.
     *
     * Natural order, so ASCC 9 comes before ASCC 100 rather than after it,
     * and case-sensitive, which puts a catalogue designation ahead of a
     * proper name beginning with the same letter.
     *
     * @return array<int, array{name: string, page: int}>
     */
    public function entries(int $variant, float $objectMagnitudeLimit, bool $showUnknownMagnitudes, ?int $lastPage = null): array
    {
        $entries = [];

        DB::table('objects')
            ->select(['name', 'ra', 'decl'])
            ->where(fn (Builder $query) => $this->applyPlotted($query, $objectMagnitudeLimit, $showUnknownMagnitudes))
            ->orderBy('name')
            ->chunk(2000, function ($objects) use ($variant, $lastPage, &$entries) {
                foreach ($objects as $object) {
                    $page = $this->pageOf($variant, $object);

                    if ($page < 1 || ($lastPage !== null && $page > $lastPage)) {
                        continue;
                    }

                    $name = $this->printedName((string) $object->name);

                    if ($name !== '') {
                        $entries[] = ['name' => $name, 'page' => $page];
                    }
                }
            });

        // Two objects can print the same label -- the catalogue holds both
        // "H 4 99" and "H 4  99" -- and an index that can send a reader to
        // either page is more use than one that quietly picks a page.
        usort(
            $entries,
            fn (array $a, array $b) => strnatcmp($a['name'], $b['name']) ?: ($a['page'] <=> $b['page'])
        );

        return $entries;
    }

    /**
     * Restrict a query to the objects an atlas page would plot.
     *
     * The same test the chart renderer applies, so the index never promises
     * an object that is not on the page it names.
     */
    private function applyPlotted(Builder $query, float $objectMagnitudeLimit, bool $showUnknownMagnitudes): void
    {
        // Double stars and single stars belong to the star layer of a chart,
        // which draws them from the star catalogue instead.
        $query->whereNotIn('type', ['DS', 'AA1STAR']);

        $query->where(function (Builder $magnitude) use ($objectMagnitudeLimit, $showUnknownMagnitudes) {
            $magnitude->where('mag', '<=', $objectMagnitudeLimit);

            // A missing magnitude is stored either as NULL or as 99.9.
            if ($showUnknownMagnitudes) {
                $magnitude->orWhereNull('mag')->orWhere('mag', '=', 99.9);
            }
        });
    }

    /**
     * The atlas page an object falls on, or 0 when its position is unusable.
     */
    private function pageOf(int $variant, object $object): int
    {
        $raDegrees = DeepskyObject::raToDecimal($object->ra ?? null);

        if ($raDegrees === null || ($object->decl ?? null) === null) {
            return 0;
        }

        return AtlasLayout::pageAt($variant, $raDegrees / 15.0, (float) $object->decl);
    }

    /**
     * A name as a chart label shows it.
     *
     * A label is set as SVG text, which collapses a run of spaces down to
     * one, so a name the catalogue spells with two prints with one. Nothing
     * else is touched: the labels are escaped rather than interpreted, so a
     * name stored with an HTML entity in it goes on the page with the entity
     * showing, and the index has to say the same.
     */
    private function printedName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name) ?? '');
    }

    /**
     * How the entries are arranged on an index page.
     *
     * As many columns as the longest line in the index will allow, rather
     * than a fixed number: three fit across a portrait page and four across
     * a landscape one, and if the catalogue ever gains a name too long for
     * that, the index loses a column instead of cutting the name short.
     *
     * @param  array<int, array{name: string, page: int}>  $entries
     * @return array<string, float>
     */
    private function layout(string $orientation, array $entries): array
    {
        $portrait = strtolower(trim($orientation)) === 'portrait';
        $width = $portrait ? 756.0 : 1120.0;
        $height = $portrait ? 1120.0 : 780.0;

        $contentWidth = $width - (2 * self::SIDE_MARGIN);
        $contentHeight = $height - self::HEADER_HEIGHT - self::FOOTER_HEIGHT;
        $characterWidth = self::FONT_SIZE * self::CHARACTER_WIDTH_RATIO;

        // The widest line the index has to set: the longest name, its page
        // number, the two spaces around the dots and the two dots that are
        // the shortest leader worth printing.
        $widest = 0;
        foreach ($entries as $entry) {
            $widest = max($widest, mb_strlen($entry['name']) + mb_strlen((string) $entry['page']) + 4);
        }

        $needed = (max($widest, 1) * $characterWidth) + self::COLUMN_GAP;
        $columns = (int) floor(($contentWidth + self::COLUMN_GAP) / max(1.0, $needed));
        $columns = max(1, min(self::MAX_COLUMNS, $columns));

        $columnWidth = ($contentWidth - (($columns - 1) * self::COLUMN_GAP)) / $columns;

        return [
            'width' => $width,
            'height' => $height,
            'columns' => (float) $columns,
            'column_width' => $columnWidth,
            'characters' => (float) (int) floor($columnWidth / $characterWidth),
            'rows' => (float) (int) floor($contentHeight / self::LINE_HEIGHT),
        ];
    }

    /**
     * @param  array<int, array{name: string, page: int}>  $entries
     * @param  array<string, float>  $layout
     */
    private function renderPage(array $entries, array $layout): string
    {
        $width = (float) $layout['width'];
        $height = (float) $layout['height'];
        $rows = (int) $layout['rows'];
        $font = htmlspecialchars($this->starChartService->monospaceFontFamily(), ENT_QUOTES, 'UTF-8');

        $svg = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid meet">', $width, $height, $width, $height);
        $svg[] = '<rect width="100%" height="100%" fill="#ffffff" />';

        // Running heads, as a dictionary has them: the first and the last
        // name on this page, so a reader can find the right leaf by thumbing
        // through rather than by reading every column.
        $svg[] = sprintf(
            '<text x="%0.2f" y="52" font-family="%s" font-size="10" fill="#111827">%s</text>'
            . '<text x="%0.2f" y="52" font-family="%s" font-size="10" text-anchor="middle" fill="#6b7280">%s</text>'
            . '<text x="%0.2f" y="52" font-family="%s" font-size="10" text-anchor="end" fill="#111827">%s</text>'
            . '<line x1="%0.2f" y1="62" x2="%0.2f" y2="62" stroke="#111827" stroke-width="0.8" />',
            self::SIDE_MARGIN,
            $font,
            htmlspecialchars($entries[array_key_first($entries)]['name'], ENT_QUOTES, 'UTF-8'),
            $width / 2.0,
            $font,
            htmlspecialchars(__('Index'), ENT_QUOTES, 'UTF-8'),
            $width - self::SIDE_MARGIN,
            $font,
            htmlspecialchars($entries[array_key_last($entries)]['name'], ENT_QUOTES, 'UTF-8'),
            self::SIDE_MARGIN,
            $width - self::SIDE_MARGIN
        );

        $column = 0;
        $row = 0;

        foreach ($entries as $entry) {
            $x = self::SIDE_MARGIN + ($column * (((float) $layout['column_width']) + self::COLUMN_GAP));
            $y = self::HEADER_HEIGHT + (($row + 1) * self::LINE_HEIGHT);

            $svg[] = sprintf(
                '<text x="%0.2f" y="%0.2f" font-family="%s" font-size="%0.1f" fill="#111827">%s</text>',
                $x,
                $y,
                $font,
                self::FONT_SIZE,
                htmlspecialchars($this->entryLine($entry['name'], $entry['page'], (int) $layout['characters']), ENT_QUOTES, 'UTF-8')
            );

            $row++;
            if ($row >= $rows) {
                $row = 0;
                $column++;
            }
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * One line of the index: the name, a run of dots, and the page number.
     *
     * Everything is counted in characters rather than measured, which only
     * works because the face is monospaced -- and which is exactly why a
     * printed index is set in one.
     */
    private function entryLine(string $name, int $page, int $characters): string
    {
        $number = (string) $page;

        // One space either side of the dots, and at least two dots, so the
        // eye has something to follow from the name to the number. Every
        // line is set to the same width, which is what right-aligns the
        // numbers down the column without padding them out with zeroes.
        $room = $characters - mb_strlen($number) - 4;
        if (mb_strlen($name) > $room) {
            $name = mb_substr($name, 0, max(1, $room - 1)) . '…';
        }

        $dots = max(2, $characters - mb_strlen($name) - mb_strlen($number) - 2);

        return $name . ' ' . str_repeat('.', $dots) . ' ' . $number;
    }
}
