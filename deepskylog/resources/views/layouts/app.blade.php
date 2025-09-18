<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config("app.name", "DeepskyLog") }}</title>

        <!-- Fonts -->
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap"
        />

        <!-- Scripts -->
        <wireui:scripts />
        @vite(["resources/css/app.css", "resources/js/app.js"])
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
        <style>
            /* Ensure TinyMCE UI is above map and other controls (Leaflet uses high z-index for some controls) */
            .tox, .tox-tinymce, .tox-tinymce-aux, .tox-tinymce-aux .tox, .tox .tox-toolbar, .tox .tox-toolbar__primary {
                z-index: 99999 !important;
            }

            /* Raise common dropdown/listbox/popover elements (WireUI, headlessui, choices, etc.)
               above TinyMCE's blocker. TinyMCE may use a very large z-index (1e15) for some
               auxiliary elements, so we choose a slightly larger value to ensure popovers
               are visible above it. Use !important to override vendor styles. */
            [x-ref="popover"],
            [x-ref="optionsContainer"],
            [role="listbox"],
            .headlessui-listbox__options,
            .listbox__options,
            .select-dropdown,
            [data-listbox],
            .choices__list,
            .dropdown-menu {
                position: relative;
                z-index: 1000000000000001 !important;
            }

            main a[href*="/messages/create"]::after,
            a[href*="/messages/create"]::after {
                content: "DSL";
                display: inline-block;
                margin-left: 0.5rem;
                font-size: 0.65rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                padding: 0.12rem 0.4rem;
                border-radius: 9999px;
                background: #2563eb; /* blue-600 */
                color: #ffffff;
                vertical-align: middle;
            }

            /* Make sure small UI links (icons/buttons) don't get overly padded by the pill */
            a.inline-flex[href*="/messages/create"]::after,
            a.inline-flex[href^="mailto:"]::after {
                margin-left: 0.35rem;
                font-size: 0.6rem;
                padding: 0.08rem 0.32rem;
            }
        </style>

        <!-- Styles -->
        @livewireStyles
    </head>

    <body class="font-sans antialiased dark">
        <div class="min-h-[calc(100vh-56px)] bg-gray-800 text-gray-300">
            @livewire("navigation-menu")

            <x-notifications />

            <!-- Page Content -->
                <main>
                    {{-- Optional page header slot (used by pages like messages.create) --}}
                    @isset($header)
                        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 py-4">
                            <h1 class="text-xl font-semibold text-gray-100">{{ $header }}</h1>
                        </div>
                    @endisset

                    @if(isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>
        </div>

        <footer class="bg-gray-800 shadow-sm">
            <div class="mx-auto flex max-w-(--breakpoint-xl) p-4">
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
                            {{ __("Privacy Policy") }}
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
        <script>
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.like-button');
                if (!btn) return;
                e.preventDefault();
                const type = btn.getAttribute('data-observation-type');
                const id = btn.getAttribute('data-observation-id');

                fetch('{{ route('observation.like') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ observation_type: type, observation_id: id })
                }).then(r => r.json()).then(data => {
                    if (data.count !== undefined) {
                        const countEl = btn.querySelector('.like-count');
                        const iconEl = btn.querySelector('.like-icon');
                        if (countEl) countEl.textContent = data.count;
                        if (iconEl) iconEl.innerHTML = data.status === 'liked' ? '❤️' : '👍';
                    }
                }).catch(() => {
                    // noop
                });
            });
        </script>
    </body>
</html>
