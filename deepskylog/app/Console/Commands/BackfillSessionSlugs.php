<?php

namespace App\Console\Commands;

use App\Models\ObservationSession;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillSessionSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:backfill-slugs {--dry-run : Do not write changes to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate slug column for existing observation_sessions, ensuring uniqueness scoped to observerid.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        $sessions = ObservationSession::where(function ($q) {
            $q->whereNull('slug')->orWhere('slug', '');
        })->orderBy('id')->get();

        $this->info('Found '.$sessions->count().' session(s) without slugs.');

        $bar = $this->output->createProgressBar($sessions->count());
        $bar->start();

        $updated = 0;

        foreach ($sessions as $session) {
            $base = Str::slug($session->name ?: 'session-'.$session->id);
            $slug = $base;
            $i = 2;

            while (ObservationSession::where('slug', $slug)->where('observerid', $session->observerid)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }

            if ($session->slug !== $slug) {
                if (! $dry) {
                    $session->slug = $slug;
                    $session->save();
                }
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        $this->info("Processed: {$sessions->count()}, Updated: {$updated} (dry-run: ".($dry ? 'yes' : 'no').')');

        return 0;
    }
}
