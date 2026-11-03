<x-app-layout>
    @php
        $options = [
            "quantity" => [
                "sky" => __("Sky brightness"),
                "sqm" => __("SQM"),
                "nelm" => __("NELM"),
            ],
            "period" => [
                "all" => __("All years"),
                "recent" => __("Last :count years", [
                    "count" => \App\Services\SkyBrightnessMap::RECENT_YEARS,
                ]),
            ],
        ];
        $texts = [
            "sky" => __("Sky brightness"),
            "sqm" => __("SQM"),
            "nelm" => __("NELM"),
            "fromNelm" => __(":count of :nights nights from the NELM"),
            // trans_choice() is PHP: one and several of each, which the script picks
            "observers" => [
                trans_choice(":count observer|:count observers", 1, ["count" => ":count"]),
                trans_choice(":count observer|:count observers", 2, ["count" => ":count"]),
            ],
            "nights" => [
                trans_choice(":count night|:count nights", 1, ["count" => ":count"]),
                trans_choice(":count night|:count nights", 2, ["count" => ":count"]),
            ],
            "year" => __("Year"),
            "darker" => __("Darker"),
            "brighter" => __("Brighter"),
        ];
        $shown = array_values(
            array_filter($cells, fn($cell) => $cell[$quantity] !== null),
        );
    @endphp
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h2 class="text-xl leading-tight font-semibold text-gray-200">
                {{
                    __(
                        "Sky brightness map",
                    )
                }}
            </h2>

            <div class="flex flex-wrap gap-2">
                @foreach ($options as $parameter => $choices)
                    <nav
                        class="inline-flex flex-wrap rounded-lg border border-gray-700 bg-gray-950 p-1"
                        aria-label="{{ $parameter === "quantity" ? __("Quantity") : __("Period") }}"
                    >
                        @foreach ($choices as $value => $label)
                            @php
                                $current = $parameter === "quantity" ? $quantity : $period;
                                $query = ["quantity" => $quantity, "period" => $period, $parameter => $value];
                            @endphp

                            <a
                                href="{{ route("sky-brightness-map", $query) }}"
                                class="{{ $value === $current ? "bg-sky-500 text-gray-950" : "text-gray-300 hover:bg-gray-800 hover:text-white" }} rounded-md px-3 py-2 text-sm font-medium transition"
                                @if ($value === $current) aria-current="page" @endif
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl bg-gray-900 px-4 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm text-gray-400">
            {{
                __(
                    "The sky brightness the observers of DeepskyLog recorded with their observations, as the median per cell of :size° by :size°. Click a cell to see how its sky changed over the years.",
                    ["size" => \App\Services\SkyBrightnessMap::CELL_DEG],
                )
            }}
        </p>
        <p class="mb-2 text-sm text-gray-400">
            {{
                __(
                    "The sky brightness combines both on the scale of the SQM: a night counts with its measured SQM, else with its NELM converted to the SQM of the same sky. A converted NELM reads about half a magnitude too dark under a dark sky, so a value mostly from NELMs is marked with ≈.",
                )
            }}
        </p>
        <p class="mb-4 text-sm text-gray-400">
            {{
                __(
                    "To protect the observers, no location is shown, only the cell it lies in, and hidden locations are left out. Every observer weighs the same, and a night counts once.",
                )
            }}
        </p>

        @if ($shown === [])
            <p class="text-gray-400">{{
                __(
                    "There are no sky brightness values for this choice yet.",
                )
            }}</p>
        @else
            <p class="mb-2 text-sm text-gray-300">
                {{
                    trans_choice(
                        ":count cell|:count cells",
                        count($shown),
                        ["count" => count($shown)],
                    )
                }}
            </p>
            <div
                id="sky-brightness-map"
                class="w-full rounded"
                style="height: 36rem"
            ></div>
            <div
                id="sky-brightness-legend"
                class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-300"
            ></div>
        @endif
    </div>

    @if ($shown !== [])
        @push ("scripts")
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    if (typeof L === "undefined") return;
                    const cells = @json ($shown);
                    const quantity = @json ($quantity);
                    const center = @json ($center);
                    const text = @json ($texts);

                    // The colours of the light pollution maps, from a dark sky to a city (SQM)
                    const stops = [
                        [21.75, "#111827"],
                        [21.5, "#1d4ed8"],
                        [21.0, "#16a34a"],
                        [20.5, "#facc15"],
                        [20.0, "#f97316"],
                        [19.0, "#dc2626"],
                        [0, "#f5f5f5"],
                    ];
                    // Magnitude::sqmToNelm() and its inverse (SkyQualityHistoryChart::sqmOfNelm()), without fstOffset
                    const nelmOfSqm = (sqm) =>
                        7.93 - 5 * Math.log10(1 + Math.pow(10, 4.316 - sqm / 5));
                    const sqmOfNelm = (nelm) => {
                        const x = Math.pow(10, 1.586 - nelm / 5) - 1;
                        return x <= 0 ? 22.5 : Math.min(22.5, 21.58 - 5 * Math.log10(x));
                    };
                    const colour = (value) => {
                        const sqm = quantity === "nelm" ? sqmOfNelm(value) : value;
                        return stops.find(([from]) => sqm >= from)[1];
                    };
                    const format = (value, q) =>
                        value === null ? "–" : value.toFixed(q === "nelm" ? 1 : 2);
                    const plural = (forms, count) =>
                        forms[count === 1 ? 0 : 1].replace(":count", count);
                    // The sky brightness with ≈ when it comes mostly from NELMs
                    const formatSky = (value, estimated) =>
                        (estimated ? "≈ " : "") + format(value, "sky");

                    const map = L.map("sky-brightness-map", {
                        fullscreenControl: true,
                    }).setView(center || [50, 5], center ? 7 : 4);
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        maxZoom: 12,
                        attribution: "© OpenStreetMap contributors",
                    }).addTo(map);

                    const element = (tag, content, style) => {
                        const node = document.createElement(tag);
                        if (content !== undefined) node.textContent = content;
                        if (style) node.style.cssText = style;
                        return node;
                    };

                    // A line through the yearly values of the quantity, darker skies up
                    const trend = (years) => {
                        const points = years.filter((y) => y[quantity] !== null);
                        if (points.length < 2) return null;
                        const width = 220,
                            height = 60,
                            pad = 6;
                        const first = points[0].year,
                            last = points[points.length - 1].year;
                        const values = points.map((p) => p[quantity]);
                        const low = Math.min(...values),
                            high = Math.max(...values);
                        const span = Math.max(high - low, quantity === "nelm" ? 0.3 : 0.5);
                        const x = (year) =>
                            pad +
                            ((year - first) / Math.max(last - first, 1)) * (width - 2 * pad);
                        const y = (value) =>
                            height -
                            pad -
                            ((value - (low + high - span) / 2) / span) * (height - 2 * pad);
                        const ns = "http://www.w3.org/2000/svg";
                        const svg = document.createElementNS(ns, "svg");
                        svg.setAttribute("width", width);
                        svg.setAttribute("height", height);
                        svg.setAttribute("role", "img");
                        const line = document.createElementNS(ns, "polyline");
                        line.setAttribute(
                            "points",
                            points.map((p) => x(p.year) + "," + y(p[quantity])).join(" "),
                        );
                        line.setAttribute("fill", "none");
                        line.setAttribute("stroke", "#0284c7");
                        line.setAttribute("stroke-width", "2");
                        svg.appendChild(line);
                        points.forEach((p) => {
                            const dot = document.createElementNS(ns, "circle");
                            dot.setAttribute("cx", x(p.year));
                            dot.setAttribute("cy", y(p[quantity]));
                            dot.setAttribute("r", "3");
                            // A year mostly from NELMs as an open dot
                            const estimated = quantity === "sky" && p.sky_estimated;
                            dot.setAttribute("fill", estimated ? "#ffffff" : "#0284c7");
                            dot.setAttribute("stroke", "#0284c7");
                            const title = document.createElementNS(ns, "title");
                            title.textContent =
                                p.year +
                                ": " +
                                (quantity === "sky"
                                    ? formatSky(p.sky, p.sky_estimated)
                                    : format(p[quantity], quantity));
                            dot.appendChild(title);
                            svg.appendChild(dot);
                        });
                        return svg;
                    };

                    const popup = (cell) => {
                        const box = element("div", undefined, "min-width: 14rem");
                        const ns = cell.south >= 0 ? "N" : "S";
                        const ew = cell.west >= 0 ? "E" : "W";
                        box.appendChild(
                            element(
                                "div",
                                Math.abs(cell.south).toFixed(2) +
                                    "°–" +
                                    Math.abs(cell.north).toFixed(2) +
                                    "° " +
                                    ns +
                                    ", " +
                                    Math.abs(cell.west).toFixed(2) +
                                    "°–" +
                                    Math.abs(cell.east).toFixed(2) +
                                    "° " +
                                    ew,
                                "font-weight: bold; margin-bottom: 4px",
                            ),
                        );
                        const sky = element(
                            "div",
                            text.sky + ": " + formatSky(cell.sky, cell.sky_estimated),
                            "font-weight: bold",
                        );
                        if (cell.sky_nelm_nights > 0) {
                            sky.appendChild(
                                element(
                                    "span",
                                    " (" +
                                        text.fromNelm
                                            .replace(":count", cell.sky_nelm_nights)
                                            .replace(":nights", cell.nights) +
                                        ")",
                                    "font-weight: normal; color: #555",
                                ),
                            );
                        }
                        box.appendChild(sky);
                        box.appendChild(
                            element("div", text.sqm + ": " + format(cell.sqm, "sqm")),
                        );
                        box.appendChild(
                            element("div", text.nelm + ": " + format(cell.nelm, "nelm")),
                        );
                        box.appendChild(
                            element(
                                "div",
                                plural(text.observers, cell.observers) +
                                    ", " +
                                    plural(text.nights, cell.nights),
                                "color: #555",
                            ),
                        );
                        const svg = trend(cell.years);
                        if (svg) {
                            const holder = element("div", undefined, "margin-top: 6px");
                            holder.appendChild(svg);
                            box.appendChild(holder);
                        }
                        const table = element(
                            "table",
                            undefined,
                            "margin-top: 6px; font-size: 12px; border-collapse: collapse",
                        );
                        const head = element("tr");
                        [text.year, text.sky, text.sqm, text.nelm].forEach((label) =>
                            head.appendChild(
                                element("th", label, "padding: 0 8px 0 0; text-align: left"),
                            ),
                        );
                        table.appendChild(head);
                        cell.years.forEach((year) => {
                            const row = element("tr");
                            row.appendChild(
                                element("td", String(year.year), "padding-right: 8px"),
                            );
                            row.appendChild(
                                element(
                                    "td",
                                    formatSky(year.sky, year.sky_estimated),
                                    "padding-right: 8px",
                                ),
                            );
                            row.appendChild(
                                element("td", format(year.sqm, "sqm"), "padding-right: 8px"),
                            );
                            row.appendChild(
                                element("td", format(year.nelm, "nelm"), "padding-right: 8px"),
                            );
                            table.appendChild(row);
                        });
                        box.appendChild(table);
                        return box;
                    };

                    // A cell is a few pixels wide on a map of a continent: until CELL_ZOOM a dot at its centre, then the cell itself
                    const CELL_ZOOM = 7;
                    const rectangles = L.layerGroup();
                    const dots = L.layerGroup();
                    const bounds = [];
                    cells.forEach((cell) => {
                        const corners = [
                            [cell.south, cell.west],
                            [cell.north, cell.east],
                        ];
                        const style = {
                            color: "#374151",
                            weight: 1,
                            fillColor: colour(cell[quantity]),
                            fillOpacity: 0.75,
                        };
                        L.rectangle(corners, style)
                            .bindPopup(() => popup(cell), { maxHeight: 360 })
                            .addTo(rectangles);
                        L.circleMarker(
                            [(cell.south + cell.north) / 2, (cell.west + cell.east) / 2],
                            { ...style, radius: 6 },
                        )
                            .bindPopup(() => popup(cell), { maxHeight: 360 })
                            .addTo(dots);
                        bounds.push(...corners);
                    });
                    const showLayer = () => {
                        const cellsVisible = map.getZoom() >= CELL_ZOOM;
                        map.removeLayer(cellsVisible ? dots : rectangles);
                        map.addLayer(cellsVisible ? rectangles : dots);
                    };
                    map.on("zoomend", showLayer);
                    if (!center) {
                        map.fitBounds(bounds, { padding: [20, 20], maxZoom: 8 });
                    }
                    showLayer();

                    // The legend: each colour with the SQM (or NELM) from which it starts; the sky brightness is an SQM
                    const legend = document.getElementById("sky-brightness-legend");
                    legend.appendChild(element("span", text.darker));
                    stops.forEach(([from, fill], index) => {
                        const item = element(
                            "span",
                            undefined,
                            "display: inline-flex; align-items: center; gap: 4px",
                        );
                        item.appendChild(
                            element(
                                "span",
                                "",
                                "display: inline-block; width: 14px; height: 14px; border: 1px solid #4b5563; background: " +
                                    fill,
                            ),
                        );
                        const label =
                            index === stops.length - 1
                                ? "< " +
                                  format(
                                      quantity === "nelm"
                                          ? nelmOfSqm(stops[index - 1][0])
                                          : stops[index - 1][0],
                                      quantity,
                                  )
                                : "≥ " +
                                  format(
                                      quantity === "nelm" ? nelmOfSqm(from) : from,
                                      quantity,
                                  );
                        item.appendChild(element("span", label));
                        legend.appendChild(item);
                    });
                    legend.appendChild(element("span", text.brighter));
                });
            </script>
        @endpush
    @endif
</x-app-layout>
