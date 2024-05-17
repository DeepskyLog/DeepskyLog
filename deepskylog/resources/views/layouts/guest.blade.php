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

    <body class="dark">
        <div class="bg-gray-800 font-sans text-gray-300 antialiased">
            <x-notifications />
            {{ $slot }}
        </div>

        @stack("scripts")
        @stack("modals")

        @livewireScripts
    </body>
</html>
