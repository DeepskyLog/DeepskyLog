<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <h1 class="mb-2 text-xl font-semibold text-white">{{ __('Import objects') }}</h1>
            <p class="mb-4 text-sm text-gray-300">
                {{ __('Compare a catalog in a CSV file with the objects of DeepskyLog, and add the objects and names DeepskyLog does not have yet. Nothing is changed until you import the checked file.') }}
            </p>

            @livewire('object-import')
        </div>
    </div>
</x-app-layout>
