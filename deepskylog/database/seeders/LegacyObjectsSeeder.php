<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class LegacyObjectsSeeder extends Seeder
{
    /**
     * Convert legacy char(14) timestamp (YYYYMMDDHHMMSS) to Y-m-d H:i:s
     */
    private function convertTimestamp(?string $ts): ?string
    {
        if (empty($ts) || strlen($ts) !== 14) {
            return null;
        }
        $year = substr($ts, 0, 4);
        $month = substr($ts, 4, 2);
        $day = substr($ts, 6, 2);
        $hour = substr($ts, 8, 2);
        $min = substr($ts, 10, 2);
        $sec = substr($ts, 12, 2);
        return "$year-$month-$day $hour:$min:$sec";
    }

    public function run()
    {
        $old = DB::connection('mysqlOld');

        // Objects
        $old->table('objects')->orderBy('name')->chunk(500, function ($rows) {
            $inserts = [];
            foreach ($rows as $r) {
                $inserts[] = [
                    'name' => $r->name,
                    'type' => $r->type,
                    'con' => $r->con,
                    'ra' => $r->ra,
                    'decl' => $r->decl,
                    'mag' => $r->mag,
                    'subr' => $r->subr,
                    'pa' => $r->pa,
                    'urano' => $r->urano,
                    'urano_new' => $r->urano_new,
                    'sky' => $r->sky,
                    'millenium' => $r->millenium,
                    'diam1' => $r->diam1,
                    'diam2' => $r->diam2,
                    'datasource' => $r->datasource,
                    'taki' => $r->taki,
                    'SBObj' => $r->SBObj,
                    'description' => $r->description,
                    'psa' => $r->psa,
                    'torresB' => $r->torresB,
                    'torresBC' => $r->torresBC,
                    'torresC' => $r->torresC,
                    'milleniumbase' => $r->milleniumbase,
                    'DSLDL' => $r->DSLDL,
                    'DSLDP' => $r->DSLDP,
                    'DSLLL' => $r->DSLLL,
                    'DSLLP' => $r->DSLLP,
                    'DSLOL' => $r->DSLOL,
                    'DSLOP' => $r->DSLOP,
                    'DeepskyHunter' => $r->DeepskyHunter,
                    'Interstellarum' => $r->Interstellarum,
                    'timestamp' => $this->convertTimestamp($r->timestamp),
                ];
            }

            if (!empty($inserts)) {
                // insert in smaller batches and use insertOrIgnore so re-runs skip duplicates
                $batches = array_chunk($inserts, 100);
                foreach ($batches as $batch) {
                    try {
                        DB::table('objects')->insertOrIgnore($batch);
                    } catch (QueryException $e) {
                        // attempt per-row insertOrIgnore to isolate bad rows
                        foreach ($batch as $row) {
                            try {
                                DB::table('objects')->insertOrIgnore($row);
                            } catch (QueryException $inner) {
                                if ($this->command) {
                                    $this->command->error('Failed to insert object ' . ($row['name'] ?? '[unknown]') . ': ' . $inner->getMessage());
                                }
                            }
                        }
                    }
                }
            }
        });

        // Comet objects
        $old->table('cometobjects')->orderBy('id')->chunk(500, function ($rows) {
            $inserts = [];
            foreach ($rows as $r) {
                $inserts[] = [
                    'id' => $r->id,
                    'name' => $r->name,
                    'icqname' => $r->icqname,
                    'timestamp' => $this->convertTimestamp($r->timestamp),
                ];
            }
            if (!empty($inserts)) {
                $batches = array_chunk($inserts, 200);
                foreach ($batches as $batch) {
                    DB::table('cometobjects')->insertOrIgnore($batch);
                }
            }
        });

        // Object names
        $old->table('objectnames')->orderBy('objectname')->chunk(500, function ($rows) {
            $inserts = [];
            foreach ($rows as $r) {
                $inserts[] = [
                    'objectname' => $r->objectname,
                    'catalog' => $r->catalog,
                    'catindex' => $r->catindex,
                    'altname' => $r->altname,
                    'timestamp' => $this->convertTimestamp($r->timestamp),
                ];
            }
            if (!empty($inserts)) {
                $batches = array_chunk($inserts, 200);
                foreach ($batches as $batch) {
                    DB::table('objectnames')->insert($batch);
                }
            }
        });

        // objectpartof
        $old->table('objectpartof')->orderBy('objectname')->chunk(500, function ($rows) {
            $inserts = [];
            foreach ($rows as $r) {
                $inserts[] = [
                    'objectname' => $r->objectname,
                    'partofname' => $r->partofname,
                    'timestamp' => $this->convertTimestamp($r->timestamp),
                ];
            }
            if (!empty($inserts)) {
                $batches = array_chunk($inserts, 200);
                foreach ($batches as $batch) {
                    DB::table('objectpartof')->insert($batch);
                }
            }
        });
    }
}
