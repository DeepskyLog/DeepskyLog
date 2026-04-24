<?php
// databaseMux.php
// Simple multiplexer that routes SQL to either the legacy DB or the new DB
// depending on which tables are referenced. Designed to keep legacy code
// unchanged while routing `objects` and `cometobjects` queries to the
// new application's database connection (`$objDatabase_new`).

class DatabaseMux
{
    private $oldDb;
    private $newDb;
    private $routeTables;
    private $lastUsedDb;
    private $debug;

    public function __construct($oldDb, $newDb, $routeTables = [], $debug = false)
    {
        $this->oldDb = $oldDb;
        $this->newDb = $newDb;
        // default tables to route to the new DB
        $defaults = ['objects', 'cometobjects', 'objectnames', 'observerobjectlist',
                     'observing_lists', 'observing_list_items', 'users'];
        $this->routeTables = array_map('strtolower', array_unique(array_merge($defaults, $routeTables)));
        $this->lastUsedDb = $oldDb;
        $this->debug = (bool)$debug;
    }

    // Sanitize a rewritten SQL string to remove outer parentheses and
    // trailing semicolons which can cause PDO/MariaDB syntax errors.
    private function sanitizeSqlString($sql)
    {
        if (!is_string($sql)) { return $sql; }
        $s = trim($sql);
        // remove trailing semicolon(s)
        $s = preg_replace('/;\s*$/', '', $s);
        // if wrapped in a single pair of outer parentheses and parentheses
        // inside are balanced, unwrap them
        if (preg_match('/^\s*\((.*)\)\s*$/s', $s, $m)) {
            $inner = $m[1];
            $open = substr_count($inner, '(');
            $close = substr_count($inner, ')');
            if ($open === $close) {
                $s = trim($inner);
            }
        }
        // If the SQL looks like it's wrapped in a single outer pair of
        // parentheses and references `observations`, unwrap that single
        // pair only when the inner text has balanced parentheses. The
        // previous aggressive stripping removed required closing parens
        // (e.g. the closing ')' of an IN(...) clause) and produced
        // syntax errors; this is safer.
        if (preg_match('/from\s+observations/i', $s)) {
            $s = trim($s);
            if (strlen($s) > 1 && $s[0] === '(' && substr($s, -1) === ')') {
                $inner = substr($s, 1, -1);
                $open = substr_count($inner, '(');
                $close = substr_count($inner, ')');
                if ($open === $close) {
                    $s = trim($inner);
                }
            }
        }
        // final trim
        return trim($s);
    }

