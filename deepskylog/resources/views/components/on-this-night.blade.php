@props (["years"])

{{-- What the observer observed in the night of this date in earlier years (OnThisNight) on the home page --}}
<x-home-section :title="__('On this night')" {{ $attributes }}>
    <ul class="divide-y divide-gray-700 px-2">
        @foreach ($years as $year)
            <li class="flex gap-3 py-3">
                @if ($year["drawing"])
                    <div class="w-20 flex-shrink-0 sm:w-24">
                        <a
                            href="{{ $year["drawing"]->detailUrl(auth()->user()->slug) }}"
                        >
                            <img
                                src="{{ $year["drawing"]->drawingUrl() }}"
                                alt="{{ __("Drawing") }}: {{ $year["drawing"]->target_name }}"
                                loading="lazy"
                                class="aspect-square w-full rounded bg-black object-cover"
                            />
                        </a>
                    </div>
                @endif
                <div class="min-w-0 flex-1 text-sm">
                    <div class="flex flex-wrap items-baseline gap-x-2">
                        <a
                            href="{{ $year["blog_url"] }}"
                            class="font-semibold text-white hover:underline"
                            >{{
                                \Carbon\Carbon::parse(
                                    $year["night"],
                                )->translatedFormat("j F Y")
                            }}</a
                        >
                        <span class="text-gray-400">{{
                            trans_choice(
                                ":count year ago|:count years ago",
                                $year["years_ago"],
                                ["count" => $year["years_ago"]],
                            )
                        }}</span>
                    </div>
                    <div class="text-gray-400">
                        {{
                            trans_choice(
                                ":count observation|:count observations",
                                $year["observations"],
                                ["count" => $year["observations"]],
                            )
                        }}
                    </div>
                    @if (!empty($year["objects"]))
                        <div class="mt-1 break-words text-gray-300">
                            @foreach ($year["objects"] as $object)
                                <span
                                    ><a
                                        href="{{ $object["url"] }}"
                                        class="text-blue-400 hover:underline"
                                        >{{
                                            $object[
                                                "name"
                                            ]
                                        }}</a
                                    >{{
                                        $loop->last
                                            ? ""
                                            : ","
                                    }}</span
                                >
                            @endforeach
                            @if ($year["more"] > 0)
                                {{
                                    __("and :count more", [
                                        "count" => $year["more"],
                                    ])
                                }}
                            @endif
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a
            href="{{ route("observer.blog", [auth()->user()->slug]) }}"
            class="text-blue-400 hover:underline"
            >{{ __("My blog") }} &rarr;</a
        >
    </x-slot>
</x-home-section>
