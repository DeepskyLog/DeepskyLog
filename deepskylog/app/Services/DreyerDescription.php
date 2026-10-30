<?php

namespace App\Services;

/**
 * The description of an object (objects.description) in plain words, in the
 * language of the reader.
 *
 * The descriptions come from the old site and are parts separated by a slash:
 * the description of the NGC and IC in Dreyer's abbreviations (vF,pL,iR,bM),
 * descriptions of observers in the same notation (13''-pB,vS,R at 220X), and
 * other notes (C 30, H400, Clark *, H I 53, Andromeda Galaxy). Only the parts
 * that are mostly written in the abbreviations are decoded (segment()); in
 * such a part an abbreviation it does not know stays as written.
 *
 * The descriptions are those of the Saguaro Astronomy Club database, which
 * uses the abbreviations of Dreyer (1888, the introduction of the NGC) and
 * Burnham (its SACDOC.TXT lists them, with er, P w, 11m and 9...13): modifiers
 * (MODIFIERS) before a quality (QUALITIES: pmE163 is pretty much elongated in
 * position angle 163°, vsmbM very suddenly much brighter in the middle), stars
 * (*10 nf, st 9...13, 2 st p, bet 2*) and the place in a group (p of 2, 2nd of
 * 3). Preceding is west and following east, as on the sky.
 *
 * The values of the constants are keys of resources/lang/<lang>/dreyer.php:
 * short keys, as plain words such as "poor" or "group" already have other
 * meanings in the JSON files.
 */
class DreyerDescription
{
    /** The modifiers before a quality, longest first. */
    public const MODIFIERS = [
        'ee' => 'exceedingly',
        'vv' => 'very_very',
        'e'  => 'extremely',
        'v'  => 'very',
        'c'  => 'considerably',
        'p'  => 'fairly',
        'l'  => 'slightly',
        's'  => 'suddenly',
        'g'  => 'gradually',
        'm'  => 'much',
        'i'  => 'irregularly',
        // Not in Dreyer, but the observers' descriptions use it (nC, nRi).
        'n' => 'not',
    ];

    /**
     * The qualities, longest first where one starts with another. Dreyer's b
     * (brighter) only counts in bM: alone it made Neb "nucleus, extremely
     * brighter".
     */
    public const QUALITIES = [
        'diffic' => 'difficult',
        'Dneb'   => 'double_nebula',
        'neby'   => 'nebulosity',
        'nebs'   => 'nebulous',
        'neb'    => 'nebula',
        'Neb'    => 'nebula',
        'stell'  => 'stellar',
        'spir'   => 'spiral',
        'susp'   => 'suspected',
        'trap'   => 'trapezium',
        'bi-N'   => 'binuclear',
        'biN'    => 'binuclear',
        'triN'   => 'trinuclear',
        'bMN'    => 'brighter_middle_nucleus',
        'BMN'    => 'brighter_middle_nucleus',
        'irR'    => 'irregularly_round',
        'irr'    => 'irregular',
        'Irr'    => 'irregular',
        'dif'    => 'diffuse',
        'def'    => 'defined',
        'com'    => 'cometic',
        'var'    => 'variable',
        'rrr'    => 'well_resolved',
        'rr'     => 'partially_resolved',
        'r'      => 'resolvable',
        'iF'     => 'irregular_figure',
        'RR'     => 'exactly_round',
        'Ri'     => 'rich',
        'Cl'     => 'cluster',
        'CM'     => 'compressed_middle',
        'BM'     => 'brighter_middle',
        'BN'     => 'bright_nucleus',
        'SN'     => 'small_nucleus',
        'bM'     => 'brighter_middle',
        'co'     => 'coarse',
        'sc'     => 'scattered',
        'gr'     => 'group',
        'R'      => 'round',
        'C'      => 'compressed',
        'B'      => 'bright',
        'F'      => 'faint',
        'L'      => 'large',
        'S'      => 'small',
        'P'      => 'poor',
        'D'      => 'double',
        'N'      => 'nucleus',
        '!!!'    => 'magnificent',
        '!!'     => 'very_remarkable',
        '!'      => 'remarkable',
        '?'      => 'uncertain',
    ];

