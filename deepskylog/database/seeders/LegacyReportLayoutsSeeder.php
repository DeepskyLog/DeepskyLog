<?php

namespace Database\Seeders;

use App\Models\ReportLayout;
use App\Services\ReportLayoutCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Imports the report layouts of the old DeepskyLog (table reportlayouts, one
 * row per field) into report_layouts (one row per layout).
 *
 * The old table kept the observer by display name ("Deepskylog default" for
 * the defaults), so a layout goes to the user with that name, or else to the
 * user with that as username. Layouts of names that match no user, or more
 * than one, are skipped. Running the seeder again updates the layouts it
 * imported before.
 *
 *   php artisan db:seed --class=LegacyReportLayoutsSeeder
 */
class LegacyReportLayoutsSeeder extends Seeder
{
    private const DEFAULT_OWNER = 'Deepskylog default';

    public function run(): void
    {
        $connection = env('LEGACY_REPORT_LAYOUTS_CONNECTION', 'mysqlOld');

        if (! Schema::hasTable('report_layouts')) {
            $this->command?->warn('Skipping LegacyReportLayoutsSeeder: report_layouts table does not exist.');

            return;
        }

        try {
            $legacy = DB::connection($connection);
            if (! $legacy->getSchemaBuilder()->hasTable('reportlayouts')) {
                $this->command?->warn("Skipping LegacyReportLayoutsSeeder: {$connection}.reportlayouts does not exist.");

                return;
            }
        } catch (\Throwable $e) {
            $this->command?->warn('Skipping LegacyReportLayoutsSeeder: ' . $e->getMessage());

            return;
        }

        $users = DB::table('users')->get(['id', 'name', 'username']);
        $byName = $users->groupBy(fn ($u) => mb_strtolower(trim((string) $u->name)));
        $byUsername = $users->keyBy(fn ($u) => mb_strtolower(trim((string) $u->username)));

        $layouts = [];
        $legacy->table('reportlayouts')
            ->where('reportname', 'ReportQueryOfObjects')
            ->orderBy('reportlayoutpk')
            ->each(function ($row) use (&$layouts) {
                $owner = html_entity_decode((string) $row->observerid, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $name = trim(html_entity_decode((string) $row->reportlayout, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($name === '' || $name === 'null') {
                    return;
                }

                $layout = &$layouts[$owner . "\0" . $name];
                $layout['owner'] = $owner;
                $layout['name'] = $name;

                if ($row->fieldstyle === 'LAYOUTMETADATA') {
                    $layout['settings'][$row->fieldname] = $row->fieldposition;
                } else {
                    $layout['fields'][$row->fieldname] = [
                        'line' => $row->fieldline,
                        'x' => $row->fieldposition,
                        'width' => $row->fieldwidth,
                        'style' => $row->fieldstyle,
                        'before' => $row->fieldbefore,
                        'after' => $row->fieldafter,
                        'legend' => $row->fieldlegend,
                    ];
                }
            });

        $imported = 0;
        $skipped = [];

        foreach ($layouts as $layout) {
            $userId = null;
            if ($layout['owner'] !== self::DEFAULT_OWNER) {
                $key = mb_strtolower(trim($layout['owner']));
                $named = $byName->get($key);
                $user = $named !== null && $named->count() === 1 ? $named->first() : ($named === null ? $byUsername->get($key) : null);
                if ($user === null) {
                    $skipped[$layout['owner']] = true;

                    continue;
                }
                $userId = (int) $user->id;
            }

            $definition = ReportLayoutCatalog::normalize([
                'settings' => $layout['settings'] ?? [],
                'fields' => $layout['fields'] ?? [],
            ]);

            ReportLayout::updateOrCreate(
                ['user_id' => $userId, 'name' => mb_substr($layout['name'], 0, 255)],
                $definition
            );
            $imported++;
        }

        $this->command?->info("Imported {$imported} report layouts.");
        if ($skipped !== []) {
            $this->command?->warn('Skipped the layouts of ' . count($skipped) . ' observers without a (unique) user: ' . implode(', ', array_keys($skipped)));
        }
    }
}
