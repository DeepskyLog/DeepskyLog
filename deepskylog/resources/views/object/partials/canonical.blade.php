{{--
    The canonical URL of an object page. An object answers under every one of
    its names (/object/ugc-1831 and /object/pgc-9031 show NGC 891), so without
    it crawlers fetch each object once per alternative name.
--}}
@php
    $canonicalObjectSlug = $canonicalSlug ?? ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
@endphp
@if (! empty($canonicalObjectSlug))
    @push('head')
        <link rel="canonical" href="{{ route('object.show', ['slug' => $canonicalObjectSlug]) }}" />
    @endpush
@endif
