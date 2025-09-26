<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    @if ($user == "")
                        {{ __("Observations") }}
                    @else
                        {{ __("Observations of " . $user->name) }}
                    @endif
                </h2>
                @php
                    $mode = $mode ?? 'deepsky';
                    $deepskyUrl = $user == '' ? url('/observations') : url('/observations/'.$user->slug);
                    $cometUrl = $user == '' ? url('/cometobservations') : url('/cometobservations/'.$user->slug);
                @endphp

                <div class="flex space-x-2">
                    @if ($mode !== 'deepsky')
                        <x-button gray icon="eye" class="mb-2" href="{{ $deepskyUrl }}">
                            {{ __('Show deepsky observations') }}
                        </x-button>
                    @endif

                    @if ($mode !== 'comet')
                        <x-button gray icon="sparkles" class="mb-2" href="{{ $cometUrl }}">
                            {{ __('Show comet observations') }}
                        </x-button>
                    @endif
                </div>
            </div>
            <div class="mt-2">
                @if ($mode === 'comet')
                    <x-card>
                        @if ($comet->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No comet observations yet...') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All comet observations') }}</h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($comet as $observation)
                                    <x-observation-comet :observation="$observation" />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $comet->links() }}</div>
                        @endif
                    </x-card>
                @else
                    <x-card>
                        @if ($deepsky->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No deepsky observations yet...') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All deepsky observations') }}</h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($deepsky as $observation)
                                    <x-observation-deepsky :observation="$observation" />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $deepsky->links() }}</div>
                        @endif
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
