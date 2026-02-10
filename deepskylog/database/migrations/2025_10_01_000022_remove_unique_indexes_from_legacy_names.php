<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveUniqueIndexesFromLegacyNames extends Migration
{
    public function up()
    {
        // This migration performed a one-time change to support a temporary
        // re-import flow used during the legacy migration. It is now a no-op
        // to make migrations idempotent and safe for new environments.
    }

    public function down()
    {
        // intentionally left blank (no-op)
    }
}
