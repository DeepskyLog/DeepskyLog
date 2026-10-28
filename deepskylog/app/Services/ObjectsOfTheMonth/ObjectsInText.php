<?php

namespace App\Services\ObjectsOfTheMonth;

use App\Models\Observation;
use App\Services\ObservingListItemNameResolver;
use Illuminate\Support\Facades\DB;

/**
 * The objects a text names: the objects of the month an Astroforum thread
 * proposes (in Dutch), found by catalogue designation ("NGC 1662", "M42/43",
 * "Messiers 58, 59 en 60", "Sh2-296"), star ("17 Comae Berenices", "Alpha
 * CMa"), asteroid ("433 Eros", "Vesta"), comet ("12P", "C/2023 A3"), planet
 * ("Saturnus") or proper name ("Albireo", "Orionnevel"), each looked up as
 * the observing list import does (ObservingListItemNameResolver). A text
 * also names objects only to compare or to find the way, so the result is
 * a proposal that a person confirms.
 */
class ObjectsInText
{
    /** Dutch names of the planets and of the asteroids with a name of their own. */
    private const DUTCH_NAMES = [
        'mercurius' => 'Mercury', 'venus' => 'Venus', 'mars' => 'Mars', 'jupiter' => 'Jupiter',
        'saturnus' => 'Saturn', 'uranus' => 'Uranus', 'neptunus' => 'Neptune', 'pluto' => 'Pluto',
    ];

    /** The written forms of a catalogue that mean a list of numbers ("Messiers 58, 59 en 60"). */
    private const PLURAL_PREFIXES = ['messiers' => 'M', "messier's" => 'M', 'ngcs' => 'NGC', "ngc's" => 'NGC'];

    /** Prefixes written out that DeepskyLog stores shorter. */
    private const PREFIX_SYNONYMS = ['messier' => 'M', 'collinder' => 'Cr', 'melotte' => 'Mel', 'trumpler' => 'Tr', 'c' => 'Caldwell'];

    /** Catalogues whose designations are reported when DeepskyLog does not know them. */
    private const KNOWN_CATALOGUES = [
        'M', 'NGC', 'IC', 'Cr', 'Mel', 'Tr', 'Abell', 'Arp', 'Hickson', 'HCG', 'UGC', 'PGC', 'MCG', 'Sh 2', 'LBN', 'LDN',
        'B', 'Barnard', 'vdB', 'PK', 'Ced', 'Pal', 'Mrk', 'Stock', 'King', 'Berkeley', 'Be', 'Ruprecht', 'Czernik', 'Pismis',
        'Dolidze', 'Haffner', 'Basel', 'STF', 'STT', 'STFA', 'HJ', 'Σ', 'OΣ', 'OΣΣ', 'Kemble', 'Caldwell',
    ];

    /** The Greek letters of a Bayer name, as DeepskyLog writes them. */
    private const GREEK = [
        'alpha', 'beta', 'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'theta', 'iota', 'kappa', 'lambda', 'mu',
        'nu', 'xi', 'omicron', 'pi', 'rho', 'sigma', 'tau', 'upsilon', 'phi', 'chi', 'psi', 'omega',
    ];

    private const GREEK_LETTERS = [
        'α' => 'alpha', 'β' => 'beta', 'γ' => 'gamma', 'δ' => 'delta', 'ε' => 'epsilon', 'ζ' => 'zeta', 'η' => 'eta',
        'θ' => 'theta', 'ι' => 'iota', 'κ' => 'kappa', 'λ' => 'lambda', 'μ' => 'mu', 'ν' => 'nu', 'ξ' => 'xi',
        'ο' => 'omicron', 'π' => 'pi', 'ρ' => 'rho', 'σ' => 'sigma', 'τ' => 'tau', 'υ' => 'upsilon', 'φ' => 'phi',
        'χ' => 'chi', 'ψ' => 'psi', 'ω' => 'omega',
    ];

