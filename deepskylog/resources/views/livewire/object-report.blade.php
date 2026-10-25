<div class="text-sm text-gray-200" @if ($pending) wire:poll.3s @endif>
    {{-- What the page does --}}
    <div class="mb-4 space-y-2 text-gray-300">
        <p>{{ __('Make a PDF that lists the objects, each on a few lines, with the fields you choose at the place you choose. Start from a DeepskyLog layout, change it below and save it as your own layout for the next time.') }}</p>
        <ul class="list-disc pl-5 space-y-1">
            <li>{{ __('Positions and sizes are in points (1/72 inch), measured from the bottom left corner of the page. A landscape A4 page is 842 points wide and 595 points high.') }}</li>
            <li>{{ __('Every field goes on a line of the object (0 is its first line), at an x-position in the column. A field with a width of 0 is left out; text that does not fit the width is cut off, except for the description, which runs on over more lines.') }}</li>
            <li>{{ __('For two columns per page, put the second column to the right of the first; for one column, give both columns the same x-position.') }}</li>
            <li>{{ __('The ephemerides, the contrast reserve and the best magnification are those of your standard location and instrument.') }}</li>
        </ul>
    </div>

    @if ($total > count($objectNames))
        <p class="mb-4 text-amber-300">{{ __('Only the first :shown of the :total objects are in the report; a report holds at most :shown objects.', ['shown' => count($objectNames), 'total' => $total]) }}</p>
    @endif

    @if (count($objectNames) === 0)
        <p class="text-gray-400">{{ __('There are no deep-sky objects to report on.') }}</p>
    @else
        {{-- Layout choice --}}
        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-md bg-gray-800 p-3">
            <label class="inline-flex items-center gap-2">
                <span class="font-medium text-white">{{ __('Layout') }}</span>
                <select wire:model.live="layoutId" class="rounded border-gray-600 bg-gray-900 py-1 text-gray-100">
                    @if ($defaults->isEmpty())
                        <option value="{{ \App\Livewire\ObjectReport::BUILT_IN }}">{{ __('DeepskyLog default') }}</option>
                    @else
                        <optgroup label="{{ __('DeepskyLog') }}">
                            @foreach ($defaults as $layout)
                                <option value="{{ $layout->id }}">{{ $layout->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($mine->isNotEmpty())
                        <optgroup label="{{ __('Your layouts') }}">
                            @foreach ($mine as $layout)
                                <option value="{{ $layout->id }}">{{ $layout->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($others->isNotEmpty())
                        <optgroup label="{{ __('Other observers') }}">
                            @foreach ($others as $layout)
                                <option value="{{ $layout->id }}">{{ ($layout->user?->name ?? '?') . ': ' . $layout->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="showAll" class="rounded border-gray-600 bg-gray-900 text-indigo-600">
                {{ __('Show the layouts of other observers') }}
            </label>

            <div class="ml-auto flex flex-wrap items-center gap-2">
                <button type="button" wire:click="save"
                    class="rounded-md bg-gray-600 px-3 py-1.5 font-medium text-white hover:bg-gray-500"
                    title="{{ $ownsCurrent ? __('Save the changes to this layout') : __('Save a copy of this layout under your name') }}">
                    {{ __('Save') }}
                </button>
                <input type="text" wire:model="newName" placeholder="{{ __('Layout name') }}" maxlength="100"
                    class="w-44 rounded border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                <button type="button" wire:click="saveAs"
                    class="rounded-md bg-gray-600 px-3 py-1.5 font-medium text-white hover:bg-gray-500">
                    {{ __('Save as...') }}
                </button>
                @if ($ownsCurrent)
                    <button type="button" wire:click="delete" wire:confirm="{{ __('Delete this layout?') }}"
                        class="rounded-md bg-red-700 px-3 py-1.5 font-medium text-white hover:bg-red-600">
                        {{ __('Delete') }}
                    </button>
                @endif
            </div>
        </div>

        @error('newName')
            <p class="mb-3 text-red-400">{{ $message }}</p>
        @enderror
        @if (session('report-layout'))
            <p class="mb-3 text-green-400">{{ session('report-layout') }}</p>
        @endif

        {{-- Report options and "generate" --}}
        <div class="mb-4 flex flex-wrap items-center gap-x-6 gap-y-2">
            <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('Generate the report (:count objects)', ['count' => count($objectNames)]) }}
            </button>

            <label class="inline-flex items-center gap-2">
                {{ __('Title') }}
                <input type="text" wire:model="reportTitle" maxlength="200"
                    class="w-80 rounded border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model="sort" class="rounded border-gray-600 bg-gray-900 text-indigo-600">
                {{ __('Group the objects by constellation') }}
            </label>
        </div>

        @if (!auth()->user()->standardLocation)
            <p class="mb-3 text-amber-300">{{ __('You have no standard location, so the report will not show ephemerides.') }}</p>
        @endif

        @error('generate')
            <p class="mb-3 text-red-400">{{ $message }}</p>
        @enderror

        {{-- Generated reports --}}
        @if ($jobs->isNotEmpty())
            <div class="mb-4 rounded-md bg-gray-800 p-3">
                <div class="mb-2 flex flex-wrap items-center gap-3">
                    <h2 class="font-semibold text-white">{{ __('Your reports') }}</h2>
                    <a href="{{ route('downloads.mine') }}" class="ml-auto text-xs text-indigo-400 hover:text-indigo-300">{{ __('All your PDFs and zip files') }}</a>
                </div>
                <p class="mb-1 text-xs text-gray-400">{{ __('You can leave this page: you get a message when a file is ready or failed.') }}</p>
                <ul class="divide-y divide-gray-700">
                    @foreach ($jobs as $job)
                        <li class="flex flex-wrap items-center gap-3 py-1.5" wire:key="job-{{ $job['token'] }}">
                            <span class="font-medium">{{ $job['label'] ?? '' }}</span>
                            @switch($job['state'] ?? '')
                                @case('complete')
                                    <a href="{{ route('object-report.download', $job['token']) }}"
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
                                            {{ __('Collecting the data of :done of :total objects...', ['done' => $job['done'], 'total' => $job['total']]) }}
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

        {{-- Page settings --}}
        <div class="mb-4 rounded-lg bg-gray-800 p-3">
            <h2 class="mb-2 font-semibold text-white">{{ __('Page') }}</h2>
            <div class="grid grid-cols-1 gap-x-6 gap-y-2 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($settingLabels as $key => $label)
                    <label class="flex items-center justify-between gap-3" wire:key="setting-{{ $key }}">
                        <span class="text-gray-300">{{ $label }}</span>
                        @if ($key === 'pagesize')
                            <select wire:model="settings.pagesize" class="w-28 shrink-0 rounded border-gray-600 bg-gray-900 py-1 text-gray-100">
                                @foreach (\App\Services\ReportLayoutCatalog::PAGE_SIZES as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                        @elseif ($key === 'pageorientation')
                            <select wire:model="settings.pageorientation" class="w-28 shrink-0 rounded border-gray-600 bg-gray-900 py-1 text-gray-100">
                                <option value="landscape">{{ __('landscape') }}</option>
                                <option value="portrait">{{ __('portrait') }}</option>
                            </select>
                        @else
                            <input type="text" wire:model.blur="settings.{{ $key }}"
                                class="w-28 shrink-0 rounded border-gray-600 bg-gray-900 px-2 py-1 text-right text-gray-100">
                        @endif
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Fields --}}
        <div class="overflow-x-auto rounded-lg bg-gray-800 p-3">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-700 align-bottom text-gray-300">
                        <th class="px-2 py-1">{{ __('Field name') }}</th>
                        <th class="px-2 py-1">{{ __('Shown') }}</th>
                        <th class="px-2 py-1">{{ __('Field is on line ... (0=first)') }}</th>
                        <th class="px-2 py-1">{{ __('Field is on x-position ... in column') }}</th>
                        <th class="px-2 py-1">{{ __('Display width of the field') }}</th>
                        <th class="px-2 py-1" title="{{ __('Font style: combination of i (italic), b (bold) and l, r or c (alignment)') }}">{{ __('Font style: i, b and l, r or c') }}</th>
                        <th class="px-2 py-1">{{ __('Text to be shown in front of the field') }}</th>
                        <th class="px-2 py-1">{{ __('Text to be shown after the field') }}</th>
                        <th class="px-2 py-1">{{ __('Legend text') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($fieldLabels as $name => $label)
                        @php($shown = (float) ($fields[$name]['width'] ?? 0) > 0)
                        <tr wire:key="field-{{ $name }}" class="{{ $shown ? '' : 'text-gray-500' }}">
                            <td class="px-2 py-1">{{ $label }}</td>
                            <td class="px-2 py-1 text-center">
                                <input type="checkbox" wire:click="toggleField('{{ $name }}')" @checked($shown)
                                    class="rounded border-gray-600 bg-gray-900 text-indigo-600">
                            </td>
                            @foreach (['line' => 'w-16', 'x' => 'w-20', 'width' => 'w-20', 'style' => 'w-16'] as $property => $width)
                                <td class="px-2 py-1">
                                    <input type="text" wire:model.blur="fields.{{ $name }}.{{ $property }}"
                                        class="{{ $width }} rounded border-gray-600 bg-gray-900 px-2 py-1 text-right {{ $shown ? 'text-gray-100' : 'text-gray-500' }}">
                                </td>
                            @endforeach
                            @foreach (['before', 'after', 'legend'] as $property)
                                <td class="px-2 py-1">
                                    <input type="text" wire:model.blur="fields.{{ $name }}.{{ $property }}" maxlength="255"
                                        class="w-32 rounded border-gray-600 bg-gray-900 px-2 py-1 {{ $shown ? 'text-gray-100' : 'text-gray-500' }}">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
