<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-4 flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold text-white">{{ $title }}</h1>
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="ml-auto inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Back') }}
                    </a>
                @endif
            </header>

            @livewire('object-sets', [
                'objects' => $objects,
                'title' => $title,
                'total' => $total,
                'listId' => $listId,
                'filename' => $filename,
            ])
        </div>
    </div>
</x-app-layout>
