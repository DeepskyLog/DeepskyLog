<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Change sessionObservations.observationid from VARCHAR(55) to UNSIGNED BIGINT
 * and add a foreign-key constraint to observations.id.
 *
 * Run AFTER BackfillSessionObservationIdsSeeder has converted all legacy string
 * IDs to the new integer observations.id values.
 */
return new class extends Migration {
    public function up(): void
    {
        // MySQL requires dropping the composite PK before modifying a PK column.
        DB::statement('ALTER TABLE `sessionObservations` DROP PRIMARY KEY');

        DB::statement('ALTER TABLE `sessionObservations` MODIFY COLUMN `observationid` BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE `sessionObservations` ADD PRIMARY KEY (`sessionid`, `observationid`)');

        DB::statement(
            'ALTER TABLE `sessionObservations`
             ADD CONSTRAINT `fk_session_observations_observation_id`
             FOREIGN KEY (`observationid`) REFERENCES `observations` (`id`) ON DELETE CASCADE'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE `sessionObservations`
             DROP FOREIGN KEY `fk_session_observations_observation_id`'
        );

        DB::statement('ALTER TABLE `sessionObservations` DROP PRIMARY KEY');

        DB::statement('ALTER TABLE `sessionObservations` MODIFY COLUMN `observationid` VARCHAR(55) NOT NULL DEFAULT \'\'');

        DB::statement('ALTER TABLE `sessionObservations` ADD PRIMARY KEY (`sessionid`, `observationid`)');
    }
};
