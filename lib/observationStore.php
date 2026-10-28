<?php
/**
 * Writes the observations of the old site in the new database.
 *
 * The new database keeps an observation in `observations` and its deep-sky
 * or comet details in `deepsky_observation_details` or
 * `comet_observation_details`. The old code reads them through the views
 * legacy_observations and legacy_cometobservations (see DatabaseMux) and
 * writes them here, with the column names of the old tables: 'objectname',
 * 'date' (YYYYMMDD), 'time' (HHMM, -9999 unknown), 'stellar' (-1 unknown),
 * 'mag' (-99.9 unknown), ...
 *
 * Every change is recorded in legacy_observation_changes, with the values
 * before it, so that the new application (observations:apply-legacy-changes,
 * every minute) updates its counters, session links and statistics.
 *
 * PHP Version 7
 *
 * @category Deepsky
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}

class ObservationStore
{
    // Number of writes in this request, so caches of rows can see a change.
    public static $writes = 0;

    // Old column => [table: 'o' observations, 'd' details, new column, kind]
    private const DEEPSKY = [
        'objectname' => ['o', 'target_name', 'object'],
        'observerid' => ['o', 'observer_username', 'observer'],
        'instrumentid' => ['o', 'instrument_id', 'id'],
        'locationid' => ['o', 'location_id', 'id'],
        'eyepieceid' => ['o', 'eyepiece_id', 'id'],
        'filterid' => ['o', 'filter_id', 'id'],
        'lensid' => ['o', 'lens_id', 'id'],
        'date' => ['o', 'observed_on', 'date'],
        'time' => ['o', 'observed_time', 'time'],
        'description' => ['o', 'description', 'string'],
        'language' => ['o', 'language', 'string'],
        'seeing' => ['o', 'seeing', 'positive'],
        'limmag' => ['o', 'limiting_magnitude', 'positive'],
        'SQM' => ['o', 'sqm', 'positive'],
        'hasDrawing' => ['o', 'has_drawing', 'int'],
        'drawingfile' => ['o', 'drawing_filename', 'optional'],
        'visibility' => ['d', 'visibility', 'positive'],
        'smallDiameter' => ['d', 'small_diameter', 'positive'],
        'largeDiameter' => ['d', 'large_diameter', 'positive'],
        'stellar' => ['d', 'stellar', 'flag'],
        'extended' => ['d', 'extended', 'flag'],
        'resolved' => ['d', 'resolved', 'flag'],
        'mottled' => ['d', 'mottled', 'flag'],
        'clusterType' => ['d', 'cluster_type', 'optional'],
        'unusualShape' => ['d', 'unusual_shape', 'flag'],
        'partlyUnresolved' => ['d', 'partly_unresolved', 'flag'],
        'colorContrasts' => ['d', 'color_contrasts', 'flag'],
        'magnification' => ['d', 'magnification', 'optional'],
        'equalBrightness' => ['d', 'equal_brightness', 'flag'],
        'niceField' => ['d', 'nice_field', 'flag'],
        'component1' => ['d', 'component1', 'flag'],
        'component2' => ['d', 'component2', 'flag'],
        'dateDec' => ['d', 'date_dec', 'positive'],
    ];

    private const COMET = [
        'objectid' => ['o', 'target_key', 'comet'],
        'observerid' => ['o', 'observer_username', 'observer'],
        'instrumentid' => ['o', 'instrument_id', 'id'],
        'locationid' => ['o', 'location_id', 'id'],
        'date' => ['o', 'observed_on', 'date'],
        'time' => ['o', 'observed_time', 'time'],
        'description' => ['o', 'description', 'string'],
        'hasDrawing' => ['o', 'has_drawing', 'int'],
        'drawingfile' => ['o', 'drawing_filename', 'optional'],
        'methode' => ['d', 'method', 'optional'],
        'mag' => ['d', 'magnitude', 'magnitude'],
        'chart' => ['d', 'chart', 'optional'],
        'magnification' => ['d', 'magnification', 'optional'],
        'maguncertain' => ['d', 'magnitude_uncertain', 'int'],
        'lessmagnitude' => ['d', 'less_magnitude', 'int'],
        'dc' => ['d', 'dc', 'optional'],
        'coma' => ['d', 'coma', 'unknown99'],
        'tail' => ['d', 'tail', 'unknown99'],
        'pa' => ['d', 'position_angle', 'unknown99'],
    ];

    /**
     * Adds a deep-sky observation and returns its id (0 when it failed).
     *
     * @param array $fields Old column => value.
     *
     * @return int
     */
    public function addDeepsky(array $fields)
    {
        return $this->add('deepsky', $fields);
    }

    /**
     * Adds a comet observation and returns its id (0 when it failed).
     *
     * @param array $fields Old column (of cometobservations) => value.
     *
     * @return int
     */
    public function addComet(array $fields)
    {
        return $this->add('comet', $fields);
    }

    /**
     * Changes fields of a deep-sky observation.
     *
     * @param int   $id     The id of the observation.
     * @param array $fields Old column => value.
     *
     * @return None
     */
    public function updateDeepsky($id, array $fields)
    {
        $this->update('deepsky', $id, $fields);
    }

    /**
     * Changes fields of a comet observation.
     *
     * @param int   $id     The id of the observation.
     * @param array $fields Old column (of cometobservations) => value.
     *
     * @return None
     */
    public function updateComet($id, array $fields)
    {
        $this->update('comet', $id, $fields);
    }

    /**
     * Deletes an observation (deep-sky or comet) with its details.
     *
     * @param int $id The id of the observation.
     *
     * @return None
     */
    public function delete($id)
    {
        global $objDatabase_new;
        $id = (int) $id;
        if ($id <= 0) {
            return;
        }
        $this->recordChange($id, 'deleted');
        $objDatabase_new->execSQL("DELETE FROM sessionObservations WHERE observationid = " . $id);
        // The details go with it (foreign key on delete cascade).
        $objDatabase_new->execSQL("DELETE FROM observations WHERE id = " . $id);
    }

    /**
     * Gives the deep-sky observations of an object another object name.
     *
     * @param string $from The old name of the object.
     * @param string $to   The new name of the object.
     *
     * @return None
     */
    public function renameObject($from, $to)
    {
        foreach ($this->deepskyIdsOf($from) as $id) {
            $this->updateDeepsky($id, ['objectname' => $to]);
        }
    }

    /**
     * Deletes the deep-sky observations of an object.
     *
     * @param string $name The name of the object.
     *
     * @return None
     */
    public function deleteObservationsOf($name)
    {
        foreach ($this->deepskyIdsOf($name) as $id) {
            $this->delete($id);
        }
    }

    /**
     * The URL of the drawing of an observation. The drawings of the old site
     * keep their file named after the old id (`drawingfile` drawings/12.jpg is
     * deepsky/drawings/12.jpg); the others are on the public disk of the new
     * application ($baseURLNew storage/...).
     *
     * @param int    $id      The id of the observation.
     * @param bool   $resized The small version (the old site's _resized.jpg).
     * @param string $kind    'deepsky' or 'comet'.
     *
     * @return string
     */
    public function drawingUrl($id, $resized = false, $kind = 'deepsky')
    {
        global $baseURL, $baseURLNew;
        list($legacy, $file) = $this->drawingFile($id, $kind);
        if ($legacy !== null) {
            return $baseURL . $legacy . ($resized ? '_resized' : '') . '.jpg';
        }
        if ($resized && $this->drawingPath($id, $kind, true) === null) {
            $resized = false;
        }
        return rtrim((string) $baseURLNew, '/') . '/storage/' . ($resized ? $this->resizedName($file) : $file);
    }

    /**
     * The file of the drawing of an observation, or null when there is none.
     *
     * @param int    $id      The id of the observation.
     * @param string $kind    'deepsky' or 'comet'.
     * @param bool   $resized The small version.
     *
     * @return string|null
     */
    public function drawingPath($id, $kind = 'deepsky', $resized = false)
    {
        global $instDir, $newStorageDir;
        list($legacy, $file) = $this->drawingFile($id, $kind);
        if ($legacy !== null) {
            $path = $instDir . $legacy . ($resized ? '_resized' : '') . '.jpg';
        } elseif ($file !== '' && $newStorageDir) {
            $path = rtrim($newStorageDir, '/') . '/' . ($resized ? $this->resizedName($file) : $file);
        } else {
            return null;
        }
        return is_file($path) ? $path : null;
    }

    /**
     * Stores an uploaded drawing on the public disk of the new application
     * (observations/dsl-old-{id}.jpg, with a small version next to it) and
     * marks the observation as having a drawing.
     *
     * @param int    $id      The id of the observation.
     * @param string $tmpFile The uploaded file.
     * @param string $kind    'deepsky' or 'comet'.
     *
     * @return bool
     */
    public function saveDrawing($id, $tmpFile, $kind = 'deepsky')
    {
        global $instDir, $newStorageDir;
        if (!$newStorageDir) {
            error_log('ObservationStore: $newStorageDir is not set, the drawing of ' . $id . ' is not saved.');
            return false;
        }
        $file = 'observations/dsl-old-' . (int) $id . '.jpg';
        $path = rtrim($newStorageDir, '/') . '/' . $file;
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0775, true);
        }
        include_once $instDir . 'common/control/resize.php';
        image_createThumb($tmpFile, rtrim($newStorageDir, '/') . '/' . $this->resizedName($file), 490, 490, 100);
        if (!move_uploaded_file($tmpFile, $path) && !copy($tmpFile, $path)) {
            return false;
        }
        $fields = ['hasDrawing' => 1, 'drawingfile' => $file];
        if ($kind === 'comet') {
            $this->updateComet($id, $fields);
        } else {
            $this->updateDeepsky($id, $fields);
        }
        return true;
    }

    private function resizedName($file)
    {
        return preg_replace('/(\.[a-z]+)$/i', '_resized$1', $file);
    }

    /**
     * [path of a drawing of the old site without .jpg, or null; drawing_filename]
     */
    private function drawingFile($id, $kind)
    {
        global $objDatabase_new;
        static $files = [];
        $id = (int) $id;
        if (!array_key_exists($id, $files)) {
            $files[$id] = (string) $objDatabase_new->selectSingleValue(
                "SELECT drawing_filename FROM observations WHERE id = " . $id,
                'drawing_filename'
            );
        }
        $file = $files[$id];
        if (preg_match('#^(drawings|cometdrawings)/(\d+)\.jpg$#', $file, $m)) {
            return [($m[1] === 'drawings' ? 'deepsky/drawings/' : 'comets/cometdrawings/') . $m[2], $file];
        }
        return [null, $file];
    }

    private function deepskyIdsOf($name)
    {
        global $objDatabase_new;
        return $objDatabase_new->selectSingleArray(
            "SELECT id FROM observations WHERE target_type = 'deepsky' AND target_name = "
            . $objDatabase_new->quote(html_entity_decode($name, ENT_QUOTES, 'UTF-8')),
            'id'
        );
    }

    private function add($kind, array $fields)
    {
        global $objDatabase_new;
        list($main, $details) = $this->columns($kind, $fields);
        $main += [
            'target_type' => $kind === 'comet' ? "'comet'" : "'deepsky'",
            'target_table' => $kind === 'comet' ? "'cometobjects'" : "'objects'",
            'language' => "'en'",
            'description' => "''",
            'created_at' => 'NOW()',
            'updated_at' => 'NOW()',
        ];
        $objDatabase_new->execSQL(
            "INSERT INTO observations (" . implode(', ', array_keys($main)) . ") VALUES ("
            . implode(', ', $main) . ")"
        );
        $id = (int) $objDatabase_new->insert_id();
        if ($id <= 0) {
            return 0;
        }
        $details = ['observation_id' => $id] + $details;
        $objDatabase_new->execSQL(
            "INSERT INTO " . $this->detailTable($kind) . " (" . implode(', ', array_keys($details))
            . ") VALUES (" . implode(', ', $details) . ")"
        );
        $this->recordChange($id, 'created');
        return $id;
    }

    private function update($kind, $id, array $fields)
    {
        global $objDatabase_new;
        $id = (int) $id;
        if ($id <= 0 || !$fields) {
            return;
        }
        list($main, $details) = $this->columns($kind, $fields);
        $this->recordChange($id, 'updated');
        if ($main) {
            $main['updated_at'] = 'NOW()';
            $set = [];
            foreach ($main as $column => $value) {
                $set[] = $column . ' = ' . $value;
            }
            $objDatabase_new->execSQL("UPDATE observations SET " . implode(', ', $set) . " WHERE id = " . $id);
        }
        if ($details) {
            $set = [];
            foreach ($details as $column => $value) {
                $set[] = $column . ' = VALUES(' . $column . ')';
            }
            $objDatabase_new->execSQL(
                "INSERT INTO " . $this->detailTable($kind) . " (observation_id, " . implode(', ', array_keys($details))
                . ") VALUES (" . $id . ", " . implode(', ', $details) . ") ON DUPLICATE KEY UPDATE " . implode(', ', $set)
            );
        }
    }

    private function detailTable($kind)
    {
        return $kind === 'comet' ? 'comet_observation_details' : 'deepsky_observation_details';
    }

    /**
     * The old fields as SQL values of the new columns: [observations, details].
     */
    private function columns($kind, array $fields)
    {
        // Column names are case-insensitive, as they were in MySQL.
        $map = array_change_key_case($kind === 'comet' ? self::COMET : self::DEEPSKY);
        $main = [];
        $details = [];
        foreach ($fields as $field => $value) {
            if (!isset($map[strtolower($field)])) {
                throw new InvalidArgumentException('Unknown observation field ' . $field);
            }
            list($table, $column, $type) = $map[strtolower($field)];
            if ($value === 'NULL') {
                $value = null;
            }
            $values = [$column => $this->sqlValue($type, $value)];
            if ($type === 'object') {
                $values['target_key'] = $values[$column];
            } elseif ($type === 'observer') {
                $values['observer_user_id'] = "(SELECT id FROM users WHERE username = " . $values[$column] . ")";
            } elseif ($type === 'time') {
                // The import kept the old time here; -9999 marked 23:59 as unknown.
                $values['legacy_time_int'] = 'NULL';
            } elseif ($type === 'comet') {
                $values['target_name'] = "(SELECT name FROM cometobjects WHERE id = " . $values[$column] . ")";
                $details['comet_object_id'] = $values[$column] === 'NULL' ? 'NULL' : (int) $value;
            }
            if ($table === 'o') {
                $main = $values + $main;
            } else {
                $details = $values + $details;
            }
        }
        return [$main, $details];
    }

    private function sqlValue($type, $value)
    {
        global $objDatabase_new;
        $number = is_numeric($value) ? $value + 0 : null;
        switch ($type) {
        case 'id':
        case 'comet':
            return ($number !== null && $number > 0) ? (string) (int) $number : 'NULL';
        case 'int':
            return (string) (int) $number;
        case 'positive':
            return ($number !== null && $number > 0) ? (string) $number : 'NULL';
        case 'flag':
            return ($number !== null && $number >= 0) ? (string) (int) $number : 'NULL';
        case 'unknown99':
            return ($number !== null && $number > -99) ? (string) (int) $number : 'NULL';
        case 'magnitude':
            return ($number !== null && $number > -99) ? (string) $number : 'NULL';
        case 'date':
            $date = preg_replace('/\D/', '', (string) $value);
            return (strlen($date) === 8 && checkdate((int) substr($date, 4, 2), (int) substr($date, 6, 2), (int) substr($date, 0, 4)))
                ? "'" . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . "'"
                : 'NULL';
        case 'time':
            if ($number === null || $number < 0) {
                return 'NULL';
            }
            $number = (int) $number;
            return sprintf("'%02d:%02d:00'", min(23, intdiv($number, 100)), min(59, $number % 100));
        case 'object':
        case 'observer':
            return $objDatabase_new->quote(html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8'));
        case 'optional':
            return ($value === null || trim((string) $value) === '') ? 'NULL' : $objDatabase_new->quote($value);
        default:
            return $objDatabase_new->quote($value === null ? '' : $value);
        }
    }

    /**
     * Records a change for the new application, with the row before it.
     */
    private function recordChange($id, $action)
    {
        global $objDatabase_new;
        self::$writes++;
        $original = 'NULL';
        if ($action !== 'created') {
            $row = $objDatabase_new->selectRecordArray("SELECT * FROM observations WHERE id = " . (int) $id);
            if (!$row) {
                return;
            }
            // The generated columns can't be written back.
            unset($row['target_name_normalized'], $row['different_object_key']);
            $original = $objDatabase_new->quote(json_encode($row));
        }
        $objDatabase_new->execSQL(
            "INSERT INTO legacy_observation_changes (observation_id, action, original, created_at) VALUES ("
            . (int) $id . ", '" . $action . "', " . $original . ", NOW())"
        );
    }
}