    /** The words that stand alone: directions, prepositions. */
    public const WORDS = [
        'n'       => 'north',
        's'       => 'south',
        'p'       => 'west',
        'f'       => 'east',
        'np'      => 'north_west',
        'nf'      => 'north_east',
        'sp'      => 'south_west',
        'sf'      => 'south_east',
        'inv'     => 'involved',
        'incl'    => 'including',
        'att'     => 'attached',
        'app'     => 'appended',
        'cont'    => 'in_contact',
        'nr'      => 'near',
        'am'      => 'among',
        'bet'     => 'between',
        'sev'     => 'several',
        'ab'      => 'about',
        'alm'     => 'almost',
        'dist'    => 'distant',
        'st'      => 'stars',
        'stellar' => 'stellar',
        'not'     => 'not',
        'or'      => 'or',
        'and'     => 'and',
        '&'       => 'and',
        'w'       => 'with',
        'in'      => 'in',
        'groups'  => 'groups',
        'mag'     => 'magnitude',
        'mags'    => 'magnitudes',
        'diam'    => 'diameter',
        // Easily resolvable, in the NGC 2000 descriptions of galaxies.
        'er' => 'easily_resolvable',
    ];

    /** The line of an extended object (E pf), after the direction it joins. */
    public const ORIENTATIONS = [
        'pf' => 'east_west',
        'ns' => 'north_south',
        'np' => 'north_west_south_east',
        'sf' => 'north_west_south_east',
        'nf' => 'north_east_south_west',
        'sp' => 'north_east_south_west',
    ];

    /** Where a star is: a direction, or one of these. */
    public const STAR_PLACES = [
        'n'   => 'to_north',
        's'   => 'to_south',
        'p'   => 'to_west',
        'f'   => 'to_east',
        'np'  => 'to_north_west',
        'nf'  => 'to_north_east',
        'sp'  => 'to_south_west',
        'sf'  => 'to_south_east',
        'M'   => 'in_middle',
        'inv' => 'involved',
        'att' => 'attached',
        'nr'  => 'nearby',
    ];

    /** The stars: a star, a double star, … */
    public const STARS = [
        '*'   => 'a_star',
        '**'  => 'a_double_star',
        'D*'  => 'a_double_star',
        '***' => 'a_triple_star',
    ];

    /** The place in a group: 2nd of 3 (or 2 of 3), else number_of. */
    public const ORDINALS = [
        '1'    => 'first_of',
        '2'    => 'second_of',
        '3'    => 'third_of',
        '4'    => 'fourth_of',
        '5'    => 'fifth_of',
        'last' => 'last_of',
    ];

    /** A part counts as written in the abbreviations from this share of its tokens. */
    public const DECODED_SHARE = 0.5;

    /** Longer words are not abbreviations (and would take long to try). */
    private const MAX_WORD_LENGTH = 24;

    /**
     * The description in plain words: the decoded parts joined by
     * semicolons, null when no part is written in the abbreviations.
     */
    public static function plain(?string $description): ?string
    {
        $description = trim(html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($description === '') {
            return null;
        }

        $parts = [];
        foreach (explode('/', $description) as $segment) {
            $plain = self::segment($segment);
            if ($plain !== null) {
                $parts[] = $plain;
            }
        }

        return $parts === [] ? null : implode('; ', $parts);
    }

    /**
     * One part in plain words, null when it is not mostly written in the
     * abbreviations.
     */
    public static function segment(string $segment): ?string
    {
        $segment = trim($segment);

        // The instrument of an observer's description: 13''-pB, 10X50-vB.
        $instrument = null;
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(?:\'\'|"|″)\s*-\s*/u', $segment, $m)) {
            $instrument = self::t('in_telescope', ['aperture' => $m[1]]);
            $segment    = substr($segment, strlen($m[0]));
        } elseif (preg_match('/^(\d+)\s*[xX×]\s*(\d+)\s*-\s*/u', $segment, $m)) {
            $instrument = self::t('in_binoculars', ['binoculars' => $m[1].'×'.$m[2]]);
            $segment    = substr($segment, strlen($m[0]));
        }

        $tokens = array_values(array_filter(array_map('trim', explode(',', $segment)), fn ($t) => $t !== ''));
        if ($tokens === []) {
            return null;
        }

        $phrases = [];
        $decoded = 0;
        $quality = false;
        foreach ($tokens as $token) {
            $result = self::token($token);
            if ($result === null) {
                $phrases[] = $token;

                continue;
            }
            [$phrase, $hasQuality] = $result;
            $phrases[]             = $phrase;
            $decoded++;
            $quality = $quality || $hasQuality;
        }

        // A part of only words such as "near" or "several" is a note.
        if (! $quality || $decoded < self::DECODED_SHARE * count($tokens)) {
            return null;
        }

        $text = implode(', ', $phrases);

        return $instrument === null ? self::upperFirst($text) : $instrument.' '.$text;
    }

