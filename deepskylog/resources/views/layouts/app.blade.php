<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config("app.name", "Laravel") }}</title>

        <!-- Fonts -->
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap"
        />

        <!-- Scripts -->
        <wireui:scripts />
        @vite(["resources/css/app.css", "resources/js/app.js"])

        <!-- Styles -->
        @livewireStyles
    </head>

    <body class="font-sans antialiased dark">
        <div class="min-h-screen bg-gray-800 text-gray-300">
            @livewire("navigation-menu")

            <x-notifications />

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <footer class="bg-gray-800 shadow">
            <div class="mx-auto flex max-w-screen-xl p-4">
                <div class="w-full md:flex md:items-center md:justify-between">
                    <span
                        class="text-sm text-gray-500 dark:text-gray-400 sm:text-center"
                    >
                        © 2004 - {{ now()->year }} - DeepskyLog - NGC / IC
                        database by
                        <a
                            href="http://www.klima-luft.de/steinicke/index_e.htm"
                            class="hover:underline"
                        >
                            Steinicke
                        </a>
                        , stars Tycho2+ & USNO-UCAC3 -
                        <a
                            href="{{ route("privacy") }}"
                            class="me-4 hover:underline md:me-6"
                        >
                            Privacy Policy
                        </a>
                    </span>
                </div>
                <div class="flex flex-row-reverse">
                    <a
                        href="https://www.facebook.com/deepskylog"
                        class="px-2 pt-0.5"
                    >
                        <x-socialstream-icons.facebook class="h-5 w-5" />
                    </a>

                    <a
                        href="https://www.instagram.com/deepskylog.be"
                        class="px-2"
                    >
                        <x-socialstream-icons.instagram class="h-5 w-5" />
                    </a>

                    <a
                        href="https://twitter.com/DeepskyLog"
                        class="px-2 pt-0.5"
                    >
                        <x-socialstream-icons.twitter class="h-5 w-5" />
                    </a>

                    <a
                        href="https://www.youtube.com/channel/UC66H7w2Fl9q3krRy_tHRK5g"
                        class="px-2"
                    >
                        <x-socialstream-icons.youtube class="h-5 w-5" />
                    </a>
                </div>
            </div>
        </footer>

        @stack("scripts")
        @stack("modals")

        @livewireScripts
    </body>
</html>
