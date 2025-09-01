<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Forms and observation sheets") }}
            </h2>
            <br />

            {!! __("Below you can download several sketch and observation forms that are useful for visual observing and recording sketches.") !!}
            <br /><br />

            <x-card title="{{ __('Compact sketch form') }}">
                <div class="flex items-start">
                    <div class="px-5">
                        <img class="w-64 h-auto border border-gray-700 rounded" src="/images/FormsExample.png" alt="Compact sketch form" />
                    </div>
                    <div>
                        <p>{!! __("A compact sketch form that folds in half so you can do two sketches on one piece of paper. This format allows you to sketch on top of a book like your PSA.") !!}</p>
                        <br />
                        <x-button href="/downloads/Sketch.pdf" class="inline-flex items-center">@lang('Download') ↧</x-button>
                    </div>
                </div>
            </x-card>

            <br />

            <x-card title="{{ __('Large sketch form') }}">
                <div class="flex items-start">
                    <div class="px-5">
                        <img class="w-64 h-auto border border-gray-700 rounded" src="/images/FormsExample1.png" alt="Large sketch form" />
                    </div>
                    <div>
                        <p>{{ __('Uses the maximum size for your sketch. Contains a 17cm sketch circle and a simplified details form.') }}</p>
                        <br />
                        <x-button href="/downloads/Sketch%20big.pdf" class="inline-flex items-center">@lang('Download') ↧</x-button>
                    </div>
                </div>
            </x-card>

            <br />

            <x-card title="{{ __('Observation log form') }}">
                <div class="flex items-start">
                    <div class="px-5">
                        <img class="w-64 h-auto border border-gray-700 rounded" src="/images/FormsExample2.png" alt="Observation log form" />
                    </div>
                    <div>
                        <p>{{ __('For when you want to quickly jot down notes about multiple objects on paper.') }}</p>
                        <br />
                        <x-button href="/downloads/Observation%20log.pdf" class="inline-flex items-center">@lang('Download') ↧</x-button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
