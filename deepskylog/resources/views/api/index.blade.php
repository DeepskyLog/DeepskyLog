<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {!! __("API Tokens") !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-screen mx-auto px-2 py-10 sm:px-6 lg:px-8">
            <p class="mb-6 text-sm text-gray-300">
                {{ __("Programs such as pydeepskylog read your observations, equipment, locations, sessions and observing lists with a token that has the permission read.") }}
                <a href="{{ route("api.documentation") }}" class="text-blue-400 hover:underline">{{ __("API documentation") }}</a>
            </p>
            {{-- The MCP server (routes/ai.php, App\Mcp\Servers\DeepskyLogServer) takes the same tokens. --}}
            <div class="mb-6 text-sm text-gray-300">
                <p>
                    {{ __("AI assistants can read your logbook and plan your nights with DeepskyLog's MCP server at :url. Connect an assistant that lets you add an MCP server with a header (such as Claude Code, Claude Desktop, Cursor or VS Code) with a token made here; a token that may also write lets the assistant create observing lists.", ["url" => url("/mcp")]) }}
                </p>
                <pre class="mt-2 overflow-x-auto rounded-sm bg-gray-800 px-3 py-2 font-mono text-xs text-gray-300">claude mcp add --transport http deepskylog {{ url("/mcp") }} --header "Authorization: Bearer &lt;token&gt;"</pre>
            </div>
            @livewire("api.api-token-manager")
        </div>
    </div>
</x-app-layout>