    /**
     * A token (the text between two commas) in plain words, with whether it
     * holds a quality, star or place in a group; null when it is not written
     * in the abbreviations.
     *
     * @return array{0: string, 1: bool}|null
     */
    private static function token(string $token): ?array
    {
        $token = (string) preg_replace('/\s+/', ' ', $token);
        // !! vB is one word: very remarkable, very bright.
        $token = (string) preg_replace('/^(!{1,3}) (?=\S)/', '$1', $token);

        // Observers' notes end with the magnification (nBM at 220X).
        $suffix = '';
        if (preg_match('/^(.+?)\s+at\s+(\d+)\s*[xX×]$/u', $token, $m)) {
            $token  = $m[1];
            $suffix = ' '.self::t('at_magnification', ['magnification' => $m[2]]);
        }

        $result = self::phrase($token);

        return $result === null ? null : [$result[0].$suffix, $result[1]];
    }

    /**
     * A token without its magnification in plain words, as token().
     *
     * @return array{0: string, 1: bool}|null
     */
    private static function phrase(string $token): ?array
    {
        // Paired with: P w NGC 3501, P w N4566, P w U 2446.
        if (preg_match('/^P ?w ([NU](?= ?\d))? ?([A-Za-z]* ?\d[\d.+-]*[A-Za-z]?)$/', $token, $m)) {
            $object = match ($m[1]) {
                'N'     => 'NGC '.ltrim($m[2]),
                'U'     => 'UGC '.ltrim($m[2]),
                default => $m[2],
            };

            return [self::t('paired_with', ['object' => $object]), true];
        }

        if (preg_match('/^(?:(\d+)(?:st|nd|rd|th)?|(last)) of (\d+)$/i', $token, $m)) {
            $position = $m[2] !== '' ? 'last' : $m[1];

            return [isset(self::ORDINALS[$position])
                ? self::t(self::ORDINALS[$position], ['count' => $m[3]])
                : self::t('number_of', ['position' => $position, 'count' => $m[3]]), true];
        }
        if (preg_match('/^(np|nf|sp|sf|n|s|p|f) of (\d+|Dneb)$/', $token, $m)) {
            $place = self::t(self::STAR_PLACES[$m[1]]);

            return [$m[2] === 'Dneb'
                ? self::t('member_of_double_nebula', ['direction' => $place])
                : self::t('member_of_group', ['direction' => $place, 'count' => $m[2]]), true];
        }
        if (preg_match('/^(np|nf|sp|sf|n|s|p|f) ?(\d+(?:\.\d+)?) ?(\'\'|"|″|\'|′)$/u', $token, $m)) {
            return [self::t(self::STAR_PLACES[$m[1]]).' '.self::distance($m[2], $m[3]), true];
        }

        $star = self::stars($token);
        if ($star !== null) {
            return [$star, true];
        }

        // neb&st is nebula and stars.
        $token = trim((string) preg_replace('/\s*&\s*/', ' & ', $token));

        // Join a position angle or line to its E: lE 90 degrees, E pf.
        $token = (string) preg_replace('/E ?(\d{1,3})(?: ?(?:°|deg|degrees?))?(?=\s|$)/u', 'E$1', $token);
        $token = (string) preg_replace('/E (pf|ns|np|nf|sp|sf)(?=\s|$)/', 'E@$1', $token);

        $words   = [];
        $quality = false;
        foreach (explode(' ', $token) as $word) {
            // A capital I for the l of lE.
            if ($word === 'IE') {
                $word = 'lE';
            }
            if (isset(self::WORDS[$word])) {
                $words[] = self::t(self::WORDS[$word]);

                continue;
            }
            // Brighter in the middle to a star: vsmbM *10.
            if ($words !== [] && preg_match('/^\*(\d+(?:\.\d+)?)?$/', $word, $m)) {
                $words[] = self::unitText(['modifiers' => [], 'quality' => '*', 'value' => $m[1] ?? null]);

                continue;
            }
            if (preg_match('/^(\d+)st$/', $word, $m)) {
                $words[] = self::t('count_stars', ['count' => $m[1]]);
                $quality = true;

                continue;
            }
            $units = strlen($word) <= self::MAX_WORD_LENGTH ? self::units($word, 0, []) : null;
            if ($units === null || $units === []) {
                return null;
            }
            $words[] = implode(', ', array_map(fn ($u) => self::unitText($u), $units));
            $quality = true;
        }

        return [implode(' ', $words), $quality];
    }

