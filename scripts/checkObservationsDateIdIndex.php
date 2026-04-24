<?php
$inIndex = true;
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";

date_default_timezone_set('UTC');

$objDatabase = new Database();

print "Checking composite index support for observations(date, id)...\n\n";

$indexRows = $objDatabase->selectRecordsetArray(
    "SHOW INDEX FROM observations"
);

$indexes = array();
foreach ($indexRows as $row) {
    $key = $row['Key_name'];
    $seq = (int)$row['Seq_in_index'];
    $col = strtolower($row['Column_name']);
    if (!array_key_exists($key, $indexes)) {
        $indexes[$key] = array();
    }
    $indexes[$key][$seq] = $col;
}

$hasDateIdComposite = false;
$matchingIndexes = array();
foreach ($indexes as $name => $columnsBySeq) {
    ksort($columnsBySeq);
    $cols = array_values($columnsBySeq);
    if (count($cols) >= 2 && $cols[0] === 'date' && $cols[1] === 'id') {
        $hasDateIdComposite = true;
        $matchingIndexes[] = $name;
    }
}

if ($hasDateIdComposite) {
    print "OK: composite index on (date, id) exists.\n";
    print "Matching index name(s): " . implode(', ', $matchingIndexes) . "\n\n";
} else {
    print "MISSING: no composite index starts with (date, id).\n\n";
    print "Suggested SQL:\n";
    print "ALTER TABLE observations ADD INDEX idx_observations_date_id (date, id);\n\n";
    print "If an old/bad duplicate exists, clean it up after validation.\n\n";
}

// Show whether the optimizer can use an index for the exact slow predicate pattern.
$sampleDate = date('Ymd', strtotime('-1 year'));
$sampleMinObservation = 0;

print "Plan check for predicate pattern:\n";
print "WHERE observations.date >= '" . $sampleDate . "' AND observations.id > "
    . $sampleMinObservation . "\n\n";

$explainRows = $objDatabase->selectRecordsetArray(
    "EXPLAIN SELECT count(observations.id) as ObsCnt "
    . "FROM observations "
    . "WHERE observations.date >= '" . $sampleDate . "' "
    . "AND observations.id > " . $sampleMinObservation
);

foreach ($explainRows as $row) {
    $possible = isset($row['possible_keys']) ? $row['possible_keys'] : '';
    $chosen = isset($row['key']) ? $row['key'] : '';
    $rows = isset($row['rows']) ? $row['rows'] : '';
    $extra = isset($row['Extra']) ? $row['Extra'] : '';

    print "possible_keys: " . $possible . "\n";
    print "chosen key: " . $chosen . "\n";
    print "rows estimate: " . $rows . "\n";
    print "extra: " . $extra . "\n";
}

print "\nDone.\n";
