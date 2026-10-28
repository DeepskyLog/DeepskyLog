<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold text-white mb-4">{{ __('Add observation — select object') }}</h1>
        <div class="bg-gray-900 p-4 rounded shadow text-gray-100">
            <p class="mb-2">{{ __('Search for the object you want to add an observation for:') }}</p>
            @livewire('quick-search', ['mode' => 'add_observation', 'query' => request()->query('q', ''), 'session' => request()->integer('session') ?: null])
        </div>
    </div>
</x-app-layout>
