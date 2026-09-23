<?php

namespace App\Services;

/**
 * What a report layout is made of, as in the old DeepskyLog: page settings,
 * and for every field of an object the line it goes on (0 is the object's
 * first line), its x position within the column, its width (0 leaves the
 * field out), its style, the texts before and after it and its legend.
 *
 * Positions and sizes are PDF points with the origin at the bottom left of
 * the page, so the layouts made in the old DeepskyLog print as they did.
 * The keys are those of the old DeepskyLog as well.
 */
class ReportLayoutCatalog
{
    public const PAGE_SIZES = ['A3', 'A4', 'A5', 'LETTER', 'LEGAL'];

    public const ORIENTATIONS = ['landscape', 'portrait'];

    /**
     * Elements of the page "showelements" can switch on: title, prepared
     * for, ephemerides, page numbers, legend, separation lines and index.
     */
    public const SHOW_ELEMENTS = 'theplsi';

    /**
     * Setting => value in the built-in default layout.
     *
     * @var array<string, string>
     */
    public const SETTINGS = [
        'pagesize' => 'A4',
        'pageorientation' => 'landscape',
        'startpagenumber' => '1',
        'top' => '550',
        'header' => '570',
        'xleft' => '20',
        'bottom' => '40',
        'footer' => '20',
        'xmid' => '431',
        'fontSizeText' => '8',
        'SectionBarWidthbase' => '400',
        'fontSizeSection' => '10',
        'deltalineextra' => '4',
        'sectionBarHeightextra' => '4',
        'deltalineSection' => '2',
        'deltaobjectline' => '4',
        'sectionbarspace' => '4',
        'showelements' => 'hetp',
    ];

    /**
     * Field => [line, x, width, style] in the built-in default layout
     * ("01 Basic A4" of the old DeepskyLog). A width of 0 leaves it out.
     *
     * @var array<string, array{0: int, 1: float, 2: float, 3: string}>
     */
    public const FIELDS = [
        'objectseen' => [0, 0, 45, ''],
        'objectlastseen' => [0, 45, 40, ''],
        'showname' => [0, 85, 70, 'bi'],
        'objectname' => [0, 0, 0, ''],
        'altname' => [0, 0, 0, ''],
        'objecttype' => [0, 145, 35, ''],
        'objecttypefull' => [0, 155, 0, ''],
        'objectconstellation' => [0, 180, 20, ''],
        'objectconstellationfull' => [0, 180, 0, ''],
        'objectmagnitude' => [0, 200, 17, ''],
        'objectsurfacebrightness' => [0, 217, 18, ''],
        'objectradecl' => [2, 235, 120, ''],
        'objectra' => [0, 0, 0, ''],
        'objectdecl' => [0, 0, 0, ''],
        'objectsizepa' => [0, 295, 55, ''],
        'objectdiam1' => [0, 0, 0, ''],
        'objectdiam2' => [0, 0, 0, ''],
        'objectsize' => [0, 0, 0, ''],
        'objectpa' => [0, 0, 0, ''],
        'objectuseratlaspage' => [0, 380, 20, 'rb'],
        'objectdescription' => [1, 20, 400, 'i'],
        'objectcontrast' => [0, 351, 17, ''],
        'objectcontrastpopup' => [0, 0, 0, ''],
        'objectcontrasttype' => [0, 0, 0, ''],
        'objectoptimalmagnification' => [0, 368, 0, ''],
        'objectoptimalmagnificationvalue' => [0, 368, 20, ''],
        'objectrise' => [0, 0, 0, ''],
        'objectrisepopup' => [0, 0, 0, ''],
        'objecttransit' => [0, 0, 0, ''],
        'objecttransitpopup' => [0, 0, 0, ''],
        'objectset' => [0, 0, 0, ''],
        'objectsetpopup' => [0, 0, 0, ''],
        'objectbest' => [0, 0, 0, ''],
        'objectmaxaltitude' => [0, 0, 0, ''],
        'objectmaxaltitudepopup' => [0, 0, 0, ''],
        'objectmaxaltstarttext' => [2, 20, 50, ''],
        'objectmaxaltmidtext' => [2, 70, 50, ''],
        'objectmaxaltendtext' => [2, 120, 50, ''],
        'objectmaxalt' => [2, 170, 50, ''],
    ];

