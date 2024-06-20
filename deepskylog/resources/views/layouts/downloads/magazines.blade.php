<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Deep-sky Magazines") }}
            </h2>
            <br />

            {{ __("During the previous years, a lot of interesting information about Deep-Sky observing has been published.  Many of the magazines do not exist anymore, but can be downloaded for free.  If you know about other magazines, please let us know (") }}
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
                        {{
__("Distant Targets was the magazine of the Deep-sky working group of the Belgian Astronomical Association (VVS). 32 editions were published, in Dutch, from 1996 to 2003.
                            A lot of useful information is still available.  Besides Deep-sky, there are also articles about Amateur Telescope Making.")
                        }}
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
                        {{
__("Distant Targets was the French magazine for Deep-sky observers. 67 editions were published, in French, from 1996 to October 2012.
                            A lot of useful information is still available.  This magazine are full of pure Deep-sky information.")
                        }}
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
                        {{
                            __("Interstellarum was a German magazine for Deep-sky observers. The first 19 editions were purely focused on Deep-sky observing, later editions were focused on visual observations (of all objects). All editions are in German.
                                                                                                                 Interstellarum was published from 1994 to 2015.")
                        }}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
