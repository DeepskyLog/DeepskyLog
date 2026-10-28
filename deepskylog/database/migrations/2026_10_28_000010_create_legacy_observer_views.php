<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /** The settings of the old site that users has no column for, in user_preferences. */
    public const PREFERENCES = [
        'UT' => 'legacy_ut',
        'usedLanguages' => 'legacy_used_languages',
        'club' => 'legacy_club',
        'lastReadObservationId' => 'legacy_last_read_observation',
    ];

    /**
     * Columns of the old accomplishments table => the achievements of this
     * site that give them, lowest tier first. This site keeps only the highest
     * achievement of a series, the old table every tier up to it.
     */
    private function accomplishments(): array
    {
        $tiers10 = ['Newbie', 'Rookie', 'Beginner', 'Talented', 'Skilled', 'Intermediate', 'Experienced', 'Advanced', 'Senior', 'Expert'];
        $metals = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Platina'];
        $newMetals = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Platinum'];

        $metal = fn (string $prefix, int $count) => array_map(fn ($m) => $prefix.' '.$m, array_slice($newMetals, 0, $count));
        $counted = function (string $one, string $many, array $counts, string $oneSuffix = '', string $manySuffix = '') {
            return array_map(fn ($n) => $n === 1 ? $one.' 1'.$oneSuffix : $many.' '.$n.$manySuffix, $counts);
        };
        $thousands = [1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000];

        $series = [
            'messier' => [$metals, $metal('Messier', 3)],
            'messierDrawings' => [$metals, $metal('Messier Drawing', 3)],
            'caldwell' => [$metals, $metal('Caldwell', 3)],
            'caldwellDrawings' => [$metals, $metal('Caldwell Drawing', 3)],
            'herschel' => [$metals, $metal('Herschel 400', 5)],
            'herschelDrawings' => [$metals, $metal('Herschel 400 Drawing', 5)],
            'herschelII' => [$metals, $metal('Herschel II', 5)],
            'herschelIIDrawings' => [$metals, $metal('Herschel II Drawing', 5)],
            'drawings' => [$tiers10, $counted('Drawing', 'Drawings', $thousands)],
            'cometObservations' => [$tiers10, $counted('Comet', 'Comets', $thousands)],
            'cometsObserved' => [$tiers10, $counted('Different Comets', 'Different Comets', $thousands)],
            'cometDrawings' => [$tiers10, $counted('Comet', 'Comets', $thousands, ' Drawing', ' Drawings')],
            'openCluster' => [$tiers10, $counted('Open Cluster', 'Open Clusters', [1, 5, 10, 25, 50, 100, 200, 500, 1000, 2500])],
            'openClusterDrawings' => [$tiers10, $counted('Open Cluster', 'Open Clusters', [1, 5, 10, 25, 50, 100, 200, 500, 1000, 2500], ' Drawing', ' Drawings')],
            'globularCluster' => [$tiers10, $counted('Globular Cluster', 'Globular Clusters', [1, 3, 5, 10, 15, 25, 50, 75, 100, 150])],
            'globularClusterDrawings' => [$tiers10, $counted('Globular Cluster', 'Globular Clusters', [1, 3, 5, 10, 15, 25, 50, 75, 100, 150], ' Drawing', ' Drawings')],
            'planetaryNebula' => [$tiers10, $counted('Planetary Nebula', 'Planetary Nebula', [1, 3, 5, 10, 25, 50, 100, 250, 500, 1000])],
            'planetaryNebulaDrawings' => [$tiers10, $counted('Planetary Nebula', 'Planetary Nebula', [1, 3, 5, 10, 25, 50, 100, 250, 500, 1000], ' Drawings', ' Drawings')],
            'galaxy' => [$tiers10, $counted('Galaxy', 'Galaxies', $thousands)],
            'galaxyDrawings' => [$tiers10, $counted('Galaxy', 'Galaxies', $thousands, ' Drawing', ' Drawings')],
            'nebula' => [$tiers10, $counted('Nebula', 'Nebulae', [1, 5, 10, 25, 50, 75, 100, 150, 200, 300])],
            'nebulaDrawings' => [$tiers10, $counted('Nebula', 'Nebulae', [1, 5, 10, 25, 50, 75, 100, 150, 200, 300], ' Drawing', ' Drawings')],
            'objects' => [$tiers10, $counted('Object', 'Objects', $thousands)],
            'objectsDrawings' => [$tiers10, $counted('Object', 'Objects', $thousands, ' Drawing', ' Drawings')],
        ];

        $columns = [];
        foreach ($series as $prefix => [$levels, $names]) {
            foreach ($names as $i => $name) {
                // A tier counts when the observer has it or a higher one.
                $columns[$prefix.$levels[$i]] = array_slice($names, $i);
            }
        }

        // The old table spelled this one so; the old site reads it by name.
        $columns = array_combine(
            array_map(fn ($c) => $c === 'caldwellDrawingsGold' ? 'caldwelldrawingsGold' : $c, array_keys($columns)),
            $columns
        );

        return $columns;
    }

    /**
     * The old site (DeepskyLog.old) reads its observers and accomplishments
     * from this database:
     *
     * - legacy_observers (replacing the smaller view of
     *   create_legacy_observation_views): the users in the columns of the old
     *   observers table. role is 0 for the Administrators team, 4 (comet
     *   administrator) for Database Experts, else 1; the old settings users
     *   has no column for are rows of user_preferences (PREFERENCES).
     * - legacy_accomplishments: the old table of badges, from the
     *   achievements of this site (update:achievements).
     *
     * Writes go through the old site's Observers class, which knows these
     * tables. MySQL/MariaDB only: the tests (SQLite) don't need them.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $preference = fn (string $alias, string $key) => "LEFT JOIN user_preferences {$alias} ON {$alias}.user_id = u.id AND {$alias}.`key` = '{$key}'";

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
                u.password                               AS password,
                CASE WHEN ta.id IS NOT NULL THEN 0
                     WHEN td.id IS NOT NULL THEN 4
                     ELSE 1 END                          AS role,
                u.language                               AS language,
                COALESCE(pc.value, '')                   AS club,
                u.stdlocation                            AS stdlocation,
                u.stdtelescope                           AS stdtelescope,
                u.icqname                                AS icqname,
                CAST(COALESCE(put.value, '1') AS SIGNED) AS UT,
                u.observationlanguage                    AS observationlanguage,
                COALESCE(pl.value, CONCAT('a:1:{i:0;s:', LENGTH(u.language), ':\"', u.language, '\";}')) AS usedLanguages,
                u.standardAtlasCode                      AS standardAtlasCode,
                DATE_FORMAT(u.created_at, '%Y%m%d')      AS registrationDate,
                u.fstOffset                              AS fstOffset,
                CAST(COALESCE(pr.value, '0') AS SIGNED)  AS lastReadObservationId,
                u.copyright                              AS copyright,
                u.overviewdsos                           AS overviewdsos,
                u.lookupdsos                             AS lookupdsos,
                u.detaildsos                             AS detaildsos,
                u.overviewstars                          AS overviewstars,
                u.lookupstars                            AS lookupstars,
                u.detailstars                            AS detailstars,
                u.atlaspagefont                          AS atlaspagefont,
                u.photosize1                             AS photosize1,
                u.overviewFoV                            AS overviewFoV,
                u.photosize2                             AS photosize2,
                u.lookupFoV                              AS lookupFoV,
                u.detailFoV                              AS detailFoV,
                u.sendMail                               AS sendMail,
                u.version                                AS version,
                u.showInches                             AS showInches
            FROM users u
            LEFT JOIN team_user ta ON ta.user_id = u.id AND ta.team_id = 2
            LEFT JOIN team_user td ON td.user_id = u.id AND td.team_id = 3
            {$preference('put', self::PREFERENCES['UT'])}
            {$preference('pl', self::PREFERENCES['usedLanguages'])}
            {$preference('pc', self::PREFERENCES['club'])}
            {$preference('pr', self::PREFERENCES['lastReadObservationId'])}
        ");

        $columns = [];
        foreach ($this->accomplishments() as $column => $names) {
            $list = implode(', ', array_map(fn ($n) => DB::getPdo()->quote($n), $names));
            $columns[] = "COALESCE(MAX(a.name IN ({$list})), 0) AS `{$column}`";
        }

        DB::statement('
            CREATE OR REPLACE SQL SECURITY INVOKER VIEW legacy_accomplishments AS
            SELECT u.username AS observer, '.implode(",\n", $columns).'
            FROM users u
            LEFT JOIN achievement_user au ON au.user_id = u.id
            LEFT JOIN achievements a ON a.id = au.achievement_id
            GROUP BY u.id, u.username
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS legacy_accomplishments');
        // Back to the view of create_legacy_observation_views.
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
};
