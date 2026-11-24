<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold text-white mb-4">{{ __('Add observation — select object') }}</h1>
        <div class="bg-gray-900 p-4 rounded shadow text-gray-100">
            <p class="mb-2">{{ __('Search for the object you want to add an observation for:') }}</p>
            @livewire('quick-search', ['mode' => 'add_observation', 'query' => request()->query('q', ''), 'session' => request()->integer('session') ?: null])
        </div>
        <div class="mt-4 rounded-lg bg-gray-800 border border-gray-700 p-4 text-sm text-gray-300">
            {!!
                __(
                    'Do you have a lot of observations to bring into DeepskyLog, in a file or still to be entered by hand? Contact the :linkDeepskyLog developers:close_link: we might be able to help, especially when your observations are in a format the importer does not recognise.',
                    [
                        'link' => '<a href="mailto:deepskylog@groups.io" class="text-indigo-400 hover:text-indigo-300 underline">',
                        'close_link' => '</a>',
                    ],
                )
            !!}
        </div>
    </div>
</x-app-layout>
