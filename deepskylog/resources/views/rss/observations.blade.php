<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ config('app.name', 'DeepskyLog') }} – Latest Observations</title>
        <link>{{ url('/observations') }}</link>
        <description>The latest observations added to DeepskyLog.</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ url('/feed/observations') }}" rel="self" type="application/rss+xml" />

        @foreach ($observations as $obs)
        @php
            // Observer: prefer full name from the related User, fall back to stored username
            $observerName = $obs->observerUser?->name ?? $obs->observer_username ?? '';
            $observerSlug = $obs->observerUser?->slug ?: ($obs->observer_username ?? '');

            $date   = $obs->observed_on ? $obs->observed_on->format('Y-m-d') : '';
            // The URL uses the UT date; the label the local date and time of the location.
            $local  = $obs->localObservedAt();
            $time   = $local?->format('H:i T');
            $object = \Illuminate\Support\Str::slug((string) $obs->target_name, '-');

            $link = ($observerSlug && $date && $object)
                ? route('observations.detail', [$observerSlug, $date, $object])
                : url('/observations');

            $title   = trim(($obs->target_name ?? 'Unknown object') . ' – ' . $observerName);
            $pubDate = $obs->created_at ? $obs->created_at->toRfc2822String() : now()->toRfc2822String();

            // Instrument label: "Make Name (Aperture mm)" when available
            $instrumentLabel = null;
            if ($obs->instrument) {
                $instrumentLabel = trim(ltrim(($obs->instrument->instrument_make->name ?? '') . ' ' . ($obs->instrument->name ?? '')));
                if ($obs->instrument->aperture_mm) {
                    $instrumentLabel .= ' (' . $obs->instrument->aperture_mm . ' mm)';
                }
            }

            // Eyepiece label
            $eyepieceLabel = null;
            if ($obs->eyepiece) {
                $eyepieceLabel = trim(($obs->eyepiece->name ?? ''));
                if ($obs->eyepiece->focal_length_mm) {
                    $eyepieceLabel .= ' (' . $obs->eyepiece->focal_length_mm . ' mm)';
                }
            }

            // Sketch URL
            $sketchUrl = null;
            if ($obs->has_drawing) {
                if (!empty($obs->drawing_filename)) {
                    try {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($obs->drawing_filename)) {
                            $sketchUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($obs->drawing_filename);
                        } else {
                            $sketchUrl = url('/images/' . $obs->drawing_filename);
                        }
                    } catch (\Throwable $_) {
                        $sketchUrl = url('/images/' . $obs->drawing_filename);
                    }
                } else {
                    $sketchUrl = url('/images/drawings/' . $obs->id . '.jpg');
                }
                // Ensure absolute URL
                if ($sketchUrl && !str_starts_with($sketchUrl, 'http')) {
                    $sketchUrl = url($sketchUrl);
                }
            }

            // Build HTML description
            $descParts = [];
            if ($date) {
                $dateLabel = $local ? $local->format('Y-m-d') . ' ' . $time : $date;
                $descParts[] = '<strong>Date:</strong> ' . e($dateLabel);
            }
            if ($observerName) {
                $descParts[] = '<strong>Observer:</strong> ' . e($observerName);
            }
            if ($instrumentLabel) {
                $descParts[] = '<strong>Telescope:</strong> ' . e($instrumentLabel);
            }
            if ($eyepieceLabel) {
                $descParts[] = '<strong>Eyepiece:</strong> ' . e($eyepieceLabel);
            }
            $plainDesc = strip_tags((string) ($obs->description ?? ''));
            if ($plainDesc) {
                $descParts[] = e($plainDesc);
            }
            if ($sketchUrl) {
                $descParts[] = '<img src="' . e($sketchUrl) . '" alt="' . e($obs->target_name ?? '') . '" style="max-width:400px" />';
            }
            $htmlDesc = implode('<br />', $descParts);
        @endphp
        <item>
            <title>{{ $title }}</title>
            <link>{{ $link }}</link>
            <guid isPermaLink="true">{{ $link }}</guid>
            <pubDate>{{ $pubDate }}</pubDate>
            @if ($htmlDesc)
            <description><![CDATA[{!! $htmlDesc !!}]]></description>
            @endif
            @if ($sketchUrl)
            <enclosure url="{{ $sketchUrl }}" length="0" type="image/jpeg" />
            <media:content url="{{ $sketchUrl }}" medium="image" />
            @endif
        </item>
        @endforeach
    </channel>
</rss>
