{{-- "Download all my data" (App\Services\AccountDataExport) --}}
<x-action-section>
    <x-slot name="title">
        {{
            __(
                "Download all my data",
            )
        }}
    </x-slot>

    <x-slot name="description">
        {{
            __(
                "One zip file with everything your account holds: your observations with their drawings, sessions, equipment, locations, observing lists, messages, goals and settings.",
            )
        }}
    </x-slot>

    <x-slot name="content">
        <div
            class="space-y-3 text-sm text-gray-300"
            @if ($pending) wire:poll.3s @endif
        >
            <p>
                {{
                    __(
                        "The observations are in the OAL format, which other observing programs can import, the other data in JSON. The zip file is made in the background and kept for :hours hours; you get a message when it is ready.",
                        ["hours" => \App\Jobs\GenerateAccountExport::KEEP_HOURS],
                    )
                }}
            </p>

            @error ("generate")
                <p class="text-red-400">{{ $message }}</p>
            @enderror

            @if ($job)
                <p>
                    @switch ($job["state"] ?? "")
                        @case ("complete")
                            <a
                                href="{{ route("account-export.download", $job["token"]) }}"
                                class="inline-flex items-center gap-1 rounded bg-green-700 px-2 py-0.5 text-white hover:bg-green-600"
                            >
                                {{
                                    __(
                                        "Download zip file",
                                    )
                                }}
                            </a>
                            @if ($expiresAt)
                                <span class="ml-2 text-xs text-gray-400">
                                    {{
                                        __("Can be downloaded until :date", [
                                            "date" => $expiresAt->locale(app()->getLocale())->isoFormat("LLL"),
                                        ])
                                    }}
                                </span>
                            @endif
                            @break
                        @case ("failed")
                            <span class="text-red-400">{{
                                __(
                                    "Generation failed.",
                                )
                            }}</span>
                            @break
                        @case ("processing")
                            @switch ($job["phase"] ?? "")
                                @case ("data")
                                    {{
                                        __(
                                            "Collecting your data...",
                                        )
                                    }}
                                    @break
                                @case ("drawings")
                                    {{
                                        __(
                                            "Looking up the drawings...",
                                        )
                                    }}
                                    @break
                                @case ("oal")
                                    {{
                                        __(
                                            "Writing the observations...",
                                        )
                                    }}
                                    @break
                                @case ("writing")
                                    {{
                                        __("Writing the zip file: :percent%", [
                                            "percent" => $job["percent"] ?? 0,
                                        ])
                                    }}
                                    @break
                                @default
                                    {{
                                        __(
                                            "Being generated...",
                                        )
                                    }}
                            @endswitch
                            @break
                        @default
                            {{
                                __(
                                    "Waiting for a worker...",
                                )
                            }}
                    @endswitch
                </p>
            @endif

            <div class="flex flex-wrap items-center gap-4">
                <button
                    type="button"
                    wire:click="generate"
                    wire:loading.attr="disabled"
                    @disabled ($pending)
                    class="rounded bg-indigo-600 px-4 py-1.5 font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{
                        __(
                            "Make the zip file",
                        )
                    }}
                </button>
                <a
                    href="{{ route("downloads.mine") }}"
                    class="text-blue-400 hover:underline"
                >
                    {{
                        __(
                            "All your PDFs and zip files",
                        )
                    }}
                </a>
            </div>
        </div>
    </x-slot>
</x-action-section>
