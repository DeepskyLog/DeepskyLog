@props (["objectName", "slug"])

{{-- The light curve of a variable star, nova or supernova from the estimates of DeepskyLog's observers (object page) --}}
@php
    $lightCurvePoints = [];
    try {
        $lightCurvePoints = \App\Services\Observations\VariableStarLightCurve::forObject($objectName);
    } catch (\Throwable $_) {
        $lightCurvePoints = [];
    }
    $lightCurve = count($lightCurvePoints) > 1
        ? (new \App\Charts\VariableStarLightCurveChart())->build(html_entity_decode($objectName, ENT_QUOTES | ENT_HTML5, "UTF-8"), $lightCurvePoints)
        : null;
@endphp

@if ($lightCurve)
    <div class="mt-6">
        <h2 class="text-xl font-semibold text-white">{{ __("Light curve") }}</h2>
        <p class="mt-1 text-sm text-gray-400">
            {{
                __("The magnitude estimates of DeepskyLog's observers (:count); a triangle is \"fainter than\". Click a point to open its observation.", [
                    "count" => count($lightCurvePoints),
                ])
            }}
            @auth
                <a href="{{ route("observation.create", ["slug" => $slug]) }}" class="text-indigo-400 hover:text-indigo-300">
                    {{ __("Log your magnitude estimate") }}
                </a>
            @endauth
        </p>
        <div class="mt-2 rounded bg-gray-800 p-2">
            {!! $lightCurve->container() !!}
        </div>
    </div>

    @push("scripts")
        <script src="{{ $lightCurve->cdn() }}"></script>
        <script>
            (function () {
                {{-- The points carry the observers' names: no tag may come out of the script --}}
                const options = {!! json_encode($lightCurve->getAdditionalOptions(), JSON_HEX_TAG | JSON_HEX_AMP) !!};
                const labels = @json(["fainter" => __("fainter than"), "timeUnknown" => __("time unknown")]);
                const pointOf = (seriesIndex, dataPointIndex, w) => w.config.series[seriesIndex].data[dataPointIndex];
                options.tooltip.x = {
                    formatter: (value, { seriesIndex, dataPointIndex, w }) => {
                        const point = pointOf(seriesIndex, dataPointIndex, w);
                        const date = new Date(value).toISOString();
                        return point && !point.timeKnown
                            ? date.slice(0, 10) + ' (' + labels.timeUnknown + ')'
                            : date.slice(0, 16).replace('T', ' ') + ' UT';
                    },
                };
                options.tooltip.y = {
                    formatter: (value, { seriesIndex, dataPointIndex, w }) => {
                        const point = pointOf(seriesIndex, dataPointIndex, w);
                        const magnitude = (point && point.fainter ? labels.fainter + ' ' : '') + Number(value).toFixed(1);
                        return point ? magnitude + ' (' + point.observer + ')' : magnitude;
                    },
                };
                options.chart.events = {
                    dataPointSelection: (event, context, { seriesIndex, dataPointIndex, w }) => {
                        const point = pointOf(seriesIndex, dataPointIndex, w);
                        if (point && point.url) {
                            window.location.href = point.url;
                        }
                    },
                };
                new ApexCharts(document.querySelector('#{{ $lightCurve->id }}'), options).render();
            })();
        </script>
    @endpush
@endif
