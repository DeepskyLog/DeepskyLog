<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacySessionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $legacy = DB::connection('mysqlOld');
        $app = DB::connection();

        try {
            // Clear target tables (idempotent seeding)
            $app->table('sessionObservers')->truncate();
            $app->table('sessionObservations')->truncate();
            $app->table('observation_sessions')->truncate();

            // Start transaction after truncates (TRUNCATE may implicitly commit)
            $app->beginTransaction();

            // Migrate sessions preserving IDs
            $legacy->table('sessions')->orderBy('id')->chunk(500, function ($rows) use ($app) {
                $inserts = [];
                foreach ($rows as $r) {
                    $inserts[] = [
                        'id' => (int) $r->id,
                        'name' => $r->name,
                        'observerid' => $r->observerid,
                        'begindate' => $r->begindate,
                        'enddate' => $r->enddate,
                        'locationid' => (int) $r->locationid,
                        'weather' => $r->weather,
                        'equipment' => $r->equipment,
                        'comments' => $r->comments,
                        'language' => $r->language,
                        'active' => (int) $r->active,
                    ];
                }
                // Insert preserving IDs. Use insert() which allows explicit PK values.
                if (! empty($inserts)) {
                    $app->table('observation_sessions')->insert($inserts);
                }
            });

            // Migrate sessionObservations pivot
            $legacy->table('sessionObservations')->orderBy('sessionid')->chunk(1000, function ($rows) use ($app) {
                $inserts = [];
                foreach ($rows as $r) {
                    $inserts[] = [
                        'sessionid' => (int) $r->sessionid,
                        'observationid' => $r->observationid,
                    ];
                }
                if (! empty($inserts)) {
                    $app->table('sessionObservations')->insert($inserts);
                }
            });

            // Migrate sessionObservers pivot
            $legacy->table('sessionObservers')->orderBy('sessionid')->chunk(1000, function ($rows) use ($app) {
                $inserts = [];
                foreach ($rows as $r) {
                    $inserts[] = [
                        'sessionid' => (int) $r->sessionid,
                        'observer' => $r->observer,
                    ];
                }
                if (! empty($inserts)) {
                    $app->table('sessionObservers')->insert($inserts);
                }
            });

            $app->commit();
        } catch (\Exception $e) {
            // Only roll back if a transaction is active
            try {
                if (method_exists($app, 'transactionLevel') && $app->transactionLevel() > 0) {
                    $app->rollBack();
                }
            } catch (\Throwable $inner) {
                // ignore errors while attempting rollback
            }

            throw $e;
        }
    }
}