    private function chooseDB($sql)
    {
        if (!is_string($sql)) {
            return $this->oldDb;
        }
        // Force legacy-only tables to always be served from the old DB.
        // This ensures `observations` (and cometobservations) are not
        // accidentally routed to the new DB which may not contain them.
        // IMPORTANT: match only structural SQL references (FROM/JOIN/column prefix),
        // NOT the word inside string literal values (e.g. a list named
        // "My observations" must not trigger this branch).
        if (preg_match('/\b(?:FROM|JOIN)\s+observations\b|\bobservations\./i', $sql) ||
            preg_match('/\b(?:FROM|JOIN)\s+cometobservations\b|\bcometobservations\./i', $sql)) {
            $this->lastUsedDb = $this->oldDb;
            return $this->oldDb;
        }
        $low = strtolower($sql);
        // Log the full original SQL for debugging (may be long)
        if ($this->debug) {
            error_log('DatabaseMux: attempting rewrite for SQL: ' . $sql);
        }

        // Quick sanity: ensure quotes are balanced. Use a simple parser that
        // toggles quote state and treats doubled quotes as SQL-escaped
        // characters. This avoids counting single quotes found inside
        // double-quoted literals (e.g. "Markarian's Chain") as unbalanced.
        $inSingle = false;
        $inDouble = false;
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === "'" && !$inDouble) {
                // treat backslash-escaped single-quotes as escaped too
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    continue;
                }
                // handle doubled single-quotes as escaped single-quote
                if ($inSingle && ($i + 1 < $len) && $sql[$i + 1] === "'") {
                    $i++;
                    continue;
                }
                $inSingle = !$inSingle;
                continue;
            }
            if ($ch === '"' && !$inSingle) {
                // treat backslash-escaped double-quotes as escaped too
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    continue;
                }
                // handle doubled double-quotes as escaped double-quote
                if ($inDouble && ($i + 1 < $len) && $sql[$i + 1] === '"') {
                    $i++;
                    continue;
                }
                $inDouble = !$inDouble;
                continue;
            }
        }
        if ($inSingle || $inDouble) {
            error_log('DatabaseMux: rewrite aborted due to unbalanced quotes; original SQL: ' . substr($sql, 0, 2000));
            $this->lastUsedDb = $this->oldDb;
            return $this->oldDb;
        }
        $hasCometObservations = (strpos($low, 'cometobservations.') !== false ||
            preg_match('/\b(?:FROM|JOIN)\s+cometobservations\b/i', $sql));

        $foundRouteTables = array();
        // Use word-boundary regex matching to avoid false positives where
        // table name substrings appear inside column names (e.g. "objectshowname").
        foreach ($this->routeTables as $t) {
            $pattern = '/\\b' . preg_quote($t, '/') . '\\b/i';
            if (preg_match($pattern, $sql)) {
                $foundRouteTables[] = $t;
            }
        }

        // Avoid routing queries that would cause cross-DB JOINs.
        // If the SQL references both a routed table (e.g. 'objects') and a legacy-only
        // related table that must remain on the old DB, keep the query on the old DB.
        // NOTE: `objectnames` is intentionally omitted here so that queries joining
        // `objects` + `objectnames` (common catalog lookups) can be routed to the
        // new database where `objects` live after migration.
        // Use structural patterns (FROM/JOIN/column prefix) to avoid false matches
        // when the table name appears inside a string literal value.
        $legacyJoinRiskTables = ['observations', 'cometobservations'];
        foreach ($legacyJoinRiskTables as $legacyTable) {
            if (in_array('objects', $foundRouteTables) &&
                (preg_match('/\b(?:FROM|JOIN)\s+' . preg_quote($legacyTable, '/') . '\b/i', $sql) ||
                 strpos($low, $legacyTable . '.') !== false)) {
                $this->lastUsedDb = $this->oldDb;
                return $this->oldDb;
            }
        }

        // If the query touches cometobservations and also references cometobjects,
        // keep the query on the old DB (avoid routing cross-DB JOINs).
        if ($hasCometObservations && in_array('cometobjects', $foundRouteTables)) {
            $this->lastUsedDb = $this->oldDb;
            return $this->oldDb;
        }

        if (count($foundRouteTables) > 0) {
            $this->lastUsedDb = $this->newDb;
            if ($this->debug) {
                error_log("DatabaseMux: routing to NEW DB for SQL: " . substr($sql, 0, 200));
            }
            return $this->newDb;
        }

        $this->lastUsedDb = $this->oldDb;
        if ($this->debug) {
            error_log("DatabaseMux: routing to OLD DB for SQL: " . substr($sql, 0, 200));
        }
        return $this->oldDb;
    }

    /**
     * If the SQL references both `observations` and `objectnames` with
     * a filter on `objectnames.altname` or `objectnames.catalog`, the
     * new `objectnames` table lives in the new DB while `observations`
     * lives in the old DB. In that case we rewrite the SQL to a two-step
     * approach: fetch the matching object names from the new DB and then
     * run the observations query on the old DB with an IN(...) filter.
     *
    * Returns either:
    *  - null if no special handling is needed (caller should use chooseDB),
    *  - an array ['db' => 'old', 'sql' => <sql>] to indicate a rewritten SQL
    *    that must be executed on the OLD DB, or
    *  - an array ['db' => 'new', 'sql' => <sql>] to indicate the original
    *    SQL should be executed on the NEW DB (used when matching
    *    observations already live in the new DB).
     */
    private function rewriteObservationObjectnamesJoin($sql)
    {
        $low = strtolower($sql);
        if (strpos($low, 'from observations') === false) {
            return null;
        }
        if (strpos($low, 'objectnames') === false) {
            return null;
        }

        // try to extract an objectnames.altname like "..." clause
        if (preg_match('/objectnames\.altname\s*like\s*(?:"|\')([^"\']+)(?:"|\')/i', $sql, $m)) {
            $altnameFilter = $m[1];
            $altnameFilterEscaped = str_replace('"', '"', $altnameFilter);
            $lookupSql = 'SELECT DISTINCT objectname FROM objectnames WHERE altname like "' . $altnameFilterEscaped . '"';
        } elseif (preg_match('/objectnames\.catalog\s*=\s*(?:"|\')([^"\']+)(?:"|\')/i', $sql, $m)) {
            $catalog = $m[1];
            $catalogEscaped = str_replace('"', '"', $catalog);
            $lookupSql = 'SELECT DISTINCT objectname FROM objectnames WHERE catalog = "' . $catalogEscaped . '"';
        } else {
            return null;
        }

        // fetch matching object names from the new DB
        try {
            $rows = $this->newDb->selectRecordsetArray($lookupSql);
        } catch (Exception $e) {
            error_log('DatabaseMux: objectnames lookup failed: ' . $e->getMessage());
            return null;
        }

        if (count($rows) == 0) {
            // no matching objects -> return a predicate that yields empty set
            return ['db' => 'old', 'sql' => 'SELECT * FROM observations WHERE 1=0'];
        }

        $escaped = [];
        foreach ($rows as $r) {
            $name = (is_array($r) ? reset($r) : $r->objectname);
            // SQL-escape single quotes by doubling them
            $escaped[] = "'" . str_replace("'", "''", $name) . "'";
        }
        $inClause = '(' . implode(',', $escaped) . ')';
        // If the original query is a COUNT query, build a compact safe
        // COUNT SQL that only references `observations` and the IN(...) clause.
        // The original SQL may be wrapped in parentheses; trim leading
        // whitespace and '(' characters before testing.
        $sqlTrimmedForTest = ltrim($sql);
        $sqlTrimmedForTest = ltrim($sqlTrimmedForTest, "(");
        if (preg_match('/select\s+count\s*\(/i', $sqlTrimmedForTest)) {
            $cntSql = 'SELECT count(DISTINCT observations.id) as ObsCnt FROM observations JOIN instruments on observations.instrumentid=instruments.id JOIN locations on observations.locationid=locations.id JOIN observers on observations.observerid=observers.id WHERE observations.id> 0 AND observations.objectname IN ' . $inClause;
            return ['db' => 'old', 'sql' => $cntSql];
        }

        // Extract LIMIT and ORDER BY from original SQL to preserve pagination
        $orderByMatch = null;
        $limitMatch = null;
        preg_match('/ORDER\s+BY\s+([^;\n]+?)(?:\s+LIMIT\s+|$)/i', $sql, $orderByMatch);
        preg_match('/LIMIT\s+([^;\n]+?)(?:;|$)/i', $sql, $limitMatch);
        
        $orderByClause = '';
        if (!empty($orderByMatch[1])) {
            // Ensure ORDER BY references are simple (observationid, observationdate, etc.)
            // to avoid joining unmigrated objects table
            $orderBy = trim($orderByMatch[1]);
            // Whitelist safe ORDER BY fields that exist in observations table
            if (preg_match('/^(observationid|observations\.id|observationdate|observations\.date|objectname|observations\.objectname)\s*(ASC|DESC)?$/i', $orderBy)) {
                // Normalize field references
                $orderBy = preg_replace('/observations\./', '', $orderBy);
                // Map presentation aliases to physical observations columns.
                $orderBy = preg_replace('/^observationid\b/i', 'id', $orderBy);
                $orderBy = preg_replace('/^observationdate\b/i', 'date', $orderBy);
                $orderByClause = ' ORDER BY ' . $orderBy;
            } else {
                // Default to observationid DESC if original ORDER BY references migrated columns
                $orderByClause = ' ORDER BY id DESC';
            }
        } else {
            $orderByClause = ' ORDER BY id DESC';
        }
        
        $limitClause = '';
        if (!empty($limitMatch[1])) {
            $limitClause = ' LIMIT ' . trim($limitMatch[1]);
        }

        // Build a minimal, safe SELECT that legacy pages expect. Rather than
        // surgically editing complex UNIONed/parenthesized SQL, return a
        // compact query selecting the known columns (with NULLs where the
        // migrated `objects` columns would have appeared) and filter by the
        // resolved base names. This is robust and avoids producing malformed
        // SQL for many legacy query shapes.
        
        // OPTIMIZATION: Apply LIMIT in a subquery BEFORE expensive JOINs
        // to reduce the intermediate result set size
        $selectSql = <<<'SQL'
    SELECT DISTINCT observations.id as observationid, observations.objectname as objectname, observations.date as observationdate, observations.description as observationdescription, observers.id as observerid, CONCAT(observers.firstname , ' ' , observers.name) as observername, CONCAT(observers.name , ' ' , observers.firstname) as observersortname, NULL as objectconstellation, NULL as objecttype, NULL as objectmagnitude, NULL as objectsurfacebrigthness, instruments.id as instrumentid, instruments.name as instrumentname, instruments.diameter as instrumentdiameter, CONCAT(10000+instruments.diameter,' mm ',instruments.name) as instrumentsort FROM observations JOIN instruments on observations.instrumentid=instruments.id JOIN locations on observations.locationid=locations.id JOIN observers on observations.observerid=observers.id WHERE observations.id> 0 AND observations.objectname IN
    SQL;
        
        if ($limitClause) {
            // TWO-STAGE OPTIMIZATION: Get limited IDs first, then JOIN full data
            $idSubquery = "SELECT observations.id FROM observations WHERE observations.id> 0 AND observations.objectname IN " . $inClause . $orderByClause . $limitClause;
            $selectSql = <<<'SQL'
    SELECT DISTINCT observations.id as observationid, observations.objectname as objectname, observations.date as observationdate, observations.description as observationdescription, observers.id as observerid, CONCAT(observers.firstname , ' ' , observers.name) as observername, CONCAT(observers.name , ' ' , observers.firstname) as observersortname, NULL as objectconstellation, NULL as objecttype, NULL as objectmagnitude, NULL as objectsurfacebrigthness, instruments.id as instrumentid, instruments.name as instrumentname, instruments.diameter as instrumentdiameter, CONCAT(10000+instruments.diameter,' mm ',instruments.name) as instrumentsort FROM observations INNER JOIN (%s) as limited_obs ON observations.id = limited_obs.id JOIN instruments on observations.instrumentid=instruments.id JOIN locations on observations.locationid=locations.id JOIN observers on observations.observerid=observers.id
    SQL;
            $selectSql = sprintf($selectSql, $idSubquery);
        } else {
            // Single-pass query without pagination
            $selectSql .= ' ' . $inClause . $orderByClause;
        }
        
        return ['db' => 'old', 'sql' => $selectSql];

        // remove JOIN objectnames and JOIN objects occurrences (stop before WHERE/JOIN/ORDER/GROUP/LIMIT/UNION)
        $newSql = preg_replace('/\bJOIN\s+objectnames\b\s+ON\s+(.+?)(?=\bWHERE\b|\bJOIN\b|\bLEFT\b|\bRIGHT\b|\bINNER\b|\bORDER\b|\bGROUP\b|\bLIMIT\b|\bUNION\b|;|$)/is', ' ', $sql);
        $newSql = preg_replace('/\bJOIN\s+objects\b\s+ON\s+(.+?)(?=\bWHERE\b|\bJOIN\b|\bLEFT\b|\bRIGHT\b|\bINNER\b|\bORDER\b|\bGROUP\b|\bLIMIT\b|\bUNION\b|;|$)/is', ' ', $newSql);

        // remove objectnames predicates from WHERE (catalog/altname), leave other predicates intact
        $newSql = preg_replace('/\bAND\b\s*\(\s*objectnames\.[a-z0-9_]+\s*(?:=|LIKE)\s*(?:"|\')?[^"\']+(?:"|\')?\s*\)\s*/i', ' ', $newSql);
        $newSql = preg_replace('/\bobjectnames\.[a-z0-9_]+\s*(?:=|LIKE)\s*(?:"|\')?[^"\']+(?:"|\')?/i', ' ', $newSql);

        // Replace selected `objects.* as alias` in the SELECT list with NULL aliases
        if (preg_match('/\bFROM\b/i', $newSql, $m, PREG_OFFSET_CAPTURE)) {
            $fromPos = $m[0][1];
            $selectPart = substr($newSql, 0, $fromPos);
            $rest = substr($newSql, $fromPos);
            // replace patterns like `objects.col as alias` -> `NULL as alias`
            $selectPart = preg_replace('/objects\.([a-z0-9_]+)\s+as\s+([a-z0-9_]+)/i', 'NULL as $2', $selectPart);
            // replace any remaining `objects.col` in select list with NULL
            $selectPart = preg_replace('/objects\.([a-z0-9_]+)/i', 'NULL', $selectPart);
            $newSql = $selectPart . $rest;
        }

        // normalize whitespace
        $newSql = preg_replace('/\s+/', ' ', $newSql);
        $newSql = trim($newSql);

        // cleanup empty parentheses and stray ANDs left by removal
        $newSql = preg_replace('/\(\s*\)/', '', $newSql);
        $newSql = preg_replace('/\(\s*AND\s*/i', '(', $newSql);
        $newSql = preg_replace('/AND\s*\)/i', ')', $newSql);
        $newSql = preg_replace('/WHERE\s+AND\s+/i', 'WHERE ', $newSql);
        $newSql = preg_replace('/\(\s*\)/', '', $newSql);
        $newSql = trim($newSql);

        // If removal left unbalanced parentheses (more closing than opening)
        // try to remove extraneous closing parens from left-to-right.
        $openCount = substr_count($newSql, '(');
        $closeCount = substr_count($newSql, ')');
        while ($closeCount > $openCount) {
            $newSql = preg_replace('/\)\s*/', ' ', $newSql, 1);
            $closeCount--;
        }
        $newSql = trim($newSql);

        // Remove any stray closing paren immediately before our IN clause insertion
        $newSql = preg_replace('/\)\s+AND\s+observations\.objectname\s+IN/i', ' AND observations.objectname IN', $newSql);

        // Final safety checks: if any obvious empty-parentheses patterns remain
        // or parentheses are still unbalanced, abort rewrite (let chooseDB handle).
        if (preg_match('/\(\s*\)/', $newSql)) {
            error_log('DatabaseMux: rewrite aborted due to empty parentheses; original SQL: ' . substr($sql,0,1000));
            return null;
        }
        $openCount = substr_count($newSql, '(');
        $closeCount = substr_count($newSql, ')');
        if ($openCount !== $closeCount) {
            error_log('DatabaseMux: rewrite aborted due to unbalanced parentheses; original SQL: ' . substr($sql,0,1000) . ' rewritten candidate: ' . substr($newSql,0,1000));
            return null;
        }

        // If the whole query is wrapped in parentheses (common in this codebase)
        // locate the matching closing paren for the first '(' and insert the
        // IN(...) clause inside that outermost parentheses. We parse while
        // respecting quoted strings to avoid matching parens inside literals.
        $trimmed = ltrim($newSql);
        if (strlen($trimmed) > 0 && $trimmed[0] === '(') {
            $s = $newSql;
            $len = strlen($s);
            $depth = 0;
            $inSingle = false;
            $inDouble = false;
            $matchPos = null;
            for ($i = 0; $i < $len; $i++) {
                $ch = $s[$i];
                if ($ch === "'" && !$inDouble) {
                    // treat backslash-escaped single-quotes as escaped too
                    if ($i > 0 && $s[$i - 1] === '\\') {
                        continue;
                    }
                    // handle doubled single-quotes as escaped quotes
                    if ($inSingle && ($i+1 < $len) && $s[$i+1] === "'") {
                        $i++; // skip escaped quote
                        continue;
                    }
                    $inSingle = !$inSingle;
                    continue;
                }
                if ($ch === '"' && !$inSingle) {
                    // treat backslash-escaped double-quotes as escaped too
                    if ($i > 0 && $s[$i - 1] === '\\') {
                        continue;
                    }
                    if ($inDouble && ($i+1 < $len) && $s[$i+1] === '"') {
                        $i++;
                        continue;
                    }
                    $inDouble = !$inDouble;
                    continue;
                }
                if ($inSingle || $inDouble) {
                    continue;
                }
                if ($ch === '(') {
                    $depth++;
                    if ($depth === 1 && $i !== 0 && trim(substr($s,0,$i)) !== '') {
                        // not an outermost leading paren, bail to normal path
                        $matchPos = null;
                        break;
                    }
                } elseif ($ch === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $matchPos = $i;
                        break;
                    }
                }
            }
            if ($matchPos !== null) {
                $inner = substr($newSql, 0, $matchPos + 1);
                $tail = substr($newSql, $matchPos + 1);
                // remove the final ')' to insert inside
                $innerNoClose = rtrim(substr($inner, 0, -1));
                if (preg_match('/\bWHERE\b/i', $innerNoClose)) {
                    $innerNoClose .= ' AND observations.objectname IN ' . $inClause;
                } else {
                    $innerNoClose .= ' WHERE observations.objectname IN ' . $inClause;
                }
                $newSql = $innerNoClose . ' )' . (strlen($tail) ? ' ' . ltrim($tail) : '');
                // Log both original and rewritten SQL for debugging
                if ($this->debug) {
                    error_log('DatabaseMux: original SQL: ' . substr($sql, 0, 1000));
                    error_log('DatabaseMux: rewritten SQL: ' . substr($newSql, 0, 1000));
                }
                // final sanity check
                if (preg_match('/\bWHERE\b\s*\(\s*\)/i', $newSql) || preg_match('/^\)/', $newSql) || preg_match('/\)\s+\)/', $newSql)) {
                    // try to repair common issues rather than aborting outright
                    $newSql = preg_replace('/\)\s+\)/', ')', $newSql);
                    $newSql = preg_replace('/\bWHERE\b\s*\(\s*\)/i', 'WHERE 1=1', $newSql);
                    $newSql = ltrim($newSql, ") ");
                    // if still problematic, log but continue with OLD DB execution
                    if (preg_match('/^\)/', $newSql)) {
                        if ($this->debug) {
                            error_log('DatabaseMux: rewrite produced leading ) but will attempt OLD DB execution; original SQL: ' . substr($sql,0,1000));
                        }
                    }
                }
                // Strip any language-restriction predicates that were added
                // by the caller (e.g. 'AND (observations.language="nl" OR ...)')
                // so that the OLD-DB IN(...) lookup returns observations from
                // all languages for catalog-based rewrites (Master/etc.).
                $newSql = preg_replace('/AND\s*\(\s*(?:observations\.language\s*=\s*"[^"]+"\s*(?:OR\s*)?)+\)/i', ' ', $newSql);
                $newSql = preg_replace('/AND\s*observations\.language\s*=\s*"[^"]+"/i', ' ', $newSql);
                $newSql = preg_replace('/\s+/', ' ', $newSql);

                // Strip trailing semicolon and balanced outer parentheses to avoid
                // returning a SQL string that will cause a syntax error when
                // executed by the legacy DB wrapper.
                $newSql = preg_replace('/;\s*$/', '', $newSql);
                $openCount2 = substr_count($newSql, '(');
                $closeCount2 = substr_count($newSql, ')');
                if ($openCount2 > 0 && $openCount2 === $closeCount2 && preg_match('/^\s*\(/', $newSql)) {
                    $newSql = preg_replace('/^\s*\(/', '', $newSql, 1);
                    $newSql = preg_replace('/\)\s*$/', '', $newSql, 1);
                    $newSql = trim($newSql);
                }

                // return an instruction to explicitly run on the OLD DB
                return ['db' => 'old', 'sql' => $newSql];
            }
        }

        // Insert the IN clause before ORDER/GROUP/LIMIT/HAVING/UNION/; if present
        if (preg_match('/\b(ORDER\s+BY|GROUP\s+BY|LIMIT\b|HAVING\b|FOR\s+UPDATE\b|UNION\b|;)/i', $newSql, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            $prefix = rtrim(substr($newSql, 0, $pos));
            $suffix = substr($newSql, $pos);
            // If prefix ends with one or more closing parens, insert before that run
            // so the IN(...) appears inside the outermost parentheses instead of
            // after them (avoids producing ") ) AND ..."). Capture the entire
            // trailing run of ')' characters and re-append it after insertion.
            $closingParen = '';
            if (preg_match('/\)+$/', $prefix, $pm)) {
                $parensRun = $pm[0];
                $prefix = preg_replace('/\)+$/', '', $prefix);
                $closingParen = $parensRun;
            }
            // ensure WHERE has something to attach to; if WHERE exists but is empty
            if (preg_match('/\bWHERE\b\s*(?:$|ORDER|GROUP|LIMIT|HAVING|UNION|\))/i', $prefix)) {
                // empty WHERE - make it neutral so we can append safely
                $prefix .= ' 1=1 ';
            }
            if (preg_match('/\bWHERE\b/i', $prefix)) {
                $prefix .= ' AND observations.objectname IN ' . $inClause . ' ';
            } else {
                $prefix .= ' WHERE observations.objectname IN ' . $inClause . ' ';
            }
            // ensure suffix has a separating space
            if (strlen($suffix) && $suffix[0] !== ' ') {
                $suffix = ' ' . $suffix;
            }
            $newSql = $prefix . $closingParen . $suffix;
        } else {
            // no trailing clauses, safe to append
            // If the SQL ends with one or more closing parens, insert before that run
            // so the IN clause is placed inside the parentheses rather than after them.
            if (preg_match('/\)+$/', $newSql, $pm)) {
                $parensRun = $pm[0];
                $newSql = preg_replace('/\)+$/', '', $newSql);
                $endingParen = $parensRun;
            } else {
                $endingParen = '';
            }
            if (preg_match('/\bWHERE\b\s*(?:$|ORDER|GROUP|LIMIT|HAVING|UNION|\))/i', $newSql)) {
                $newSql .= ' 1=1 ';
            }
            if (preg_match('/\bWHERE\b/i', $newSql)) {
                $newSql .= ' AND observations.objectname IN ' . $inClause;
            } else {
                $newSql .= ' WHERE observations.objectname IN ' . $inClause;
            }
            $newSql .= $endingParen;
        }

        // Log both original and rewritten SQL for debugging
        if ($this->debug) {
            error_log('DatabaseMux: original SQL: ' . substr($sql, 0, 1000));
            error_log('DatabaseMux: rewritten SQL: ' . substr($newSql, 0, 1000));
        }

        // Last sanity check: attempt light repair of common formatting issues
        if (preg_match('/\)\s+\)/', $newSql)) {
            $newSql = preg_replace('/\)\s+\)/', ')', $newSql);
        }
        if (preg_match('/\bWHERE\b\s*\(\s*\)/i', $newSql)) {
            $newSql = preg_replace('/\bWHERE\b\s*\(\s*\)/i', 'WHERE 1=1', $newSql);
        }
        $newSql = ltrim($newSql);
        if (preg_match('/^\)/', $newSql)) {
            // log but proceed; caller expects OLD DB results if available
            if ($this->debug) {
                error_log('DatabaseMux: rewritten SQL starts with ) after cleanup; proceeding with OLD DB execution.');
            }
        }

        // Fix common incorrect insertion where the IN-clause was appended
        // after a closing paren/semicolon (e.g. ") ; AND observations.objectname IN ...")
        // Move such occurrences inside the WHERE by removing the stray
        // closing paren and semicolon before the AND. This repairs malformed
        // rewrites seen in the wild and is safe because the IN predicate
        // belongs in the WHERE of the outermost select.
        $newSql = preg_replace('/\)\s*;\s*AND\s+observations\.objectname\s+IN\s*/i', ' AND observations.objectname IN ', $newSql);
        $newSql = preg_replace('/\)\s+AND\s+observations\.objectname\s+IN\s*/i', ' AND observations.objectname IN ', $newSql);
        $newSql = preg_replace('/;\s*AND\s+observations\.objectname\s+IN\s*/i', ' AND observations.objectname IN ', $newSql);

        // also remove any language predicates for the same reason as above
        $newSql = preg_replace('/AND\s*\(\s*(?:observations\.language\s*=\s*"[^"]+"\s*(?:OR\s*)?)+\)/i', ' ', $newSql);
        $newSql = preg_replace('/AND\s*observations\.language\s*=\s*"[^"]+"/i', ' ', $newSql);
        $newSql = preg_replace('/\s+/', ' ', $newSql);

        // Strip a trailing semicolon which can confuse PDO execution
        $newSql = preg_replace('/;\s*$/', '', $newSql);

        // If the entire query is wrapped in balanced outer parentheses,
        // strip one level so the SQL executes cleanly (many legacy callers
        // generate queries wrapped in parentheses for UNIONs). Only do
        // this when parentheses are balanced to avoid breaking nested SQL.
        $openCount = substr_count($newSql, '(');
        $closeCount = substr_count($newSql, ')');
        if ($openCount > 0 && $openCount === $closeCount && preg_match('/^\s*\(/', $newSql)) {
            $newSql = preg_replace('/^\s*\(/', '', $newSql, 1);
            $newSql = preg_replace('/\)\s*$/', '', $newSql, 1);
            $newSql = trim($newSql);
        }

        return ['db' => 'old', 'sql' => $newSql];
    }

    /**
     * Rewrites SQL that mixes `observations` with `observerobjectlist`.
     *
     * `observations` remains on the old DB while `observerobjectlist`
     * is served from the new DB. We resolve matching list object names
     * from the new DB first, then run an observations-only query on the
     * old DB constrained with an IN(...) clause.
     */
    private function rewriteObservationObserverObjectListJoin($sql)
    {
        $low = strtolower($sql);
        if (strpos($low, 'observations') === false || strpos($low, 'observerobjectlist') === false) {
            return null;
        }

        if (!preg_match('/observerobjectlist\.listname\s*=\s*(?:"|\')([^"\']+)(?:"|\')/i', $sql, $m)) {
            return null;
        }

        $listname = $m[1];
        $lookupSql = 'SELECT DISTINCT objectname FROM observerobjectlist WHERE listname = "' . str_replace('"', '""', $listname) . '" AND objectname <> ""';

        if (preg_match('/observerobjectlist\.observerid\s*=\s*(?:"|\')([^"\']+)(?:"|\')/i', $sql, $obsM)) {
            $lookupSql .= ' AND observerid = "' . str_replace('"', '""', $obsM[1]) . '"';
        }
        if (preg_match('/observerobjectlist\.public\s*=\s*(?:"|\')([^"\']+)(?:"|\')/i', $sql, $pubM)) {
            $lookupSql .= ' AND public = "' . str_replace('"', '""', $pubM[1]) . '"';
        }

        try {
            $rows = $this->newDb->selectRecordsetArray($lookupSql);
        } catch (Exception $e) {
            error_log('DatabaseMux: observerobjectlist lookup failed: ' . $e->getMessage());
            return null;
        }

        if (!is_array($rows) || count($rows) === 0) {
            return ['db' => 'old', 'sql' => 'SELECT * FROM observations WHERE 1=0'];
        }

        $escaped = [];
        foreach ($rows as $r) {
            $name = (is_array($r) ? reset($r) : $r->objectname);
            $escaped[] = "'" . str_replace("'", "''", $name) . "'";
        }
        $inClause = '(' . implode(',', $escaped) . ')';

        $newSql = $sql;

        // Query shape 1: FROM observations JOIN observerobjectlist ...
        if (preg_match('/\bFROM\s+observations\b/i', $newSql)) {
            $newSql = preg_replace(
                '/\b(?:INNER\s+JOIN|JOIN)\s+observerobjectlist\b\s+ON\s+(.+?)(?=\bWHERE\b|\bJOIN\b|\bLEFT\b|\bRIGHT\b|\bINNER\b|\bORDER\b|\bGROUP\b|\bLIMIT\b|\bUNION\b|;|$)/is',
                ' ',
                $newSql
            );
        }

        // Query shape 2: FROM observerobjectlist JOIN observations ...
        if (preg_match('/\bFROM\s+observerobjectlist\b/i', $newSql)) {
            $newSql = preg_replace('/\bFROM\s+observerobjectlist\b/i', 'FROM observations', $newSql, 1);
            $newSql = preg_replace(
                '/\b(?:INNER\s+JOIN|JOIN)\s+observations\b\s+ON\s+(.+?)(?=\bWHERE\b|\bJOIN\b|\bLEFT\b|\bRIGHT\b|\bINNER\b|\bORDER\b|\bGROUP\b|\bLIMIT\b|\bUNION\b|;|$)/is',
                ' ',
                $newSql,
                1
            );
        }

        $newSql = str_ireplace('observerobjectlist.objectname', 'observations.objectname', $newSql);

        // If the original query was a part-of list query, keep semantics by
        // selecting the parent name while filtering observations by child name.
        if (preg_match('/objectpartof\.partofname\s*=\s*observations\.objectname/i', $newSql)) {
            $newSql = preg_replace('/objectpartof\.partofname\s*=\s*observations\.objectname/i', 'objectpartof.objectname = observations.objectname', $newSql);
            $newSql = preg_replace('/SELECT\s+DISTINCT\s+observations\.objectname/i', 'SELECT DISTINCT objectpartof.partofname AS objectname', $newSql, 1);
        }

        // Remove predicates that reference observerobjectlist; they are
        // replaced by the IN(...) predicate below. Use neutral 1=1 so nested
        // boolean groups like "WHERE ((observerobjectlist.listname=...) AND ...)"
        // stay syntactically valid.
        $newSql = preg_replace('/observerobjectlist\.(?:listname|observerid|public)\s*(?:=|!=|<>)\s*(?:"|\')[^"\']*(?:"|\')/i', '1=1', $newSql);
        $newSql = preg_replace('/observerobjectlist\.objectname\s*<>\s*(?:"|\')\s*(?:"|\')/i', '1=1', $newSql);
        // Cleanup common "(1=1)" noise created by replacement.
        $newSql = preg_replace('/\(\s*1\s*=\s*1\s*\)/i', '1=1', $newSql);

        // Inject IN(...) before ORDER/GROUP/LIMIT/HAVING/UNION when present.
        if (preg_match('/\b(ORDER\s+BY|GROUP\s+BY|LIMIT\b|HAVING\b|FOR\s+UPDATE\b|UNION\b|;)/i', $newSql, $mm, PREG_OFFSET_CAPTURE)) {
            $pos = $mm[0][1];
            $prefix = rtrim(substr($newSql, 0, $pos));
            $suffix = ltrim(substr($newSql, $pos));
            if (preg_match('/\bWHERE\b/i', $prefix)) {
                $prefix .= ' AND observations.objectname IN ' . $inClause . ' ';
            } else {
                $prefix .= ' WHERE observations.objectname IN ' . $inClause . ' ';
            }
            $newSql = $prefix . $suffix;
        } else {
            if (preg_match('/\bWHERE\b/i', $newSql)) {
                $newSql .= ' AND observations.objectname IN ' . $inClause;
            } else {
                $newSql .= ' WHERE observations.objectname IN ' . $inClause;
            }
        }

        // Basic cleanup for leftovers from predicate removal.
        $newSql = preg_replace('/\bWHERE\s+AND\b/i', 'WHERE', $newSql);
        $newSql = preg_replace('/\(\s*\)/', '', $newSql);
        $newSql = preg_replace('/\s+/', ' ', trim($newSql));

        if ($this->debug) {
            error_log('DatabaseMux: observerobjectlist original SQL: ' . substr($sql, 0, 1000));
            error_log('DatabaseMux: observerobjectlist rewritten SQL: ' . substr($newSql, 0, 1000));
        }

        return ['db' => 'old', 'sql' => $newSql];
    }

    private function getRewriteForObservationQuery($sql)
    {
        $rewritten = $this->rewriteObservationObserverObjectListJoin($sql);
        if ($rewritten !== null) {
            return $rewritten;
        }
        return $this->rewriteObservationObjectnamesJoin($sql);
    }

    public function execSQL($sql)
    {
        $db = $this->chooseDB($sql);
        return $db->execSQL($sql);
    }

    public function selectRecordset($sql)
    {
        // special-case queries that JOIN observations with objectnames
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectRecordset($clean);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectRecordset($rewritten['sql']);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectRecordset($clean);
            }
        }
        // If the query mentions both observations and objectnames and we
        // couldn't produce a safe rewrite, prefer trying the NEW DB first
        // (some observations may have been migrated there). If the NEW DB
        // returns results, use them; otherwise fall back to the normal
        // routing decision.
        $low = strtolower($sql);
        if (strpos($low, 'from observations') !== false && strpos($low, 'objectnames') !== false) {
            try {
                // Use the array-oriented API to safely detect rows without
                // handing callers a statement that may be consumed. If the
                // NEW DB returns rows, wrap them in a PDO-like object that
                // supports fetch()/fetchColumn()/rowCount() so callers
                // expecting a PDOStatement continue to work.
                $rows = $this->newDb->selectRecordsetArray($sql);
                if (is_array($rows) && count($rows) > 0) {
                    if ($this->debug) {
                        error_log('DatabaseMux: used NEW DB result for observations+objectnames query');
                    }
                    $stub = new class($rows) {
                        private $rows;
                        private $idx = 0;
                        public function __construct($rows) { $this->rows = $rows; }
                        public function fetch($mode = null) {
                            if ($this->idx >= count($this->rows)) { return false; }
                            $row = (object) $this->rows[$this->idx++];
                            return $row;
                        }
                        public function fetchColumn() {
                            $r = $this->fetch();
                            if ($r === false) { return false; }
                            $arr = array_values((array) $r);
                            return isset($arr[0]) ? $arr[0] : false;
                        }
                        public function rowCount() { return count($this->rows); }
                        public function execute($params = null) { return true; }
                    };
                    return $stub;
                }
            } catch (Exception $e) {
                error_log('DatabaseMux: probing NEW DB for observations failed: ' . $e->getMessage());
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectRecordset($sql);
    }

    public function selectSingleArray($sql, $name)
    {
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectSingleArray($clean, $name);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectSingleArray($rewritten['sql'], $name);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectSingleArray($clean, $name);
            }
        }
        $low = strtolower($sql);
        if (strpos($low, 'from observations') !== false && strpos($low, 'objectnames') !== false) {
            try {
                $res = $this->newDb->selectSingleArray($sql, $name);
                if ($res && count($res) > 0) {
                    if ($this->debug) {
                        error_log('DatabaseMux: used NEW DB single-array result for observations+objectnames query');
                    }
                    return $res;
                }
            } catch (Exception $e) {
                error_log('DatabaseMux: probing NEW DB single-array failed: ' . $e->getMessage());
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectSingleArray($sql, $name);
    }

    public function selectSingleValue($sql, $name, $nullvalue = '')
    {
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectSingleValue($clean, $name, $nullvalue);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectSingleValue($rewritten['sql'], $name, $nullvalue);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectSingleValue($clean, $name, $nullvalue);
            }
        }
        $low = strtolower($sql);
        if (strpos($low, 'from observations') !== false && strpos($low, 'objectnames') !== false) {
            try {
                $val = $this->newDb->selectSingleValue($sql, $name, $nullvalue);
                if ($val !== $nullvalue && $val !== null) {
                    if ($this->debug) {
                        error_log('DatabaseMux: used NEW DB single-value result for observations+objectnames query');
                    }
                    return $val;
                }
            } catch (Exception $e) {
                error_log('DatabaseMux: probing NEW DB single-value failed: ' . $e->getMessage());
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectSingleValue($sql, $name, $nullvalue);
    }

    public function selectRecordsetArray($sql)
    {
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectRecordsetArray($clean);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectRecordsetArray($rewritten['sql']);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectRecordsetArray($clean);
            }
        }
        $low = strtolower($sql);
        if (strpos($low, 'from observations') !== false && strpos($low, 'objectnames') !== false) {
            try {
                $res = $this->newDb->selectRecordsetArray($sql);
                if ($res && count($res) > 0) {
                    if ($this->debug) {
                        error_log('DatabaseMux: used NEW DB recordset-array for observations+objectnames query');
                    }
                    return $res;
                }
            } catch (Exception $e) {
                error_log('DatabaseMux: probing NEW DB recordset-array failed: ' . $e->getMessage());
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectRecordsetArray($sql);
    }

    public function selectRecordArray($sql)
    {
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectRecordArray($clean);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectRecordArray($rewritten['sql']);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectRecordArray($clean);
            }
        }
        $low = strtolower($sql);
        if (strpos($low, 'from observations') !== false && strpos($low, 'objectnames') !== false) {
            try {
                $res = $this->newDb->selectRecordArray($sql);
                if ($res && count($res) > 0) {
                    if ($this->debug) {
                        error_log('DatabaseMux: used NEW DB record-array for observations+objectnames query');
                    }
                    return $res;
                }
            } catch (Exception $e) {
                error_log('DatabaseMux: probing NEW DB record-array failed: ' . $e->getMessage());
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectRecordArray($sql);
    }

    public function selectKeyValueArray($sql, $key, $value)
    {
        $rewritten = $this->getRewriteForObservationQuery($sql);
        if ($rewritten !== null) {
            if (is_array($rewritten) && isset($rewritten['db']) && isset($rewritten['sql'])) {
                if ($rewritten['db'] === 'old') {
                    $clean = $this->sanitizeSqlString($rewritten['sql']);
                    return $this->oldDb->selectKeyValueArray($clean, $key, $value);
                } elseif ($rewritten['db'] === 'new') {
                    return $this->newDb->selectKeyValueArray($rewritten['sql'], $key, $value);
                }
            } elseif (is_string($rewritten)) {
                $clean = $this->sanitizeSqlString($rewritten);
                return $this->oldDb->selectKeyValueArray($clean, $key, $value);
            }
        }
        $db = $this->chooseDB($sql);
        return $db->selectKeyValueArray($sql, $key, $value);
    }

    public function prepareAndSelectRecordsetArray($sql, $values)
    {
        $db = $this->chooseDB($sql);
        return $db->prepareAndSelectRecordsetArray($sql, $values);
    }

    public function result($sql)
    {
        $db = $this->chooseDB($sql);
        return $db->result($sql);
    }

    public function insert_id()
    {
        // return lastInsertId from the last DB used for a write
        if ($this->lastUsedDb) {
            return $this->lastUsedDb->insert_id();
        }
        return null;
    }
}

?>