    /** The genitives of the constellations and their abbreviations, for "17 Comae Berenices". */
    private const GENITIVES = [
        'Andromedae' => 'And', 'Antliae' => 'Ant', 'Apodis' => 'Aps', 'Aquarii' => 'Aqr', 'Aquilae' => 'Aql', 'Arae' => 'Ara',
        'Arietis' => 'Ari', 'Aurigae' => 'Aur', 'Bootis' => 'Boo', 'Caeli' => 'Cae', 'Camelopardalis' => 'Cam', 'Cancri' => 'Cnc',
        'Canum Venaticorum' => 'CVn', 'Canis Majoris' => 'CMa', 'Canis Minoris' => 'CMi', 'Capricorni' => 'Cap', 'Carinae' => 'Car',
        'Cassiopeiae' => 'Cas', 'Centauri' => 'Cen', 'Cephei' => 'Cep', 'Ceti' => 'Cet', 'Chamaeleontis' => 'Cha', 'Circini' => 'Cir',
        'Columbae' => 'Col', 'Comae Berenices' => 'Com', 'Coronae Australis' => 'CrA', 'Coronae Borealis' => 'CrB', 'Corvi' => 'Crv',
        'Crateris' => 'Crt', 'Crucis' => 'Cru', 'Cygni' => 'Cyg', 'Delphini' => 'Del', 'Doradus' => 'Dor', 'Draconis' => 'Dra',
        'Equulei' => 'Equ', 'Eridani' => 'Eri', 'Fornacis' => 'For', 'Geminorum' => 'Gem', 'Gruis' => 'Gru', 'Herculis' => 'Her',
        'Horologii' => 'Hor', 'Hydrae' => 'Hya', 'Hydri' => 'Hyi', 'Indi' => 'Ind', 'Lacertae' => 'Lac', 'Leonis Minoris' => 'LMi',
        'Leonis' => 'Leo', 'Leporis' => 'Lep', 'Librae' => 'Lib', 'Lupi' => 'Lup', 'Lyncis' => 'Lyn', 'Lyrae' => 'Lyr',
        'Mensae' => 'Men', 'Microscopii' => 'Mic', 'Monocerotis' => 'Mon', 'Muscae' => 'Mus', 'Normae' => 'Nor', 'Octantis' => 'Oct',
        'Ophiuchi' => 'Oph', 'Orionis' => 'Ori', 'Pavonis' => 'Pav', 'Pegasi' => 'Peg', 'Persei' => 'Per', 'Phoenicis' => 'Phe',
        'Pictoris' => 'Pic', 'Piscium' => 'Psc', 'Piscis Austrini' => 'PsA', 'Puppis' => 'Pup', 'Pyxidis' => 'Pyx',
        'Reticuli' => 'Ret', 'Sagittae' => 'Sge', 'Sagittarii' => 'Sgr', 'Scorpii' => 'Sco', 'Sculptoris' => 'Scl', 'Scuti' => 'Sct',
        'Serpentis' => 'Ser', 'Sextantis' => 'Sex', 'Tauri' => 'Tau', 'Telescopii' => 'Tel', 'Trianguli Australis' => 'TrA',
        'Trianguli' => 'Tri', 'Tucanae' => 'Tuc', 'Ursae Majoris' => 'UMa', 'Ursae Minoris' => 'UMi', 'Velorum' => 'Vel',
        'Virginis' => 'Vir', 'Volantis' => 'Vol', 'Vulpeculae' => 'Vul',
    ];

    /** The kind of observation (observations.target_type) of each table of the search index. */
    private const TARGET_TYPES = [
        'objects' => Observation::TYPE_DEEPSKY, 'cometobjects' => Observation::TYPE_COMET, 'planets' => 'planet',
        'moons' => 'lunar', 'lunar_features' => 'lunar', 'asteroids' => 'solarsystem',
    ];

    public function __construct(private ObservingListItemNameResolver $resolver = new ObservingListItemNameResolver) {}

    /**
     * The objects the title and text name, most likely the objects of the
     * month first (named in the title, then most often), each as target()
     * gives it plus the number of mentions; and the designations of known
     * catalogues DeepskyLog does not have.
     *
     * @return array{objects: list<array<string, mixed>>, unresolved: list<string>}
     */
    public function find(string $title, string $text): array
    {
        $inTitle = $this->names($title);
        $inText = $this->names($text);

        $mentions = [];
        foreach ([$inTitle, $inText] as $names) {
            foreach ($names as $name) {
                $mentions[$name] = ($mentions[$name] ?? 0) + 1;
            }
        }

        $resolved = $this->resolver->resolveMany(array_keys($mentions));
        $targets = $this->targets(array_filter($resolved));

        $objects = [];
        $unresolved = [];
        $order = 0;
        foreach ($mentions as $name => $count) {
            $canonical = $resolved[$this->resolver->clean($name)] ?? null;
            $target = $canonical !== null ? ($targets[$canonical] ?? null) : null;
            if ($target === null) {
                // Any number before a capitalised word looks like an asteroid: only those of the title count
                if ($this->isCatalogueDesignation($name) || (preg_match('/^\(\d+\) \p{Lu}/u', $name) && in_array($name, $inTitle, true))) {
                    $unresolved[$name] = true;
                }

                continue;
            }
            $key = $target['type'].':'.$target['key'];
            if (! isset($objects[$key])) {
                $objects[$key] = $target + ['mentions' => 0, 'in_title' => false, 'order' => $order++];
            }
            $objects[$key]['mentions'] += $count;
            $objects[$key]['in_title'] = $objects[$key]['in_title'] || in_array($name, $inTitle, true);
        }

        usort($objects, fn (array $a, array $b) => [! $a['in_title'], -$a['mentions'], $a['order']] <=> [! $b['in_title'], -$b['mentions'], $b['order']]);

        return [
            'objects' => array_map(fn (array $object) => array_diff_key($object, ['order' => true]), $objects),
            'unresolved' => array_keys($unresolved),
        ];
    }

