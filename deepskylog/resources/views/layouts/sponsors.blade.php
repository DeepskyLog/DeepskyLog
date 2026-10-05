<x-app-layout>
    <div>
        <div
            class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl leading-tight font-semibold">
                {{
                    __(
                        "DeepskyLog sponsors",
                    )
                }}
            </h2>
            <br />

            {{
                __(
                    "DeepskyLog is free to use and will always stay free and without advertisements. It has been built and maintained by volunteers since 2004, in their spare time. Running it still costs money, though: the web server, its disk space and backups, the domain name and the mail that DeepskyLog sends all have to be paid for, and with every new observation, sketch and object the database grows.",
                )
            }}
            <br />
            <br />
            <x-card title="{{ __('Sponsor DeepskyLog') }}">
                {{
                    __(
                        "Sponsorships are used to keep DeepskyLog running and to upgrade the server, so that the pages stay fast and there is room for all observations and sketches. You can sponsor DeepskyLog once or every month through GitHub Sponsors. Companies, clubs and associations that want to sponsor DeepskyLog in another way can send us a mail. Every sponsor who wants to is mentioned on this page, with their name or the name and logo of their company or club.",
                    )
                }}
                <br />
                <br />
                <div class="flex flex-wrap gap-2">
                    <a href="https://github.com/sponsors/WimDeMeester">
                        <x-button
                            type="button"
                            primary
                            icon="heart"
                            label="{{ __('Sponsor on GitHub') }}"
                        />
                    </a>
                    <a href="mailto:deepskylog@groups.io">
                        <x-button
                            type="button"
                            secondary
                            icon="envelope"
                            label="{{ __('Send us a mail') }}"
                        />
                    </a>
                </div>
            </x-card>
            <br />
            <x-card title="{{ __('Other ways to help') }}">
                {{
                    __(
                        "Not every contribution has to be money. DeepskyLog is always looking for observers who want to help: by translating DeepskyLog into their language, maintaining the deep-sky database, testing new versions, writing documentation, or reporting errors in the program or the object data. You don't need to be a programmer for any of this. If you like DeepskyLog, giving the project a star on GitHub and telling other observers about it also helps. And most of all: keep sharing your observations and sketches, they are what makes DeepskyLog worth visiting.",
                    )
                }}
                <br />
                <br />
                <div class="flex flex-wrap gap-2">
                    <a href="https://github.com/DeepskyLog/DeepskyLog">
                        <x-button
                            type="button"
                            secondary
                            icon="star"
                            label="{{ __('DeepskyLog on GitHub') }}"
                        />
                    </a>
                    <a href="https://github.com/DeepskyLog/DeepskyLog/issues">
                        <x-button
                            type="button"
                            secondary
                            icon="bolt"
                            label="{{ __('Report issue') }}"
                        />
                    </a>
                </div>
            </x-card>
            <br />
            <h5 class="text-lg font-semibold">
                {{
                    __(
                        "Main Sponsors",
                    )
                }}
            </h5>
            <hr />
            <br />
            <x-card title="Vereniging Voor Sterrenkunde (VVS)">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="shrink-0 px-5">
                        <a href="https://www.vvs.be/">
                            <img src="/images/VVS.png" alt="VVS Logo" />
                        </a>
                    </div>
                    <div>
                        {{
                            __(
                                "The Vereniging Voor Sterrenkunde (VVS) is the association of amateur astronomers in Flanders, Belgium. DeepskyLog was started by its Deep-sky working group, and the VVS sponsors the web server that DeepskyLog runs on.",
                            )
                        }}
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
