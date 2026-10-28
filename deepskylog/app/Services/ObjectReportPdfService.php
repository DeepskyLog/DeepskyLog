<?php

namespace App\Services;

use Dompdf\Adapter\CPDF as CpdfAdapter;
use Dompdf\Cpdf;
use Dompdf\Dompdf;

/**
 * Draws an object report: every object on a few lines, each field where the
 * layout puts it, in one or two columns per page.
 *
 * This is the report of the old DeepskyLog (util::pdfReportPersonalised)
 * drawn the same way -- PDF points from the bottom left, Helvetica -- so the
 * layouts observers made there print as they did. The PDF is drawn directly
 * with dompdf's Cpdf rather than from HTML, because the layouts position
 * every field themselves.
 */
class ObjectReportPdfService
{
    private Cpdf $pdf;

    private string $fontDir;

    /** @var array<string, string> */
    private array $s;

    /** @var array<string, array<string, mixed>> the fields the layout shows, by line */
    private array $fields;

    /** @var array<string, mixed> */
    private array $options;

    private float $y;

    private float $xbase;

    private int $pagenr;

    private float $deltaline;

    private float $sectionBarHeight;

    private float $sectionBarWidth;

    /**
     * @param  array{settings: array<string, string>, fields: array<string, array<string, mixed>>}  $layout  a normalised layout
     * @param  array<int, array<string, string>>  $rows  from ObjectReportDataBuilder
     * @param  array{title?: string, sort?: bool, date?: string, prepared_for?: ?string, ephemerides?: ?string}  $options
     */
    public function render(array $layout, array $rows, array $options = []): string
    {
        $this->s = $layout['settings'];
        $this->options = $options;
        $this->fields = collect($layout['fields'])
            ->filter(fn (array $f) => (float) $f['width'] > 0)
            ->sortBy('line')
            ->all();

        $fontSizeText = $this->num('fontSizeText');
        $this->deltaline = $this->num('deltalineextra') + $fontSizeText;
        $this->sectionBarHeight = $this->num('sectionBarHeightextra') + $this->num('fontSizeSection');
        $this->sectionBarWidth = $this->num('SectionBarWidthbase') + $this->num('sectionbarspace');
        $this->pagenr = (int) $this->num('startpagenumber');

        $this->fontDir = dirname((new \ReflectionClass(Dompdf::class))->getFileName(), 2) . '/lib/fonts';
        $fontCache = storage_path('fonts');
        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0755, true);
        }
        $this->pdf = new Cpdf($this->paper(), false, $fontCache, sys_get_temp_dir());
        $this->font('');

        $sort = (bool) ($options['sort'] ?? false);
        if ($sort) {
            // Grouped by constellation; the order within a constellation stays.
            $rows = collect($rows)->values()
                ->sortBy([fn ($a, $b) => strcoll($a['objectconstellationfull'] ?? '', $b['objectconstellationfull'] ?? '')])
                ->values()
                ->all();
        }

        $bottom = $this->num('bottom');
        $deltaobjectline = $this->num('deltaobjectline');
        $sectionBarSpace = $this->num('sectionbarspace');
        $deltalineSection = $this->num('deltalineSection');
        $lines = $this->has('s');
        $indexlist = [];
        $actualsort = null;

        $this->firstPage();

        foreach ($rows as $row) {
            $con = (string) ($row['objectconstellationfull'] ?? '');

            $deltaymax = 0;
            foreach ($this->fields as $name => $field) {
                if ($name !== 'objectdescription' || trim($row[$name] ?? '') !== '') {
                    $deltaymax = max($deltaymax, $field['line']);
                }
            }
            $deltaymax++;

            $newSection = $sort && $con !== $actualsort;
            if ($this->y - ($this->deltaline * $deltaymax) < $bottom) {
                $this->newPage($sort ? $con : null);
                if (! $sort && $lines) {
                    $this->separator($deltaobjectline);
                }
            } elseif ($newSection) {
                if ($this->y - ($this->deltaline * $deltaymax) - $sectionBarSpace - $deltalineSection < $bottom) {
                    $this->newPage($con);
                } else {
                    $this->sectionBar($con);
                }
            } elseif ($lines) {
                $this->separator($deltaobjectline);
            }

            if ($newSection) {
                $indexlist[$con] ??= $this->pagenr;
            }

            $deltaymax = 0;
            foreach ($this->fields as $name => $field) {
                if ($this->y - ($this->deltaline * $field['line']) < $bottom) {
                    $this->newPage($sort ? $con : null);
                }

                $value = trim((string) ($row[$name] ?? ''));
                if ($value === '') {
                    continue;
                }

                $this->font($field['style']);
                $text = $field['before'] . $value . $field['after'];

                if ($name === 'objectdescription') {
                    // Descriptions run on over as many lines as they need.
                    $text = $this->textWrap($field, $text);
                    while ($text !== '') {
                        $this->y -= $this->deltaline;
                        if ($this->y - ($this->deltaline * $field['line']) < $bottom) {
                            $this->newPage($sort ? $con : null);
                            $this->font($field['style']);
                            $this->y += $this->deltaline * $field['line'];
                        }
                        $text = $this->textWrap($field, $text);
                    }
                } else {
                    $this->textWrap($field, $text);
                }

                $deltaymax = max($deltaymax, $field['line']);
                $this->font('');
            }

            $this->y -= $this->deltaline * $deltaymax;
            $this->y -= $this->deltaline + $deltaobjectline;
            $actualsort = $con;
        }

        if ($sort && $this->has('i') && $indexlist !== []) {
            $this->index($indexlist);
        }

        return $this->pdf->output();
    }

    /**
     * The first page: its header and footer, and the legend.
     */
    private function firstPage(): void
    {
        $this->y = $this->num('top');
        $this->xbase = $this->num('xleft');
        $this->pageHeader();
        $this->legend();
    }

    /**
     * The next column, or the next page when the second column is full. A
     * single column layout has its second column where the first is.
     */
    private function newPage(?string $section, bool $legend = true): void
    {
        $this->font('');
        $this->y = $this->num('top');
        $sectionBarSpace = $this->num('sectionbarspace');
        $fontSizeText = $this->num('fontSizeText');
        $xmid = $this->num('xmid');

        if ($this->xbase == $xmid) {
            $this->pagenr++;
            $this->pdf->newPage();
            $this->pageHeader();
            $this->xbase = $this->num('xleft');
        } else {
            $x = ($this->xbase + $this->sectionBarWidth + $xmid - $sectionBarSpace) / 2;
            $this->pdf->setLineStyle(0.5);
            $this->pdf->line($x, $this->num('top') + $fontSizeText, $x, $this->num('bottom') + $fontSizeText);
            $this->pdf->setLineStyle(1);
            $this->xbase = $xmid;
        }

        if ($legend) {
            $this->legend();
        }

        if ($section !== null) {
            $this->sectionBar($section);
        }
    }

    /**
     * Date, title and page number at the top; for whom the report was made
     * and tonight's ephemerides at the bottom.
     */
    private function pageHeader(): void
    {
        $xleft = $this->num('xleft');
        $header = $this->num('header');
        $footer = $this->num('footer');
        $size = $this->num('fontSizeText');
        $width = $this->num('xmid') + $this->sectionBarWidth;

        $this->font('');
        $this->addTextWrap($xleft, $header, $size, (string) ($this->options['date'] ?? ''), 100);

        if ($this->has('h') && ($this->options['prepared_for'] ?? '') !== '') {
            $this->addTextWrap($xleft, $footer, $size, (string) $this->options['prepared_for'], $width, 'center');
        }

        if ($this->has('e') && ($this->options['ephemerides'] ?? '') !== '') {
            $this->addTextWrap($xleft, $footer - $this->deltaline, $size, (string) $this->options['ephemerides'], $width, 'center');
        }

        if ($this->has('p')) {
            $this->addTextWrap(
                $this->num('xmid') + $this->sectionBarWidth - $this->num('sectionbarspace') - 100,
                $header,
                $size,
                __('Page :page', ['page' => $this->pagenr]),
                100,
                'right'
            );
        }

        if ($this->has('t')) {
            $this->addTextWrap($xleft, $header, 10, (string) ($this->options['title'] ?? ''), $width, 'center');
        }
    }

    /**
     * The legend of every field, between two lines, at the top of a column.
     */
    private function legend(): void
    {
        if (! $this->has('l')) {
            return;
        }

        $sectionBarSpace = $this->num('sectionbarspace');
        $size = $this->num('fontSizeText');
        $left = $this->xbase - $sectionBarSpace;
        $right = $this->xbase + $this->sectionBarWidth;

        $this->pdf->line($left, $this->y + $size + $sectionBarSpace, $right, $this->y + $size + $sectionBarSpace);

        $deltaymax = 0;
        foreach ($this->fields as $field) {
            $this->font($field['style']);
            $this->textWrap($field, $field['legend']);
            $this->font('');
            $deltaymax = max($deltaymax, $field['line']);
        }

        $this->y -= $this->deltaline * $deltaymax;
        $this->pdf->line($left, $this->y - $sectionBarSpace, $right, $this->y - $sectionBarSpace);
        $this->y -= $this->deltaline + $sectionBarSpace;
    }

    /**
     * The bar with the constellation name above its objects.
     */
    private function sectionBar(string $section): void
    {
        $sectionBarSpace = $this->num('sectionbarspace');
        $deltalineSection = $this->num('deltalineSection');

        $this->y -= $deltalineSection;
        $this->pdf->rectangle($this->xbase - $sectionBarSpace, $this->y - $sectionBarSpace, $this->sectionBarWidth, $this->sectionBarHeight);
        $this->font('');
        $this->pdf->addText($this->xbase, $this->y, $this->num('fontSizeSection'), self::encodable($section));
        $this->y -= $this->deltaline + $deltalineSection;
    }

    private function separator(float $deltaobjectline): void
    {
        $y = $this->y + (($this->deltaline + $deltaobjectline) * .75);

        $this->pdf->setLineStyle(0.5);
        $this->pdf->line($this->xbase - $this->num('sectionbarspace'), $y, $this->xbase + $this->sectionBarWidth, $y);
        $this->pdf->setLineStyle(1);
    }

    /**
     * The constellations and the page each starts on.
     *
     * @param  array<string, int>  $indexlist
     */
    private function index(array $indexlist): void
    {
        $sectionBarSpace = $this->num('sectionbarspace');
        $step = $this->deltaline + $this->num('deltaobjectline');
        $size = $this->num('fontSizeText');

        // The index starts on a page of its own, without the legend of the
        // objects.
        $this->xbase = $this->num('xmid');
        $this->newPage(null, false);
        $this->pdf->setLineStyle(0.5);

        foreach ($indexlist as $section => $page) {
            $this->pdf->line($this->xbase - $sectionBarSpace, $this->y + $step * .75, $this->xbase + $this->sectionBarWidth, $this->y + $step * .75);
            $this->addTextWrap($this->xbase, $this->y, $size, (string) $section, $this->sectionBarWidth - $sectionBarSpace - 50);
            $this->addTextWrap($this->xbase + $this->sectionBarWidth - $sectionBarSpace - 50, $this->y, $size, (string) $page, 50, 'right');

            $this->y -= $step;
            if ($this->y - $step < $this->num('bottom')) {
                $this->newPage(null, false);
                $this->pdf->setLineStyle(0.5);
            }
        }
    }

    /**
     * A field at its place in the current object.
     *
     * @param  array<string, mixed>  $field
     * @return string  what did not fit
     */
    private function textWrap(array $field, string $text): string
    {
        $justification = match (true) {
            str_contains($field['style'], 'r') => 'right',
            str_contains($field['style'], 'c') => 'center',
            default => 'left',
        };

        return $this->addTextWrap(
            $this->xbase + $field['x'],
            $this->y - ($this->deltaline * $field['line']),
            $this->num('fontSizeText'),
            $text,
            (float) $field['width'],
            $justification
        );
    }

    /**
     * As much of the text as fits in the width, broken after a word when it
     * can be, up to the first line break. Returns the rest.
     */
    private function addTextWrap(float $x, float $y, float $size, string $text, float $width, string $justification = 'left'): string
    {
        $text = self::encodable($text);
        if ($width <= 0 || $text === '') {
            return '';
        }

        $rest = '';
        $newline = strpos($text, "\n");
        if ($newline !== false) {
            $rest = substr($text, $newline + 1);
            $text = rtrim(substr($text, 0, $newline), "\r");
        }

        if ($this->pdf->getTextWidth($size, $text) > $width) {
            $characters = mb_str_split($text);
            $fits = 0;
            $lastSpace = null;
            $line = '';
            foreach ($characters as $i => $character) {
                if ($this->pdf->getTextWidth($size, $line . $character) > $width) {
                    break;
                }
                $line .= $character;
                $fits = $i + 1;
                if ($character === ' ') {
                    $lastSpace = $i;
                }
            }

            $breakAt = ($lastSpace !== null && $fits < count($characters)) ? $lastSpace : max(1, $fits);
            $shown = implode('', array_slice($characters, 0, $breakAt));
            $rest = ltrim(implode('', array_slice($characters, $breakAt))) . ($rest !== '' ? "\n" . $rest : '');
            $text = rtrim($shown);
        }

        $textWidth = $this->pdf->getTextWidth($size, $text);
        $x = match ($justification) {
            'right' => $x + $width - $textWidth,
            'center' => $x + ($width - $textWidth) / 2,
            default => $x,
        };

        $this->pdf->addText($x, $y, $size, $text);

        return $rest;
    }

    /**
     * Helvetica, bold and/or italic as the style asks.
     */
    private function font(string $style): void
    {
        $bold = str_contains($style, 'b');
        $italic = str_contains($style, 'i');

        $name = 'Helvetica' . match (true) {
            $bold && $italic => '-BoldOblique',
            $bold => '-Bold',
            $italic => '-Oblique',
            default => '',
        };

        $this->pdf->selectFont($this->fontDir . '/' . $name);
    }

    /**
     * The core fonts only know Windows-1252; anything else is written as the
     * nearest letter (ř as r) rather than dropped.
     */
    private static function encodable(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], $text);

        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return $converted === false ? $text : mb_convert_encoding($converted, 'UTF-8', 'Windows-1252');
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function paper(): array
    {
        $paper = CpdfAdapter::$PAPER_SIZES[strtolower($this->s['pagesize'])] ?? CpdfAdapter::$PAPER_SIZES['a4'];
        [$width, $height] = [(float) $paper[2], (float) $paper[3]];

        if ($this->s['pageorientation'] === 'landscape') {
            [$width, $height] = [max($width, $height), min($width, $height)];
        } else {
            [$width, $height] = [min($width, $height), max($width, $height)];
        }

        return [0, 0, $width, $height];
    }

    private function num(string $key): float
    {
        return (float) ($this->s[$key] ?? 0);
    }

    private function has(string $element): bool
    {
        return str_contains($this->s['showelements'] ?? '', $element);
    }
}
