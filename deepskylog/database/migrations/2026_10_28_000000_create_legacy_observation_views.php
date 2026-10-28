<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The old PHP site (DeepskyLog.old) reads and writes the observations of
     * this database.
     *
     * - The views legacy_observations, legacy_cometobservations and
     *   legacy_observers show the observations, comet observations and users
     *   in the columns of the old tables observations, cometobservations and
     *   observers (its DatabaseMux puts them in place of those tables), with
     *   the old placeholders for unknown values (-1, -99, -99.9, time -9999).
     *   They are views on the tables themselves, so the indexes on
     *   target_name, observer_username and id are used.
     * - legacy_observation_changes holds the observations the old site
     *   created, changed or deleted, with the values before the change;
     *   observations:apply-legacy-changes runs ObservationObserver for them.
     *
     * The views are MySQL/MariaDB only: the tests (SQLite) don't need them.
     */
    public function up(): void
    {
        if (! Schema::hasTable('legacy_observation_changes')) {
            Schema::create('legacy_observation_changes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('observation_id');
                $table->string('action', 16);
                $table->json('original')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        // The old site leaves out the objects not seen (visibility 7) with
        // a lookup of these few observations (DatabaseMux).
        if (! Schema::hasIndex('deepsky_observation_details', ['visibility'])) {
            Schema::table('deepsky_observation_details', function (Blueprint $table) {
                $table->index('visibility');
            });
        }

        // Its rankings and statistics count per observer (seen, drawings,
        // since a date): this index answers them without reading the rows.
        if (! Schema::hasIndex('observations', 'idx_obs_legacy_observer_counts')) {
            Schema::table('observations', function (Blueprint $table) {
                $table->index(['target_type', 'observer_username', 'has_drawing', 'observed_on'], 'idx_obs_legacy_observer_counts');
            });
        }

        // MAX(id) of the deep-sky or comet observations (the newest one).
        if (! Schema::hasIndex('observations', 'idx_obs_legacy_target_type')) {
            Schema::table('observations', function (Blueprint $table) {
                $table->index('target_type', 'idx_obs_legacy_target_type');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $date = "COALESCE(CAST(DATE_FORMAT(o.observed_on, '%Y%m%d') AS SIGNED), 0)";
        // The import stored the old unknown time -9999 as 23:59.
        $time = "CASE WHEN o.observed_time IS NULL OR (o.legacy_time_int = -9999 AND o.observed_time = '23:59:00') THEN -9999 ELSE HOUR(o.observed_time) * 100 + MINUTE(o.observed_time) END";
        $timestamp = "COALESCE(o.legacy_timestamp, DATE_FORMAT(o.created_at, '%Y%m%d%H%i%s'), '')";

        DB::statement("
            CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW legacy_observations AS
            SELECT
                -- The old columns were FLOAT: DECIMAL would give the string 0.00,
                -- which PHP takes as true (an empty limiting magnitude shown as 0.0).
                o.id                                     AS id,
                o.target_name                            AS objectname,
                o.observer_username                      AS observerid,
                COALESCE(o.instrument_id, 0)             AS instrumentid,
                COALESCE(o.location_id, 0)               AS locationid,
                {$date}                                  AS date,
                {$time}                                  AS time,
                COALESCE(o.description, '')              AS description,
                COALESCE(o.seeing, 0)                    AS seeing,
                CAST(COALESCE(o.limiting_magnitude, 0) AS DOUBLE) AS limmag,
                COALESCE(d.visibility, o.visibility, 0)  AS visibility,
                o.language                               AS language,
                COALESCE(o.eyepiece_id, 0)               AS eyepieceid,
                COALESCE(o.filter_id, 0)                 AS filterid,
                COALESCE(o.lens_id, 0)                   AS lensid,
                CAST(COALESCE(d.small_diameter, 0) AS DOUBLE) AS smallDiameter,
                CAST(COALESCE(d.large_diameter, 0) AS DOUBLE) AS largeDiameter,
                COALESCE(d.stellar, -1)                  AS stellar,
                COALESCE(d.extended, -1)                 AS extended,
                COALESCE(d.resolved, -1)                 AS resolved,
                COALESCE(d.mottled, -1)                  AS mottled,
                COALESCE(d.cluster_type, '')             AS clusterType,
                COALESCE(d.unusual_shape, -1)            AS unusualShape,
                COALESCE(d.partly_unresolved, -1)        AS partlyUnresolved,
                COALESCE(d.color_contrasts, -1)          AS colorContrasts,
                CAST(COALESCE(o.sqm, -1) AS DOUBLE)      AS SQM,
                o.has_drawing                            AS hasDrawing,
                COALESCE(d.date_dec, 0)                  AS dateDec,
                COALESCE(d.magnification, '')            AS magnification,
                COALESCE(d.equal_brightness, -1)         AS equalBrightness,
                COALESCE(d.nice_field, -1)               AS niceField,
                COALESCE(d.component1, -1)               AS component1,
                COALESCE(d.component2, -1)               AS component2,
                {$timestamp}                             AS timestamp,
                o.drawing_filename                       AS drawingfile,
                o.legacy_id                              AS legacyid
            FROM observations o
            LEFT JOIN deepsky_observation_details d ON d.observation_id = o.id
            WHERE o.target_type = 'deepsky'
        ");

        // Comet observations made on this site have no detail row; their
        // target_key is the id of the comet.
        DB::statement("
            CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW legacy_cometobservations AS
            SELECT
                o.id                                     AS id,
                COALESCE(c.comet_object_id, CAST(o.target_key AS UNSIGNED), 0) AS objectid,
                o.observer_username                      AS observerid,
                COALESCE(o.instrument_id, 0)             AS instrumentid,
                COALESCE(o.location_id, 0)               AS locationid,
                {$date}                                  AS date,
                {$time}                                  AS time,
                COALESCE(o.description, '')              AS description,
                c.method                                 AS methode,
                COALESCE(c.magnitude, -99.9)             AS mag,
                c.chart                                  AS chart,
                c.magnification                          AS magnification,
                COALESCE(c.magnitude_uncertain, 0)       AS maguncertain,
                COALESCE(c.less_magnitude, 0)            AS lessmagnitude,
                c.dc                                     AS dc,
                COALESCE(c.coma, -99)                    AS coma,
                COALESCE(c.tail, -99)                    AS tail,
                COALESCE(c.position_angle, -99)          AS pa,
                {$timestamp}                             AS timestamp,
                o.has_drawing                            AS hasDrawing,
                o.drawing_filename                       AS drawingfile,
                o.legacy_id                              AS legacyid
            FROM observations o
            LEFT JOIN comet_observation_details c ON c.observation_id = o.id
            WHERE o.target_type = 'comet'
        ");

        // The old observers table had the first name and the last name apart.
        DB::statement("
            CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW legacy_observers AS
            SELECT
                u.username                               AS id,
                CASE WHEN LOCATE(' ', TRIM(u.name)) > 0
                     THEN TRIM(SUBSTRING(TRIM(u.name), LOCATE(' ', TRIM(u.name)) + 1))
                     ELSE TRIM(u.name) END               AS name,
                CASE WHEN LOCATE(' ', TRIM(u.name)) > 0
                     THEN SUBSTRING_INDEX(TRIM(u.name), ' ', 1)
                     ELSE '' END                         AS firstname,
                u.email                                  AS email,
                u.stdlocation                            AS stdlocation,
                u.stdtelescope                           AS stdtelescope,
                u.language                               AS language,
                u.observationlanguage                    AS observationlanguage,
                u.icqname                                AS icqname,
                DATE_FORMAT(u.created_at, '%Y%m%d')      AS registrationDate
            FROM users u
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            foreach (['legacy_observations', 'legacy_cometobservations', 'legacy_observers'] as $view) {
                DB::statement('DROP VIEW IF EXISTS ' . $view);
            }
        }

        Schema::dropIfExists('legacy_observation_changes');

        if (Schema::hasIndex('observations', 'idx_obs_legacy_target_type')) {
            Schema::table('observations', function (Blueprint $table) {
                $table->dropIndex('idx_obs_legacy_target_type');
            });
        }

        if (Schema::hasIndex('observations', 'idx_obs_legacy_observer_counts')) {
            Schema::table('observations', function (Blueprint $table) {
                $table->dropIndex('idx_obs_legacy_observer_counts');
            });
        }

        if (Schema::hasIndex('deepsky_observation_details', ['visibility'])) {
            Schema::table('deepsky_observation_details', function (Blueprint $table) {
                $table->dropIndex(['visibility']);
            });
        }
    }
};
