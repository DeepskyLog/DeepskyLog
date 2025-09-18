<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                    $picturePath = null;

                    // Attempt to copy legacy session image from old filesystem if it exists.
                    // Assumption: legacy images are located in /var/www/DeepskyLog.old/deepsky/sessions
                    // and named either by session id + extension or stored in a 'picture' column (if present).
                    $legacyImageDir = '/var/www/DeepskyLog.old/deepsky/sessions';

                    // New storage location for session photos (matches CreateSession behavior)
                    // Files should be placed under storage/app/public/photos/sessions so they are
                    // accessible via the storage symlink as asset('storage/photos/sessions/...')
                    $targetDir = storage_path('app/public/photos/sessions');

                    if (! File::exists($targetDir)) {
                        File::makeDirectory($targetDir, 0755, true);
                    }

                    // If the legacy row contains a picture column with a filename, use it; otherwise try common names.
                    if (! empty($r->picture)) {
                        $possible = [$r->picture];
                    } else {
                        // try id-based filenames with common extensions
                        $possible = [
                            $r->id.'.jpg',
                            $r->id.'.jpeg',
                            $r->id.'.png',
                            $r->id.'.gif',
                        ];
                    }

                    foreach ($possible as $fname) {
                        // ensure we only use the basename when constructing destination
                        $base = basename($fname);
                        $src = $legacyImageDir.DIRECTORY_SEPARATOR.$base;
                        if (File::exists($src)) {
                            // copy to storage/app/public/photos/sessions keeping the same filename
                            $dest = $targetDir.DIRECTORY_SEPARATOR.$base;
                            try {
                                File::copy($src, $dest);
                                // record storage-relative path (same format as ->storePubliclyAs returns)
                                $picturePath = 'photos/sessions/'.$base;
                                break;
                            } catch (\Throwable $e) {
                                // ignore copy errors but don't abort seeding
                                $picturePath = null;
                            }
                        }
                    }

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
                        // Keep legacy picture path in comments? No — we don't have a picture column on target.
                        // Potentially store in a migration table or handle via filesystem only.
                        'language' => $r->language,
                        'active' => (int) $r->active,
                        'picture' => $picturePath,
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
