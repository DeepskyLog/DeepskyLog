<?php

use App\Models\Observation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Store every observation's target the same way, whether the observation was
     * migrated from the old DeepskyLog or made in this version (see the scheme in
     * App\Models\Observation): deep-sky objects as target_table 'objects' with the
     * canonical object name, comets by the id of the comet. The observation form
     * used to store deep-sky objects as 'objectnames' with the slug of the name
     * that was picked, and comets by their slug.
     *
     * Rows whose object cannot be found are left as they are.
     */
    public function up(): void
    {
        DB::table('observations')
            ->where('target_type', 'deepsky')
            ->where(function ($q) {
                $q->where('target_table', '<>', 'objects')
                    ->orWhereNull('target_table')
                    ->orWhereNotExists(fn($objects) => $objects->from('objects')->whereColumn('objects.name', 'observations.target_key'));
            })
            ->orderBy('id')
            ->get(['id', 'target_key', 'target_name'])
            ->each(function ($row) {
                $name = Observation::canonicalDeepskyName($row->target_key) ?? Observation::canonicalDeepskyName($row->target_name);
                if ($name === null) {
                    return;
                }
                DB::table('observations')->where('id', $row->id)->update([
                    'target_table' => 'objects',
                    'target_key' => $name,
                    'target_name' => $name,
                ]);
            });

        DB::table('observations')
            ->where('target_type', 'comet')
            ->whereNotExists(fn($comets) => $comets->from('cometobjects')->whereRaw('CAST(cometobjects.id AS CHAR) = observations.target_key'))
            ->orderBy('id')
            ->get(['id', 'target_key'])
            ->each(function ($row) {
                $comet = DB::table('cometobjects')->where('slug', $row->target_key)->first(['id', 'name']);
                if (!$comet) {
                    return;
                }
                DB::table('observations')->where('id', $row->id)->update([
                    'target_table' => 'cometobjects',
                    'target_key' => (string) $comet->id,
                    'target_name' => $comet->name,
                ]);
            });
    }

    /**
     * The old keys are not kept; they are not needed to go back, because the
     * code before this migration also read targets stored this way.
     */
    public function down(): void
    {
    }
};
