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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3DP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 15)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4DP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3LP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 12)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4LP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">A4</td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OL.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                            {{ __('Download') }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OP.pdf" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A3 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA3OP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Landscape') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OL.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded">
                            <div class="flex justify-between items-center">
                                <div class="text-sm font-medium">A4 — {{ __('Portrait') }} <span class="text-xs text-gray-400">(mag 10)</span></div>
                                <a class="inline-flex items-center px-3 py-1 rounded bg-secondary-700 hover:bg-secondary-600 text-sm" href="https://www.deepskylog.org/DSL/atlasses/ENA4OP.pdf" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h10a1 1 0 011 1v6a1 1 0 11-2 0V5H6v10h4a1 1 0 110 2H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/><path d="M9 7a1 1 0 012 0v4.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 11.586V7z"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-400">{{ __('Files open in a new tab. Contact site admins if a file is missing.') }}</p>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
