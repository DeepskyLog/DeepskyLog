<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <h1 class="mb-2 text-xl font-semibold text-white">{{ __('Complete catalogs') }}</h1>
            <p class="mb-4 text-sm text-gray-300">
                {{ __('All objects of these catalogs are in DeepskyLog. Observers can\'t add objects to them, with the form or with an OAL import; administrators and database experts still can.') }}
            </p>

            @livewire('complete-catalogs')
        </div>
    </div>
</x-app-layout>