    /**
     * The objects of the given names (one per name, as people type them),
     * keyed by the name; null for a name DeepskyLog does not know.
     *
     * @param  list<string>  $names
     * @return array<string, array<string, string>|null>
     */
    public function resolve(array $names): array
    {
        $candidates = [];
        foreach ($names as $name) {
            $candidates[$name] = array_values(array_unique(array_merge([$this->resolver->clean($name)], $this->names($name))));
        }
        $resolved = $this->resolver->resolveMany(array_merge(...array_values($candidates) ?: [[]]));
        $targets = $this->targets(array_filter($resolved));

        $result = [];
        foreach ($candidates as $name => $variants) {
            $result[$name] = null;
            foreach ($variants as $variant) {
                $canonical = $resolved[$this->resolver->clean($variant)] ?? null;
                if ($canonical !== null && isset($targets[$canonical])) {
                    $result[$name] = $targets[$canonical];

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * The names written in the text that may be objects, in the order they
     * appear, once per mention, in the spelling the resolver looks up.
     *
     * @return list<string>
     */
    public function names(string $text): array
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $text)));
        $found = [];
        $add = function (int $offset, string $name) use (&$found) {
            $found[] = [$offset, $name];
        };

        // Catalogue designations, with the numbers that follow them after a slash
        // ("M42/43"), or after a plural ("Messiers 58, 59 en 60")
        $designation = '/(?<![\p{L}\d\/])(Sh\s?2|OΣΣ|OΣ|Σ|[A-Z][A-Za-z]{0,9}\'?s?)[\s-]?(\d{1,6}(?:[+-]\d{2,3}(?:\.\d{1,2})?)?(?:[A-Z]\b)?)((?:\s?\/\s?\d{1,6}|(?:,\s?|\s(?:en|and|&)\s)\d{1,6})*)/u';
        preg_match_all($designation, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $written = $match[1][0];
            $plural = self::PLURAL_PREFIXES[mb_strtolower($written)] ?? null;
            $prefix = $plural ?? self::PREFIX_SYNONYMS[mb_strtolower($written)] ?? $written;
            if (preg_match('/^Sh\s?2$/', $prefix)) {
                $prefix = 'Sh 2-';
            }
            $separator = str_ends_with($prefix, '-') ? '' : ' ';
            $add($match[0][1], $prefix.$separator.$match[2][0]);
            if (str_contains($match[3][0], '/') || $plural !== null) {
                foreach (preg_split('/\s?\/\s?|,\s?|\s(?:en|and|&)\s/u', $match[3][0], -1, PREG_SPLIT_NO_EMPTY) as $number) {
                    // "NGC 1973/75/77": a shorter number replaces the last digits
                    $number = trim($number);
                    $base = (string) preg_replace('/\D.*$/', '', $match[2][0]);
                    if (strlen($number) < strlen($base)) {
                        $number = substr($base, 0, strlen($base) - strlen($number)).$number;
                    }
                    $add($match[0][1], $prefix.$separator.$number);
                }
            }
        }

        // Stars: a Flamsteed number or Bayer letter and a constellation, written out or abbreviated
        $genitives = implode('|', array_map(fn (string $genitive) => str_replace(' ', '\s', $genitive), array_keys(self::GENITIVES)));
        $letters = implode('|', array_merge(self::GREEK, array_keys(self::GREEK_LETTERS)));
        preg_match_all('/(?<![\p{L}\d])(\d{1,3}|'.$letters.')(?:[\s-]?(\d))?\s('.$genitives.'|[A-Z][a-zA-Z]{2})(?![\p{L}])/iu', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $star = mb_strtolower($match[1][0]);
            $star = self::GREEK_LETTERS[$star] ?? $star;
            $star = ctype_digit($star) ? $star : ucfirst($star);
            if (! empty($match[2][0])) {
                $star .= '-'.$match[2][0];
            }
            $constellation = $this->constellation($match[3][0]);
            if ($constellation !== null) {
                $add($match[0][1], $star.' '.$constellation);
            }
        }

        // Numbered asteroids ("433 Eros", "(4) Vesta")
        preg_match_all('/(?<![\p{L}\d])\(?(\d{1,6})\)?\s([A-Z][a-z]{2,})/u', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $add($match[0][1], '('.$match[1][0].') '.$match[2][0]);
        }

        // Comets ("12P", "12P/Pons-Brooks", "C/2023 A3")
        preg_match_all('/(?<![\p{L}\d])(\d{1,4}P(?:\/[A-Z][\p{L}-]+)?|[CP]\/\d{4}\s?[A-Z]{1,2}\d{0,3})/u', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $add($match[0][1], $this->comet($match[1][0]) ?? $match[1][0]);
        }

        // Planets in Dutch, proper names ("Albireo") and Dutch nebulae ("Orionnevel")
        preg_match_all('/(?<![\p{L}\d-])(\p{Lu}[\p{L}-]{2,})(?![\p{L}\d])/u', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $words = [];
        foreach ($matches as $match) {
            $word = $match[1][0];
            $name = self::DUTCH_NAMES[mb_strtolower($word)] ?? (preg_match('/^(\p{Lu}[\p{L}-]+?)-?nevel$/u', $word, $nebula) ? $nebula[1].' Nebula' : $word);
            $words[] = [$match[0][1], $name];
        }
        foreach ($this->properNames(array_column($words, 1)) as $offset => $name) {
            $add($words[$offset][0], $name);
        }

        usort($found, fn (array $a, array $b) => $a[0] <=> $b[0]);

        return array_column($found, 1);
    }

    /**
     * Of the capitalised words, those the search index holds as a name of
     * their own (a proper name such as "Albireo", a planet or an asteroid),
     * keyed by their position in $words. Catalogue designations are found
     * above; a word such as "Messier" or "Orion" is no object.
     *
     * @param  list<string>  $words
     * @return array<int, string>
     */
    private function properNames(array $words): array
    {
        $unique = array_values(array_unique($words));
        if ($unique === []) {
            return [];
        }
        $known = [];
        foreach (array_chunk($unique, 500) as $chunk) {
            foreach (DB::table('search_index')->whereIn('name', $chunk)->whereIn('source_table', ['objects', 'planets', 'asteroids'])->pluck('name') as $name) {
                $known[mb_strtolower((string) $name)] = true;
            }
        }

        return array_filter($words, fn (string $word) => isset($known[mb_strtolower($word)]) && mb_strtolower($word) !== 'sun');
    }

    /** The full name of a comet written by its number or designation alone ("12P" for "12P/Pons-Brooks"). */
    private function comet(string $written): ?string
    {
        $prefix = preg_match('/^\d+P$/', $written) ? $written.'/' : $written;

        return DB::table('cometobjects')->where('name', $written)->orWhere('name', 'like', $prefix.'%')->orderBy('name')->value('name');
    }

    private function constellation(string $written): ?string
    {
        $written = (string) preg_replace('/\s+/u', ' ', $written);
        foreach (self::GENITIVES as $genitive => $abbreviation) {
            if (strcasecmp($genitive, $written) === 0 || $abbreviation === $written) {
                return $abbreviation;
            }
        }

        return null;
    }

    private function isCatalogueDesignation(string $name): bool
    {
        foreach (self::KNOWN_CATALOGUES as $catalogue) {
            if (preg_match('/^'.preg_quote($catalogue, '/').'[\s-]+\d/u', $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The object of each canonical name (as the resolver gives it): its name,
     * the table, slug and kind of observation, and the key the observations
     * of it are stored under (observations.target_key: objects.name for a
     * deep-sky object, the id of a comet, else the slug).
     *
     * @param  array<string, string>  $canonical
     * @return array<string, array<string, string>>
     */
    private function targets(array $canonical): array
    {
        $names = array_values(array_unique($canonical));
        if ($names === []) {
            return [];
        }

        $targets = [];
        foreach (DB::table('objects')->whereIn('name', $names)->get(['name', 'slug', 'type']) as $object) {
            $targets[$object->name] = [
                'name' => (string) $object->name, 'table' => 'objects', 'slug' => (string) $object->slug,
                'type' => Observation::TYPE_DEEPSKY, 'key' => (string) $object->name, 'object_type' => (string) $object->type,
            ];
        }

        $others = DB::table('search_index')
            ->whereIn('name', array_diff($names, array_keys($targets)))
            ->whereIn('source_table', array_keys(array_diff_key(self::TARGET_TYPES, ['objects' => true])))
            ->get(['name', 'source_table', 'source_pk']);
        foreach ($others as $row) {
            // An alias ("Vesta") gives the object under its own name ("(4) Vesta")
            $record = DB::table($row->source_table)->where('id', $row->source_pk)->first(['id', 'name', 'slug']);
            if ($record === null || isset($targets[$row->name])) {
                continue;
            }
            $targets[$row->name] = [
                'name' => (string) $record->name, 'table' => (string) $row->source_table, 'slug' => (string) $record->slug,
                'type' => self::TARGET_TYPES[$row->source_table],
                'key' => $row->source_table === 'cometobjects' ? (string) $record->id : (string) $record->slug,
                'object_type' => $row->source_table,
            ];
        }

        return $targets;
    }
}
