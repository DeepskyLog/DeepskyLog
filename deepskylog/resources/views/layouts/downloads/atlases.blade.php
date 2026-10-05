<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Atlases") }}
            </h2>
            <br />

            {!! __("Below are some popular star atlases and online resources useful for deep-sky observers. Click the items for more information or to download the atlas pages.") !!}
            <br /><br />

            <x-card title="DeepskyLog Atlases">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="/downloads/dsl-atlas">
                            <img src="/images/logo_small.jpg" alt="DeepskyLog atlases" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("The DeepskyLog atlases are printable star atlases in three scales: overview charts of 20° with stars to magnitude 10, lookup charts of 12° with stars to magnitude 12, and detail charts of 6° with stars to magnitude 15. Each atlas can be downloaded as a PDF in A3 or A4, in landscape or portrait. Logged-in observers can also make their own atlas, a single page centred on an object or position or a whole atlas, drawn with the magnitude limits set in their profile and showing which objects they have already seen.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="Taki Star Atlas">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://archive.org/details/takis-atlas">
                            <img src="/images/taki_atlas.png" alt="Taki Star Atlas" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("Taki's 8.5 Magnitude Star Atlas by Toshimi Taki is a free atlas of about 150 A4 charts with stars to magnitude 8.5, made as a handier alternative to Sky Atlas 2000.0 at the telescope. Besides the Messier, NGC, IC and Caldwell objects it shows many open clusters from smaller catalogues, Sharpless nebulae and planetary nebulae. It is available with and without constellation lines.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="TriAtlas (Torres)">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.uv.es/jrtorres/triatlas.html">
                            <img src="/images/triatlas.png" alt="TriAtlas" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("TriAtlas by José Ramón Torres is a free atlas in three sets that cover the whole sky in increasing detail: set A has 25 charts with stars to magnitude 9, set B has 107 charts with stars to magnitude 11 at about the scale of Sky Atlas 2000.0, and set C has 571 charts with stars to magnitude 12.6 and some 37,000 galaxies and thousands of clusters, nebulae and double stars. The charts are PDFs in A4 and US Letter.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="Deep-Sky Hunter Star Atlas">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.deepskywatch.com/deep-sky-hunter-atlas.html">
                            <img src="/images/deepskyhunter.png" alt="Deep-Sky Hunter" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("The Deep-Sky Hunter Star Atlas by Michael Vlasov has 101 charts of the whole sky and 21 close-up charts of galaxy clusters and crowded regions, with stars to magnitude 10.2 and about 7,000 deep-sky objects to magnitude 14. More than 500 of the finest objects are highlighted. The charts are made for A3 paper and also come in a field edition, white on black. The PDFs are free to download.") !!}
                    </div>
                </div>
            </x-card>
            
            <br />
            <x-card title="Stellaversum Maps">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.josefpirkl.com/stellaversum_maps.php?page=2">
                            <img src="/images/stellaversum.png" alt="Stellaversum Maps" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("The Stellaversum atlases by Josef Pirkl come in four editions of increasing depth, from BASE-A (77 charts, 780 deep-sky objects) to BEST-D (496 charts, about 2.5 million stars and 18,000 deep-sky objects), and a simple colour edition for children. They show objects from the Messier, NGC and IC catalogues down to PGC, UGC and ESO galaxies, in A3 and A4 and in black and white or colour. The atlases are free; donations are welcome.") !!}
                    </div>
                </div>
            </x-card>
            
            <br />
            <x-card title="Mag-7 Star Atlas Project">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.astro.cz/mirror/atlas/">
                            <img src="/images/mag7.png" alt="Mag-7 Star Atlas" class="w-64" />
                        </a>

                    </div>
                    <div>
                        {!! __("The Mag-7 Star Atlas by Andrew L. Johnson covers the whole sky in 20 charts and an extra chart of the Virgo–Coma region, with stars to magnitude 7.25. It shows all Messier objects, the RASC Finest NGC list and the Herschel 400, more than 550 deep-sky objects in all, which makes it a good first atlas. It is free under a Creative Commons licence and available in black and white and in colour.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="OpenSkyAtlas">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://github.com/jromang/osa/releases">
                            <img src="/images/openskyatlas.png" alt="OpenSkyAtlas" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("OpenSkyAtlas by Jean-François Romang is a free, printable A4 deep-sky field atlas built entirely from open catalogues. It shows stars down to magnitude 8.0 and about 1500 deep-sky objects — including every Messier, Caldwell and Herschel 400 object — with galaxies and nebulae drawn at their true size and orientation. The charts are black and white on purpose, so that they stay readable under a red torch. Three editions are available: standard (black on white, for printing), field (white on black, for screens at the telescope) and mirror (flipped for use with a star diagonal). Labels are available in English, French, Spanish and German, and the atlas is released under the CC BY-NC-SA 4.0 licence.") !!}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
