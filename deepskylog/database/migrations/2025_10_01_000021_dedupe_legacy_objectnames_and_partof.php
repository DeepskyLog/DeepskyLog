<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DedupeLegacyObjectnamesAndPartof extends Migration
{
  /**
   * This migration was a one-off maintenance operation performed during
   * the legacy import process. It has been converted to a no-op so that
   * running migrations from scratch (e.g. on CI or new environments)
   * won't unintentionally delete or alter data.
   * The original statements remain in repository history if needed.
   */
  public function up()
  {
    // intentionally left blank (no-op)
  }

  public function down()
  {
    // intentionally left blank (no-op)
  }
}
