<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Image catalogs") }}
            </h2>
            <br />

            {!! __("Below are some image catalog resources and observing guides that may be helpful.") !!}
            <br /><br />

            <x-card title="DeepskyLog Image Catalogs">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="/downloads/dsl-image-catalogs">
                            <img src="/images/logo_small.jpg" alt="DeepskyLog catalog" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("The DeepskyLog image catalogs contain catalogs for the Abell Planetary Nebulae and for each constellation. They contain information about the objects, a small star chart and two DSS images, one with a field of view of 60' and another one with 25'.") !!}
                    </div>
                </div>
                <!-- Catalog tables moved to a dedicated page: /downloads/dsl-image-catalogs -->
            </x-card>

            <br />
            <x-card title="Faint Fuzzies">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.faintfuzzies.com/DownloadableObservingGuides2.html">
                            <img src="/images/FaintFuzzies.png" alt="FaintFuzzies.com" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("Alvin Huey has compiled a set of visual observing guides of a lot of interesting catalogs.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="Reiner Vogel">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.reinervogel.net/index_e.html?/Artikel_e.html">
                            <img src="/images/reiner_vogel.png" alt="Reiner Vogel" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("Reiner Vogel's material includes image collections and observing notes for many deep-sky objects.") !!}
                    </div>
                </div>
            </x-card>

            <br />
            <x-card title="Clearskies">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.clearskies.eu/">
                            <img src="/images/clearskies.png" alt="Clearskies" class="w-64" />
                        </a>
                    </div>
                    <div>
                        {!! __("Clearskies provides a selection of images and observational resources useful for both beginners and experienced observers. An amazing amount of observing guides is available.  A small fee is required to be able to download these guides.") !!}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
