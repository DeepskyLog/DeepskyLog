@php
    use App\Models\DeepskyObject;

    $status = request()->query('status', DeepskyObject::REVIEW_NEW);
    if (! in_array($status, [...DeepskyObject::REVIEW_STATUSES, 'none', 'all'], true)) {
        $status = DeepskyObject::REVIEW_NEW;
    }
    $counts = DeepskyObject::reviewCounts();
    $tabs = [
        DeepskyObject::REVIEW_NEW => $counts[DeepskyObject::REVIEW_NEW],
        DeepskyObject::REVIEW_ATTENTION => $counts[DeepskyObject::REVIEW_ATTENTION],
        DeepskyObject::REVIEW_CHECKED => $counts[DeepskyObject::REVIEW_CHECKED],
        'none' => $counts['none'],
        'all' => array_sum($counts),
    ];
@endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <h1 class="mb-2 text-xl font-semibold text-white">{{ __('Object reviews') }}</h1>
            <p class="mb-4 text-sm text-gray-300">
                {{ __('Objects added to DeepskyLog start as new. Review an object on its edit page, or mark it as checked here.') }}
            </p>

            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ($tabs as $key => $count)
                    <a href="{{ route('admin.object-reviews', ['status' => $key]) }}"
                       @class([
                           'rounded px-3 py-1 text-sm',
                           'bg-blue-600 text-white' => $status === $key,
                           'bg-gray-700 text-gray-200 hover:bg-gray-600' => $status !== $key,
                       ])>
                        {{ $key === 'all' ? __('All') : DeepskyObject::reviewStatusLabel($key === 'none' ? null : $key) }}
                        ({{ number_format($count) }})
                    </a>
                @endforeach
            </div>

            @livewire('object-review-table', ['status' => $status])
        </div>
    </div>
</x-app-layout>
