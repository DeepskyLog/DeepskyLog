<x-app-layout>
    <div>
        <div class="mx-auto max-w-7xl bg-gray-900 px-4 py-6 sm:px-4 lg:px-6">
            <header class="mb-6">
                <h1 class="text-3xl font-extrabold">{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>
                <p class="text-sm flex items-center gap-2 text-gray-300 mt-2">
                    <span class="text-gray-400">{{ __('Object type') }}</span>
                    <span class="text-white font-medium ml-2">{{ $session->source_type ?? __('Unknown') }}</span>
                </p>
            </header>

            <div class="grid md:grid-cols-3 gap-4">
                <article class="md:col-span-2">
                    @if(!empty($image))
                        <img class="w-full rounded shadow mb-3" src="{{ $image }}" alt="{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
                    @endif

                    <div class="mb-4 text-gray-100">
                        <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                        <table class="table-auto w-full text-sm text-gray-100">
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Name') }}</td>
                                <td>{{ $session->name }}</td>
                            </tr>
                            @if(isset($session->ra) && isset($session->decl))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('RA / Dec') }}</td>
                                    <td>{{ $session->ra }} / {{ $session->decl }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Description') }}</td>
                                <td>{!! nl2br(e($session->comments ?? '')) !!}</td>
                            </tr>
                        </table>
                    </div>

                    <section>
                        <h3 class="text-lg font-semibold text-white">{{ __('Observations') }}</h3>
                        <p class="text-sm text-gray-600">{{ __('No observations listed for this object here. Use the search or check user pages for observations.') }}</p>
                    </section>
                </article>

                <aside class="md:col-span-1">
                    <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                        <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('session.all') }}" class="text-gray-300 hover:underline">{{ __('All sessions') }}</a></li>
                            <li><a href="{{ route('observations.index') }}" class="text-gray-300 hover:underline">{{ __('All observations') }}</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
