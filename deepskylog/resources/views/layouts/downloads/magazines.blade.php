<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Deep-sky magazines") }}
            </h2>
            <br />

            {!! __("Over the years, many magazines for deep-sky observers have been published. Most of them no longer appear, but their archives can be downloaded for free and are still full of observing reports, drawings, object descriptions and practical tips. If you know of other magazines, please let us know (") !!}
            <a href="mailto:deepskylog@groups.io">
                {{ "deepskylog@groups.io" }}
            </a>
            {{ ")." }}
            <br />
            <br />
            <x-card title="Distant Targets">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a
                            href="https://www.vvs.be/werkgroepen/werkgroep-deep-sky/alle-edities-van-distant-targets-online"
                        >
                            <img
                                src="/images/DT.jpg"
                                alt="Example Distant Targets magazine"
                            />
                        </a>
                    </div>
                    <div>
                        {!! __("Distant Targets, the “practical forum for the deep-sky observer”, was the magazine of the Deep-sky working group of the Flemish astronomical association Vereniging Voor Sterrenkunde (VVS). 32 issues appeared in Dutch from 1996 to 2003, with observing reports, an object of the season, constellation tours, tips for hunting galaxies with small telescopes, astrophotos, book reviews and articles about amateur telescope making. Since 2022 all issues can be read or downloaded as PDFs on the website of the VVS.") !!}
                    </div>
                </div>
            </x-card>
            <br />
            <x-card title="Ciel Extrême">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a
                            href="https://www.astrosurf.com/cielextreme/archives-ce.html"
                        >
                            <img
                                src="/images/CielExtreme.png"
                                alt="Example Ciel Extreme magazine"
                            />
                        </a>
                    </div>
                    <div>
                        {{ __("Ciel Extrême was a French quarterly devoted entirely to deep-sky observing. Yann Pothier started it in 1996, after the example of the American newsletter Betelgeuse. Jean-Raphaël Gilis was its editor-in-chief from 2002, and from 2006 it was distributed as a PDF on the internet. Issues 0 to 67 appeared until October 2012, full of observing reports, drawings and object descriptions by French observers. The archive has each issue in a version to read on screen and a version to print, together with the abbreviations and the sky quality scales the magazine used.") }}
                    </div>
                </div>
            </x-card>
            <br />
            <x-card title="Interstellarum">
                <div class="flex-justify-center flex">
                    <div class="px-5">
                        <a href="https://www.interstellarum.de/">
                            <img
                                src="/images/Interstellarum.png"
                                alt="Example Interstellarum magazine"
                            />
                        </a>
                    </div>
                    <div>
                        {!! __("Interstellarum was a German magazine for visual observers. From 1994 to 2001 it was the magazine for deep-sky observers, with four issues a year (numbers 1 to 19), edited by Jürgen Lamprecht, Ronald Stoyan and Klaus Veit. From 2002 it covered all areas of practical astronomy, with six issues a year, Ronald Stoyan as editor-in-chief and tests of telescopes, binoculars, eyepieces, filters and other equipment. After 100 issues it continued in 2015 as Abenteuer Astronomie, until the end of 2018. All issues of both magazines and their special editions can be downloaded as PDFs, in German and without the advertisements.") !!}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
