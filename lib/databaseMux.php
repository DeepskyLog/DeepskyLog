<?php
// databaseMux.php
// Runs the SQL of the old code on the new application's database
// (`$objDatabase_new`), which holds all data of this site now; the old
// database is not used any more.
//
// The observations, observers and accomplishments live in the new DB, in
// other tables and columns (observations + deepsky_observation_details /
// comet_observation_details, users, achievements). The new DB has views in
// the old columns (legacy_observations, legacy_cometobservations,
// legacy_observers, legacy_accomplishments, created by the migrations
// create_legacy_observation_views and create_legacy_observer_views of the new
// application): a statement on one of these tables gets the views in place of
// the old tables and runs on the new DB, joins and all. Observations are
// written through lib/observationStore.php, observers through
// Observers::setObserverProperty(); accomplishments are not written at all
// (the new application awards them). The other tables of the old site that
// the new application has no equivalent for (stars8 .. stars1775,
// formlayouts, observerqueries, ICQ_METHOD, ICQ_REFERENCE_KEY) were moved
// into the new DB unchanged, as legacy_* (sql/move_tables_to_new_database.sql).

class DatabaseMux
{
    private $newDb;
    private $debug;

    // Old table => view in the new DB.
    private const LEGACY_VIEWS = [
        'observations' => 'legacy_observations',
        'cometobservations' => 'legacy_cometobservations',
        'observers' => 'legacy_observers',
        'accomplishments' => 'legacy_accomplishments',
    ];

    // The tables of LEGACY_VIEWS, as a regex alternation (longest first).
    private const LEGACY_TABLES = 'cometobservations|observations|observers|accomplishments';

    // Words that can follow a table name and are no alias.
    private const NOT_ALIASES = ['where', 'join', 'left', 'right', 'inner', 'outer', 'cross',
        'natural', 'straight_join', 'on', 'using', 'order', 'group', 'limit', 'union', 'having',
        'set', 'for', 'into', 'values', 'select', 'and', 'or', 'as'];

    public function __construct($newDb, $debug = false)
    {
        $this->newDb = $newDb;
        $this->debug = (bool)$debug;
    }

    /**
     * Splits SQL into code and string literals: a list of [isLiteral, text].
     * Handles doubled and backslash-escaped quotes.
     */
    private function splitLiterals($sql)
    {
        $parts = [];
        $len = strlen($sql);
        $start = 0;
        $i = 0;
        while ($i < $len) {
            $ch = $sql[$i];
            if ($ch !== "'" && $ch !== '"') {
                $i++;
                continue;
            }
            if ($i > $start) {
                $parts[] = [false, substr($sql, $start, $i - $start)];
            }
            $quote = $ch;
            $j = $i + 1;
            while ($j < $len) {
                if ($sql[$j] === '\\') {
                    $j += 2;
                    continue;
                }
                if ($sql[$j] === $quote) {
                    if ($j + 1 < $len && $sql[$j + 1] === $quote) {
                        $j += 2;
                        continue;
                    }
                    break;
                }
                $j++;
            }
            $parts[] = [true, substr($sql, $i, min($j + 1, $len) - $i)];
            $i = $j + 1;
            $start = $i;
        }
        if ($start < $len) {
            $parts[] = [false, substr($sql, $start)];
        }
        return $parts;
    }

    /**
     * The SQL without its string literals, to look for table names.
     */
    private function codeOnly($sql)
    {
        $code = '';
        foreach ($this->splitLiterals($sql) as $part) {
            $code .= $part[0] ? "''" : $part[1];
        }
        return $code;
    }

    /**
     * Does the statement read one of the tables of LEGACY_VIEWS?
     */
    private function isLegacyViewStatement($sql)
    {
        if (!is_string($sql)) {
            return false;
        }
        return (bool) preg_match(
            '/(?:\b(?:FROM|JOIN)\s+|,\s*)(?:' . self::LEGACY_TABLES . ')\b|\b(?:' . self::LEGACY_TABLES . ')\s*\./i',
            $this->codeOnly($sql)
        );
    }

    /**
     * The views can't be written: observations go through ObservationStore,
     * observers through Observers::setObserverProperty().
     */
    private function assertNoLegacyViewWrite($sql)
    {
        if (preg_match('/^\s*\(?\s*(?:INSERT\s+INTO|UPDATE|DELETE\s+FROM|REPLACE\s+INTO)\s+(?:' . self::LEGACY_TABLES . ')\b/i', $this->codeOnly($sql))) {
            throw new RuntimeException('Write to a table of the new DB through its class: ' . substr($sql, 0, 200));
        }
    }

