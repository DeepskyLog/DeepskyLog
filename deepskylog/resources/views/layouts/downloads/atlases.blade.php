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
                        {!! __("The original DeepskyLog atlases provide a convenient listing and printable atlas pages used by observers. Click the link to download the various A3/A4 overview, lookup and detail PDFs.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="Taki Star Atlas">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="http://ss977526.stars.ne.jp/atlas/atlas.htm">
                            <img src="/images/taki_atlas.png" alt="Taki Star Atlas" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("Taki's Star Atlas (Taki Atlas) is a well-known online resource with printable star charts and observing notes useful for planning deep-sky sessions.") !!}
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
                        {!! __("TriAtlas (Torres) provides detailed atlas pages focusing on small fields and useful finder charts for deep-sky targets.") !!}
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
                        {!! __("Deep-Sky Hunter provides searchable atlases and charts focused on visual observing and deep-sky hunting techniques.") !!}
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
                        {!! __("Stellaversum provides printable star maps and atlases created by Josef Pirkl.") !!}
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
                        {!! __("The Mag-7 Star Atlas Project offers printable atlas pages and maps focused on stars down to approximately magnitude 7 — useful for visual observers.") !!}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
