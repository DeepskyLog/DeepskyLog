<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <h1 class="mb-4 text-xl font-semibold text-white">{{ __('Your PDFs and zip files') }}</h1>

            @livewire('my-downloads')
        </div>
    </div>
</x-app-layout>
