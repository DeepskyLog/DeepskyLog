<div class="text-sm text-gray-200" @if ($pending) wire:poll.3s @endif>
    {{-- What the page does --}}
    <div class="mb-4 space-y-2 text-gray-300">
        <p>{{ __('Generate a set of pages for each object: atlas charts at the fields of view given below, two photos from the Digitized Sky Survey and, if you want, a data page and an index.') }}</p>
        <ul class="list-disc pl-5 space-y-1">
            <li>{{ __('FoVs: the fields of view of the charts, in arcminutes. "120 60 15" gives three charts, 120, 60 and 15 arcminutes high.') }}</li>
            <li>{{ __('Object and star magnitudes: how faint the objects and stars on each chart go. Give one magnitude per field of view; "10 12 15" draws the 120 arcminute chart to magnitude 10, the 60 arcminute chart to 12 and the 15 arcminute chart to 15.') }}</li>
            <li>{{ __('Photos: the sizes of the two DSS photos, in arcminutes (at most :max). Leave the field empty for no photos.', ['max' => \App\Services\ObjectSetPdfService::MAX_PHOTO_ARCMIN]) }}</li>
            <li>{{ __('The data page holds the elementary data and the description of the object; the ephemerides are those of your standard location. The index lists all the objects on each of the charts.') }}</li>
            <li>{{ __('Tick rows and press V to copy the value above a column to them. The V in the first column copies all four values to the rows ticked there. A field outlined in red is smaller than the object.') }}</li>
        </ul>
    </div>

    <div class="mb-4 rounded-md border border-amber-500/60 bg-amber-900/30 px-4 py-3 text-amber-100" role="note">
        <p class="font-semibold">{{ __('Generating the charts can take a while.') }}</p>
        <p class="mt-1">{{ __('Every object takes some 10 to 30 seconds: its charts are drawn and its photos are downloaded from the Digitized Sky Survey. The PDF is made in the background; the download link appears below as soon as it is ready. You can leave this page and come back later, the link stays available for two days.') }}</p>
    </div>

    @if ($total > count($rows))
        <p class="mb-4 text-amber-300">{{ __('Only the first :shown of the :total objects are shown; a set holds at most :shown objects.', ['shown' => count($rows), 'total' => $total]) }}</p>
    @endif

    @if (count($rows) === 0)
        <p class="text-gray-400">{{ __('There are no deep-sky objects to make sets for.') }}</p>
    @else
        {{-- Options and "generate all" --}}
        <div class="mb-4 flex flex-wrap items-center gap-x-6 gap-y-2">
            <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('Generate all (:count objects)', ['count' => count($rows)]) }}
            </button>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="dataPage" class="rounded border-gray-600 bg-gray-800 text-indigo-600">
                {{ __('Add a data page') }}
            </label>
            <label class="inline-flex items-center gap-2 {{ $dataPage ? '' : 'opacity-50' }}">
                <input type="checkbox" wire:model="ephemerides" @disabled(!$dataPage) class="rounded border-gray-600 bg-gray-800 text-indigo-600">
                {{ __('with ephemerides') }}
            </label>
            <label class="inline-flex items-center gap-2 {{ $dataPage ? '' : 'opacity-50' }}">
                <input type="checkbox" wire:model="yearEphemerides" @disabled(!$dataPage) class="rounded border-gray-600 bg-gray-800 text-indigo-600">
                {{ __('with year ephemerides') }}
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model="indexPage" class="rounded border-gray-600 bg-gray-800 text-indigo-600">
                {{ __('Add an index page') }}
            </label>
        </div>

        @if (!auth()->user()->standardLocation && $dataPage && ($ephemerides || $yearEphemerides))
            <p class="mb-3 text-amber-300">{{ __('You have no standard location, so the data pages will not show ephemerides.') }}</p>
        @endif

        @error('generate')
            <p class="mb-3 text-red-400">{{ $message }}</p>
        @enderror

        {{-- Generated sets --}}
        @if ($jobs->isNotEmpty())
            <div class="mb-4 rounded-md bg-gray-800 p-3">
                <h2 class="mb-2 font-semibold text-white">{{ __('Your object sets') }}</h2>
                <ul class="divide-y divide-gray-700">
                    @foreach ($jobs as $job)
                        <li class="flex flex-wrap items-center gap-3 py-1.5" wire:key="job-{{ $job['token'] }}">
                            <span class="font-medium">{{ $job['label'] ?? '' }}</span>
                            @switch($job['state'] ?? '')
                                @case('complete')
                                    <a href="{{ route('object-sets.download', $job['token']) }}"
                                        class="inline-flex items-center gap-1 rounded bg-green-700 px-2 py-0.5 text-white hover:bg-green-600">
                                        {{ __('Download PDF') }}
                                    </a>
                                    @break
                                @case('failed')
                                    <span class="text-red-400">{{ __('Generation failed.') }} {{ $job['detail'] ?? '' }}</span>
                                    @break
                                @case('processing')
                                    <span class="text-gray-300">
                                        @if (($job['phase'] ?? '') === 'rendering')
                                            {{ __('Building the PDF...') }}
                                        @elseif (isset($job['done'], $job['total']))
                                            {{ __('Drawing object :done of :total (:object)...', ['done' => $job['done'] + 1, 'total' => $job['total'], 'object' => $job['object'] ?? '']) }}
                                        @else
                                            {{ __('Being generated...') }}
                                        @endif
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

        {{-- The objects --}}
        <div class="overflow-x-auto rounded-lg bg-gray-800 p-3">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-300">
                        <th class="px-2 py-1">{{ __('Object') }}</th>
                        <th class="px-2 py-1"></th>
                        <th class="px-2 py-1">{{ __('Size') }}</th>
                        <th class="px-2 py-1">{{ __('FoVs: shown fields of view') }}</th>
                        <th class="px-2 py-1">{{ __('Object magnitudes') }}</th>
                        <th class="px-2 py-1">{{ __('Star magnitudes') }}</th>
                        <th class="px-2 py-1">{{ __('Photos (arcminutes)') }}</th>
                    </tr>
                    <tr class="border-b border-gray-700">
                        <td class="px-2 py-2"></td>
                        <td class="px-2 py-2">
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="applyAll"
                                    class="rounded bg-indigo-600 px-2 py-0.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                    title="{{ __('Copy all four values to the ticked rows') }}">V</button>
                                <input type="checkbox" wire:click="toggleColumn('all')" @checked(collect($rows)->every(fn ($r) => $r['all']))
                                    class="rounded border-gray-600 bg-gray-900 text-indigo-600" title="{{ __('Tick all rows') }}">
                            </div>
                        </td>
                        <td class="px-2 py-2"></td>
                        @foreach (\App\Livewire\ObjectSets::FIELDS as $field)
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-1">
                                    <input type="text" wire:model="{{ $field }}"
                                        class="w-28 rounded border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                                    <button type="button" wire:click="applyColumn('{{ $field }}')"
                                        class="rounded bg-indigo-600 px-2 py-0.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                        title="{{ __('Copy this value to the ticked rows') }}">V</button>
                                    <input type="checkbox" wire:click="toggleColumn('{{ $field }}')" @checked(collect($rows)->every(fn ($r) => $r['checked'][$field]))
                                        class="rounded border-gray-600 bg-gray-900 text-indigo-600" title="{{ __('Tick all rows') }}">
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($rows as $i => $row)
                        @php
                            $fovTooSmall = $row['diam_arcmin'] > 0 && $row['diam_arcmin'] > $smallestFov($row);
                            $photoTooSmall = $row['diam_arcmin'] > 0 && $row['diam_arcmin'] > $smallestPhoto($row);
                        @endphp
                        <tr wire:key="row-{{ $i }}-{{ $row['name'] }}">
                            <td class="px-2 py-1.5">
                                <button type="button" wire:click="generate({{ $i }})" wire:loading.attr="disabled" wire:target="generate({{ $i }})"
                                    class="whitespace-nowrap rounded bg-green-700 px-2 py-1 text-xs font-medium text-white hover:bg-green-600 disabled:opacity-60"
                                    title="{{ __('Generate the set of :object', ['object' => $row['name']]) }}">{{ $row['name'] }}</button>
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="checkbox" wire:model="rows.{{ $i }}.all" class="rounded border-gray-600 bg-gray-900 text-indigo-600">
                            </td>
                            <td class="whitespace-nowrap px-2 py-1.5">{{ $row['size'] }}</td>
                            @foreach (\App\Livewire\ObjectSets::FIELDS as $field)
                                @php
                                    $warn = ($field === 'fovs' && $fovTooSmall) || ($field === 'photos' && $photoTooSmall);
                                @endphp
                                <td class="px-2 py-1.5">
                                    <div class="flex items-center gap-1">
                                        <input type="text" wire:model.blur="rows.{{ $i }}.{{ $field }}"
                                            class="w-28 rounded bg-gray-900 px-2 py-1 text-gray-100 {{ $warn ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-600' }}"
                                            @if ($warn) title="{{ __('The object (:size) is larger than the smallest value here.', ['size' => $row['size']]) }}" @endif>
                                        <input type="checkbox" wire:model="rows.{{ $i }}.checked.{{ $field }}" class="rounded border-gray-600 bg-gray-900 text-indigo-600">
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