    /** The stars of a token (*10 nf, st 9...13, st vS, bet 2*), else null. */
    private static function stars(string $token): ?string
    {
        // st 9...13, * 11..., 12...14, 16* mags 11..13.
        if (preg_match('/^(?:(\d+) ?\* ?)?(?:st|\*|stars)? ?(?:mags? )?(\d+) ?\.{2,} ?(\d+)?\.*$/', $token, $m)) {
            [, $count, $from] = $m;
            $to               = $m[3] ?? '';
            if ($count !== '') {
                return $to !== ''
                    ? self::t('count_stars_from_to', ['count' => $count, 'from' => $from, 'to' => $to])
                    : self::t('count_stars_from', ['count' => $count, 'from' => $from]);
            }

            return $to !== ''
                ? self::t('stars_from_to', ['from' => $from, 'to' => $to])
                : self::t('stars_from', ['from' => $from]);
        }
        if (preg_match('/^st ?(\d+)$/', $token, $m)) {
            return self::t('stars_of_magnitude', ['magnitude' => $m[1]]);
        }
        if (preg_match('/^st ?L ?(?:&|and) ?S$/', $token)) {
            return self::t('bright_and_faint_stars');
        }
        // The size of the stars is their brightness: st L are bright stars.
        if (preg_match('/^st ((?:ee|vv|e|v|c|p|l)*)(L|S)$/', $token, $m)) {
            return self::t('quality_stars', ['quality' => self::starQuality($m[1], $m[2])]);
        }
        if (preg_match('/^(?:bet|between) (\d+) ?(?:\*|st|stars)$/', $token, $m)) {
            return self::t('between_stars', ['count' => $m[1]]);
        }
        if (preg_match('/^am (\d+) ?(?:\*|st)$/', $token, $m)) {
            return self::t('among_stars', ['count' => $m[1]]);
        }

        // A star, with a number (2* nr, 2 st p), its brightness (vS* att,
        // F* inv), magnitude (*9.5, *12m), place and distance (*12 f 1').
        if (! preg_match('/^(?:(\d+) ?)?((?:ee|vv|e|v|c|p|l)*)(S|F|B|L)?(\*{1,3}|D\*|st) ?(?:(\d+(?:\.\d+)?)m?)? ?(np|nf|sp|sf|inv|att|nr|n|s|p|f|M)? ?(?:(\d+(?:\.\d+)?) ?(\'\'|"|″|\'|′))?$/u', $token, $m)) {
            return null;
        }
        [, $count, $modifiers, $size, $stars] = $m;
        if ($stars === 'st') {
            if ($count === '') {
                return null;
            }
            $stars = '*';
        }
        if (($modifiers !== '' && $size === '') || (($count !== '' || $size !== '') && $stars !== '*')) {
            return null;
        }
        $quality = $size === '' ? null : self::starQuality($modifiers, $size);
        $text    = match (true) {
            $count   !== '' && $quality !== null => [self::t('count_quality_stars', ['count' => $count, 'quality' => $quality])],
            $count   !== ''                      => [self::t('count_stars', ['count' => $count])],
            $quality !== null                    => [self::t('a_quality_star', ['quality' => $quality])],
            default                              => [self::t(self::STARS[$stars])],
        };
        if (($m[5] ?? '') !== '') {
            $text[] = self::t('of_magnitude', ['magnitude' => $m[5]]);
        }
        if (($m[6] ?? '') !== '') {
            $text[] = self::t(self::STAR_PLACES[$m[6]]);
        }
        if (($m[7] ?? '') !== '') {
            $text[] = self::distance($m[7], $m[8]);
        }

        return implode(' ', $text);
    }

