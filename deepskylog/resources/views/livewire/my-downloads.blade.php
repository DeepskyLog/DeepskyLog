<div class="text-sm text-gray-200" @if ($pending) wire:poll.3s @endif>
    <p class="mb-4 text-gray-300">
        {{ __('The atlases, object sets, object reports and zip files you asked for are made in the background, one after the other. You can leave the page meanwhile: you get a message when a file is ready. Finished files are kept for :hours hours.', ['hours' => \App\Jobs\QueuedPdfJob::KEEP_HOURS]) }}
    </p>

    @if ($jobs->isEmpty())
        <p class="text-gray-400">{{ __('You have no PDFs or zip files at the moment.') }}</p>
    @else
        <ul class="divide-y divide-gray-700 rounded-md bg-gray-800 px-3">
            @foreach ($jobs as $job)
                @php
                    $kind = $job['kind'];
                    $label = trim((string) ($job['label'] ?? '')) ?: (string) ($job['filename'] ?? '');
                    $created = isset($job['created_at']) ? \Illuminate\Support\Carbon::parse($job['created_at'])->locale(app()->getLocale()) : null;
                @endphp
                <li class="flex flex-wrap items-center gap-2 py-2" wire:key="download-{{ $job['token'] }}">
                    <div class="min-w-0 flex-1">
                        <div>
                            <span class="text-gray-400">{{ $kind::kindLabel() }}</span>
                            @if ($label !== '')
                                <span class="font-medium text-white">{{ $label }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400">
                            @if ($created)
                                {{ __('Asked for on :date', ['date' => $created->isoFormat('LLL')]) }}
                            @endif
                            @if ($expires = $kind::expiresAt($job))
                                &middot; {{ __('Can be downloaded until :date', ['date' => $expires->locale(app()->getLocale())->isoFormat('LLL')]) }}
                            @endif
                        </div>
                    </div>

                    @switch($job['state'] ?? '')
                        @case('complete')
                            @if ($url = $kind::downloadUrl($job['token']))
                                <a href="{{ $url }}"
                                    class="inline-flex items-center gap-1 rounded bg-green-700 px-2 py-0.5 text-white hover:bg-green-600">
                                    {{ __('Download') }}
                                </a>
                            @endif
                            @break
                        @case('failed')
                            <span class="text-red-400">{{ __('Generation failed.') }}</span>
                            @break
                        @case('processing')
                            <span class="text-gray-300">
                                @if (($percent = $kind::progressPercent($job)) !== null)
                                    {{ __('Being generated: :percent%', ['percent' => $percent]) }}
                                @else
                                    {{ __('Being generated...') }}
                                @endif
                            </span>
                            @break
                        @default
                            <span class="text-gray-400">{{ __('Waiting for a worker...') }}</span>
                    @endswitch

                    @if (($job['state'] ?? '') !== 'processing')
                        <button type="button" wire:click="remove('{{ $job['token'] }}')"
                            class="rounded px-2 py-0.5 text-gray-400 hover:bg-gray-700 hover:text-white">
                            {{ ($job['state'] ?? '') === 'queued' ? __('Cancel') : __('Remove') }}
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
