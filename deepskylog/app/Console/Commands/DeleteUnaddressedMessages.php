<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Delete the messages without a receiver.
 *
 * The old site sent its achievement notices ('Nieuwe DeepskyLog ster!', 2015
 * and 2016) with an empty receiver. No inbox lists them (a message is for its
 * receiver, or for everybody when the receiver is 'all'), so they only take
 * space. Their read and deleted marks go with them.
 *
 * Safe to run more than once.
 */
class DeleteUnaddressedMessages extends Command
{
    protected $signature = 'messages:delete-unaddressed
        {--apply : Delete the messages}';

    protected $description = 'Delete the messages without a receiver, which no inbox shows';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $unaddressed = fn () => DB::table('messages')->where(fn ($q) => $q->where('receiver', '')->orWhereNull('receiver'));

        $senders = $unaddressed()
            ->select('sender', DB::raw('COUNT(*) as messages'), DB::raw('MIN(date) as first'), DB::raw('MAX(date) as last'))
            ->groupBy('sender')
            ->orderByDesc('messages')
            ->get();

        if ($senders->isNotEmpty()) {
            $this->table(['Sender', 'Messages', 'First', 'Last'], $senders->map(fn ($s) => [$s->sender, $s->messages, $s->first, $s->last])->all());
        }

        $count = (int) $senders->sum('messages');

        if ($apply && $count > 0) {
            DB::transaction(function () use ($unaddressed) {
                foreach (['messages_read', 'messages_deleted'] as $table) {
                    DB::table($table)->whereIn('id', $unaddressed()->select('id'))->delete();
                }
                $unaddressed()->delete();
            });
        }

        $this->newLine();
        $this->info('Messages without a receiver: '.$count);

        if (! $apply && $count > 0) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }
}