    /** A star of size L or B is bright, of size S or F faint. */
    private static function starQuality(string $modifiers, string $size): string
    {
        return self::withModifiers(self::modifiers($modifiers), in_array($size, ['B', 'L'], true) ? 'bright' : 'faint');
    }

    private static function distance(string $distance, string $unit): string
    {
        return self::t(in_array($unit, ["'", '′'], true) ? 'away_arcmin' : 'away_arcsec', ['distance' => $distance]);
    }

    /**
     * A word as a list of qualities, each with its modifiers (vsmbM is one,
     * sbMvSN two), null when it is not one. The qualities are tried before
     * the modifiers, so sc is scattered and not suddenly compressed. The
     * value of E is its position angle or line (E@pf), of * its magnitude.
     *
     * @param  list<string>  $modifiers
     * @return list<array{modifiers: list<string>, quality: string, value: ?string}>|null
     */
    private static function units(string $word, int $at, array $modifiers): ?array
    {
        if ($at === strlen($word)) {
            return $modifiers === [] ? [] : null;
        }

        // Brighter in the middle to a star: vsmbM*12.
        if ($at > 0 && $modifiers === [] && preg_match('/\G\*(\d+(?:\.\d+)?)?$/', $word, $m, 0, $at)) {
            return [['modifiers' => [], 'quality' => '*', 'value' => $m[1] ?? null]];
        }
        if (preg_match('/\GE(\d{1,3}|@(?:pf|ns|np|nf|sp|sf))?/', $word, $m, 0, $at)) {
            $rest = self::units($word, $at + strlen($m[0]), []);
            if ($rest !== null) {
                return [['modifiers' => $modifiers, 'quality' => 'E', 'value' => ($m[1] ?? '') !== '' ? $m[1] : null], ...$rest];
            }
        }
        foreach (self::QUALITIES as $abbreviation => $key) {
            if (substr_compare($word, $abbreviation, $at, strlen($abbreviation)) === 0) {
                $rest = self::units($word, $at + strlen($abbreviation), []);
                if ($rest !== null) {
                    return [['modifiers' => $modifiers, 'quality' => $abbreviation, 'value' => null], ...$rest];
                }
            }
        }
        foreach (self::MODIFIERS as $abbreviation => $key) {
            if (substr_compare($word, $abbreviation, $at, strlen($abbreviation)) === 0) {
                $units = self::units($word, $at + strlen($abbreviation), [...$modifiers, $abbreviation]);
                if ($units !== null) {
                    return $units;
                }
            }
        }

        return null;
    }

    /** @param  array{modifiers: list<string>, quality: string, value: ?string}  $unit */
    private static function unitText(array $unit): string
    {
        $value = $unit['value'];
        if ($unit['quality'] === '*') {
            return $value === null ? self::t('with_star') : self::t('with_star_of_magnitude', ['magnitude' => $value]);
        }
        if ($unit['quality'] !== 'E') {
            return self::withModifiers($unit['modifiers'], self::QUALITIES[$unit['quality']]);
        }

        $text = self::withModifiers($unit['modifiers'], 'extended');
        if ($value !== null && str_starts_with($value, '@')) {
            $text .= ' '.self::t(self::ORIENTATIONS[substr($value, 1)]);
        } elseif ($value !== null) {
            $text .= ' '.self::t('in_position_angle', ['angle' => (int) $value]);
        }

        return $text;
    }

    /** @return list<string> */
    private static function modifiers(string $abbreviations): array
    {
        $modifiers = [];
        while ($abbreviations !== '') {
            foreach (self::MODIFIERS as $abbreviation => $key) {
                if (str_starts_with($abbreviations, $abbreviation)) {
                    $modifiers[]   = $abbreviation;
                    $abbreviations = substr($abbreviations, strlen($abbreviation));

                    continue 2;
                }
            }

            break;
        }

        return $modifiers;
    }

    /** @param  list<string>  $modifiers */
    private static function withModifiers(array $modifiers, string $quality): string
    {
        $words   = array_map(fn ($m) => self::t(self::MODIFIERS[$m]), $modifiers);
        $words[] = self::t($quality);

        return implode(' ', $words);
    }

    /** @param  array<string, string|int>  $replace */
    private static function t(string $key, array $replace = []): string
    {
        return (string) __('dreyer.'.$key, $replace);
    }

    private static function upperFirst(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }
}