    /**
     * Puts the views of the new DB in place of the old tables. The view keeps
     * the old table name as its alias, so `observations.id` still works.
     * Observer names in string literals are decoded (`Torbj&ouml;rn`): the
     * old observers table has a few of them HTML-encoded, the new DB not.
     */
    private function toLegacyViews($sql)
    {
        $out = '';
        foreach ($this->splitLiterals($sql) as $part) {
            if ($part[0]) {
                $out .= preg_replace_callback(
                    '/&[a-zA-Z]+;/',
                    function ($m) {
                        return in_array(strtolower($m[0]), ['&quot;', '&amp;', '&lt;', '&gt;', '&apos;'])
                            ? $m[0] : html_entity_decode($m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    },
                    $part[1]
                );
                continue;
            }
            $out .= preg_replace_callback(
                '/(\b(?:FROM|JOIN)\s+|,\s*)(' . self::LEGACY_TABLES . ')\b(?!\s*\.)(?=\s+(?:AS\s+)?([A-Za-z_][A-Za-z0-9_]*))?/i',
                function ($m) {
                    $view = self::LEGACY_VIEWS[strtolower($m[2])];
                    $alias = isset($m[3]) ? strtolower($m[3]) : '';
                    if ($alias !== '' && !in_array($alias, self::NOT_ALIASES)) {
                        return $m[1] . $view;
                    }
                    return $m[1] . $view . ' AS ' . strtolower($m[2]);
                },
                $part[1]
            );
        }
        $out = $this->rewriteNotSeen($out);
        if ($this->debug) {
            error_log('DatabaseMux: observation SQL on NEW DB: ' . substr($out, 0, 1000));
        }
        return $out;
    }

    /**
     * `visibility != 7` (seen) needs the detail table of every observation;
     * the few not seen (indexed) are found much faster, and MariaDB then
     * leaves the detail table out of the view.
     */
    private function rewriteNotSeen($sql)
    {
        if (!preg_match('/\blegacy_observations\s+(?:AS\s+)?([A-Za-z_][A-Za-z0-9_]*)/i', $sql, $m)) {
            return $sql;
        }
        $alias = $m[1];
        return preg_replace_callback(
            '/(?:\b([A-Za-z_][A-Za-z0-9_]*)\.)?\bvisibility\s*(?:!=|<>)\s*([\'"]?)7\2(?![0-9])/i',
            function ($v) use ($alias) {
                if ($v[1] !== '' && strcasecmp($v[1], $alias) !== 0) {
                    return $v[0];
                }
                return $alias . '.id NOT IN (SELECT observation_id FROM deepsky_observation_details WHERE visibility = 7)';
            },
            $sql
        );
    }

    /**
     * Returns [db, sql] for a statement.
     */
    private function route($sql)
    {
        if (is_string($sql)) {
            $this->assertNoLegacyViewWrite($sql);
        }
        if ($this->isLegacyViewStatement($sql)) {
            return [$this->newDb, $this->toLegacyViews($sql)];
        }
        return [$this->newDb, $sql];
    }

    public function execSQL($sql)
    {
        list($db, $sql) = $this->route($sql);
        return $db->execSQL($sql);
    }

    public function selectRecordset($sql)
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectRecordset($sql);
    }

    public function selectSingleArray($sql, $name)
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectSingleArray($sql, $name);
    }

    public function selectSingleValue($sql, $name, $nullvalue = '')
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectSingleValue($sql, $name, $nullvalue);
    }

    public function selectRecordsetArray($sql)
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectRecordsetArray($sql);
    }

    public function selectRecordArray($sql)
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectRecordArray($sql);
    }

    public function selectKeyValueArray($sql, $key, $value)
    {
        list($db, $sql) = $this->route($sql);
        return $db->selectKeyValueArray($sql, $key, $value);
    }

    public function prepareAndSelectRecordsetArray($sql, $values)
    {
        list($db, $sql) = $this->route($sql);
        return $db->prepareAndSelectRecordsetArray($sql, $values);
    }

    public function result($sql)
    {
        list($db, $sql) = $this->route($sql);
        return $db->result($sql);
    }

    public function insert_id()
    {
        return $this->newDb->insert_id();
    }
}

?>
