<?php

namespace App\Livewire;

use App\Jobs\QueuedPdfJob;
use Livewire\Component;

/**
 * /downloads/mine: the observer's PDFs and zip files of all kinds
 * (QueuedPdfJob::KINDS), waiting, being made, finished or failed, with until
 * when a finished one can be downloaded.
 */
class MyDownloads extends Component
{
    public function remove(string $token): void
    {
        QueuedPdfJob::remove((int) auth()->id(), $token);
    }

    public function render()
    {
        $jobs = QueuedPdfJob::jobsFor((int) auth()->id());

        // The observer sees the result here and needs no message about it.
        QueuedPdfJob::markSeen($jobs);

        return view('livewire.my-downloads', [
            'jobs' => $jobs,
            'pending' => $jobs->contains(fn (array $job) => in_array($job['state'] ?? '', ['queued', 'processing'], true)),
        ]);
    }
}
