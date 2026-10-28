<div class="text-sm text-gray-200" @if ($pending) wire:poll.3s @endif>
    <div class="mb-4 space-y-2 text-gray-300">
        <p>{{ __('Download observations together with their drawings in one zip file. The zip file holds:') }}</p>
        <ul class="list-disc space-y-1 pl-5">
            <li>{{ __('observations.xml: the observations in the OAL format, which other observing programs can import. Each observation with a drawing names its file.') }}</li>
            <li>{{ __('drawings: the drawings as they were uploaded.') }}</li>
            <li>{{ __('drawings.csv: a list of the drawings with the date, the object, the observer and a link to the observation.') }}</li>
        </ul>
        <p>{{ __('The zip file is made in the background and kept for :hours hours.', ['hours' => \App\Jobs\GenerateObservationsZip::KEEP_HOURS]) }}</p>
    </div>

    <div class="mb-4 rounded-md bg-gray-800 p-3">
        <h2 class="mb-1 font-semibold text-white">{{ $title }}</h2>
        <p class="mb-3 text-gray-300">{{ $countsLabel }}</p>

        <label class="mb-3 flex items-center gap-2">
            <input type="checkbox" wire:model.live="drawingsOnly" class="rounded border-gray-600 bg-gray-900 text-indigo-600">
            {{ __('Only the observations with a drawing') }}
        </label>

        @if ($refusal)
            <p class="mb-3 text-amber-300">{{ $refusal }}</p>
        @endif

        @error('generate')
            <p class="mb-3 text-red-400">{{ $message }}</p>
        @enderror

        <button type="button" wire:click="generate" wire:loading.attr="disabled" @disabled($refusal)
            class="rounded bg-indigo-600 px-4 py-1.5 font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
            {{ __('Make the zip file') }}
        </button>
    </div>

    @if ($jobs->isNotEmpty())
        <div class="rounded-md bg-gray-800 p-3">
            <div class="mb-2 flex flex-wrap items-center gap-3">
                <h2 class="font-semibold text-white">{{ __('Your zip files') }}</h2>
                <a href="{{ route('downloads.mine') }}" class="ml-auto text-xs text-indigo-400 hover:text-indigo-300">{{ __('All your PDFs and zip files') }}</a>
            </div>
            <p class="mb-1 text-xs text-gray-400">{{ __('You can leave this page: you get a message when a file is ready or failed.') }}</p>
            <ul class="divide-y divide-gray-700">
                @foreach ($jobs as $job)
                    <li class="flex flex-wrap items-center gap-3 py-1.5" wire:key="job-{{ $job['token'] }}">
                        <span class="font-medium">{{ $job['label'] ?? '' }}</span>
                        @switch($job['state'] ?? '')
                            @case('complete')
                                <a href="{{ route('observations.zip.download', $job['token']) }}"
                                    class="inline-flex items-center gap-1 rounded bg-green-700 px-2 py-0.5 text-white hover:bg-green-600">
                                    {{ __('Download zip file') }}
                                </a>
                                @break
                            @case('failed')
                                <span class="text-red-400">{{ __('Generation failed.') }}</span>
                                @break
                            @case('processing')
                                <span class="text-gray-300">
                                    @switch($job['phase'] ?? '')
                                        @case('drawings')
                                            {{ __('Looking up the drawings...') }}
                                            @break
                                        @case('oal')
                                            {{ __('Writing the observations...') }}
                                            @break
                                        @case('writing')
                                            {{ __('Writing the zip file: :percent%', ['percent' => $job['percent'] ?? 0]) }}
                                            @break
                                        @default
                                            {{ __('Being generated...') }}
                                    @endswitch
                                </span>
                                @break
                            @default
                                <span class="text-gray-400">{{ __('Waiting for a worker...') }}</span>
                        @endswitch
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
