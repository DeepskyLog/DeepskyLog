<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("DeepskyLog Atlases (Downloads)") }}
            </h2>
            <br />

            {!! __("Below are the DeepskyLog atlas PDF files.") !!}
            <br /><br />

            <x-card title="DeepskyLog Atlas PDFs - Detail (stars to mag 15)">
                <div>
                    <!-- Desktop/tablet layout -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm text-gray-200">
                            <thead>
                                <tr class="bg-gray-800 text-gray-300">
                                    <th class="px-4 py-2 text-left">&nbsp;</th>
                                    <th class="px-4 py-2 text-center">{{ __('Landscape') }}<br><span class="text-xs text-gray-400">(mag 15)</span></th>
                                    <th class="px-4 py-2 text-center">{{ __('Portrait') }}<br><span class="text-xs text-gray-400">(mag 15)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr>
                                    <td class="px-4 py-3 font-medium">A3</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile stacked layout -->
                    <div class="sm:hidden space-y-3">
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <br />

            <x-card title="DeepskyLog Atlas PDFs - Lookup (stars to mag 12)">
                <div>
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm text-gray-200">
                            <thead>
                                <tr class="bg-gray-800 text-gray-300">
                                    <th class="px-4 py-2 text-left">&nbsp;</th>
                                    <th class="px-4 py-2 text-center">{{ __('Landscape') }}<br><span class="text-xs text-gray-400">(mag 12)</span></th>
                                    <th class="px-4 py-2 text-center">{{ __('Portrait') }}<br><span class="text-xs text-gray-400">(mag 12)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr>
                                    <td class="px-4 py-3 font-medium">A3</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden space-y-3">
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <br />

            <x-card title="DeepskyLog Atlas PDFs - Overview (stars to mag 10)">
                <div>
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm text-gray-200">
                            <thead>
                                <tr class="bg-gray-800 text-gray-300">
                                    <th class="px-4 py-2 text-left">&nbsp;</th>
                                    <th class="px-4 py-2 text-center">{{ __('Landscape') }}<br><span class="text-xs text-gray-400">(mag 10)</span></th>
                                    <th class="px-4 py-2 text-center">{{ __('Portrait') }}<br><span class="text-xs text-gray-400">(mag 10)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr>
                                    <td class="px-4 py-3 font-medium">A3</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden space-y-3">
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="even_odd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="even_odd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-400">{{ __('Files open in a new tab. Contact site admins if a file is missing.') }}</p>
                </div>
            </x-card>
            
            @auth
            <br />

            <x-card title="Create a custom DeepskyLog atlas">
                <div class="text-sm text-gray-200">
                    <form id="custom-atlas-form" method="POST" action="{{ route('downloads.dsl-atlas.queue') }}">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('Atlas type') }}</label>
                                <select name="type" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2">
                                    <option value="overview">Overview</option>
                                    <option value="lookup">Lookup</option>
                                    <option value="detail" selected>Detail</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('Paper size') }}</label>
                                <select name="paper" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2">
                                    <option value="A4" selected>A4</option>
                                    <option value="A3">A3</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('Orientation') }}</label>
                                <select name="orientation" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2">
                                    <option value="portrait">Portrait</option>
                                    <option value="landscape" selected>Landscape</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('Center object (slug or name)') }}</label>
                                <input type="text" name="slug" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2" placeholder="m-31 or m31" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('RA (hours or degrees)') }}</label>
                                <input type="text" name="ra" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2" placeholder="hh:mm:ss or degrees" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400">{{ __('Dec (degrees)') }}</label>
                                <input type="text" name="dec" class="w-full mt-1 rounded bg-gray-800 text-gray-200 p-2" placeholder="±dd:mm:ss or degrees" />
                            </div>
                        </div>

                        <div class="mt-3 flex gap-3">
                            <button type="submit" name="scope" value="single" class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm">{{ __('Create single page') }}</button>
                            <button type="submit" name="scope" value="whole" class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm">{{ __('Create whole atlas') }}</button>
                        </div>
                        <p id="custom-atlas-status" class="mt-3 text-sm text-gray-300" role="status"></p>
                        <p class="mt-3 text-xs text-gray-400">
                            {{ __('You can leave this page: you get a message when a file is ready or failed.') }}
                            <a href="{{ route('downloads.mine') }}" class="text-indigo-400 hover:text-indigo-300">{{ __('All your PDFs and zip files') }}</a>
                        </p>
                        <p class="mt-3 text-xs text-gray-400">{{ __('Leave slug empty and supply RA/Dec to center a custom page. Whole atlas generation may take a while.') }}</p>
                    </form>
                </div>
            </x-card>
            @endauth
        </div>
    </div>
</x-app-layout>

<script>
    document.getElementById('custom-atlas-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const form = event.currentTarget;
        const status = document.getElementById('custom-atlas-status');
        const submitter = event.submitter;
        const formData = new FormData(form);
        formData.set('scope', submitter?.value || 'single');
        status.textContent = @json(__('Atlas generation queued...'));

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) {
                const refused = response.status === 429 ? await response.json().catch(() => null) : null;
                if (refused?.message) {
                    status.textContent = refused.message;
                    return;
                }
                throw new Error('queue-failed');
            }

            const job = await response.json();
            const poll = async () => {
                const result = await fetch(job.status_url, { headers: { 'Accept': 'application/json' } });
                if (!result.ok) {
                    throw new Error('status-failed');
                }

                const state = await result.json();
                if (state.state === 'complete') {
                    status.innerHTML = '<a class="underline" href="' + job.download_url + '">' + @json(__('Download atlas PDF')) + '</a>';
                    return;
                }
                if (state.state === 'failed') {
                    const failed = @json(__('Atlas generation failed.'));
                    status.textContent = state.detail ? failed + ' ' + state.detail : failed;
                    return;
                }

                if (state.state !== 'processing') {
                    status.textContent = @json(__('Atlas is waiting for a worker...'));
                } else if (state.phase === 'indexing') {
                    status.textContent = @json(__('Indexing the objects on :total pages...')).replace(':total', state.total_pages);
                } else if (state.phase === 'rendering') {
                    status.textContent = @json(__('Building the PDF from :total pages...')).replace(':total', state.total_pages);
                } else if (state.page && state.total_pages) {
                    status.textContent = @json(__('Drawing page :page of :total...'))
                        .replace(':page', state.page)
                        .replace(':total', state.total_pages);
                } else {
                    status.textContent = @json(__('Atlas is being generated...'));
                }

                window.setTimeout(poll, 3000);
            };

            await poll();
        } catch (error) {
            status.textContent = @json(__('Atlas generation failed.'));
        }
    });
</script>