    /** Fields that need tonight's ephemerides at the standard location. */
    public const NIGHT_FIELDS = [
        'objectrise', 'objectrisepopup', 'objecttransit', 'objecttransitpopup', 'objectset',
        'objectsetpopup', 'objectbest', 'objectmaxaltitude', 'objectmaxaltitudepopup',
    ];

    /** Fields that need the object's ephemerides through the year. */
    public const YEAR_FIELDS = ['objectmaxaltstarttext', 'objectmaxaltmidtext', 'objectmaxaltendtext', 'objectmaxalt'];

    /** Fields that come from the contrast reserve of the standard instrument. */
    public const CONTRAST_FIELDS = [
        'objectcontrast', 'objectcontrastpopup', 'objectcontrasttype',
        'objectoptimalmagnification', 'objectoptimalmagnificationvalue',
    ];

    /**
     * @return array<string, string>
     */
    public static function settingLabels(): array
    {
        return [
            'pagesize' => __('Page size (A4/A3/LETTER/...)'),
            'pageorientation' => __('Page orientation (landscape/portrait)'),
            'startpagenumber' => __('First page number'),
            'top' => __('Top margin position for text (y-coordinate)'),
            'header' => __('Position of the header line (y-coordinate)'),
            'xleft' => __('Position of the 1st column (x-coordinate)'),
            'bottom' => __('Bottom margin position for the text (y-coordinate)'),
            'footer' => __('Position of the footer line (y-coordinate)'),
            'xmid' => __('Position of the 2nd column (x-coordinate)'),
            'fontSizeText' => __('Font size text'),
            'SectionBarWidthbase' => __('Width of the section title bars (e.g. constellation)'),
            'fontSizeSection' => __('Font size for section bar'),
            'deltalineextra' => __('Extra line separation'),
            'sectionBarHeightextra' => __('Extra section bar height'),
            'deltalineSection' => __('Extra space after a section bar'),
            'deltaobjectline' => __('Extra separation between the different objects'),
            'sectionbarspace' => __('Reverse indent of the section bar'),
            'showelements' => __('Show (t) title, (h) made up for, (e) ephemerides, (p) page numbers, (l) legend, (s) separation lines, (i) index'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fieldLabels(): array
    {
        return [
            'objectseen' => __('Object seen by you or others'),
            'objectlastseen' => __('Object last seen date'),
            'showname' => __('Object name as in search (e.g. Caldwell number)'),
            'objectname' => __('Object principal name'),
            'altname' => __('Alternative object names'),
            'objecttype' => __('Object type abbreviation'),
            'objecttypefull' => __('Object type'),
            'objectconstellation' => __('Constellation abbreviation'),
            'objectconstellationfull' => __('Constellation name'),
            'objectmagnitude' => __('Object magnitude'),
            'objectsurfacebrightness' => __('Object surface brightness'),
            'objectradecl' => __('Right ascension and declination'),
            'objectra' => __('Right ascension'),
            'objectdecl' => __('Declination'),
            'objectsizepa' => __('Object size and position angle'),
            'objectdiam1' => __('Object size 1'),
            'objectdiam2' => __('Object size 2'),
            'objectsize' => __('Object size (size1 x size2)'),
            'objectpa' => __('Object orientation - position angle'),
            'objectuseratlaspage' => __('Atlas page the object is on'),
            'objectdescription' => __('Object description'),
            'objectcontrast' => __('Object contrast-visibility value'),
            'objectcontrastpopup' => __('Object contrast-visibility explanation'),
            'objectcontrasttype' => __('Object contrast-visibility type'),
            'objectoptimalmagnification' => __('Optimal magnification explanation'),
            'objectoptimalmagnificationvalue' => __('Optimal magnification value'),
            'objectrise' => __('Object rise time'),
            'objectrisepopup' => __('Object rise time explanation'),
            'objecttransit' => __('Object transit time'),
            'objecttransitpopup' => __('Object transit time explanation'),
            'objectset' => __('Object set time'),
            'objectsetpopup' => __('Object set time explanation'),
            'objectbest' => __('Best observing time'),
            'objectmaxaltitude' => __('Maximal altitude at best time'),
            'objectmaxaltitudepopup' => __('Maximal altitude explanation'),
            'objectmaxaltstarttext' => __('Highest from the month'),
            'objectmaxaltmidtext' => __('Highest around the month'),
            'objectmaxaltendtext' => __('Highest till the month'),
            'objectmaxalt' => __('Highest altitude during the year'),
        ];
    }

    /**
     * The built-in default layout, for when no DeepskyLog default layouts
     * have been imported.
     *
     * @return array{settings: array<string, string>, fields: array<string, array<string, mixed>>}
     */
    public static function builtInDefault(): array
    {
        return self::normalize(['settings' => self::SETTINGS, 'fields' => []]);
    }

    /**
     * Clean up a layout as it comes from the editor or the database: every
     * setting and field present, numbers where numbers belong, unknown keys
     * dropped.
     *
     * @param  array{settings?: array<string, mixed>, fields?: array<string, array<string, mixed>>}  $layout
     * @return array{settings: array<string, string>, fields: array<string, array{line: int, x: float, width: float, style: string, before: string, after: string, legend: string}>}
     */
    public static function normalize(array $layout): array
    {
        $settings = [];
        foreach (self::SETTINGS as $key => $default) {
            $settings[$key] = self::setting($key, $layout['settings'][$key] ?? null, $default);
        }

        $fields = [];
        foreach (self::FIELDS as $name => [$line, $x, $width, $style]) {
            $given = $layout['fields'][$name] ?? null;
            $given = is_array($given) ? $given : [];

            $fields[$name] = [
                'line' => (int) self::number($given['line'] ?? $line, 0, 50),
                'x' => self::number($given['x'] ?? $x, -2000, 5000),
                'width' => self::number($given['width'] ?? $width, 0, 5000),
                'style' => self::style($given['style'] ?? $style),
                'before' => self::text($given['before'] ?? ''),
                'after' => self::text($given['after'] ?? ''),
                'legend' => self::text($given['legend'] ?? ''),
            ];
        }

        return ['settings' => $settings, 'fields' => $fields];
    }

    private static function setting(string $key, mixed $value, string $default): string
    {
        $value = trim((string) ($value ?? ''));

        return match ($key) {
            'pagesize' => in_array(strtoupper($value), self::PAGE_SIZES, true) ? strtoupper($value) : $default,
            'pageorientation' => in_array(strtolower($value), self::ORIENTATIONS, true) ? strtolower($value) : $default,
            'showelements' => implode('', array_unique(array_filter(
                str_split(strtolower($value)),
                fn (string $c) => str_contains(self::SHOW_ELEMENTS, $c)
            ))),
            default => is_numeric($value) ? self::formatNumber(self::number($value, -5000, 5000)) : $default,
        };
    }

    private static function number(mixed $value, float $min, float $max): float
    {
        $value = is_numeric($value) ? (float) $value : 0.0;

        return max($min, min($max, $value));
    }

    private static function formatNumber(float $value): string
    {
        return (string) (0 + round($value, 2));
    }

    /**
     * Any combination of i (italic), b (bold) and l, r or c (alignment).
     */
    private static function style(mixed $value): string
    {
        return implode('', array_unique(array_filter(
            str_split(strtolower(trim((string) $value))),
            fn (string $c) => str_contains('iblrc', $c)
        )));
    }

    private static function text(mixed $value): string
    {
        return mb_substr(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 0, 255);
    }
}
