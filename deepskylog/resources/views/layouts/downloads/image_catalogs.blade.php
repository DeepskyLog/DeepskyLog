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
                        <p>{{ __("The DeepskyLog image catalogs are PDF files to print and take along to the telescope. There is a catalog of the Abell Planetary Nebulae and a catalog for every constellation.") }}</p>
                        <p class="mt-2">{{ __("Every object has its own entry with the information from the DeepskyLog database, a small star chart to find it and two DSS images: one with a field of view of 60' that shows the surroundings and one with a field of view of 25' that shows the object itself.") }}</p>
                        <p class="mt-2">{!! __('Do you want such a catalog for your own selection of objects? With the <a class="font-bold hover:underline" href=":url">object sets</a> you make one from an observing list, a search result or the objects near another object.', ['url' => route('object-sets.index')]) !!}</p>
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
                        <p>{{ __("Alvin Huey has compiled a set of visual observing guides of a lot of interesting catalogs. The guides are made for star hopping: every object has a naked-eye chart with a Telrad overlay, a finder chart and a DSS image, together with eyepiece sketches and observing notes.") }}</p>
                        <p class="mt-2">{{ __("There are guides for telescopes of 8 inch and larger, such as the Herschel 400, globular clusters, planetary nebulae, small galaxy groups and galaxy trios, and challenging guides for telescopes of 16 to 20 inch and larger, such as the Abell galaxy clusters, Shakhbazian galaxy groups, flat galaxies, the Vorontsov-Velyaminov catalogue and the objects within the galaxies of the Local Group.") }}</p>
                        <p class="mt-2">{{ __("The guides can be downloaded for free as PDF files. Most of them can also be ordered as a printed, coil-bound book.") }}</p>
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
                        <p>{{ __("Reiner Vogel is a German deep-sky observer who builds his own large Dobsonians. His website, in English and German, has observing guides with images and his own observing reports for the Hickson compact galaxy groups, the Abell planetary nebulae, large planetary nebulae, protoplanetary nebulae, Sharpless HII regions, Wolf-Rayet nebulae, young stellar objects and some exotic objects.") }}</p>
                        <p class="mt-2">{{ __("You also find articles on observing, filters and telescope making, and the observing lists of the Deep Sky Meeting.") }}</p>
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
                        <p>{{ __("The Clear Skies Observing Guides are an amazing collection of observing guides, useful for both beginners and experienced observers.") }}</p>
                        <p class="mt-2">{{ __("There are editions per constellation for telescopes of 5–6, 8–10 and 12–14 inch, editions for catalogs and lists such as Messier, Caldwell and the Herschel 400, for galaxies (Hickson, Arp, Holmberg, …), nebulae (Sharpless, van den Bergh, …), planetary nebulae (Abell, Minkowski, …), star clusters (Collinder, Melotte, Trumpler, …), double stars and binoculars, and for the observing programs of star parties. There are also tour files for GoTo telescopes and plan files for AstroPlanner.") }}</p>
                        <p class="mt-2">{{ __("You have to register to download the guides, and a small fee is required.") }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
