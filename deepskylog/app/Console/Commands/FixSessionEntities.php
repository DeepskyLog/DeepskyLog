<?php

namespace App\Console\Commands;

use App\Models\ObservationSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSessionEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --apply   Actually write fixes to the database. By default the command runs as a dry-run.
     * --chunk=N Chunk size for processing rows.
     */
    protected $signature = 'sessions:fix-html-entities {--apply : Apply fixes to the database} {--chunk=500 : Chunk size}';

    /**
     * The console command description.
     */
    protected $description = 'Fix malformed HTML entities that end with a comma instead of a semicolon in observation_sessions';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $chunkSize = (int) $this->option('chunk');

        $this->info('Scanning observation_sessions for malformed HTML entities...');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $columns = ['name', 'weather', 'equipment', 'comments'];

        $totalScanned = 0;
        $totalMatches = 0;
        $totalUpdated = 0;

        // Pattern: &name, OR &#123, OR &#x1f4a9,
        $pattern = '/&(#x?[0-9A-Fa-f]+|[A-Za-z][A-Za-z0-9]+),/';

        ObservationSession::chunkById($chunkSize, function ($sessions) use (&$totalScanned, &$totalMatches, &$totalUpdated, $columns, $pattern, $apply) {
            foreach ($sessions as $session) {
                $totalScanned++;
                $changed = false;
                $before = [];
                $after = [];

                foreach ($columns as $col) {
                    $val = $session->{$col} ?? '';
                    if ($val === null || $val === '') {
                        continue;
                    }

                    if (preg_match($pattern, $val)) {
                        $totalMatches++;
                        $before[$col] = $val;

                        // Replace comma after entity with semicolon (global)
                        $new = preg_replace($pattern, '&$1;', $val);

                        // Safety: if replacement changed the value, assign it
                        if ($new !== $val) {
                            $session->{$col} = $new;
                            $after[$col] = $new;
                            $changed = true;
                        }
                    }
                }

                if ($changed) {
                    if ($apply) {
                        // Try to persist; wrap in transaction for safety on each row
                        DB::transaction(function () use ($session) {
                            $session->save();
                        });
                        $totalUpdated++;
                        $this->line("Updated session id={$session->id}");
                    } else {
                        $this->line("Would update session id={$session->id}: ".implode(', ', array_keys($after)));
                    }
                }
            }
        });

        $this->info('Scan complete.');
        $this->info("Rows scanned: {$totalScanned}");
        $this->info("Rows with matches: {$totalMatches}");
        $this->info("Rows updated: {$totalUpdated}");

        if (! $apply && $totalMatches > 0) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return 0;
    }
}
