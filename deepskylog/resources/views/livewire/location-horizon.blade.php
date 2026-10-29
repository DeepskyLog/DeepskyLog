<div>
    @if ($hasHorizon || $canEdit)
        <div class="mt-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="text-xl font-bold">
                    {{
                        __(
                            "Local horizon",
                        )
                    }}
                </h4>
                @if ($canEdit && !$editing)
                    <x-button
                        xs
                        secondary
                        icon="pencil"
                        wire:click="edit"
                        label="{{ $hasHorizon ? __('Edit the horizon') : __('Draw the horizon') }}"
                    />
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-400">
                {{
                    __(
                        "The trees, houses and hills around the location. Tonight's best, the lists for a night, the times of observing lists, the Messier marathon, the months of a list and the field views leave out what stands behind them.",
                    )
                }}
            </p>
            @if (session("horizon"))
                <p class="mt-1 text-sm text-green-400">{{
                    session(
                        "horizon",
                    )
                }}</p>
            @endif

            {{-- The panorama: azimuth from the north through the east, 2 units per degree; altitude 0 to MAX_ALTITUDE --}}
            <div
                class="mt-2 rounded bg-gray-800 p-2"
                x-data="{
                    values: $wire.entangle('altitudes'),
                    drawing: false,
                    step: {{ $step }},
                    max: {{ $maxAlt }},
                    y(alt) {
                        return 170 - 2 * alt
                    },
                    outline() {
                        const points = []
                        for (let i = 0; i <= this.values.length; i++) {
                            points.push(i * this.step * 2 + ',' + this.y(Number(this.values[i % this.values.length]) || 0))
                        }
                        return '0,170 ' + points.join(' ') + ' 720,170'
                    },
                    set(event) {
                        const svg = this.$refs.panorama
                        const point = svg.createSVGPoint()
                        point.x = event.clientX
                        point.y = event.clientY
                        const p = point.matrixTransform(svg.getScreenCTM().inverse())
                        const index = Math.round(Math.max(0, Math.min(360, p.x / 2)) / this.step) % this.values.length
                        const alt = Math.round(Math.max(0, Math.min(this.max, (170 - p.y) / 2)))
                        this.values = this.values.map((v, i) => (i === index ? alt : v))
                    },
                }"
            >
                <svg
                    x-ref="panorama"
                    viewBox="0 0 720 196"
                    class="w-full select-none"
                    style="touch-action: {{ $editing ? "none" : "auto" }}; {{ $editing ? "cursor: crosshair;" : "" }}"
                    role="img"
                    aria-label="{{ __("Local horizon") }}"
                    @if ($editing)
                        x-on:pointerdown="
                            drawing = true;
                            $refs.panorama.setPointerCapture?.(
                                $event.pointerId,
                            );
                            set($event);
                        "
                        x-on:pointermove="if (drawing) set($event);"
                        x-on:pointerup="drawing = false"
                        x-on:pointercancel="drawing = false"
                    @endif
                >
                    <rect x="0" y="10" width="720" height="160" fill="#0b1020" />
                    @foreach ([10, 20, 30, 40, 50, 60, 70] as $alt)
                        <line
                            x1="0"
                            x2="720"
                            y1="{{ 170 - 2 * $alt }}"
                            y2="{{ 170 - 2 * $alt }}"
                            stroke="{{ $alt === 30 ? "#6b7280" : "#374151" }}"
                            stroke-width="{{ $alt === 30 ? 1 : 0.5 }}"
                            stroke-dasharray="{{ $alt === 30 ? "6 4" : "none" }}"
                        />
                        <text
                            x="4"
                            y="{{ 168 - 2 * $alt }}"
                            font-size="9"
                            fill="#9ca3af"
                            >{{ $alt }}°</text
                        >
                    @endforeach
                    @foreach (range(0, 360, 45) as $az)
                        <line
                            x1="{{ 2 * $az }}"
                            x2="{{ 2 * $az }}"
                            y1="10"
                            y2="170"
                            stroke="#374151"
                            stroke-width="0.5"
                        />
                    @endforeach
                    <polygon x-bind:points="
                            outline()
                        " fill="#1c2414" stroke="#7c9a3d" stroke-width="1.5" />
                    @if ($editing)
                        <template x-for="(value, index) in values" :key="index">
                            <circle
                                x-bind:cx="index * step * 2"
                                x-bind:cy="y(Number(value) || 0)"
                                r="3"
                                fill="#7c9a3d"
                            />
                        </template>
                    @endif
                    @foreach ([
                            0 => __("N"),
                            45 => __("NE"),
                            90 => __("E"),
                            135 => __("SE"),
                            180 => __("S"),
                            225 => __("SW"),
                            270 => __("W"),
                            315 => __("NW"),
                            360 => __("N")
                        ]
                        as $az => $label)
                        <text
                            x="{{ min(712, max(8, 2 * $az)) }}"
                            y="186"
                            font-size="11"
                            fill="#d1d5db"
                            text-anchor="middle"
                            >{{ $label }}</text
                        >
                    @endforeach
                </svg>

                @if ($editing)
                    <p class="mt-2 text-xs text-gray-400">
                        {{
                            __(
                                "Drag over the panorama to draw the horizon, or type the altitude in degrees above the horizon for each direction (azimuth from the north through the east). The dashed line is the 30° that Tonight's best asks.",
                            )
                        }}
                    </p>
                    <div
                        class="mt-2 grid grid-cols-3 gap-2 text-xs sm:grid-cols-6"
                    >
                        <template x-for="(value, index) in values" :key="index">
                            <label class="flex items-center gap-1">
                                <span
                                    class="w-9 text-right text-gray-400"
                                    x-text="index * step + '°'"
                                ></span>
                                <input
                                    type="number"
                                    min="0"
                                    max="{{ $maxAlt }}"
                                    step="1"
                                    x-model.number="values[index]"
                                    class="w-16 rounded border-gray-600 bg-gray-900 px-1 py-0.5 text-xs text-gray-100"
                                />
                            </label>
                        </template>
                    </div>
                    @error ("altitudes.*")
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror

                    <details class="mt-3 text-sm">
                        <summary class="cursor-pointer text-gray-300">
                            {{
                                __(
                                    "Paste a horizon",
                                )
                            }}
                        </summary>
                        <p class="mt-1 text-xs text-gray-400">
                            {{
                                __(
                                    "Lines of an azimuth and an altitude in degrees, such as the horizon.txt of a Stellarium landscape or the export of a horizon app.",
                                )
                            }}
                        </p>
                        <textarea
                            wire:model="import"
                            rows="5"
                            class="mt-1 w-full rounded border-gray-600 bg-gray-900 text-xs text-gray-100"
                            placeholder="0 12&#10;45 20&#10;90 8"
                        ></textarea>
                        @error ("import")
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <x-button
                            xs
                            secondary
                            class="mt-1"
                            wire:click="importText"
                            label="{{ __('Use these points') }}"
                        />
                    </details>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-button
                            sm
                            primary
                            wire:click="save"
                            label="{{ __('Save') }}"
                        />
                        <x-button
                            sm
                            secondary
                            wire:click="clear"
                            label="{{ __('Open horizon') }}"
                        />
                        <x-button
                            sm
                            flat
                            wire:click="cancel"
                            label="{{ __('Cancel') }}"
                        />
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
