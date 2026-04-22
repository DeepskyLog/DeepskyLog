<?php

namespace App\Console\Commands;

use App\Services\ObservingListImportService;
use Illuminate\Console\Command;

class ImportObservingLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'observing-lists:import {--dry-run : Run the import in dry-run mode without committing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import observing lists from legacy observerobjectlist table to new observing_lists schema';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->line('');
            $this->warn('⚠️  DRY RUN MODE - No changes will be committed to the database');
            $this->line('');
        }

        $service = new ObservingListImportService();

        try {
            $this->info('Starting observing lists import...');
            $this->newLine();

            $result = $service->execute($dryRun);

            if ($result['success']) {
                $this->line('✓ Import completed successfully!');
                $this->newLine();
                $this->info('Import Summary:');
                $this->line("  • Lists created: {$result['lists_created']}");
                $this->line("  • Items created: {$result['items_created']}");
                $this->line("  • List names processed: {$result['list_names_processed']}");

                if (!empty($result['unmapped_users'])) {
                    $this->newLine();
                    $this->warn('⚠️  Unmapped Users (usernames not found in users table):');
                    foreach ($result['unmapped_users'] as $username) {
                        $this->line("  • {$username}");
                    }
                }

                if ($dryRun) {
                    $this->newLine();
                    $this->info('This was a dry run. No changes were committed.');
                } else {
                    // Set initial active lists
                    $this->info('Setting initial active observing lists...');
                    $activeResult = $service->setInitialActiveLists();
                    $this->line("  • Users updated: {$activeResult['users_updated']}");
                }

                $this->newLine();
                $this->line('✓ All done!');

                return self::SUCCESS;
            } else {
                $this->error('Import failed!');
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error during import: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
