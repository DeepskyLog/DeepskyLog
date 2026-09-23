{{-- Link to the object sets page for a list of objects; $params says which list. --}}
<a href="{{ route('object-sets.index', $params) }}"
    class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-sky-700 text-white hover:bg-sky-600 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
    title="{{ __('Make a PDF with atlas charts, DSS photos and a data page for every object') }}">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        <path d="M9 4v14M15 6v14" stroke="currentColor" stroke-width="2" />
    </svg>
    <span>{{ __('Charts per object') }}</span>
</a>
