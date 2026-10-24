@props(["best"])

{{-- The objects of a TonightsBest result: on the home page and in the night list wizard --}}
<div class="mt-4 overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="text-xs uppercase text-gray-400">
            <tr>
                <th class="px-2 py-1" title="{{ __("The best moment: the object stands highest, or the sky is darkest") }}">{{ __("Time") }}</th>
                <th class="px-2 py-1">{{ __("Object") }}</th>
                <th class="px-2 py-1">{{ __("Type") }}</th>
                <th class="hidden px-2 py-1 md:table-cell">{{ __("Constellation") }}</th>
                <th class="hidden px-2 py-1 text-right sm:table-cell">{{ __("Mag") }}</th>
                <th class="px-2 py-1 text-right">{{ __("Altitude") }}</th>
                @if ($best["mode"] === "personal")
                    <th class="hidden px-2 py-1 md:table-cell">{{ __("Visibility") }}</th>
                @endif
                @auth
                    <th class="px-2 py-1"></th>
                @endauth
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach ($best["objects"] as $object)
                <tr>
                    <td class="whitespace-nowrap px-2 py-1">{{ $object["time"] }}</td>
                    <td class="px-2 py-1">
                        <a href="{{ route("object.show", $object["slug"]) }}" class="text-blue-400 hover:underline">{{ $object["name"] }}</a>
                        @if ($best["mode"] === "personal" && $object["kind"] === "deepsky")
                            <a href="{{ route("object.field-views", ["slug" => $object["slug"], "datetime" => $object["moment"]]) }}" class="ml-1 text-xs text-gray-400 hover:underline" title="{{ __("Field views") }}">&#128301;</a>
                        @endif
                    </td>
                    <td class="px-2 py-1">{{ __($object["type"]) }}</td>
                    <td class="hidden px-2 py-1 md:table-cell">{{ $object["constellation"] ? __($object["constellation"]) : "" }}</td>
                    <td class="hidden px-2 py-1 text-right sm:table-cell">{{ $object["magnitude"] !== null ? number_format($object["magnitude"], 1) : "" }}</td>
                    <td class="px-2 py-1 text-right">{{ $object["altitude"] }}°</td>
                    @if ($best["mode"] === "personal")
                        <td class="hidden px-2 py-1 md:table-cell">
                            @if ($object["category"])
                                <span
                                    @class([
                                        "text-green-400" => $object["category"] === "very_easy",
                                        "text-green-600" => $object["category"] === "easy",
                                        "text-orange-400" => $object["category"] === "quite_difficult",
                                        "text-orange-600" => $object["category"] === "difficult",
                                    ])
                                    @if ($object["sqm"] !== null) title="{{ __("Sky brightness: :sqm mag/arcsec²", ["sqm" => number_format($object["sqm"], 2)]) }}" @endif
                                >{{ __("contrast.reserve.category." . $object["category"]) }}</span>
                            @endif
                        </td>
                    @endif
                    @auth
                        <td class="whitespace-nowrap px-2 py-1 text-xs">
                            @if ($object["status"] === "new")
                                <span class="rounded bg-green-800 px-1.5 py-0.5 text-green-100">{{ __("New") }}</span>
                            @elseif ($object["status"] === "seen_other")
                                <span class="rounded bg-gray-700 px-1.5 py-0.5 text-gray-200" title="{{ __("Seen with another instrument") }}">{{ __("Seen elsewhere") }}</span>
                            @elseif ($object["status"] === "seen")
                                <span class="rounded bg-gray-700 px-1.5 py-0.5 text-gray-400">{{ __("Seen") }}</span>
                            @endif
                        </td>
                    @endauth
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
