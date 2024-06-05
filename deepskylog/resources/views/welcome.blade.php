<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Laravel</title>

        <!-- Fonts -->
        <link
            href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap"
            rel="stylesheet"
        />

        <!-- Styles -->
        <style>
            html {
                line-height: 1.15;
                -webkit-text-size-adjust: 100%;
            }

            body {
                margin: 0;
            }

            a {
                background-color: transparent;
            }

            [hidden] {
                display: none;
            }

            html {
                font-family:
                    system-ui,
                    -apple-system,
                    BlinkMacSystemFont,
                    Segoe UI,
                    Roboto,
                    Helvetica Neue,
                    Arial,
                    Noto Sans,
                    sans-serif,
                    Apple Color Emoji,
                    Segoe UI Emoji,
                    Segoe UI Symbol,
                    Noto Color Emoji;
                line-height: 1.5;
            }

            *,
            :after,
            :before {
                box-sizing: border-box;
                border: 0 solid #e2e8f0;
            }

            a {
                color: inherit;
                text-decoration: inherit;
            }

            svg,
            video {
                display: block;
                vertical-align: middle;
            }

            video {
                max-width: 100%;
                height: auto;
            }

            .bg-gray-100 {
                --tw-bg-opacity: 1;
                background-color: rgb(243 244 246 / var(--tw-bg-opacity));
            }

            .flex {
                display: flex;
            }

            .hidden {
                display: none;
            }

            .justify-center {
                justify-content: center;
            }

            .font-semibold {
                font-weight: 600;
            }

            .text-sm {
                font-size: 0.875rem;
            }

            .mt-2 {
                margin-top: 0.5rem;
            }

            .ml-4 {
                margin-left: 1rem;
            }

            .ml-12 {
                margin-left: 3rem;
            }

            .min-h-screen {
                min-height: 100vh;
            }

            .p-6 {
                padding: 1.5rem;
            }

            .py-4 {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .fixed {
                position: fixed;
            }

            .relative {
                position: relative;
            }

            .top-0 {
                top: 0;
            }

            .right-0 {
                right: 0;
            }

            .text-gray-300 {
                --tw-text-opacity: 1;
                color: rgb(209 213 219 / var(--tw-text-opacity));
            }

            .text-gray-700 {
                --tw-text-opacity: 1;
                color: rgb(55 65 81 / var(--tw-text-opacity));
            }

            .underline {
                text-decoration: underline;
            }

            .antialiased {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            .w-auto {
                width: auto;
            }

            @media (min-width: 640px) {
                .sm\:block {
                    display: block;
                }

                .sm\:items-center {
                    align-items: center;
                }

                .sm\:pt-0 {
                    padding-top: 0;
                }
            }

            @media (prefers-color-scheme: dark) {
                .dark\:bg-gray-800 {
                    --tw-bg-opacity: 1;
                    background-color: rgb(31 41 55 / var(--tw-bg-opacity));
                }

                .dark\:text-gray-500 {
                    --tw-text-opacity: 1;
                    color: rgb(107 114 128 / var(--tw-text-opacity));
                }
            }
        </style>

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>

    <body class="antialiased">
        <div
            class="items-top relative flex min-h-screen justify-center bg-gray-100 py-4 dark:bg-gray-800 sm:items-center sm:pt-0"
        >
            @if (Route::has("login"))
                <div class="fixed right-0 top-0 hidden px-6 py-4 sm:block">
                    @auth
                        <a
                            href="{{ url("/dashboard") }}"
                            class="text-sm text-gray-700 underline dark:text-gray-500"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route("login") }}"
                            class="text-sm text-gray-700 underline dark:text-gray-500"
                        >
                            Log in
                        </a>

                        @if (Route::has("register"))
                            <a
                                href="{{ route("register") }}"
                                class="ml-4 text-sm text-gray-700 underline dark:text-gray-500"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif

            <div>
                <div>
                    <x-application-logo class="block h-12 w-auto" />
                </div>

                <div class="bg-gray-900">
                    <div class="p-6">
                        <div class="ml-12">
                            <div class="mt-2 text-gray-300">
                                Visit the real
                                <a
                                    class="font-semibold text-indigo-700"
                                    href="https://www.deepskylog.org"
                                >
                                    DeepskyLog
                                </a>
                                site!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
