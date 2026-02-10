<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLegacyTimestampsNullable extends Migration
{
    public function up()
    {
        // Use raw SQL to avoid requiring doctrine/dbal
        // Only run these statements on MySQL: SQLite doesn't support ALTER ... MODIFY
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            Schema::getConnection()->statement("ALTER TABLE `objects` MODIFY `timestamp` DATETIME NULL");
            Schema::getConnection()->statement("ALTER TABLE `cometobjects` MODIFY `timestamp` DATETIME NULL");
            Schema::getConnection()->statement("ALTER TABLE `objectnames` MODIFY `timestamp` DATETIME NULL");
            Schema::getConnection()->statement("ALTER TABLE `objectpartof` MODIFY `timestamp` DATETIME NULL");
        }
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            Schema::getConnection()->statement("ALTER TABLE `objects` MODIFY `timestamp` DATETIME NOT NULL");
            Schema::getConnection()->statement("ALTER TABLE `cometobjects` MODIFY `timestamp` DATETIME NOT NULL");
            Schema::getConnection()->statement("ALTER TABLE `objectnames` MODIFY `timestamp` DATETIME NOT NULL");
            Schema::getConnection()->statement("ALTER TABLE `objectpartof` MODIFY `timestamp` DATETIME NOT NULL");
        }
    }
}
