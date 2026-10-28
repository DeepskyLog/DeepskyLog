{{-- Link to the object report page for a list of objects; $params says which list. --}}
<a href="{{ route('object-report.index', $params) }}"
    class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-teal-700 text-white hover:bg-teal-600 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-teal-400 transition"
    title="{{ __('Make a PDF listing the objects with a layout of your own') }}">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M7 3h7l5 5v13H7V3z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        <path d="M14 3v5h5M10 12h6M10 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
    <span>{{ __('Report') }}</span>
</a>
